<?php
include './includes/header.php';
include './assets/db.php';

// Fetch events from the database
$events = [];
$loadingEvents = true;

$result = $conn->query("SELECT * FROM service ORDER BY date DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $loadingEvents = false;
} else {
    $loadingEvents = false;
}

$conn->close();
?>

<div class="container min-h-screen mt-16 py-6 text-cyan-950 mx-auto p-6">

    <!-- News Section -->
    <h1 class="text-2xl text-center font-bold mb-4">SERVICES</h1>

    <?php if ($loadingEvents): ?>
        <div class="text-center">Loading...</div>
    <?php elseif (empty($events)): ?>
        <div class="text-center">No services available...!</div>
    <?php else: ?>
        <div class="my-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($events as $event): ?>
                <div onclick="viewEvent(<?php echo $event['id']; ?>)" class="block relative">
                    <div class="bg-white min-h-[25rem] rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <img
                            src="./public/images/<?php echo htmlspecialchars($event['image']); ?>"
                            alt="<?php echo htmlspecialchars($event['title']); ?>"
                            width="700"
                            height="600"
                            class="w-full h-64 object-cover" />
                        <div class="p-4">
                            <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($event['title']); ?></h2>
                            <p class="mt-2 text-gray-600">
                                <?php echo htmlspecialchars($event['locations']); ?> |
                                <?php echo date("F j, Y", strtotime($event['date'])); ?>
                            </p>
                            <p class="mt-2 text-gray-600"><?php echo htmlspecialchars($event['description']); ?></p>
                        </div>

                        <div
                            onclick="event.stopPropagation(); viewEvent(<?php echo $event['id']; ?>)"
                            class="bg-green-500 text-white p-2 px-4 rounded-lg flex items-center w-fit m-1 cursor-pointer hover:bg-green-600 transition-colors duration-300"
                            title="View More">
                            <!-- Font Awesome Icon -->
                            <i class="fas fa-eye mr-2"></i> 
                            <span class="font-semibold">MORE</span>
                        </div>
                    </div>
            
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // Navigate to the project details page
    function viewEvent(eventid) {
        window.location.href = `service_details.php?id=${eventid}`;
    }
</script>

<?php
include "./includes/footer.php";
?>