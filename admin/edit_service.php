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

// Fetch the service data if an ID is provided in the query string
$serviceId = $_GET['id'] ?? null;
$service = null;
if ($serviceId) {
    $stmt = $mysqli->prepare("SELECT * FROM service WHERE id = ?");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $locations = $_POST['locations'];
    $category = $_POST['category'];
    $status = $_POST['status'];

    // Initialize image paths to existing values
    $mainImagePath = $service['image'] ?? '';
    $image1Path = $service['image1'] ?? '';
    $image2Path = $service['image2'] ?? '';

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

$stmt = $mysqli->prepare(
    "UPDATE service SET title = ?, description = ?, locations = ?, category = ?, status = ?, image = ?, image1 = ?, image2 = ?, updatedAt = NOW() WHERE id = ?"
);
$stmt->bind_param(
    "ssssssssi", // 8 strings and 1 integer
    $title, $description, $locations, $category, $status, $mainImagePath, $image1Path, $image2Path, $serviceId
);

    if ($stmt->execute()) {
        echo "<script>window.location.href = 'service.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$mysqli->close();

// Function to handle file upload
function handleFileUpload($file) {
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
                return $uniqueFileName; // Return the unique file name
            } else {
                echo "Error uploading the file.";
            }
        } else {
            echo "Invalid image size or type.";
        }
    } else {
        echo "File is not an image.";
    }
    return null; // Return null if there was an error
}
?>

<!-- Main Content Area -->
<div class="mt-20 mx-auto max-w-3xl py-4 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <h1 class="text-4xl font-bold mb-6">Edit Service</h1>
            <form id="serviceForm" class="space-y-6" method="POST" enctype="multipart/form-data">
                <!-- Title Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Title</label>
                    <input type="text" name="title" id="title" placeholder="Service Title" value="<?php echo htmlspecialchars($service['title'] ?? ''); ?>" required
                        class="w-full p-3 border rounded-md" />
                </div>

                <!-- Description Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Description</label>
                    <textarea name="description" id="description" placeholder="Brief service description" required
                        rows="5" class="w-full p-3 border rounded-md resize-none"><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
                </div>

                <!-- Locations Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Locations</label>
                    <input type="text" name="locations" id="locations" placeholder="Locations" value="<?php echo htmlspecialchars($service['locations'] ?? ''); ?>" required
                        class="w-full p-3 border rounded-md" />
                </div>

                <!-- Category Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Category</label>
                    <input type="text" name="category" id="category" placeholder="Category" value="<?php echo htmlspecialchars($service['category'] ?? ''); ?>" required
                        class="w-full p-3 border rounded-md" />
                </div>

                <!-- Status Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Status</label>
                    <input type="text" name="status" id="status" placeholder="Status" value="<?php echo htmlspecialchars($service['status'] ?? ''); ?>" required
                        class="w-full p-3 border rounded-md" />
                </div>

                <!-- Image Upload Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2">Main Image</label>
                        <input type="file" name="image" id="image" class="block w-full p-3 border rounded-md" />
                    </div>
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2">Image 1</label>
                        <input type="file" name="image1" id="image1" class="block w-full p-3 border rounded-md" />
                    </div>
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2">Image 2</label>
                        <input type="file" name="image2" id="image2" class="block w-full p-3 border rounded-md" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700">
                        Update 
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>