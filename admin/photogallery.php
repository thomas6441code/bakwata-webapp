<?php
        session_save_path($_SERVER['DOCUMENT_ROOT'] . '/sessions');
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $timeout_duration = 3600;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
            session_unset();
            session_destroy();
            header('Location: login.php');
            exit;
        }

        $_SESSION['last_activity'] = time();

        include '../assets/db.php';
        include './includes/header.php';

        $mysqli = $conn;

        $limit = 6;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        function fetchData($table, $limit, $offset) {
            global $mysqli; 
            $stmt = $mysqli->prepare("SELECT * FROM $table LIMIT ? OFFSET ?");
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        function countPhotos($table) {
            global $mysqli;
            $result = $mysqli->query("SELECT COUNT(*) as count FROM $table");
            return $result->fetch_assoc()['count'];
        }

        $photos = fetchData('photogallery', $limit, $offset);
        $totalPhotos = countPhotos('photogallery');
        $totalPages = ceil($totalPhotos / $limit);

        define('UPLOAD_TMP_DIR', $_SERVER['DOCUMENT_ROOT'] . '/tmp/uploads/');

        if (!is_dir(UPLOAD_TMP_DIR)) {
            if (!mkdir(UPLOAD_TMP_DIR, 0777, true)) {
                error_log("Failed to create temporary upload directory: " . UPLOAD_TMP_DIR);
                die('Temporary upload directory is not writable. Please check permissions.');
            }
        }

        $errorMessage = "";
        $successMessage = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = htmlspecialchars($_POST['title']);
            $description = htmlspecialchars($_POST['description']);
            $image = $_FILES['image'];

            if ($image['error'] === UPLOAD_ERR_OK) {
                $uniqueFileName = uniqid() . '-' . basename($image['name']);
                $tempFilePath = UPLOAD_TMP_DIR . $uniqueFileName;
                $uploadDir = '../public/images/';
                $finalFilePath = $uploadDir . $uniqueFileName;

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($image['tmp_name'], $finalFilePath)) {
                    $stmt = $mysqli->prepare("INSERT INTO photogallery (title, description, imageUrl, createdAt) VALUES (?, ?, ?,  NOW())");
                    $stmt->bind_param("sss", $title, $description, $uniqueFileName);

                    if ($stmt->execute()) {
                        $successMessage = "Slide created successfully!";
                        echo "<script type='text/javascript'>
                                window.location.href = 'photogallery.php';
                              </script>";
                        exit();
                    } else {
                        $errorMessage = "Error saving to database: " . $mysqli->error;
                    }

                    $stmt->close();
                } else {
                    $errorMessage = "Failed to upload the image.";
                }
            } else {
                switch ($image['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMessage = "The uploaded file exceeds the allowed size.";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errorMessage = "The uploaded file was only partially uploaded.";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errorMessage = "No file was uploaded.";
                        break;
                    default:
                        $errorMessage = "An unknown error occurred.";
                        break;
                }
            }
        }

        $mysqli->close();
    ?>

    <div class="container mt-20 mx-auto p-4">
        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
            <h1 class="text-2xl font-bold mb-4">PHOTO GALLERY</h1>
            <?php if (!empty($successMessage)): ?>
                <p class="text-green-500 mb-4"><?php echo $successMessage; ?></p>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <p class="text-red-500 mb-4"><?php echo $errorMessage; ?></p>
            <?php endif; ?>

            <form id="photoForm" class="mb-4" method="POST" enctype="multipart/form-data">
                <div class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-2">
                    <input type="text" id="title" name="title" placeholder="Photo title" class="border px-2 py-2" required />
                    <input type="text" id="description" name="description" placeholder="Photo description" class="border px-2 py-2" required />
                    <input type="file" id="image" name="image" accept="image/*" class="border py-2 px-2" />
                    <button id="add" type="submit" name="action" class="bg-blue-500 hover:bg-blue-700 text-white rounded-md px-4 py-2">Add Photo</button>
                </div>
            </form>

            <div id="photoGallery" class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($photos as $photo): ?>
                    <div class="bg-gray-100 p-4 rounded-lg shadow-md">
                        <img src="../public/images/<?= htmlspecialchars($photo['imageUrl']) ?>" alt="<?= htmlspecialchars($photo['title']) ?>" class="w-full h-48 object-cover rounded-lg mb-4" />
                        <p class="text-lg font-bold"><?= htmlspecialchars($photo['title']) ?></p>
                        <p class="text-gray-700 mb-4"><?= htmlspecialchars($photo['description']) ?></p>
                        <button onclick="deletePhoto(<?= $photo['id'] ?>)" class="bg-red-500 hover:bg-red-700 text-white rounded-md px-4 py-2">Delete</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pagination my-10 flex justify-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="mx-1 px-4 py-2 rounded-md <?= ($i === $page) ? 'bg-blue-600 text-white' : 'bg-blue-200 text-gray-700' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <script>
        const deletePhoto = async (id) => {
            try {
                const model = 'photogallery';
                const response = await fetch(`delete.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, model }),
                });

                if (!response.ok) {
                    throw new Error(`Error: ${response.statusText}`);
                }

                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(`Error: ${result.error}`);
                }
            } catch (error) {
                console.error('Failed to delete record:', error);
            }
        };
    </script>
</body>
</html>
