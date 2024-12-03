<?php
include './includes/header.php';
include './assets/db.php';

// Fetch events from the database
$events = [];
$loadingEvents = true;

$result = $conn->query("SELECT * FROM event ORDER BY date DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $loadingEvents = false;
} else {
    $loadingEvents = false;
}

?>

<div class="container min-h-screen mt-16 py-6 text-cyan-950 mx-auto p-6">
    <div class="">

<h1 class="text-3xl font-bold my-6">VIDEO GALLERY</h1>
<div class="overflow-x-auto min-w-full scrollbar-hide">
    <div class="flex gap-4 py-2">
        <?php
        // Fetch videos from the database
        $sql = "SELECT * FROM videos";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo '<div class="w-full md:w-1/3 bg-white rounded-lg shadow-md overflow-hidden flex-shrink-0">';
                echo '<iframe class="w-full h-48" src="' . $row['youtube_url'] . '" frameborder="0" allowfullscreen></iframe>';
                echo '<h3 class="p-4 text-lg font-semibold">' . htmlspecialchars($row['description']) . '</h3>';
                echo '</div>';
            }
        } else {
            echo '<div class="flex-shrink-0 text-center text-gray-500">No videos found.</div>';
        }
        ?>
    </div>
</div>


</div>

    <!-- News Section -->
    <h1 class="text-2xl text-center font-bold my-4">NEWS AND EVENTS</h1>

    <?php if ($loadingEvents): ?>
        <div class="text-center">Loading...</div>
    <?php elseif (empty($events)): ?>
        <div class="text-center text-gray-400">No news or updates available...!</div>
    <?php else: ?>
        <div class="my-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($events as $event): ?>
                <div onclick="viewEvent(<?php echo $event['id']; ?>)" class="block">
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
                                <?php echo htmlspecialchars($event['location']); ?> |
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
        window.location.href = `event_details.php?id=${eventid}`;
    }
</script>

<?php
include "./includes/footer.php";
?>