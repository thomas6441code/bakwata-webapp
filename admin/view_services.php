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

// Fetch service data from the database (replace with actual table and column names)
$projectId = isset($_GET['id']) ? $_GET['id'] : 0;
$sql = "SELECT * FROM service WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $projectId);
$stmt->execute();
$result = $stmt->get_result();

// If no service is found
if ($result->num_rows == 0) {
    echo "Service not found.";
    exit;
}

// Fetch the service data
$service = $result->fetch_assoc();

// Close the database connection
$stmt->close();
$mysqli->close();
?>

    <!-- Main Content Area -->
    <div class="mt-20 w-full max-w-3xl py-4 pb-10">
        <div class="min-w-full px-3 text-lg md:px-5">
            <div class="text-cyan-950 mb-20">
        <h1 id="projectTitle" class="text-4xl font-bold mb-6"><?php echo htmlspecialchars($service['title']); ?></h1>

        <div id="projectDetails" class="mb-8 bg-teal-100 p-4 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-2">Service Details</h2>
            <p class="mb-1"><strong>Location:</strong> <span id="projectLocation"><?php echo htmlspecialchars($service['locations']); ?></span></p>
            <p class="mb-1"><strong>Date:</strong> <span id="projectDate"><?php echo date('Y-m-d', strtotime($service['createdAt'])); ?></span></p>
            <p class="mb-1"><strong>Category:</strong> <span id="projectcategory"><?php echo htmlspecialchars($service['category']); ?></span></p>
            <p class="mb-1"><strong>Status:</strong> <span id="projectstatus"><?php echo htmlspecialchars($service['status']); ?></span></p>
            <p class="mb-1"><strong>Description:</strong> <span id="projectDescription"><?php echo htmlspecialchars($service['description']); ?></span></p>
        </div>

        <div class="mt-4 mb-8">
            <h2 class="text-2xl font-semibold mb-4">Images</h2>
            <div id="imageGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                // Array of image fields to display
                $images = [
                    'image' => $service['image'],
                    'image1' => $service['image1'],
                    'image2' => $service['image2'],
                ];

                foreach ($images as $key => $image) {
                    if ($image) {
                        echo '<div class="p-2 rounded-lg shadow-md">';
                        echo '<h3 class="font-medium mb-2">' . ucfirst($key) . '</h3>';
                        echo '<img src="../public/images/' . htmlspecialchars($image) . '" alt="' . ucfirst($key) . '" class="object-cover h-48 w-full rounded-md cursor-pointer" />';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>

        <a href="service.php" class="bg-blue-500 text-white px-6 py-4 rounded-lg shadow hover:bg-blue-600 transition">Back to Services</a>
    </div>
        </div>
    </div>

<script>
    const handleImageClick = (imageSrc) => {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModal').classList.remove('hidden');
    };

    document.getElementById('closeModal').onclick = () => {
        document.getElementById('imageModal').classList.add('hidden');
    };
</script>
</body>
</html>
