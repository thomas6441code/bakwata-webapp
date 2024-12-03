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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id']) && isset($data['isRead'])) {
        $id = $data['id'];
        $isRead = $data['isRead'] ? 1 : 0;

        $sql = "UPDATE message SET isRead = ? WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ii", $isRead, $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update message status."]);
        }

        $stmt->close();
        exit;
    }
}
