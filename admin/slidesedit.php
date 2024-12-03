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

// Fetch the slide data if an ID is provided in the query string
$slideId = $_GET['id'] ?? null;
$slide = null;
if ($slideId) {
    $stmt = $mysqli->prepare("SELECT * FROM slide WHERE id = ?");
    $stmt->bind_param("i", $slideId);
    $stmt->execute();
    $result = $stmt->get_result();
    $slide = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Initialize imagePath to the existing image
    $imagePath = $slide['image'] ?? '';

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../public/images/";
        $originalFileName = basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        // Check if file is a valid image
        if (getimagesize($_FILES["image"]["tmp_name"]) !== false) {
            if ($_FILES["image"]["size"] <= 5000000 && in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                // Create a unique name for the image
                $uniqueFileName = uniqid('img_', true) . '.' . $imageFileType;

                // Move the uploaded file to the target directory with the unique name
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir . $uniqueFileName)) {
                    // Set imagePath to the unique file name
                    $imagePath = $uniqueFileName;
                } else {
                    echo "Error uploading the file.";
                }
            } else {
                echo "Invalid image size or type.";
            }
        } else {
            echo "File is not an image.";
        }
    }

    // Update slide in the database
    $stmt = $mysqli->prepare("UPDATE slide SET title = ?, description = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $description, $imagePath, $slideId);

    if ($stmt->execute()) {
        echo "<script>window.location.href = 'slides.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$mysqli->close();
?>

<!-- Main Content Area -->
<div class="mt-20 w-full max-w-3xl mx-auto py-4 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <h1 class="text-4xl font-bold mb-6">Edit Slide</h1>
            <form id="projectForm" class="space-y-6" method="POST" enctype="multipart/form-data">
                <!-- Title Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Title</label>
                    <input type="text" name="title" id="title" placeholder="Slide Title" value="<?php echo htmlspecialchars($slide['title'] ?? ''); ?>" required
                        class="w-full p-3 border rounded-md" />
                </div>

                <!-- Description Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Description</label>
                    <textarea name="description" id="description" placeholder=" Brief project description" required
                        rows="5" class="w-full p-3 border rounded-md resize-none"><?php echo htmlspecialchars($slide['description'] ?? ''); ?></textarea>
                </div>

                <!-- Image Upload Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2">Image</label>
                        <input type="file" placeholder="Image" name="image" id="Image"
                            class="block w-full p-3 border rounded-md" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700">
                        Update Slide
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>

</html>