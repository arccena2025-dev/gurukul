<?php
/**
 * ========================================================
 * SECURITY & AUTHENTICATION MIDDLEWARE (GURUKUL)
 * ========================================================
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    // Enable secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    // Set secure cookie parameter if accessed over HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}

// 1. Session Hijacking / Fixation Prevention
if (isset($_SESSION['admin_logged_in'])) {
    // Regenerate session id periodically to prevent fixation
    if (!isset($_SESSION['session_created']) || time() - $_SESSION['session_created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['session_created'] = time();
    }
    
    // Check if browser user agent or IP address changed
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'] || $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
        // Potential hijack attempt - clear session immediately
        session_unset();
        session_destroy();
        header("Location: login.php?err=hijack");
        exit();
    }
}

// 2. Access Protection Guard Check
function check_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }
    
    // 3. Force Password Change Policy on First Login
    if (isset($_SESSION['is_first_login']) && $_SESSION['is_first_login'] == 1) {
        $currentPage = basename($_SERVER['PHP_SELF']);
        if ($currentPage !== 'profile.php' && $currentPage !== 'logout.php') {
            header("Location: profile.php?first_login=1");
            exit();
        }
    }
}

// 4. Input Sanitization Helpers (XSS Protection)
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

// 5. CSRF Defense Mechanics
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
?>
