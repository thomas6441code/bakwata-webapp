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

$error = '';
$message = '';

// Fetch data from the database
function fetchData($table)
{
    global $mysqli; // Assuming you have a mysqli connection in db.php
    $result = $mysqli->query("SELECT * FROM $table");
    return $result->fetch_all(MYSQLI_ASSOC);
}

$missionVision = fetchData('missionvision');
$objectives = fetchData('corevalues');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'editMissionVision':
            $id = $_POST['id'];
            $vision = $mysqli->real_escape_string($_POST['vision']);
            $mission = $mysqli->real_escape_string($_POST['mission']);
            $Cvalues = $mysqli->real_escape_string($_POST['Cvalues']);

            if (!empty($vision) && !empty($mission) && !empty($Cvalues)) {
                $query = "UPDATE missionvision SET vision = '$vision', mission = '$mission', Cvalues='$Cvalues' WHERE id = $id";
                if ($mysqli->query($query)) {
                    $message = "Mission, Vision and values updated successfully!";
                } else {
                    $error = "Error: " . $mysqli->error;
                }
            } else {
                $error = "Both Vision and Mission must be provided!";
            }
            break;

        case 'editObjective':
            $id = $_POST['id'];
            $objectivesText = $_POST['objectives'];

            if (!empty($objectivesText)) {
                $mysqli->query("UPDATE corevalues SET objectives = '$objectivesText' WHERE id = $id");
                if ($mysqli) {
                    $message = "Core value updated successfully!";
                } else {
                    $error = "Error: " . $mysqli->error;
                }
            } else {
                $error = "Core value must be provided!";
            }
            break;

        case 'addObjective':
            $objectivesText = trim($_POST['objectives']);
            if (!empty($objectivesText)) {
                $stmt = $mysqli->prepare("INSERT INTO corevalues (objectives) VALUES (?)");
                $stmt->bind_param("s", $objectivesText);
                $stmt->execute();
                $stmt->close();
                if ($mysqli->query($query)) {
                    $message = "corevalues added successfully!";
                } else {
                    $error = "Error: " . $mysqli->error;
                    echo "Error :" . $mysqli->error;
                }
            }
            break;
    }

    // Redirect to avoid form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>

<div class="mt-16 md:w-full md:p-4 py-6 px-2 pb-10">
    <div class="max-w-full px-2">
        <div class="mt-10 mb-14 p-5 bg-white shadow-md text-cyan-950">
            <h2 class="text-2xl my-5 font-semibold">MISSION & VISION & VALUES</h2>
            <?php if (!empty($error)): ?>
                <div class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($message)): ?>
                <div class="text-red-500 mb-4"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <div class="overflow-x-auto scrollbar-hide">
                <table class="min-w-full rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-teal-300 border-gray-200">
                            <th class="border px-4 py-3">#</th>
                            <th class="border pr-44 pl-4 py-3">Vision</th>
                            <th class="border pr-44 pl-4 py-3">Mission</th>
                            <th class="border pr-44 pl-4 py-3">Values</th>
                            <th class="border px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($missionVision as $index => $item): ?>
                            <tr class="border-b border-gray-300">
                                <td class="border px-4 py-2"><?= $index + 1 ?></td>
                                <td class="border px-4 py-2"><?= htmlspecialchars($item['vision']) ?></td>
                                <td class="border px-4 py-2"><?= htmlspecialchars($item['mission']) ?></td>
                                <td class="border px-4 py-2"><?= htmlspecialchars($item['Cvalues']) ?></td>
                                <td class="border px-4 py-2">
                                    <button onclick="editMissionvision(1, '<?= htmlspecialchars($item['vision']) ?>', '<?= htmlspecialchars($item['mission']) ?>', '<?= htmlspecialchars($item['Cvalues']) ?>')" class="bg-sky-500 text-white p-1 px-4 m-1 rounded">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="editModal" class="space-x-2 mt-2 hidden w-full">
                <div id="editForm" class="w-full px-2 py-3">
                    <div class="mb-4">
                        <label for="vision" class="block text-sm font-medium text-gray-700">Vision</label>
                        <input type="text" placeholder="Enter the vision" class="mt-1 px-3 py-2 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="vision" name="vision" required>
                    </div>
                    <div class="mb-4">
                        <label for="mission" class="block text-sm font-medium text-gray-700">Mission</label>
                        <input type="text" placeholder="Enter the mission" class="mt-1 px-3 py-2 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="mission" name="mission" required>
                    </div>
                    <div class="mb-4">
                        <label for="Cvalues" class="block text-sm font-medium text-gray-700">Values</label>
                        <input type="text" placeholder="Enter the values" class="mt-1 px-3 py-2 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="Cvalues" name="Cvalues" required>
                    </div>
                    <button type="submit" id="editmissionvalues" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                    <button type="button" onclick="closeModal()" class="bg-red-500 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full px-2">
        <div class="mt-5 p-5 bg-white shadow-md text-cyan-950">
            <h2 class="text-2xl my-5 font-semibold">COREVALUES</h2>
            <div class="overflow-x-auto scrollbar-hide">
                <table class="min-w-full rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-teal-300 border-gray-200">
                            <th class="border px-4 py-3">#</th>
                            <th class="border pl-4 pr-44 py-3">Values</th>
                            <th class="border px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($objectives as $index => $objective): ?>
                            <tr class="border-b border-gray-300">
                                <td class="border px-4 py-2"><?= $index + 1 ?></td>
                                <td class="border px-4 py-2"><?= htmlspecialchars($objective['objectives']) ?></td>
                                <td class="border px-4 py-2">
                                    <button onclick="editObjective(<?= $objective['id'] ?>, '<?= htmlspecialchars($objective['objectives']) ?>')" class="bg-sky-500 text-white p-1 px-4 m-1 rounded">Edit</button>
                                    <button onclick="deletePhoto(<?= $objective['id'] ?>)" class="bg-red-500 rounded-sm text-white px-4 py-1 ml-1">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="edit" class="space-x-2 mt-2 hidden">
                <input type="text" id="newObjective" placeholder="Objectives" class="border p-3 rounded w-full" />
                <button id="addObjective" class="bg-blue-500 hover:bg-blue-700 text-white text-sm p-2 my-2 px-4 rounded">Save</button>
            </div>
            <div class="flex space-x-2 mt-2">
                <input type="text" id="newObjectivee" placeholder="Enter a new objective" required class="border p-2 rounded w-full" />
                <?php if (!empty($message)): ?>
                    <div class="text-red-500 mb-4"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <button id="addObjectivee" class="bg-blue-500 hover:bg-blue-700 text-white text-sm p-2 px-4 rounded">Add</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('addObjectivee').onclick = async () => {
        const objectivesText = document.getElementById('newObjectivee').value.trim();
        if (!objectivesText) {
            $message = 'Please enter a valid objective.';
            return;
        }

        try {

            console.log(objectivesText)
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'addObjective',
                    objectives: objectivesText
                })
            });

            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to add objective. Please try again.');
            }
        } catch (error) {
            console.error('Error adding objective:', error);
            alert('An unexpected error occurred. Please try again.');
        }
    };

    function editMissionvision(id, vision, mission, Cvalues) {
        // Set the values in the modal
        document.getElementById('vision').value = vision;
        document.getElementById('mission').value = mission;
        document.getElementById('Cvalues').value = Cvalues;

        // Show the modal
        document.getElementById('editModal').classList.remove('hidden');

        document.getElementById('editmissionvalues').onclick = async () => {
            await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'editMissionVision',
                    id: id,
                    vision: document.getElementById('vision').value,
                    mission: document.getElementById('mission').value,
                    Cvalues: document.getElementById('Cvalues').value
                })
            });
            location.reload();
        };
    }

    function editObjective(id, objectives) {
        const objectivesedit = document.getElementById('edit')
        objectivesedit.classList.remove('hidden');

        document.getElementById('newObjective').value = objectives;
        document.getElementById('addObjective').onclick = async () => {
            await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'editObjective',
                    id: id,
                    objectives: document.getElementById('newObjective').value
                })
            });
            location.reload();
        };
    }

    const deletePhoto = async (id) => {
        try {
            const model = 'corevalues'
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

    function closeModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
</body>

</html>