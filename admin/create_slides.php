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

include '../assets/db.php';
include './includes/header.php';

$mysqli = $conn;

define('UPLOAD_TMP_DIR', $_SERVER['DOCUMENT_ROOT'] . '/tmp/uploads/');

if (!is_dir(UPLOAD_TMP_DIR)) {
    if (!mkdir(UPLOAD_TMP_DIR, 0777, true)) {
        error_log("Failed to create temporary upload directory: " . UPLOAD_TMP_DIR);
        die('Temporary upload directory is not writable. Please check permissions.');
    }
}

// Initialize variables for error/success messages
$errorMessage = "";
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $image = $_FILES['image'];

    // Check if the image was uploaded
    if ($image['error'] === UPLOAD_ERR_OK) {
        $uniqueFileName = uniqid() . '-' . basename($image['name']);
        $tempFilePath = UPLOAD_TMP_DIR . $uniqueFileName;
        $uploadDir = '../public/images/';
        $finalFilePath = $uploadDir . $uniqueFileName;

        // Create the uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($image['tmp_name'], $finalFilePath)) {
            // Insert data into the database
            $stmt = $mysqli->prepare("INSERT INTO slide (title, description, image, updatedAt) VALUES (?, ?, ?,  NOW())");
            $stmt->bind_param("sss", $title, $description, $uniqueFileName);

            if ($stmt->execute()) {
                $successMessage = "Slide created successfully!";
                echo "<script type='text/javascript'>
                            window.location.href = 'slides.php';
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

<!-- Main Content Area -->
<div class="mt-20 md:w-1/2 w-full mx-auto py-4 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <h1 class="text-3xl font-bold mb-6">Create New Slide</h1>
            <?php if (!empty($successMessage)): ?>
                <p style="color: green;"><?php echo $successMessage; ?></p>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <p style="color: red;"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <input type="text" name="title" id="title" placeholder="Slide Title" required class="block w-full border p-3 rounded mb-2" />
                </div>

                <div class="md:col-span-2">
                    <textarea name="description" id="description" placeholder="Description" required class="block w-full border p-4 rounded mb- 2 h-28"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Image</label>
                    <input type="file" name="image" id="image" accept="image/*" required class="block w-full border p-3 rounded mb-4" />
                </div>

                <div class="md:col-span-2">
                    <button type="submit" class="w-fit bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>

</html>