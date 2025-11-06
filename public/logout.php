<?php
// logout.php
// Location: public/logout.php
// MUST start session first before destroying it!
session_start();

// Destroy all session data
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Start a new session for the flash message
session_start();
$_SESSION['flash_messages'] = ["You have been logged out successfully."];

// Redirect to login page
header("Location: login.php");
exit;