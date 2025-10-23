<?php
// logout.php
require_once __DIR__ . '/../src/Controllers/AuthController.php';
use Angel\IapGroupProject\Controllers\AuthController;
$auth = new AuthController();
$auth->logout();

// Redirect to login page with a success message
$_SESSION['flash_messages'] = ["You have been logged out successfully."];
header("Location: login.php");
exit;
?>