<?php
session_save_path($_SERVER['DOCUMENT_ROOT'] . '/sessions');
session_start();
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session
header('Location: login.php'); // Redirect to login page
exit;
?>