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

// Check if the 'id' parameter exists in the URL
if (isset($_GET['id'])) {
    $recordId = $_GET['id'];

    // Fetch record data from the database
    $query = "SELECT * FROM aboutus WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $recordId);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();

    // Handle form submission for updating the record
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tittle = $_POST['tittle'];
        $description1 = $_POST['description1'];
        $description2 = $_POST['description2'];
        $source = $_POST['source'];
        $quote = $_POST['quote'];

        // Update the record data in the database
        $updateQuery = "UPDATE aboutus SET tittle = ?, description1 = ?, description2 = ?, source = ?, quote = ? WHERE id = ?";
        $updateStmt = $mysqli->prepare($updateQuery);
        $updateStmt->bind_param('sssssi', $tittle, $description1, $description2, $source, $quote, $recordId);

        if ($updateStmt->execute()) {
            echo "<script>window.location.href = 'aboutus.php';</script>";
            exit;
        } else {
            echo "<p>Error: Could not update record.</p>";
        }
    }
} else {
    echo "<script>window.location.href = 'aboutus.php';</script>";
    exit;
}
?>

<!-- Main Content Area -->
<div class="mt-20 max-w-3xl w-full p-4 mx-auto pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <h1 class="text-4xl font-bold mb-6">Edit Quote</h1>
            <form method="POST" class="space-y-6">
                <!-- Title Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Title</label>
                    <input type="text" name="tittle" id="tittle" placeholder="Title" required class="w-full p-3 border rounded-md" value="<?php echo htmlspecialchars($record['tittle']); ?>" />
                </div>

                <!-- Description 1 Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Description 1</label>
                    <textarea name="description1" id="description1" placeholder="Description 1" required class="w-full p-3 border rounded-md resize-none"><?php echo htmlspecialchars($record['description1']); ?></textarea>
                </div>

                <!-- Description 2 Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Description 2</label>
                    <textarea name="description2" id="description2" placeholder="Description 2" required class="w-full p-3 border rounded-md resize-none"><?php echo htmlspecialchars($record['description2']); ?></textarea>
                </div>

                <!-- Source Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Source</label>
                    <input type="text" name="source" id="source" placeholder="Source" required class="w-full p-3 border rounded-md" value="<?php echo htmlspecialchars($record['source']); ?>" />
                </div>

                <!-- Quote Field -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-2">Quote</label>
                    <input type="text" name="quote" id="quote" placeholder="Quote" required class="w-full p-3 border rounded-md" value="<?php echo htmlspecialchars($record['quote']); ?>" />
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