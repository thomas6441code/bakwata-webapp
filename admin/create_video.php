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

// Initialize variables for error/success messages
$errorMessage = "";
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $youtubeUrl = htmlspecialchars($_POST['youtube_url']);

    // Insert data into the database
    $stmt = $mysqli->prepare("INSERT INTO videos (title, description, youtube_url, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("sss", $title, $description, $youtubeUrl);

    if ($stmt->execute()) {
        $successMessage = "Video created successfully!";
        echo "<script type='text/javascript'>
                    window.location.href = 'videos.php';
                  </script>";
        exit();
    } else {
        $errorMessage = "Error saving to database: " . $mysqli->error;
    }

    $stmt->close();
}

$mysqli->close();
?>

<!-- Main Content Area -->
<div class="mt-20 md:w-1/2 w-full mx-auto py-4 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <h1 class="text-3xl font-bold mb-6">Create New Video</h1>
            <?php if (!empty($successMessage)): ?>
                <p style="color: green;"><?php echo $successMessage; ?></p>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <p style="color: red;"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
            <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <input type="text" name="title" id="title" placeholder="Video Title" required class="block w-full border p-3 rounded mb-2" />
                </div>

                <div class="md:col-span-2">
                    <textarea name="description" id="description" placeholder="Description" required class="block w-full border p-4 rounded mb-2 h-28"></textarea>
                </div>

                <div class="md:col-span-2">
                    <input type="text" name="youtube_url" id="youtube_url" placeholder="https://www.youtube.com/embed/U0x3y3f1roM?s=z6aS2dEPthfYOkXf" required class="block w-full border p-3 rounded mb-2" />
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