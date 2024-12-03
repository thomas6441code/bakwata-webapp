<?php
include './assets/db.php';

// Fetch random "About Us" data
$sql = "SELECT * FROM aboutus ORDER BY RAND() LIMIT 1";
$result = $conn->query($sql);

$aboutData = null;
if ($result && $result->num_rows > 0) {
    $aboutData = $result->fetch_assoc();
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($aboutData);
