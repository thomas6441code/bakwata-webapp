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
include './includes/header.php';

// Fetch statistics
$stats = [
    'totalSubscribers' => 0,
    'unreadSubscribers' => 0,
    'totalMessages' => 0,
    'unreadMessages' => 0,
    'projects' => 0,
    'events' => 0,
];

// Query to get total subscribers
$result = $conn->query("SELECT COUNT(*) as total FROM subscriber");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['totalSubscribers'] = $row['total'];
}

// Query to get unread subscribers
$result = $conn->query("SELECT COUNT(*) as total FROM subscriber WHERE isRead = '0'");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['unreadSubscribers'] = $row['total'];
}

// Query to get total messages
$result = $conn->query("SELECT COUNT(*) as total FROM message");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['totalMessages'] = $row['total'];
}

// Query to get unread messages
$result = $conn->query("SELECT COUNT(*) as total FROM message WHERE isRead = '0'");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['unreadMessages'] = $row['total'];
}

// Query to get total projects
$result = $conn->query("SELECT COUNT(*) as total FROM project");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['projects'] = $row['total'];
}

// Query to get total events
$result = $conn->query("SELECT COUNT(*) as total FROM event");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['events'] = $row['total'];
}

$conn->close();
?>

<div class="min-h-screen w-full bg-gray-50 p-6 text-gray-800">
    <h1 class="text-3xl text-left font-bold mb-8 md:text-center">Admin Dashboard</h1>

    <!-- Statistics Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
        <div class="bg-white p-6 shadow-lg rounded-lg transition-transform transform hover:scale-105">
            <h2 class="text-gray-600 text-lg font-semibold">Subscribers</h2>
            <p class="text-2xl font-bold text-blue-600">Total: <?= $stats['totalSubscribers'] ?></p>
            <p class="text-xl text-blue-400">Unread: <?= $stats['unreadSubscribers'] ?></p>
        </div>
        <div class="bg-white p-6 shadow-lg rounded-lg transition-transform transform hover:scale-105">
            <h2 class="text-gray-600 text-lg font-semibold">Messages</h2>
            <p class="text-2xl font-bold text-green-600">Total: <?= $stats['totalMessages'] ?></p>
            <p class="text-xl text-green-400">Unread: <?= $stats['unreadMessages'] ?></p>
        </div>
        <div class="bg-white p-6 shadow-lg rounded-lg transition-transform transform hover:scale-105">
            <h2 class="text-gray-600 text-lg font-semibold">Projects</h2>
            <p class="text-3xl font-bold text-yellow-600"><?= $stats['projects'] ?></p>
        </div>
        <div class="bg-white p-6 shadow-lg rounded-lg transition-transform transform hover:scale-105">
            <h2 class="text-gray-600 text-lg font-semibold">Events</h2>
            <p class="text-3xl font-bold text-red-600"><?= $stats['events'] ?></p>
        </div>
    </div>

    <!-- Graphs -->
    <div class="grid mb-5 grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Bar Chart -->
        <div class="bg-white p-6 shadow-lg rounded-lg">
            <h3 class="text-gray-600 font-semibold mb-4 text-lg">Statistics Overview</h3>
            <canvas id="barChart"></canvas>
        </div>

        <!-- Pie Chart -->
        <div class="bg-white p-6 shadow-lg rounded-lg">
            <h3 class="text-gray-600 font-semibold mb-4 text-lg">Proportion of Entities</h3>
            <canvas id="pieChart"></canvas>
        </div>
    </div>
</div>

<script>
    const ctxBar = document.getElementById('barChart').getContext('2d');
    const ctxPie = document.getElementById('pieChart').getContext('2d');

    const chartLabels = ['Subscribers', 'Messages', 'Projects', 'Events'];
    const chartValues = [<?= $stats['totalSubscribers'] ?>, <?= $stats['totalMessages'] ?>, <?= $stats['projects'] ?>, <?= $stats['events'] ?>];
    const chartColors = ['#3B82F6', '#22C55E', '#FBBF24', '#EF4444'];

    // Bar Chart
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Count',
                backgroundColor: chartColors,
                borderColor: '#FFFFFF',
                borderWidth: 1,
                data: chartValues,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 14,
                            weight: 'bold',
                        },
                    },
                },
            },
            scales: {
                x: {
                    ticks: {
                        color: '#4B5563',
                    },
                },
                y: {
                    ticks: {
                        color: '#4B5563',
                    },
                },
            },
        },
    });

    // Pie Chart
    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: chartLabels,
            datasets: [{
                backgroundColor: chartColors,
                data: chartValues,
                borderColor: '#FFFFFF',
                borderWidth: 1,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 14,
                            weight: 'bold',
                        },
                    },
                },
            },
        },
    });
</script>
</body>

</html>