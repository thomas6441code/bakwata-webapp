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

// Define number of projects per page
$projectsPerPage = 15;
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1; 

$startIndex = ($currentPage - 1) * $projectsPerPage;

// Fetch total number of services
$result = $mysqli->query("SELECT COUNT(*) AS total FROM service");
$row = $result->fetch_assoc();
$totalServices = $row['total'];
$totalPages = ceil($totalServices / $projectsPerPage);

// Fetch the services for the current page
$sql = "SELECT * FROM service LIMIT $startIndex, $projectsPerPage";
$result = $mysqli->query($sql);

?>


    <!-- Main Content Area -->
    <div class="mt-20 mx-auto w-full py-4 pb-10">
        <div class="min-w-full px-3 text-lg md:px-5">
            <div class="text-cyan-950 mb-20">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-3xl font-bold">SERVICES</h1>
                    <button onclick="createProject()" class="bg-blue-500 text-white px-4 py-2 rounded">CREATE SERVICE</button>
                </div>

                <div id="projectsTable" class="overflow-x-auto scrollbar-hidden scrollbar-hide">
                    <table class="min-w-full rounded-lg shadow-md">
                        <thead>
                            <tr class="bg-teal-300 text-left">
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Title</th>
                                <th class="px-4 pr-28 py-3">Description</th>
                                <th class="px-4 py-3">Locations</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 pr-44 py-3">Image</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                             $i=1;
                             while($project = $result->fetch_assoc()): ?>
                                <tr class="border-t">
                                    <td class="px-4 py-2"><?=  $i  ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($project['title']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($project['description']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($project['locations']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($project['status']) ?></td>
                                    <td class="px-4 py-2">
                                        <img src="/public/images/<?= htmlspecialchars($project['image']) ?>" alt="Main" class="h-40 min-w-60 object-cover">
                                    </td>
                                    <td class="px-2 py-2">
                                        <button onclick="viewProject(<?= $project['id'] ?>)" class="bg-green-500 text-white px-4 py-1 my-1 mx-1 rounded">View</button>
                                        <button onclick="editProject(<?= $project['id'] ?>)" class="bg-sky-500 text-white px-4 py-1 my-1 mx-1 rounded">Edit</button>
                                        <button onclick="deleteMessage(<?= $project['id'] ?>)" class="bg-red-500 text-white px-4 py-1 my-1 rounded mx-1">Delete</button>
                                    </td>
                                </tr>
                            <?php $i++; ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div id="pagination" class="justify-between mt-10">
                    <button id="prevButton" class="bg-gray-300 text-gray-700 px-4 py-2 rounded" <?= $currentPage == 1 ? 'disabled' : '' ?>>Previous</button>
                    <span id="pageInfo" class="self-center">Page <?= $currentPage ?> of <?= $totalPages ?></span>
                    <button id="nextButton" class="bg-gray-300 text-gray-700 px-4 py-2 rounded" <?= $currentPage == $totalPages ? 'disabled' : '' ?>>Next</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const prevButton = document.getElementById('prevButton');
        const nextButton = document.getElementById('nextButton');
        const pageInfo = document.getElementById('pageInfo');

        prevButton.onclick = () => {
            window.location.href = 'services.php?page=' + (<?= $currentPage ?> - 1);
        };

        nextButton.onclick = () => {
            window.location.href = 'services.php?page=' + (<?= $currentPage ?> + 1);
        };

        function createProject() {
            window.location.href = 'create_service.php';
        }

        function viewProject(projectid) {
            window.location.href = `view_services.php?id=${projectid}`;
        }

        function editProject(projectid) {
            window.location.href = `edit_service.php?id=${projectid}`;
        }

        
           const deleteMessage = async (id) => {
            try {
                const model = 'service'
                const response = await fetch(`delete.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, model }),
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

<?php
// Close the connection
$mysqli->close();
?>
