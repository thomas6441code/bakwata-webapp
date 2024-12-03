<?php
include '../assets/db.php';

$mysqli = $conn;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id']) && isset($data['model'])) {
        $id = (int)$data['id']; // Ensure id is an integer
        $model = $data['model'];

        // Define the valid models
        $validModels = [
            'slide',
            'photogallery',
            'missionvision',
            'message',
            'project',
            'event',
            'videos',
            'service',
            'corevalues',
        ];

        // Check if the model is valid
        if (in_array($model, $validModels)) {
            // Prepare the SQL statement based on the model
            $sql = "DELETE FROM $model WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo json_encode(["success" => true]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to delete record."]);
            }

            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Invalid model specified."]);
        }
        exit;
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Invalid input."]);
    }
}

// Close the database connection
$mysqli->close();
