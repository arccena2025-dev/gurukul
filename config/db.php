<?php
/**
 * ========================================================
 * SECURE DATABASE CONNECTION & CONFIGURATION (GURUKUL)
 * ========================================================
 */

// Database Hostinger shared hosting parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'gurukul_db');
define('DB_USER', 'root');
define('DB_PASS', 'GurukulLocal2026!');

// Options for secure PDO connections
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use true prepared statements
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"     // Force UTF-8 character encoding
];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $options
    );
} catch (PDOException $e) {
    // Graceful secure failure - hide credential details in production logs
    die("Database Connection Failure: Contact the technical administrator immediately.");
}

// Global Sanitization Helper (XSS Protection)
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
?>
