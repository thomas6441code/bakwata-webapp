<?php
$current_page = $_SERVER['REQUEST_URI'];
$title = "BAKWATA | HOME";

switch ($current_page) {
    case '/':
        $title = $title;
        break;
    case '/#aboutus':
        $title = "ABOUT US";
        break;
    case '/projects.php':
        $title = "PROJECTS";
        break;
    case '/services.php':
        $title = "SERVICES";
        break;
    case '/events.php':
        $title = "NEWS UPDATES";
        break;
    case '/contactus.php':
        $title = "CONTACT US";
        break;
    default:
        $title = "BAKWATA | HOME";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
     <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./src/output.css">
    <link rel="stylesheet" href="./public/fontawesome/css/all.min.css">
    <link rel="icon" href="./favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        /* Custom scrollbar-hide class */
        .scrollbar-hide::-webkit-scrollbar {
          display: none;
        }
        .scrollbar-hide {
          -ms-overflow-style: none; /* Internet Explorer 10+ */
          scrollbar-width: none;    /* Firefox */
        }


        .h-100 {
            height: 34.5rem;
        }

        @media (min-width: 768px) {
            .md\:h-\[20rem\] {
                height: 26rem;
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
    
        .bg-opacity-50 {
        --tw-bg-opacity: 0.5;
       }

    #sliding-text {
        transition: transform 1s ease-in-out;
    }
    </style>
</head>

<body>
    <nav class="bg-gray-900 text-white fixed top-0 ring-0 z-50 w-full">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center lg:justify-center justify-between md:justify-center h-20 mx-10">
                <!-- Logo -->
                <div class="flex-shrink-0 md:ml-5">
                    <img src="./public/images/bakwata.png" alt="Logo" class="md:h-20 h-16 w-16 md:w-20 animate-spinn">
                </div>

                <!-- Menu Items -->
                <div class="hidden md:flex space-x-4 ml-32 mx-24 text-sm items-center">
                    <a href="/" class="text-teal-500 hover:text-teal-400">HOME</a>
                    <a href="/#aboutus" class="hover:text-gray-300">ABOUT US</a>
                    <a href="https://bakwatahajjumrah.or.tz/" target="_blank" class="hover:text-gray-300">HAJJ & UMRAH</a>
                    <a href="https://bakaid.or.tz" target="_blank" class="hover:text-gray-300">BAKAID</a>
                    <a href="projects.php" class="hover:text-gray-300">PROJECTS</a>
                    <a href="services.php" class="hover:text-gray-300">SERVICES</a>
                    <a href="events.php" class="hover:text-gray-300">NEW UPDATES</a>
                    <a href="contactus.php" class="hover:text-gray-300">CONTACT US</a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center text-green-600 md:hidden">
                    <button onclick="toggleMenu()" id="menu-icon" class="focus:outline-none text-2xl">
                        <i id="menu-icon-bar" class="fas fa-bars text-green-600"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden bg-gray-900 hidden">
            <ul class="space-y-3 p-4">
                <li><a href="/" class="block hover:text-gray-300">HOME</a></li>
                <li><a href="/#aboutus" class="block hover:text-gray-300">ABOUT US</a></li>
                <li><a href="https://bakwatahajjumrah.or.tz/" target="_blank" class="block hover:text-gray-300">HAJJ & UMRAH</a></li>
                <li><a href="https://bakaid.or.tz" target="_blank" class="block hover:text-gray-300">BAKAID</a></li>
                <li><a href="projects.php" class="block hover:text-gray-300">PROJECTS</a></li>
                <li><a href="services.php" class="block hover:text-gray-300">SERVICES</a></li>
                <li><a href="events.php" class="block hover:text-gray-300">NEW UPDATES</a></li>
                <li><a href="contactus.php" class="block hover:text-gray-300">CONTACT US</a></li>
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
    </script>