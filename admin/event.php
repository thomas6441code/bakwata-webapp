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

// Pagination logic
$eventsPerPage = 15;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $eventsPerPage;

// Fetching events from database
$sql = "SELECT * FROM event LIMIT $eventsPerPage OFFSET $offset";
$result = $mysqli->query($sql);

// Fetch total events for pagination
$totalSql = "SELECT COUNT(*) AS total FROM event";
$totalResult = $mysqli->query($totalSql);
$totalEvents = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalEvents / $eventsPerPage);

// Close connection
$mysqli->close();
?>

<!-- Main Content Area -->
<div class="mt-20 w-full py-4 pb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-3xl font-bold">EVENTS</h1>
                <button onclick="window.location.href='create_event.php'" class="bg-blue-500 text-white px-4 py-2 rounded">CREATE EVENT</button>
            </div>
            <div id="loading" class="h-screen flex-1 items-center justify-center hidden">
                <div class="flex-col justify-center items-center">
                    Loading...
                </div>
            </div>
            <div id="error" class="text-red-500 text-center hidden"></div>
            <div id="eventsTable" class="overflow-x-auto scrollbar-hide">
                <table class="min-w-full rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-green-300 text-left">
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2">Location</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Event Type</th>
                            <th class="px-4 pr-44 py-2">Image</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Loop through the fetched events and display in table
                        if ($result->num_rows > 0) {
                            $i = 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='border-t'>
                                            <td class='px-4 py-2'>" . $i . "</td>
                                            <td class='px-4 py-2'>" . $row['title'] . "</td>
                                            <td class='px-4 py-2'>" . $row['location'] . "</td>
                                            <td class='px-4 py-2'>" . date('Y-m-d', strtotime($row['date'])) . "</td>
                                            <td class='px-4 py-2'>" . $row['eventType'] . "</td>
                                            <td class='px-4 py-2'>
                                                <img src='../public/images/" . $row['image'] . "' alt='Main' class='h-40 min-w-60 object-cover'>
                                            </td>
                                            <td class='px-2 py-2'>
                                                <button onclick=\"window.location.href='view_event.php?id=" . $row['id'] . "'\" class='bg-green-500 text-white px-4 py-1 my-1 mx-1 rounded'>View</button>
                                                <button onclick=\"window.location.href='edit_event.php?id=" . $row['id'] . "'\" class='bg-sky-500 text-white px-4 py-1 my-1 mx-1 rounded'>Edit</button>
                                                <button onclick=\"deleteMessage(" . $row['id'] . ")\" class='bg-red-500 text-white px-4 py-1 my-1 rounded mx-1'>Delete</button>
                                            </td>
                                          </tr>";
                                $i++;
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center text-gray-700 px-4 py-2'>No news or events found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div id="pagination" class="justify-between mt-10">
                <button id="prevButton" class="bg-gray-300 text-gray-700 px-4 py-2 rounded"
                    <?php echo ($page == 1) ? 'disabled' : ''; ?> onclick="window.location.href='events.php?page=<?php echo $page - 1; ?>'">
                    Previous
                </button>
                <span id="pageInfo" class="self-center">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                <button id="nextButton" class="bg-gray-300 text-gray-700 px-4 py-2 rounded"
                    <?php echo ($page == $totalPages) ? 'disabled' : ''; ?> onclick="window.location.href='events.php?page=<?php echo $page + 1; ?>'">
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Optional JS for handling delete
    const deleteMessage = async (id) => {
        try {
            const model = 'event'
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
</body>

</html>