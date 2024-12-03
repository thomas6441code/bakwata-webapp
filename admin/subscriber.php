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

$donationPerPage = 30;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $donationPerPage;

$sql = "SELECT * FROM subscriber LIMIT $startIndex, $donationPerPage";
$result = $mysqli->query($sql);

$totalDonationsSql = "SELECT COUNT(*) as total FROM subscriber";
$totalDonationsResult = $mysqli->query($totalDonationsSql);
$totalDonations = $totalDonationsResult->fetch_assoc()['total'];
$totalPages = ceil($totalDonations / $donationPerPage);
?>

<!-- Main Content Area -->
<div class="mt-20 w-full p-4 pb-10 mb-10">
    <div class="min-w-full px-3 text-lg md:px-5">
        <div class="text-cyan-950 mb-20">
            <div class="mb-16 text-cyan-950">
                <h1 class="text-3xl font-bold mb-4">SUBSCRIBERS</h1>

                <div id="loading" class="h-screen items-center justify-center hidden">
                    <div class="spinner"></div>
                </div>

                <div id="error" class="text-red-500 text-center hidden">
                    <h2>Error: <span id="errorMessage"></span></h2>
                </div>

                <div id="donationsTable" class="overflow-x-auto scrollbar-hidden">
                    <table class="min-w-full rounded-lg shadow-md">
                        <thead>
                            <tr class="bg-teal-300 border-gray-200">
                                <th class="border px-4 py-2 text-left">#</th>
                                <th class="border px-4 py-2 text-left">Email</th>
                                <th class="border px-4 py-2 text-left">Date</th>
                                <th class="border px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="donationRows">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="border-b border-gray-300">
                                        <td class="border px-4 py-2"><?php echo $row['id']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $row['email']; ?></td>
                                        <td class="border px-4 py-2"><?php echo date('Y-m-d H:i:s', strtotime($row['createdAt'])); ?></td>
                                        <td class="border px-4 py-2">
                                            <div class="flex space-x-2">
                                                <?php if ($row['isRead']): ?>
                                                    <button onclick="updateSubscriberStatus(<?php echo $row['id']; ?>, false)" class="bg-yellow-500 text-white px-4 py-2 rounded">
                                                        Unmark
                                                    </button>
                                                <?php else: ?>
                                                    <button onclick="updateSubscriberStatus(<?php echo $row['id']; ?>, true)" class="bg-green-500 text-white px-4 py-2 rounded">
                                                        Mark
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="border px-4 py-2 text-center">No subscribers found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div id="pagination" class="justify-between mt-4">
                    <a href="?page=<?php echo max($currentPage - 1, 1); ?>" class="bg-gray-300 text-gray-700 px-4 py-2 rounded <?php echo $currentPage == 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>">Previous</a>
                    <span id="pageInfo" class="self-center">Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>
                    <a href="?page=<?php echo min($currentPage + 1, $totalPages); ?>" class="bg-gray-300 text-gray-700 px-4 py-2 rounded <?php echo $currentPage == $totalPages ? 'opacity-50 cursor-not-allowed' : ''; ?>">Next</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const updateSubscriberStatus = async (id, isRead) => {
        try {
            const response = await fetch(`subscribers.php`, {
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
            console.error('Failed to update subscriber status:', error);
        }
    };
</script>

</body>

</html>