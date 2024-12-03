<?php
include './includes/header.php';
include './assets/db.php';

// Variables to hold data
$mission = $vission = $values = "";
$objectives = [];
$success = $error = null;

// Handle GET requests for mission, vision, and objectives
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $missionVisionQuery = "SELECT mission, vision, `Cvalues` FROM missionvision";
    $objectivesQuery = "SELECT objectives FROM corevalues";

    $missionVisionResult = $conn->query($missionVisionQuery);
    if ($missionVisionResult && $missionVisionResult->num_rows > 0) {
        $row = $missionVisionResult->fetch_assoc();
        $mission = $row['mission'];
        $vission = $row['vision'];
        $values = $row['Cvalues'];
    }

   $objectivesResult = $conn->query($objectivesQuery);
    if ($objectivesResult && $objectivesResult->num_rows > 0) {
        while ($row = $objectivesResult->fetch_assoc()) {
            $objectives[] = $row['objectives'];
        }
    } else {
        $error = "Failed to fetch objectives.";
    }
}

$objectivesJson = json_encode($objectives);

// Handle POST request for newsletter subscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    if (empty($email)) {
        $error = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // Check if email exists
        $checkQuery = "SELECT id FROM subscriber WHERE email = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $error = "This email is already subscribed.";
        } else {
            $stmt = $conn->prepare("INSERT INTO subscriber (email) VALUES (?)");
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                $success = "Successfully Thank You!";
            } else {
                $error = "Failed to subscribe. Please try again.";
            }
            $stmt->close();
        }
    }
}


// Fetch slide data
$slidesData = [];
$sql = "SELECT id, title, description, image FROM slide ORDER BY id ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $slidesData[] = $row;
    }
} else {
    $error = "No slides available or failed to fetch slides.";
}

// Fetch slides
$slides = [];
$slidesQuery = "SELECT * FROM slide";
$slidesResult = $conn->query($slidesQuery);
while ($row = $slidesResult->fetch_assoc()) {
    $slides[] = $row;
}

// Pagination settings
$ITEMS_PER_PAGE = 15;
$ITEMS_PER_PAGEE = 6;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPagee = isset($_GET['mobilePage']) ? (int)$_GET['mobilePage'] : 1;

// Calculate offsets
$startIndex = ($currentPage - 1) * $ITEMS_PER_PAGE;
$mobileIndex = ($currentPagee - 1) * $ITEMS_PER_PAGEE;

// Fetch images for desktop
$sql = "SELECT * FROM photogallery LIMIT $startIndex, $ITEMS_PER_PAGE";
$result = $conn->query($sql);
$images = $result->fetch_all(MYSQLI_ASSOC);

// Fetch images for mobile
$sqlMobile = "SELECT * FROM photogallery LIMIT $mobileIndex, $ITEMS_PER_PAGEE";
$resultMobile = $conn->query($sqlMobile);
$mobileImages = $resultMobile->fetch_all(MYSQLI_ASSOC);

// Get total number of images
$totalImages = $conn->query("SELECT COUNT(*) AS total FROM photogallery")->fetch_assoc()['total'];
$totalPages = ceil($totalImages / $ITEMS_PER_PAGE);
$totalMobilePages = ceil($totalImages / $ITEMS_PER_PAGEE);

// Fetch random "About Us" data
$sql = "SELECT * FROM aboutus ORDER BY RAND() LIMIT 1";
$result = $conn->query($sql);

$aboutData = null;
if ($result && $result->num_rows > 0) {
    $aboutData = $result->fetch_assoc();
}

$conn->close();
?>

<div id="slides" class="relative w-full mt-20 slide hidden">
    <!-- Sliding Text -->
    <div id="sliding-text" class="absolute md:top-10 top-3 left-1/2 transform -translate-x-1/2 text-white font-bold text-sm md:text-4xl text-center px-4 py-2 rounded-lg z-30 transition-transform duration-1000 ease-in-out">
        THE NATIONAL MUSLIM COUNCIL OF TANZANIA
    </div>

    <div class="overflow-hidden shadow-lg h-100">
        <img id="slide-image" src="" alt="" class="object-top object-cover w-full h-full">
        <div class="absolute inset-0 flex justify-between items-center bg-gray-900 bg-opacity-50 text-white px-0 md:px-6">
            <div class="mt-20 md:mx-12 mx-5">
                <h2 id="slide-title" class="md:text-4xl text-xl font-bold md:mt-16 mt-5 md:mb-2"></h2>
                <p id="slide-description" class="md:text-xl text-sm"></p>
            </div>
        </div>
    </div>

    <!-- Numbered Navigation -->
    <div id="navigation" class="absolute hidden md:flex bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-4 z-20">
    </div>
</div>

<script>
    // Function to slide out and slide back in
    window.addEventListener("load", () => {
        const slidingText = document.getElementById("sliding-text");
        if (slidingText) {
            setInterval(() => {
                // Slide out
                slidingText.classList.add("translate-y-[-100px]");
                
                
                setTimeout(() => {
                    // Slide back in
                    slidingText.classList.remove("translate-y-[-100px]");
                }, 1000);

            }, 4000); 
        }
    });
</script>

<!-- Welcome Section -->
<div class="py-12 mt-5 px-4 lg:px-20 lg:text-left">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl font-bold mx-5 md:mx-0 text-gray-800 mb-4">WHO WE ARE</h2>
        <p class="md:text-lg text-[1rem] mx-5 md:mx-0 text-gray-600 mb-8">
            The National Muslim Council of Tanzania (BAKWATA) is a well-established
            faith-based Islamic organization registered since 1968, recognized both in
            Tanzania and internationally. In promoting unity, peace, and harmony, BAKWATA
            collaborates with various faith groups and local, national government organizations
            to achieve the following goals:
        </p>

        <div class="grid gap-10 lg:grid-cols-3 md:mx-0 mx-[4%] mt-10">
            <div class="p-6">
                <div class="text-orange-600 mb-4 flex items-center justify-center ">
                    <i class="fas fa-leaf text-green-600 text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    Sustainable Environmental Management
                </h3>
                <p class="text-gray-600">
                    Using religious teachings to promote sustainable
                    environmental management and the sustainable use
                    of natural resources.
                </p>
            </div>

            <div class="p-6">
                <div class="text-orange-600 mb-4 flex items-center justify-center">
                    <i class="fas fa-book text-blue-600 text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    Environmental Education
                </h3>
                <p class="text-gray-600">
                    Mainstreaming environmental education into the
                    curriculum to raise awareness on environmental
                    issues among students.
                </p>
            </div>

            <div class="p-6">
                <div class="text-orange-600 mb-4 flex items-center justify-center">
                    <i class="fas fa-hands-helping text-yellow-600 text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    Environmental Protection Activities
                </h3>
                <p class="text-gray-600">
                    Using extracurricular activities to promote
                    environmental protection and advocate for
                    alternative technologies.
                </p>
            </div>
        </div>

    </div>
</div>

<!-- Mission, Vision, and Values Section -->
<section class="py-5 lg:mx-[5%] px-3">
    <h2 class="text-2xl font-bold pb-10 pt-5 text-center text-cyan-950">OUR MISSION & VISION</h2>
    <div class="flex flex-col lg:flex-row justify-center items-center space-y-8 lg:space-y-0 lg:space-x-8">
        <div class="bg-white rounded shadow-md p-6 text-center">
            <h3 class="text-lg font-semibold text-cyan-950 mb-4">MISSION</h3>
            <p class="text-gray-700"><?php echo htmlspecialchars($mission); ?></p>
        </div>
        <div class="bg-white rounded shadow-md p-6 text-center">
            <h3 class="text-lg font-semibold text-cyan-950 mb-4">VISION</h3>
            <p class="text-gray-700"><?php echo htmlspecialchars($vission); ?></p>
        </div>
    </div>

    <div class="mt-6 rounded">
        <h2 class="text-2xl font-bold py-6 text-center text-cyan-950">OUR CORE VALUES</h2>
        <div class="bg-white rounded shadow-md p-8">
            <p class="text-cyan-950 text-lg mb-3 font-semibold"><?php echo htmlspecialchars($values); ?> </p>
            <?php foreach ($objectives as $index => $objective): ?>
                <p class="text-gray-800 py-1 text-[1rem]"><?php echo ($index + 1) . ". " . htmlspecialchars($objective); ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- About us Section -->
<div id="aboutus" class="bg-green-100 pt-12 mt-5 text-cyan-950">
    <section class="bg-green-100 text-start py-12">
        <h1 class="text-3xl text-center font-bold mb-4 text-green-800">
            ABOUT US
        </h1>

        <div id="about-content" class="container mx-auto px-4 fade-in">
            <h1 class="text-2xl text-center font-bold mb-4 text-green-900">
                <?= $aboutData['tittle'] ?? "Loading..." ?>
            </h1>

            <p class="text-[1rem] text-center max-w-3xl mx-auto mb-3 text-gray-800">
                <?= $aboutData['description1'] ?? "Loading..." ?>
            </p>

            <p class="text-[1rem] text-center max-w-3xl mx-auto mb-3 text-gray-800">
                <?= $aboutData['description2'] ?? "Loading..." ?>
            </p>

            <div class="text-center mt-8">
                <p class="text-xl font-semibold text-green-700 mb-3 quote">
                    "<?= $aboutData['quote'] ?? "Loading..." ?>"
                </p>
                <p class="text-lg italic text-gray-600 source">
                    <?= $aboutData['source'] ?? "" ?>
                </p>
            </div>
        </div>
    </section>
</div>

<!-- What we Do -->

<!-- What we Do -->
<div class="py-16 bg-cover md:h-[35rem] bg-center">
    <h2 class="text-3xl pt-8 font-extrabold text-center text-cyan-900 mb-8">WHAT WE DO</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 px-4 py-10 md:mx-[2%] mx-[5%] md:px-10">
        <!-- Card 1 -->
        <div class="group relative p-8 px-4 bg-white bg-opacity-80 shadow-2xl rounded-lg text-center flex flex-col items-center transition-transform transform hover:scale-105 hover:shadow-2xl">
            <div class="relative p-4 px-5 bg-green-100 rounded-full transition-transform transform group-hover:scale-125">
                <i class="fa fa-book text-green-600 text-4xl"></i>
            </div>
            <h3 class="relative mt-6 text-xl font-semibold text-gray-800 group-hover:text-green-900">
                Religious Teachings
            </h3>
            <p class="relative mt-4 text-gray-900 text-sm group-hover:text-green-900">
                Use religious teachings to promote sustainable environmental management and sustainable use of natural resources and other environmental issues.
            </p>
        </div>

        <!-- Card 2 -->
        <div class="group relative p-8 bg-white bg-opacity-80 shadow-2xl rounded-lg text-center flex flex-col items-center transition-transform transform hover:scale-105 hover:shadow-2xl">
            <div class="relative p-4 px-5 bg-blue-100 rounded-full transition-transform transform group-hover:scale-125">
                <i class="fa fa-tree text-4xl text-blue-900"></i>
            </div>
            <h3 class="relative mt-6 text-xl font-semibold text-gray-800 group-hover:text-blue-900">
                Environmental Education
            </h3>
            <p class="relative mt-4 text-gray-900 text-sm group-hover:text-blue-900">
                Mainstream environmental education into the curriculum.
            </p>
        </div>

        <!-- Card 3 -->
        <div class="group relative p-8 bg-white bg-opacity-80 shadow-2xl rounded-lg text-center flex flex-col items-center transition-transform transform hover:scale-105 hover:shadow-2xl">
            <div class="relative p-4 px-5 bg-purple-100 rounded-full transition-transform transform group-hover:scale-125">
                <i class="fa fa-check-square text-4xl text-purple-900"></i>
            </div>
            <h3 class="relative mt-6 text-xl font-semibold text-gray-800 group-hover:text-purple-900">
                Extracurricular Activities
            </h3>
            <p class="relative mt-4 text-gray-900 text-sm group-hover:text-purple-900">
                Use extracurricular activities to promote environmental protection and promote alternative technology.
            </p>
        </div>

        <!-- Card 4 -->
        <div class="group relative p-8 bg-white bg-opacity-80 shadow-2xl rounded-lg text-center flex flex-col items-center transition-transform transform hover:scale-105 hover:shadow-2xl">
            <div class="relative p-4 px-5 bg-red-100 rounded-full transition-transform transform group-hover:scale-125">
                <i class="fa fa-heart text-4xl text-red-900"></i>
            </div>
            <h3 class="relative mt-6 text-xl font-semibold text-gray-800 group-hover:text-red-900">
                Teaching Love and Harmony
            </h3>
            <p class="relative mt-4 text-gray-900 text-sm group-hover:text-red-900">
                Use religious teachings to teach and spread the word of love, harmony, and good values among the society.
            </p>
        </div>
    </div>
</div>

<!-- Last updates  -->
<?php include './includes/rescent.php' ?>

<!-- Gallery section  -->
<div class="max-w-[100vw] md:px-16 px-2 py-10 items-center justify-center flex-col">
    <h1 class="py-6 text-center text-3xl text-cyan-950 font-semibold">GALLERY</h1>

    <!-- Desktop Thumbnails -->
    <div class="hidden md:block">
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4 p-2">
            <?php foreach ($images as $image): ?>
                <img
                    src="./public/images/<?= htmlspecialchars($image['imageUrl']) ?>"
                    alt="<?= htmlspecialchars($image['title']) ?>"
                    class="w-full h-[11rem] object-cover cursor-pointer border"
                    onclick="openModal('<?= htmlspecialchars($image['imageUrl']) ?>', '<?= htmlspecialchars($image['title']) ?>', '<?= htmlspecialchars($image['description']) ?>', '<?= htmlspecialchars($image['createdAt']) ?>')">
            <?php endforeach; ?>
        </div>
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center my-4">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="mx-2 px-4 py-2 rounded <?= $i == $currentPage ? 'bg-green-700 text-white' : 'bg-gray-300 text-black' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Mobile Thumbnails -->
    <div class="block md:hidden">
        <div class="grid grid-cols-1 gap-3 p-2">
            <?php foreach ($mobileImages as $image): ?>
                <img
                    src="./public/images/<?= htmlspecialchars($image['imageUrl']) ?>"
                    alt="<?= htmlspecialchars($image['title']) ?>"
                    class="w-full h-[16rem] object-cover cursor-pointer border"
                    onclick="openModal('<?= htmlspecialchars($image['imageUrl']) ?>', '<?= htmlspecialchars($image['title']) ?>', '<?= htmlspecialchars($image['description']) ?>', '<?= htmlspecialchars($image['createdAt']) ?>')">
            <?php endforeach; ?>
        </div>
        <!-- Pagination -->
        <?php if ($totalMobilePages > 1): ?>
            <div class="flex justify-center my-4">
                <?php for ($i = 1; $i <= $totalMobilePages; $i++): ?>
                    <a href="?mobilePage=<?= $i ?>" class="mx-2 px-4 py-2 rounded <?= $i == $currentPagee ? 'bg-green-700 text-white' : 'bg-gray-300 text-black' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div id="modal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 z-50">
        <div class="bg-white rounded-lg p-2 mx-2 max-w-lg w-full relative">
            <button onclick="closeModal()" class="absolute top-2 right-2 bg-gray-300 font-bold rounded-full p-1 px-3">&times;</button>
            <img id="modalImage" src="" alt="" class="w-full h-auto mb-4">
            <h2 id="modalTitle" class="text-xl font-semibold"></h2>
            <p id="modalDate" class="text-gray-600"></p>
            <p id="modalDescription" class="mt-2 text-gray-700"></p>
        </div>
    </div>
</div>

<!-- Newsletter Subscription -->
<section class="bg-teal-500 bg-opacity-30 text-cyan-950 py-12">
    <div class="max-w-lg mx-auto text-center">
        <h2 class="lg:text-3xl text-lg font-bold mb-4">Subscribe to Our Newsletter and Books</h2>
        <p class="mb-4">Stay updated with our latest events and initiatives.</p>
        <form method="POST" class="space-y-4">
            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                class="text-cyan-950 w-[80%] p-3 border rounded-lg focus:outline-none focus:ring focus:border-cyan-700"
                required>
            <?php if ($success): ?>
                <p class="text-green-500"><?php echo htmlspecialchars($success); ?></p>
            <?php elseif ($error): ?>
                <p class="text-red-500"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <button
                type="submit"
                class="px-4 py-2 mb-5 bg-cyan-600 text-white rounded-lg hover:bg-cyan-500 focus:outline-none focus:ring">
                Subscribe
            </button>
        </form>
    </div>
</section>

<!-- Slides Scripts -->
<script>
    const slidesData = <?php echo json_encode($slides); ?>;
    let currentSlide = 0;
    let loading = true;

    const loadingslides = document.getElementById('slides');
    const slideImage = document.getElementById('slide-image');
    const slideTitle = document.getElementById('slide-title');
    const slideDescription = document.getElementById('slide-description');
    const navigation = document.getElementById('navigation');

    // Function to hide loading slides
    function hideLoadingSlides() {
        loadingslides.classList.remove('hidden');
    }

    // Function to display slides after a delay
    function displaySlidesAfterDelay() {
        setTimeout(() => {
            loading = false;
            hideLoadingSlides();
            displaySlide();
        }, 1);
    }

    // Display the current slide
    function displaySlide() {
        if (slidesData.length === 0) {
            slideTitle.textContent = 'No slides available.';
            return;
        }
        slideImage.src = `./public/images/${slidesData[currentSlide]?.image}`;
        slideImage.alt = slidesData[currentSlide]?.id;
        slideTitle.textContent = slidesData[currentSlide]?.title;
        slideDescription.textContent = slidesData[currentSlide]?.description;

        updateNavigation();
    }

    // Update navigation dots
    function updateNavigation() {
        navigation.innerHTML = '';
        slidesData.forEach((_, index) => {
            const navDot = document.createElement('div');
            navDot.className = `cursor-pointer ${currentSlide === index ? 'bg-green-800 text-white' : 'bg-gray-300 text-black'} md:w-10 w-6 md:h-10 h-6 flex items-center justify-center border-2 rounded-lg transition-all`;
            navDot.onclick = () => goToSlide(index);
            navDot.innerHTML = `<span class="skew-x-[-12deg]">${`0${index + 1}`}</span>`;
            navigation.appendChild(navDot);
        });
    }

    // Go to a specific slide
    function goToSlide(index) {
        currentSlide = index;
        displaySlide();
    }

    // Automatic slide change
    setInterval(() => {
        if (!loading) {
            currentSlide = (currentSlide + 1) % slidesData.length;
            displaySlide();
        }
    }, 5000);

    displaySlidesAfterDelay();
</script>

<script>
    function openModal(imageUrl, title, description, createdAt) {
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('modalImage').src = `./public/images/${imageUrl}`;
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalDescription').innerText = description;
        document.getElementById('modalDate').innerText = new Date(createdAt).toLocaleDateString();
    }

    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }
</script>

<?php
include "./includes/footer.php";
?>