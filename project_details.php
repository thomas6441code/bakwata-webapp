<?php
include './includes/header.php';
include './assets/db.php';

function fetchEventById($conn, $id) {
    $stmt = $conn->prepare("SELECT id, title, description, moreDescription, date, image, image1, image2, location FROM project WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$event = null;
$loading = true;

if ($id) {
    $event = fetchEventById($conn, $id);
}
$loading = false;
?>

<div class="container mx-auto mt-20 p-4">
    <?php if ($loading): ?>
        <div class="text-center text-lg font-medium">Loading...</div>
    <?php elseif (!$event): ?>
        <div class="text-center text-lg font-medium text-red-500">Project not found...</div>
    <?php else: ?>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <img alt="Main event image" class="w-full md:hidden md:h-72 h-64 object-cover" src="./public/images/<?php echo htmlspecialchars($event['image']); ?>" />
            <div class="p-6">
                <h1 class="text-3xl md:flex hidden font-bold mb-2"><?php echo htmlspecialchars($event['title']); ?></h1>
                <p class="text-gray-500 text-sm mb-4">Location: <?php echo htmlspecialchars($event['location']); ?> | <?php echo date('F j, Y', strtotime($event['date'])); ?></p>
                <h1 class="text-3xl flex md:hidden font-bold mb-2"><?php echo htmlspecialchars($event['title']); ?></h1>
                <p class="text-gray-700 mb-4"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                <p class="text-gray-700 mb-4"><?php echo nl2br(htmlspecialchars($event['moreDescription'])); ?></p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if (!empty($event['image'])): ?>
                        <img alt="Project main image" class="w-full hidden md:flex h-64 object-cover" src="./public/images/<?php echo htmlspecialchars($event['image']); ?>" />
                    <?php endif; ?>
                    <?php if (!empty($event['image1'])): ?>
                        <img alt="Project additional image 1" class="w-full h-64 object-cover" src="./public/images/<?php echo htmlspecialchars($event['image1']); ?>" />
                    <?php endif; ?>
                    <?php if (!empty($event['image2'])): ?>
                        <img alt="Project additional image 2" class="w-full h-64 object-cover" src="./public/images/<?php echo htmlspecialchars($event['image2']); ?>" />
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-8 text-left">
        <a href="projects.php" class="inline-flex items-center text-blue-500 font-medium hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back
        </a>
    </div>
</div>

<?php
$conn->close();
include './includes/footer.php';
?>
