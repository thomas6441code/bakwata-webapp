<?php
session_save_path($_SERVER['DOCUMENT_ROOT'] . '/sessions');
session_start();
include '../assets/db.php';

$mysqli = $conn;
$error = "";

// Check if the form was submitted and if 'ad_email' and 'ad_password' are set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ad_email']) && isset($_POST['ad_password'])) {
    // Get the user input
    $email = $_POST['ad_email'];
    $password = $_POST['ad_password'];

    // Basic validation for email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Prepare SQL query to check if the email exists
        $stmt = $mysqli->prepare("SELECT id, email, password FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Check if the email is found in the database
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $dbEmail, $dbPassword);
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $dbPassword)) {
                // After successful login
                $_SESSION['user_id'] = $id;
                $_SESSION['last_activity'] = time();
                header('Location: index.php');
                exit;
            } else {
                $error = "Incorrect password";
            }
        } else {
            $error = "Email not found";
        }

        $stmt->close();
    }
}

// Close the database connection
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN PAGE</title>
    <link href="../src/output.css" rel="stylesheet">
    <script src="../public/fontawesome/css/all.min.css" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }

        .animate-spin {
            animation: spin 3s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen pb-14 pt-20  px-10">
    <div class="bg-white relative py-10 px-10 rounded-lg shadow-lg  md:w-1/3 w-full mx-auto text-center">
        <div class="absolute -top-1/4 left-1/2 transform -translate-x-1/2 translate-y-1/2 bg-white p-3 h-36 w-36 rounded-full flex justify-center items-center">
            <div class="border-green-700 rounded-full border-4 h-full w-full">
                <img src="../public/images/bakwata.png" alt="Logo" class="animate-spin p-3 h-28 w-32">
            </div>
        </div>

        <h1 class='mt-20 text-green-700 font-semibold mb-5 text-xl'>LOGIN</h1>

        <!-- Display error message if exists -->
        <?php if ($error): ?>
            <p class="mb-4 text-red-500"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="relative mb-4">
                <!-- User Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-green-700 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                </svg>
                <input
                    type="email"
                    name="ad_email"
                    placeholder="Email"
                    required
                    class="w-full pl-10 p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-green-500" />
            </div>

            <div class="relative mb-6">
                <!-- Lock Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-green-700 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 17v1m6-7v-3a6 6 0 10-12 0v3m-2 0h16m-5 0v6a2 2 0 01-2 2h-2a2 2 0 01-2-2v-6" />
                </svg>
                <input
                    type="password"
                    name="ad_password"
                    placeholder="Password"
                    required
                    class="w-full pl-10 p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-green-500" />
            </div>

            <button
                type="submit"
                class="w-full bg-green-800 text-white p-2 rounded-lg hover:bg-green-700 transition duration-200 flex items-center justify-center gap-2">
                <!-- Login Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6l6 6-6 6" />
                </svg>
                Login
            </button>

        </form>

        <div class="mt-4">
            <!--  <a href="forgot_password.php" class="text-green-700 hover:underline">Forgot Password</a>-->
        </div>
        <div class="mt-4">
            <a href="/" class="text-green-700 hover:underline">Back Home</a>
        </div>
    </div>
</body>

</html>