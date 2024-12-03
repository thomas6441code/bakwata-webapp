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

include '../assets/db.php';
include './includes/header.php';

$mysqli = $conn;

// Fetch the project data if an ID is provided in the query string
$slideId = $_GET['id'] ?? null;
$slide = null;
if ($slideId) {
    $stmt = $mysqli->prepare("SELECT * FROM project WHERE id = ?");
    $stmt->bind_param("i", $slideId);
    $stmt->execute();
    $result = $stmt->get_result();
    $slide = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $moreDescription = htmlspecialchars(trim($_POST['moreDescription']), ENT_QUOTES, 'UTF-8');
    $date = htmlspecialchars(trim($_POST['date']), ENT_QUOTES, 'UTF-8');
    $location = htmlspecialchars(trim($_POST['location']), ENT_QUOTES, 'UTF-8');

    // Initialize image paths to existing values
    $mainImagePath = $slide['image'] ?? '';
    $image1Path = $slide['image1'] ?? '';
    $image2Path = $slide['image2'] ?? '';

    // Handle file uploads
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $mainImagePath = handleFileUpload($_FILES['image']);
    }
    if (isset($_FILES['image1']) && $_FILES['image1']['error'] === UPLOAD_ERR_OK) {
        $image1Path = handleFileUpload($_FILES['image1']);
    }
    if (isset($_FILES['image2']) && $_FILES['image2']['error'] === UPLOAD_ERR_OK) {
        $image2Path = handleFileUpload($_FILES['image2']);
    }

    // Update project in the database
    $stmt = $mysqli->prepare("UPDATE project SET title = ?, description = ?, moreDescription = ?, date = ?, location = ?, image = ?, image1 = ?, image2 = ?, updatedAt = NOW() WHERE id = ?");
    $stmt->bind_param("ssssssssi", $title, $description, $moreDescription, $date, $location, $mainImagePath, $image1Path, $image2Path, $slideId);

    if ($stmt->execute()) {
        echo "<script>window.location.href = 'project.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$mysqli->close();

// Function to handle file upload
function handleFileUpload($file)
{
    $targetDir = "../public/images/";
    $originalFileName = basename($file["name"]);
    $imageFileType = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

    // Check if file is a valid image
    if (getimagesize($file["tmp_name"]) !== false) {
        if ($file["size"] <= 5000000 && in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            // Create a unique name for the image
            $uniqueFileName = uniqid('img_', true) . '.' . $imageFileType;

            // Move the uploaded file to the target directory with the unique name
            if (move_uploaded_file($file["tmp_name"], $targetDir . $uniqueFileName)) {
                return $uniqueFileName;
            } else {
                echo "Error uploading the file.";
            }
        } else {
            echo "Invalid image size or type.";
        }
    } else {
        echo "File is not an image.";
    }
    return null;
}
?>

<!-- Main Content Area -->
<div class="mt-20 w-full max-w-3xl mx auto py-4 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <h1 class="text-4xl font-bold mb-6">Edit Project</h1>
            <form id="projectForm" class="space-y-6" method="POST" enctype="multipart/form-data">
                <!-- Title Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Title</label>
                    <input type="text" name="title" id="title" placeholder="Slide Title" value="<?php echo htmlspecialchars($slide['title'] ?? ''); ?>" required
                        class="w-full p-3 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Description Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Description</label>
                    <textarea name="description" id="description" placeholder="Brief project description" required
                        rows="5" class="w-full p-3 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"><?php echo htmlspecialchars($slide['description'] ?? ''); ?></textarea>
                </div>

                <!-- More Description Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">More Description</label>
                    <textarea name="moreDescription" id="moreDescription" placeholder="Additional details" required
                        rows="5" class="w-full p-3 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"><?php echo htmlspecialchars($slide['moreDescription'] ?? ''); ?></textarea>
                </div>

                <!-- Date Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Date</label>
                    <input type="datetime-local" name="date" id="date" value="<?php echo htmlspecialchars($slide['date'] ?? ''); ?>" required
                        class="w-full p-3 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Location Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Location</label>
                    <input type="text" name="location" id="location" placeholder="Location" value="<?php echo htmlspecialchars($slide['location'] ?? ''); ?>" required
                        class="w-full p-3 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Image Upload Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2">Image</label>
                        <input type="file" name="image" id="image" class="block w-full p-3 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2">Image 1</label>
                        <input type="file" name="image1" id="image1" class="block w-full p-3 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2">Image 2</label>
                        <input type="file" name="image2" id="image2" class="block w-full p-3 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-200">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>