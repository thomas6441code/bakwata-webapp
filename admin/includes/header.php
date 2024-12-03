<?php
session_start(); // Start the session to manage user authentication

// Function to handle cookie sign out
function handleCookieSignOut()
{
    setcookie('token', '', time() - 3600, '/'); // Delete the token cookie
    header('Location: /login'); // Redirect to login page
    exit();
}

// Check if the logout button was clicked
if (isset($_POST['logout'])) {
    handleCookieSignOut();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN | NAVBAR</title>
    <link rel="icon" href="./favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="../public/fontawesome/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }

        .h-100 {
            height: 31rem;
        }

        @media (min-width: 768px) {
            .md\:h-\[20rem\] {
                height: 25rem;
            }
        }

        @media (max-width: 900px) {
            .h-100 {
                height: 20rem;
            }
        }

        @media (max-width: 768px) {
            .h-100 {
                height: 15rem;
            }
        }

        .slider {
            position: relative;
            overflow: hidden;
        }

        .slide {
            display: none;
        }

        .slide.active {
            display: block;
        }

        .h-72 {
            height: 28rem;
        }

        @keyframes spinn {
            to {
                transform: rotate(360deg);
            }
        }

        .animate-spinn {
            animation: spin 2.5s linear infinite;
        }
    </style>
</head>

<body>
    <nav class="bg-gray-900 text-white fixed top-0 ring-0 z-50 w-full">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center sm:mx-10 lg:justify-center justify-between md:justify-center h-20 md:ml-10 ml-4 mr-5">

                <!-- Logo -->
                <div class="flex-shrink-0 animate-spinn">
                    <img src="../public/images/bakwata.png" alt="Logo" class="h-20 w-20">
                </div>

                <!-- Menu Items (hidden on small screens) -->
                <div class="hidden md:flex space-x-4 mx-28 text-[0.73rem] items-center">
                    <a href="/admin" class="text-teal-500 hover:text-teal-400">HOME</a>
                    <a href="aboutus.php" class="hover:text-gray-300">ABOUTUS</a>
                    <a href="slides.php" class="hover:text-gray-300">SLIDES</a>
                    <a href="photogallery.php" class="hover:text-gray-300">GALLERY</a>
                    <a href="mission.php" class="hover:text-gray-300">MISSION</a>
                    <a href="videos.php" class="hover:text-gray-300">VIDEOS</a>
                    <a href="subscriber.php" class="hover:text-gray-300">SUBSCRIBERS</a>
                    <a href="project.php" class="hover:text-gray-300">PROJECTS</a>
                    <a href="service.php" class="hover:text-gray-300">SERVICES</a>
                    <a href="event.php" class="hover:text-gray-300">NEWS</a>
                    <a href="messages.php" class="hover:text-gray-300">MESSAGES</a>
                </div>

                <!-- Right: BAKAID Button -->
                <div class="lg:flex md:flex hidden items-center">
                    <button  onclick="logout()" name="logout" class="bg-red-500 hover:bg-red-600 text-sm px-3 py-2 text-white rounded">
                        LOG OUT
                    </button>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button onclick="toggleMenu()" id="menu-icon" class="text-white focus:outline-none text-2xl">
                        <i id="menu-icon-bar" class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu (only visible when toggled) -->
        <div id="mobile-menu" class="md:hidden bg-gray-900 text-sm hidden">
            <ul class="space-y-4 p-4">
                <li>
                    <a href="/admin" class="block hover:text-gray-300">HOME</a>
                </li>
                <li>
                    <a href="aboutus.php" class="block hover:text-gray-300">ABOUTUS</a>
                </li>
                <li>
                    <a href="mission.php" class="block hover:text-gray-300">MISSION</a>
                </li>
                <li>
                    <a href="videos.php" class="block hover:text-gray-300">VIDEOS</a>
                </li>
                <li>
                    <a href="slides.php" class="block hover:text-gray -300">SLIDES</a>
                </li>
                <li>
                    <a href="photogallery.php" class="block hover:text-gray -300">GALLERY</a>
                </li>
                <li>
                    <a href="subscriber.php" class="block hover:text-gray-300">SUBSCRIBERS</a>
                </li>
                <li>
                    <a href="project.php" class="block hover:text-gray-300">PROJECTS</a>
                </li>
                <li>
                    <a href="service.php" class="block hover:text-gray-300">SERVICES</a>
                </li>
                <li>
                    <a href="event.php" class="block hover:text-gray-300">NEWS</a>
                </li>
                <li>
                    <a href="messages.php" class="block hover:text-gray-300">MESSAGES</a>
                </li>
                <li>
                    <button  onclick="logout()" name="logout" class="bg-red-500 hover:bg-red-600 text-sm px-3 py-2 text-white rounded">
                        LOG OUT
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    <script>
        // Toggle menu visibility
        let isMenuOpen = false;

        function toggleMenu() {
            isMenuOpen = !isMenuOpen;
            const menu = document.getElementById('mobile-menu');
            const menuIconBar = document.getElementById('menu-icon-bar');

            if (isMenuOpen) {
                menu.classList.remove('hidden');
                menuIconBar.classList.replace('fa-bars', 'fa-times');
            } else {
                menu.classList.add('hidden');
                menuIconBar.classList.replace('fa-times', 'fa-bars');
            }
        }
        
        function logout() {
         window.location.href = 'logout.php';
        }
    </script>