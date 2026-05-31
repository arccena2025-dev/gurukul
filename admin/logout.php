<?php
/**
 * ========================================================
 * SECURE LOGOUT HANDLER (GURUKUL)
 * ========================================================
 */

require_once 'includes/auth.php';

// Unset all session variables
$_SESSION = [];

// Destroy session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session execution
session_destroy();

// Redirect back to login page
header("Location: login.php");
exit();
?>
