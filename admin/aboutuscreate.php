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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $tittle = $_POST['tittle'];
    $description1 = $_POST['description1'];
    $description2 = $_POST['description2'];
    $source = $_POST['source'];
    $quote = $_POST['quote'];

    // Insert data into the database
    $query = "INSERT INTO aboutus (tittle, description1, description2, source, quote) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sssss', $tittle, $description1, $description2, $source, $quote);

    if ($stmt->execute()) {
        echo "<script>window.location.href = 'aboutus.php';</script>";
        exit;
    } else {
        // Handle error
        echo "<p>Error: Could not create new record.</p>";
    }
}
?>

<!-- Main Content Area -->
<div class="mt-20 mx-auto max-w-3xl w-full p-6 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <h1 class="text-3xl font-bold mb-6">Create New Record</h1>
            <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <input type="text" name="tittle" id="tittle" placeholder="Title" required class="block w-full border p-3 rounded mb-2" />
                </div>

                <div class="md:col-span-2">
                    <textarea name="description1" id="description1" placeholder="Description 1" required class="block w-full border p-4 rounded mb-2"></textarea>
                </div>

                <div class="md:col-span-2">
                    <textarea name="description2" id="description2" placeholder="Description 2" required class="block w-full border p-4 rounded mb-2"></textarea>
                </div>

                <div>
                    <input type="text" name="source" id="source" placeholder="Source" required class="block w-full border p-3 rounded mb-2" />
                </div>

                <div class="md:col-span-2">
                    <input type="text" name="quote" id="quote" placeholder="Quote" required class="block w-full border p-3 rounded mb-2" />
                </div>

                <div class="md:col-span-2">
                    <button type="submit" class="w-fit bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>

</html>