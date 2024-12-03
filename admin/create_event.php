<?php
session_save_path($_SERVER['DOCUMENT_ROOT'] . '/sessions');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check for session timeout (1 hour)
$timeout_duration = 3600; // 1 hour in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

include './includes/header.php';
include '../assets/db.php';

$mysqli = $conn;

define('UPLOAD_TMP_DIR', $_SERVER['DOCUMENT_ROOT'] . '/tmp/uploads/');

if (!is_dir(UPLOAD_TMP_DIR)) {
    if (!mkdir(UPLOAD_TMP_DIR, 0777, true)) {
        error_log("Failed to create temporary upload directory: " . UPLOAD_TMP_DIR);
        die('Temporary upload directory is not writable. Please check permissions.');
    }
}

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $moreDescription = htmlspecialchars(trim($_POST['moreDescription']), ENT_QUOTES, 'UTF-8');
    $date = htmlspecialchars(trim($_POST['date']), ENT_QUOTES, 'UTF-8');
    $location = htmlspecialchars(trim($_POST['location']), ENT_QUOTES, 'UTF-8');
    $eventType = htmlspecialchars(trim($_POST['eventType']), ENT_QUOTES, 'UTF-8');

    // Upload images
    $mainImage = $_FILES['image'];
    $image1 = $_FILES['image1'];
    $image2 = $_FILES['image2'];

    $uploadDir = '../public/images/';
    $allowedFileTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];

    // Function to handle file upload
    function handleFileUpload($file, $uploadDir)
    {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if (!in_array($file['type'], $GLOBALS['allowedFileTypes'])) {
            return null;
        }

        $uniqueFileName = uniqid() . '-' . basename($file['name']);
        $tempFilePath = UPLOAD_TMP_DIR . $uniqueFileName;
        $finalFilePath = $uploadDir . $uniqueFileName;

        if (move_uploaded_file($file['tmp_name'], $tempFilePath)) {
            if (rename($tempFilePath, $finalFilePath)) {
                return $uniqueFileName;
            }
        }
        return null;
    }

    $uploadedMainImage = handleFileUpload($mainImage, $uploadDir);
    $uploadedImage1 = handleFileUpload($image1, $uploadDir);
    $uploadedImage2 = handleFileUpload($image2, $uploadDir);

    $query = "INSERT INTO event (title, description, moreDescription, date, location, eventType, image, image1, image2, createdAt, updatedAt)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    $stmt = $mysqli->prepare($query);

    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("sssssssss", $title, $description, $moreDescription, $date, $location, $eventType, $uploadedMainImage, $uploadedImage1, $uploadedImage2);

    if ($stmt->execute()) {
        $message = "New event created successfully";
        echo "<script>window.location.href = 'event.php';</script>";
        exit;
    } else {
        $error = "Failed to insert event information into the database: " . $stmt->error;
    }

    $stmt->close();
}
?>

<title>ADMIN | CREATE EVENT</title>

<div class="mt-20 mx-auto max-w-3xl py-4 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <h1 class="text-3xl font-bold mb-6">Create New Event</h1>
        <?php if (!empty($error)): ?>
            <div class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div class="text-green-500 mb-4"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form id="eventForm" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <input type="text" name="title" id="title" placeholder="Event Title" required
                    class="block w-full border p-3 rounded mb-2" />
            </div>

            <div class="md:col-span-2">
                <textarea name="description" id="description" placeholder="Description" required
                    class="block w-full border p-4 rounded mb-2 h-28"></textarea>
            </div>

            <div class="md:col-span-2">
                <textarea name="moreDescription" id="moreDescription" placeholder="More Description" required
                    class="block w-full border p-4 rounded mb-2 h-28"></textarea>
            </div>

            <div class="md:col-span-2">
                <input type="datetime-local" name="date" id="date" required
                    class="block w-full border p-3 rounded mb-2" />
            </div>

            <div class="md:col-span-2">
                <input type="text" name="location" id="location" placeholder="Location" required
                    class="block w-full border p-3 rounded mb-2" />
            </div>

            <div class="md:col-span-2">
                <input type="text" name="eventType" id="eventType" placeholder="Event Type" required
                    class="block w-full border p-3 rounded mb-2" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Main Image</label>
                <input type="file" name="image" id="image" required
                    class="block w-full border p-3 rounded mb-4" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Image 1</label>
                <input type="file" name="image1" id="image1" class="block w-full border p-3 rounded mb-4" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Image 2</label>
                <input type="file" name="image2" id="image2" class="block w-full border p-3 rounded mb-4" />
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="w-fit bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700">
                    Create
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
