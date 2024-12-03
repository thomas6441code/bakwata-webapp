<?php
$host = 'localhost';
$username = 'bakaid_bakaid';
$password = '@1Godisgood...';
$dbname = 'bakaid_db_test';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>