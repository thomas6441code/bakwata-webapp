<?php
// Fetch all data from the database
function fetchData()
{
    include './assets/db.php';

    $data = [
        'projects' => [],
        'events' => [],
        'services' => []
    ];

    // Fetch Projects
    $projectQuery = "SELECT id, title, description, image, date, location FROM project ORDER BY id DESC LIMIT 2";
    $projectResult = $conn->query($projectQuery);
    if ($projectResult->num_rows > 0) {
        $data['projects'] = $projectResult->fetch_all(MYSQLI_ASSOC);
    }

    // Fetch Events
    $eventQuery = "SELECT id, title, description, image, date, location FROM event ORDER BY id DESC LIMIT 2";
    $eventResult = $conn->query($eventQuery);
    if ($eventResult->num_rows > 0) {
        $data['events'] = $eventResult->fetch_all(MYSQLI_ASSOC);
    }

    // Fetch Services
    $serviceQuery = "SELECT id, title, description, image, date FROM service ORDER BY id DESC LIMIT 2";
    $serviceResult = $conn->query($serviceQuery);
    if ($serviceResult->num_rows > 0) {
        $data['services'] = $serviceResult->fetch_all(MYSQLI_ASSOC);
    }

    $conn->close();
    return $data;
}

$data = fetchData();
?>

<div class="text-neutral-950 mx-[5%] py-5">

    <h2 class="text-2xl font-bold text-center my-5">LATEST UPDATES</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php
        // Function to render a card
        function renderCard($item, $label)
        {
        ?>
            <div
                class="bg-white relative text-cyan-950 shadow-md rounded p-3 pb-7 mb-6 hover:bg-green-100 cursor-pointer"
                onclick="window.location.href='<?php echo strtolower($label); ?>_details.php?id=<?php echo $item['id']; ?>';">
                <!-- Label -->
                <div class="<?php
                            // Base classes
                            $classes = 'p-6 shadow-lg rounded-lg text-center flex flex-col items-center transition-transform transform lg:hover:scale-105 hover:shadow-2xl';

                            // Conditional classes based on $label
                            if ($label === 'Event') {
                                $classes .= ' bg-yellow-100 hover:bg-yellow-50';
                            } elseif ($label === 'Service') {
                                $classes .= ' bg-green-100 hover:bg-green-50';
                            } elseif ($label === 'Project') {
                                $classes .= ' bg-pink-100 hover:bg-pink-50';
                            } else {
                                $classes .= ' hover:bg-orange-50';
                            }

                            // Output the final classes
                            echo $classes;
                            ?>">
                    <?php echo $label; ?>
                </div>

                <!-- Image -->
                <img
                    src="./public/images/<?php echo $item['image']; ?>"
                    alt="<?php echo htmlspecialchars($item['title']); ?>"
                    class="w-full h-56 object-cover rounded-lg my-2" />

                <!-- Location -->
                <?php if (!empty($item['location'])): ?>
                    <p class="text-sm text-gray-600">
                        <strong>Location: </strong> <?php echo htmlspecialchars($item['location']); ?>
                    </p>
                <?php endif; ?>

                <!-- Date -->
                <p class="text-gray-500 mb-2">
                    <?php echo date('d M Y', strtotime($item['date'])); ?>
                </p>

                <!-- Title -->
                <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($item['title']); ?></h3>

                <!-- Description -->
                <p class="text-gray-700 mb-4">
                    <?php echo strlen($item['description']) > 100
                        ? htmlspecialchars(substr($item['description'], 0, 100)) . '...'
                        : htmlspecialchars($item['description']); ?>
                </p>
            </div>
        <?php
        }

        // Render last 3 Projects
        foreach ($data['projects'] as $project) {
            renderCard($project, 'Project');
        }

        // Render last 3 Events
        foreach ($data['events'] as $event) {
            renderCard($event, 'Event');
        }

        // Render last 3 Services
        foreach ($data['services'] as $service) {
            renderCard($service, 'Service');
        }
        ?>
    </div>
</div>