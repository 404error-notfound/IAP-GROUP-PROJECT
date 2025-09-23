<?php
// logout.php
require_once 'AuthController.php';

$auth = new AuthController();
$auth->logout();

// Redirect to login page with a success message
$_SESSION['flash_messages'] = ["You have been logged out successfully."];
header("Location: login.php");
exit;
?>