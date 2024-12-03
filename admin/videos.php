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

// Handle deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['videoId'])) {
    $videoId = $_POST['videoId'];

    // Prepare the DELETE statement
    $stmt = $mysqli->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->bind_param("i", $videoId);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Video deleted successfully.']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error deleting video: ' . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
    // Close the database connection
    $mysqli->close();
    exit(); // Exit to prevent further output
}

// Pagination setup
$videosPerPage = 15;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $videosPerPage;

// Fetch total number of videos
$totalQuery = "SELECT COUNT(*) FROM videos";
$totalResult = $mysqli->query($totalQuery);
$totalVideos = $totalResult->fetch_row()[0];
$totalPages = ceil($totalVideos / $videosPerPage);

// Fetch videos for the current page
$query = "SELECT * FROM videos LIMIT $startIndex, $videosPerPage";
$result = $mysqli->query($query);

$videos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
}
?>

<!-- Main Content Area -->
<div class="mt-20 w-full mx-auto py-4 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-3xl font-bold">VIDEOS</h1>
                <button onclick="createVideo()" class="bg-blue-500 text-white px-4 py-2 rounded">Create Video</button>
            </div>
            <div id="loading" class="h-screen flex-1 items-center justify-center hidden">
                <div class="flex-col justify-center items-center">Loading...</div>
            </div>
            <div id="error" class="text-red-500 text-center hidden"></div>
            <div id="videosTable" class="overflow-x-auto scrollbar-hide">
                <table class="min-w-full rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-green-300 text-left">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3 pr-20">Description</th>
                            <th class="px-4 pr-44 py-3">YouTube URL</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($videos as $index => $video): ?>
                            <tr class="border-t" id="video-<?= $video['id'] ?>">
                                <td class="px-4 py-2"><?= $index + 1 + $startIndex ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($video['title']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($video['description']) ?></td>
                                <td class="px-4 py-2 "><iframe class="w-full h-48" src="<?= htmlspecialchars($video['youtube_url']) ?>" frameborder="0" allowfullscreen></iframe></td>
                                <td class="px-2 py-2">
                                    <button onclick="handleDelete(<?= $video['id'] ?>)" class="bg-red-500 text-white px-4 py-1 my-1 rounded mx-1">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function createVideo() {
        window.location.href = 'create_video.php';
    }

    const handleDelete = async (id) => {
        try {
            const model = 'videos'
            const response = await fetch(`delete.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id,
                    model
                }),
            });

            if (!response.ok) {
                throw new Error(`Error: ${response.statusText}`);
            }

            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(`Error: ${result.message}`);
            }
        } catch (error) {
            console.error('Failed to delete record:', error);
        }
    };
</script>