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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slideId'])) {
    $slideId = $_POST['slideId'];

    // Prepare the DELETE statement
    $stmt = $mysqli->prepare("DELETE FROM slide WHERE id = ?");
    $stmt->bind_param("i", $slideId);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Slide deleted successfully.']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error deleting slide: ' . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
    // Close the database connection
    $mysqli->close();
    exit(); // Exit to prevent further output
}

// Pagination setup
$projectsPerPage = 15;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $projectsPerPage;

// Fetch total number of slides
$totalQuery = "SELECT COUNT(*) FROM slide";
$totalResult = $mysqli->query($totalQuery);
$totalSlides = $totalResult->fetch_row()[0];
$totalPages = ceil($totalSlides / $projectsPerPage);

// Fetch slides for the current page
$query = "SELECT * FROM slide LIMIT $startIndex, $projectsPerPage";
$result = $mysqli->query($query);

$slides = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $slides[] = $row;
    }
}
?>

<!-- Main Content Area -->
<div class="mt-20 w-full mx-auto py-4 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-3xl font-bold">SLIDES</h1>
                <button onclick="createProject()" class="bg-blue-500 text-white px-4 py-2 rounded">Create Slides</button>
            </div>
            <div id="loading" class="h-screen flex-1 items-center justify-center hidden">
                <div class="flex-col justify-center items-center">Loading...</div>
            </div>
            <div id="error" class="text-red-500 text-center hidden"></div>
            <div id="projectsTable" class="overflow-x-auto scrollbar-hide">
                <table class="min-w-full rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-green-300 text-left">
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2 pr-20">Description</th>
                            <th class="px-4 pr-44 py-2">Image</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slides as $index => $slide): ?>
                            <tr class="border-t" id="slide-<?= $slide['id'] ?>">
                                <td class="px-4 py-2"><?= $index + 1 + $startIndex ?></td>
                                <td class="px-4 py- 2"><?= htmlspecialchars($slide['title']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($slide['description']) ?></td>
                                <td class="px-4 py-2">
                                    <img src="../public/images/<?= htmlspecialchars($slide['image']) ?>" alt="Main" class="h-40 min-w-60 object-cover">
                                </td>
                                <td class="px-2 py-2">
                                    <button onclick="editProject(<?= $slide['id'] ?>)" class="bg-sky-500 text-white px-4 py-1 my-1 mx-1 rounded">Edit</button>
                                    <button onclick="handleDelete(<?= $slide['id'] ?>)" class="bg-red-500 text-white px-4 py-1 my-1 rounded mx-1">Delete</button>
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
    function createProject() {
        window.location.href = 'create_slides.php';
    }

    function editProject(projectId) {
        window.location.href = `slidesedit.php?id=${projectId}`;
    }

    const handleDelete = async (id) => {
        try {
            const model = 'slide'
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
                alert(`Error: ${result.error}`);
            }
        } catch (error) {
            console.error('Failed to delete record:', error);
        }
    };
</script>