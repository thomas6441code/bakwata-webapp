<?php
include './includes/header.php';
include './assets/db.php';

// Initialize variables for form data and messages
$formData = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'subject' => '',
    'message' => ''
];
$success = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name'] = $_POST['name'];
    $formData['email'] = $_POST['email'];
    $formData['phone'] = $_POST['phone'];
    $formData['address'] = $_POST['address'];
    $formData['subject'] = $_POST['subject'];
    $formData['message'] = $_POST['message'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO message (fullName, email, phone, address, subject, message) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $formData['name'], $formData['email'], $formData['phone'], $formData['address'], $formData['subject'], $formData['message']);

    // Execute the statement
    if ($stmt->execute()) {
        $success = true;
        // Clear form data
        $formData = [
            'name' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'subject' => '',
            'message' => ''
        ];
    } else {
        $success = false;
    }

    $stmt->close();
}

$conn->close();
?>

<div class="bg-gray-100 min-h-screen text-cyan-950 pb-5 mt-20">
    <h1 class="text-2xl font-bold text-center  py-3 mb-10">OUR LOCATION</h1>

    <!-- Google Map Component Placeholder -->
    <div class="w-full h-96 flex justify-center items-center px-8 lg:px-12 pb-0 lg:pb-10">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126778.75818911377!2d39.12379204335937!3d-6.789778099999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x185c4d6c51b218b3%3A0x46a73df628844d78!2sMsikiti%20mkuu%20wa%20Bakwata%20Kinondoni%20(Darusselam%20Camii)!5e0!3m2!1sen!2stz!4v1732093684034!5m2!1sen!2stz"
            class="border-0 md:w-[85%] w-[100%]  h-[25rem] mb-5 shadow-md" loading="lazy">
        </iframe>
    </div>

    <h1 class="text-2xl font-bold text-center md:mb-6 my-6">REACH US</h1>

    <div class="container mb-20 mx-auto px-6 md:px-16 grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Contact Form Section -->
        <div class="bg-white p-10 rounded-lg shadow-md">
            <h1 class="text-lg text-center font-semibold mb-8">CONTACT US</h1>
            <form method="POST" class="space-y-3">
                <div class="grid grid-cols-1 text-cyan-950 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-gray-600 text-sm mb-1">Name</label>
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($formData['name']); ?>" required class="w-full p-2 border rounded text-sm focus:outline-none focus:ring focus:border-blue-300" />
                    </div>
                    <div>
                        <label for="email" class="block text-gray-600 text-sm mb-1">Email</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required class="w-full p-2 border rounded text-sm focus:outline-none focus:ring focus:border-blue-300" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-gray-600 text-sm mb-1">Phone</label>
                        <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>" required class="w-full p-2 border rounded text-sm focus:outline-none focus:ring focus:border-blue-300" />
                    </div>
                    <div>
                        <label for="address" class="block text-gray-600 text-sm mb-1">Address</label>
                        <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($formData['address']); ?>" required class="w-full p-2 border rounded text-sm focus:outline-none focus:ring focus:border-blue-300" />
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-gray-600 text-sm mb-1">Subject</label>
                    <input type="text" name="subject" id="subject" value="<?php echo htmlspecialchars($formData['subject']); ?>" required rows="2" class="w-full p-2 border rounded text-sm focus:outline-none focus:ring focus:border-blue-300" />
                </div>

                <div>
                    <label for="message" class="block text-gray-600 text-sm mb-1">Message</label>
                    <textarea name="message" id="message" required rows="5" class="w-full p-2 border rounded text-sm focus:outline-none focus:ring focus:border-blue-300"><?php echo htmlspecialchars($formData['message']); ?></textarea>
                </div>

                <?php if ($success === true): ?>
                    <p class="mt-2 text-green-500 text-sm">Message sent successfully!</p>
                <?php elseif ($success === false): ?>
                    <p class="mt-2 text-red-500 text-sm">Failed to send message.</p>
                <?php endif; ?>

                <div class="text-right py-3">
                    <button type="submit" class="px-3 py-3 text-sm text-white bg-blue-500 rounded focus:outline-none focus:ring hover:bg-blue-600">
                        Send Message
                    </button>
                </div>
            </form>
        </div>

        <!-- Contact Information Section -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-2 gap-4">
            <div class="bg-white p-4 text-center flex flex-col items-center justify-center rounded-lg shadow-md">
                <i class="fas fa-map-marker-alt text-3xl text-blue-500 mb-3"></i>
                <h3 class="text-lg font-semibold mb-2">ADDRESS</h3>
                <p class="text-gray-600 lg:text-[.78rem] text-sm md:text-[.78rem]">P.O.BOX 21422, Dar es Salaam</p>
            </div>

            <div class="bg-white p-4 text-center flex flex-col items-center justify-center rounded-lg shadow-md">
                <i class="fas fa-clock text-3xl text-blue-500 mb-3"></i>
                <h3 class="text-lg font-semibold mb-2">WORKING HOURS</h3>
                <p class="text-gray-600 lg:text-[.78rem] text-sm md:text-[.78rem]">Mon - Thu: 8:00AM to 4:00PM</p>
                <p class="text-gray-600 lg:text-[.78rem] text-sm md:text-[.78rem]">Saturday: 8:00AM to 2:00PM</p>
            </div>

            <div class="bg-white p-4 text-center flex flex-col items-center justify-center rounded-lg shadow-md">
                <i class="fas fa-phone text-3xl text-blue-500 mb-3"></i>
                <h3 class="text-lg font-semibold mb-2">CALL US</h3>
                <p class="text-gray-600 lg:text-[.78rem] text-sm md:text-[.78rem]">GS: Nuhu Jabir Mruma</p>
                <p class="text-gray-600 lg:text-[.78rem] text-sm md:text-[.78rem]">+255717082543 / +255754453200</p>
            </div>

            <div class="bg-white p-4 text-center flex flex-col items-center justify-center rounded-lg shadow-md">
                <i class="fas fa-phone text-3xl text-blue-500 mb-3"></i>
                <h3 class="text-lg font-semibold mb-2">CALL US</h3>
                <p class="text-gray-600 lg:text-[.78rem] text-sm md:text-[.78rem]">CA: Mwenda Said Mwenda</p>
                <p class="text-gray-600 lg:text-[.78rem] text-sm md:text-[.78rem]">+255717011207 / +255620820630</p>
            </div>

            <div class="bg-white p-4 text-center flex flex-col items-center justify-center rounded-lg shadow-md">
                <i class="fas fa-phone text-3xl text-blue-500 mb-3"></i>
                <h3 class="text-lg font-semibold mb-2">CALL US</h3>
                <p class="text-gray-600 lg:text-[.78rem] text-sm md:text-[.78rem]">Suluisho La Migogoro <br /> (BAKWATA)</p>
                <p class="text-gray-600 lg:text-[.78rem] text-sm md:text-[.78rem]">+255655763792</p>
            </div>

            <div class="bg-white p-3 text-center flex flex-col items-center justify-center rounded-lg shadow-md">
                <i class="fas fa-envelope text-3xl text-blue-500 mb-3"></i>
                <h3 class="text-lg font-semibold mb-2">EMAIL US</h3>
                <p class="text-gray-600 lg:text-[.78rem] text-sm md:text-[.78rem]">info@bakwata.or.tz</p>
                <p class="text-gray-600 lg:text-[.78rem] break-words text-sm md:text-[.78rem]">muftitanzania @bakwata.or.tz</p>
                <p class="text-gray-600 lg:text-[.78rem] text-sm md:text-[.78rem]">secretarygeneral @bakwata.or.tz</p>
            </div>
        </div>
    </div>
</div>

<?php
include "./includes/footer.php";
?>