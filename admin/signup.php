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

$mysqli = $conn;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the input data
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode(['error' => 'Email and password are required.']);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL statement to prevent SQL injection
    $stmt = $mysqli->prepare("INSERT INTO admin (ad_email, ad_password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashedPassword);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['message' => 'Signup successful! You can login.']);
    } else {
        echo json_encode(['error' => 'Error creating account.']);
    }

    // Close the statement
    $stmt->close();
    // Close the database connection
    $mysqli->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup Page</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
            <img src="../public/images/logo.png" alt="Logo" class="animate-spin p-3 h-28 w-32">
        </div>
    </div>

    <h1 class='mt-24 text-green-700 font-semibold mb-5 text-xl'>SIGNUP</h1>

    <form id="signupForm" method="POST">
      <div class="relative mb-4">
        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-green-700"></i>
        <input type="email" name="email" id="email" placeholder="Email" required
          class="w-full text-green-900 pl-8 p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-green-500" />
      </div>

      <div class="relative mb-6">
        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-green-700"></i>
        <input type="password" name="password" id="password" placeholder="Password" required
          class="w-full text-green-900 pl-8 p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-green-500" />
      </div>

      <p id="messageSuccess" class="mb-4 text-left text-sm text-green-500 hidden"></p>
      <p id="messageError" class="mb-4 text-left text-sm text-red-500 hidden "></p>

      <button type="submit" id="signupButton"
        class="w-full bg-green-700 text-white p-2 rounded-lg hover:bg-green-800 transition duration-200">
        Sign up
      </button>
    </form>

    <div class="mt-4">
    <p >
    Already have an account,
      <a href="login.php" class="text-green-700 hover:underline">Log in</a>
    </p>
    </div>
  </div>

  <script>
    document.getElementById('signupForm').addEventListener('submit', async function (e) {
      e.preventDefault();

      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
      const signupButton = document.getElementById('signupButton');
      const messageSuccess = document.getElementById('messageSuccess');
      const messageError = document.getElementById('messageError');

      messageSuccess.classList.add('hidden');
      messageError.classList.add('hidden');
      signupButton.textContent = 'Signing...';
      signupButton.disabled = true;

      try {
        const res = await fetch('signup.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({ email, password }),
        });

        if (res.ok) {
          const data = await res.json();
          messageSuccess.textContent = data.message || 'Signup successful! You can login.';
          messageSuccess.classList.remove('hidden');
          window.location.href = 'login.php';
        } else {
          const data = await res.json();
          messageError.textContent = data.error || 'Error creating account.';
          messageError.classList.remove('hidden');
        }
      } catch (error) {
        messageError.textContent = 'Network error. Please try again.';
        messageError.classList.remove('hidden');
      } finally {
        signupButton.textContent = 'Sign up';
        signupButton.disabled = false;
      }
    });
  </script>
</body>

</html>