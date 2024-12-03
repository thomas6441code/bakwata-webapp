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
    // Last request was more than 1 hour ago
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

    // Upload images
    $mainImage = $_FILES['image'];
    $image1 = $_FILES['image1'];
    $image2 = $_FILES['image2'];

    $uploadDir = '../public/images/';
    $allowedFileTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];

    // Function to handle file upload
    function handleFileUpload($file, $uploadDir)
    {
        // If no file was uploaded, return null
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        // Check for other upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validate file type
        if (!in_array($file['type'], $GLOBALS['allowedFileTypes'])) {
            return null;
        }

        $uniqueFileName = uniqid() . '-' . basename($file['name']);
        $tempFilePath = UPLOAD_TMP_DIR . $uniqueFileName;
        $finalFilePath = $uploadDir . $uniqueFileName;

        // Move the uploaded file to the temporary directory
        if (move_uploaded_file($file['tmp_name'], $tempFilePath)) {
            // Move the file from temp directory to the final upload directory
            if (rename($tempFilePath, $finalFilePath)) {
                return $uniqueFileName;
            }
        }
        return null;
    }

    // Handle file uploads
    $uploadedMainImage = handleFileUpload($mainImage, $uploadDir);
    $uploadedImage1 = handleFileUpload($image1, $uploadDir);
    $uploadedImage2 = handleFileUpload($image2, $uploadDir);

    // Example SQL query
    $query = "INSERT INTO project (title, description, moreDescription, date, location, image, image1, image2, createdAt, updatedAt)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    // Prepare the SQL statement
    $stmt = $mysqli->prepare($query);

    // Check if preparation is successful
    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }

    // Bind the parameters
    $stmt->bind_param("ssssssss", $title, $description, $moreDescription, $date, $location, $uploadedMainImage, $uploadedImage1, $uploadedImage2 );

    // Execute the query once
    if ($stmt->execute()) {
        $message = "New project created successfully";
        echo "<script>window.location.href = 'project.php';</script>";
        exit;
    } else {
        $error = "Failed to insert project information into the database: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}
?>

<title>ADMIN | CREATE PROJECT</title>

<!-- Main Content Area -->
<div class="mt-20 mx-auto max-w-3xl py-4 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <h1 class="text-3xl font-bold mb-6">Create New Project</h1>
        <?php if (!empty($error)): ?>
            <div class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div class="text-green-500 mb-4"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form id="projectForm" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <input type="text" name="title" id="title" placeholder="Project Title" required
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