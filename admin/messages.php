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

$messagesPerPage = 30;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $messagesPerPage;

$sql = "SELECT * FROM message LIMIT $startIndex, $messagesPerPage";
$result = $mysqli->query($sql);

$totalMessagesSql = "SELECT COUNT(*) as total FROM message";
$totalMessagesResult = $mysqli->query($totalMessagesSql);
$totalMessages = $totalMessagesResult->fetch_assoc()['total'];
$totalPages = ceil($totalMessages / $messagesPerPage);
?>

<!-- Main Content Area -->
<div class="mt-20 w-full p-4 pb-10">
    <div class="min-w-full px-2 text-lg">
        <div class="text-cyan-950 mb-20">
            <h1 class="text-2xl font-bold mt-2 mb-4">MESSAGES</h1>

            <div id="loading" class="mt-64 h-fit items-center justify-center hidden">
                <div class="spinner"></div>
            </div>

            <div id="error" class="text-red-500 text-center hidden">
                <h2>Error: <span id="errorMessage"></span></h2>
            </div>

            <div id="messagesTable" class="overflow-x-auto scrollbar-hidden scrollbar-hide">
                <table class="min-w-full rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-teal-300 border-gray-200">
                            <th class="border px-4 py-2 text-left">#</th>
                            <th class="border px-4 py-2 text-left">Name</th>
                            <th class="border px-4 py-2 text-left">Email</th>
                            <th class="border px-4 py-2 text-left">Phone</th>
                            <th class="border px-4 py-2 text-left">Address</th>
                            <th class="border px-4 py-2 text-left">Subject</th>
                            <th class="border px-4 py-2 text-left">Message</th>
                            <th class="border px-4 py-2 pr-20 text-left">ReceivedAt</th>
                            <th class="border px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="messageRows">
                        <?php if ($result->num_rows > 0): ?>
                            <?php
                            $i = 1;
                            while ($row = $result->fetch_assoc()): ?>
                                <tr class="border-b border-gray-300">
                                    <td class="border px-4 py-2"><?php echo $i ?></td>
                                    <td class="border px-4 py-2"><?php echo $row['fullName']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $row['email']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $row['phone']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $row['address']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $row['subject']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $row['message']; ?></td>
                                    <td class="border px-4 py-2"><?php echo date('Y-m-d H:i:s', strtotime($row['createdAt'])); ?></td>
                                    <td class="border px-4 py-2">
                                        <div class="flex-col space-y-2">
                                            <?php if ($row['isRead']): ?>
                                                <button onclick="updateMessageStatus(<?php echo $row['id']; ?>, false)" class="bg-yellow-500 text-white text-sm px-4 py-2 rounded">
                                                    Unmark
                                                </button>
                                            <?php else: ?>
                                                <button onclick="updateMessageStatus(<?php echo $row['id']; ?>, true)" class="bg-green-500 text-white text-sm px-4 py-2 rounded">
                                                    Mark
                                                </button>
                                            <?php endif; ?>
                                            <button onclick="deleteMessage(<?php echo $row['id']; ?>)" class="bg-red-500 text-white text-sm px-4 py-2 rounded">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                                $i++;
                            endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="border px-4 py-2 text-center">No messages found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="pagination" class="justify-between mt-10">
                <a href="?page=<?php echo max($currentPage - 1, 1); ?>" class="bg-gray-300 text-gray-700 px-4 py-2 rounded <?php echo $currentPage == 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>">Previous</a>
                <span id="pageInfo" class="self-center">Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>
                <a href="?page=<?php echo min($currentPage + 1, $totalPages); ?>" class="bg-gray-300 text-gray-700 px-4 py-2 rounded <?php echo $currentPage == $totalPages ? 'opacity-50 cursor-not-allowed' : ''; ?>">Next</a>
            </div>
        </div>
    </div>
</div>

<script>
    const updateMessageStatus = async (id, isRead) => {
        try {
            const response = await fetch(`message.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id,
                    isRead
                }),
            });

            if (!response.ok) {
                throw new Error(`Error: ${response.statusText}`);
            }

            location.reload();
        } catch (error) {
            console.error('Failed to update message status:', error);
        }
    };

    const deleteMessage = async (id) => {
        try {
            const model = 'message'
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