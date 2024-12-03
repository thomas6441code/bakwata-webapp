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

$eventId = isset($_GET['id']) ? $_GET['id'] : 0;
$query = "SELECT * FROM event WHERE id = $eventId";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    $project = $result->fetch_assoc();
} else {
    $message = "No news or event found.";
    exit;
}

// Close connection
$mysqli->close();
?>


<!-- Main Content Area -->
<div class="mt-20 w-full max-w-3xl mx-auto py-4 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <h1 id="projectTitle" class="text-4xl font-bold mb-6"><?php echo htmlspecialchars($project['title']); ?></h1>

            <div id="projectDetails" class="mb-8 bg-teal-200 p-4 shadow-md">
                <h2 class="text-2xl font-semibold mb-2">Event Details</h2>
                <p class="mb-1"><strong>Location:</strong> <span id="projectLocation"><?php echo htmlspecialchars($project['location']); ?></span></p>
                <p class="mb-1"><strong>Date:</strong> <span id="projectDate"><?php echo date("F j, Y", strtotime($project['date'])); ?></span></p>
                <p class="mb-1"><strong>Description:</strong> <span id="projectDescription"><?php echo nl2br(htmlspecialchars($project['description'])); ?></span></p>
                <div id="projectMoreDescription" class="mt-2"><?php echo nl2br(htmlspecialchars($project['moreDescription'])); ?></div>
            </div>

            <div class="mt-4 mb-8">
                <h2 class="text-2xl font-semibold mb-4">Images</h2>
                <div id="imageGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    $images = [
                        'mainImage' => $project['image'],
                        'image1' => $project['image1'],
                        'image2' => $project['image2'],
                    ];

                    foreach ($images as $label => $image) {
                        if (!empty($image)) {
                            echo "
                            <div class='rounded-lg shadow-sm p-2'>
                            <h3 class='font-medium mb-2'>" . ucfirst(str_replace('image', 'Image ', $label)) . "</h3>
                                <img src='../public/images/$image' alt='$label' class='object-cover h-48 w-full rounded-md cursor-pointer' />
                            </div>";
                        }
                    }
                    ?>
                </div>
            </div>

            <a href="event.php" class="bg-blue-500 text-white px-6 py-4 rounded-lg shadow hover:bg-blue-600 transition">
                Back to Events
            </a>
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