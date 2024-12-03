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

// Handle different request methods
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Handle delete request
if ($requestMethod === 'POST' && isset($_POST['delete_id'])) {
    deleteRecord($conn, $_POST['delete_id']);
}

// Handle edit request
if ($requestMethod === 'POST' && isset($_POST['edit_id'])) {
    updateRecord($conn, $_POST);
}

// Fetch records
$records = fetchRecords($conn);

function fetchRecords($conn)
{
    $sql = "SELECT * FROM aboutus";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function deleteRecord($conn, $id)
{
    $sql = "DELETE FROM aboutus WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function updateRecord($conn, $data)
{
    $sql = "UPDATE aboutus SET tittle = ?, description1 = ?, description2 = ?, source = ?, quote = ? WHERE id = ?"; // Replace with your actual table name
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $data['tittle'], $data['description1'], $data['description2'], $data['source'], $data['quote'], $data['edit_id']);
    $stmt->execute();
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<div class="mt-20 w-full p-4 pb-10">
    <div class="min-w-full px-2 text-lg md:px-3">
        <div class="text-cyan-950 mb-20">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold mt-2 mb-4">QUOTES ABOUTUS</h1>
                <button onclick="createProject()" class="bg-blue-500 text-white px-2 py-2 my-2 rounded">CREATE QUOTE</button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr class="bg-teal-300">
                            <th class="border px-4 py-3">ID</th>
                            <th class="border px-4 py-3">Title</th>
                            <th class="border px-4 pr-20 py-3">Description 1</th>
                            <th class="border px-4 pr-20 py-3">Description 2</th>
                            <th class="border px-4 py-3">Source</th>
                            <th class="border px-4 pr-20 py-3">Quote</th>
                            <th class="border px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                            <tr class="border-b">
                                <td class="border px-4 py-2"><?php echo $record['id']; ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($record['tittle']); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($record['description1']); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($record['description2']); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($record['source']); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($record['quote']); ?></td>
                                <td class="px-4 py-2 flex-col justify-between">
                                    <button onclick="editProject(<?php echo $record['id']; ?>)" class="bg-sky-500 text-white px-4 py-1 my-1 rounded">Edit</button>
                                    <form method="POST" class="inline mt-1">
                                        <input type="hidden" name="delete_id" value="<?php echo $record['id']; ?>">
                                        <button type="submit" class="bg-red-500 text-white px-4 py-1 rounded">Delete</button>
                                    </form>
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
        window.location.href = 'aboutuscreate.php';
    }

    function editProject(projectId) {
        window.location.href = `aboutusedit.php?id=${projectId}`;
    }
</script>

</html>