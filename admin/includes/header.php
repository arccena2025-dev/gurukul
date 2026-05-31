<?php
/**
 * ========================================================
 * ADMIN PORTAL HEADER COMPONENT (GURUKUL)
 * ========================================================
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/auth.php';

// Enforce authentication controls
check_auth();

// Determine dynamic page name/title
$page_name = basename($_SERVER['PHP_SELF'], '.php');
$display_title = ucwords(str_replace('_', ' ', $page_name));
if ($page_name === 'index') {
    $display_title = 'Dashboard Overview';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $display_title; ?> | Gurukul Admin Panel</title>
    
    <!-- Modern typography styling -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Admin Dashboard Main Stylesheet -->
    <link rel="stylesheet" href="css/admin-style.css?v=1.0">
</head>
<body>
    <script>
        if (localStorage.getItem('gurukul_admin_theme') === 'light') {
            document.body.classList.add('light-theme-active');
        }
    </script>

    <!-- Ambient background highlights -->
    <div class="ambient-glow glow-1"></div>
    <div class="ambient-glow glow-2"></div>

    <div class="admin-wrapper">
        
        <?php include_once 'sidebar.php'; ?>
        
        <div class="admin-main">
            
            <header class="admin-header">
                <div class="header-title">
                    <h2><?php echo $display_title; ?></h2>
                </div>
                
                <div class="header-user">
                    <div class="user-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span>Logged in as <strong><?php echo sanitize($_SESSION['admin_username']); ?></strong></span>
                    </div>
                    
                    <button id="admin-theme-toggle" class="theme-toggle-btn" title="Toggle Light/Dark Theme" aria-label="Toggle Light/Dark Theme">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="theme-icon-svg"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                    </button>
                    
                    <a href="logout.php" class="btn-logout">
                        <span>Log Out</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </a>
                </div>
            </header>
            
            <main class="admin-viewport">
