<?php
/**
 * ========================================================
 * SHARED PREVENT-CACHE RESPONSIVE FRONTEND HEADER (GURUKUL)
 * ========================================================
 */

// Start session if not active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

function is_nav_active($file, $current) {
    return ($file === $current) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $meta_description ?? 'Welcome to Gurukul Academy - A premium institute of learning fostering academic excellence, moral leadership, and holistic development.'; ?>">
    <title><?php echo $page_title ?? 'Gurukul Academy | Academic Excellence & Holistic Learning'; ?></title>
    
    <!-- Open Graph SEO -->
    <meta property="og:title" content="<?php echo $page_title ?? 'Gurukul Academy | Academic Excellence & Holistic Learning'; ?>">
    <meta property="og:description" content="<?php echo $meta_description ?? 'Welcome to Gurukul Academy - A premium institute of learning fostering academic excellence, moral leadership, and holistic development.'; ?>">
    <meta property="og:type" content="website">
    
    <!-- Favicon Branding -->
    <link rel="icon" type="image/png" href="images/Logo PNG.png">
    
    <!-- Global Stylesheets (Preventing Cache Versioning) -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    
    <!-- Custom Page CSS Injected locally -->
    <?php if (isset($custom_css)) echo $custom_css; ?>
</head>
<body>
    <!-- Scroll Progress Bar -->
    <div id="scroll-progress"></div>

    <!-- Page Preloader Loading Animation -->
    <div id="page-preloader">
        <div class="preloader-content">
            <div class="preloader-crest">G</div>
            <div class="preloader-spinner"></div>
        </div>
    </div>

    <!-- Page Transition Shutter -->
    <div class="page-shutter"></div>

    <!-- BACK TO TOP BUTTON -->
    <div class="back-to-top" id="back-to-top" title="Scroll back to top">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <path d="m18 15-6-6-6 6"/>
        </svg>
    </div>

    <!-- MAIN RESPONSIVE HEADER -->
    <header class="header">
        <div class="container header-container">
            <a href="index.php" class="logo">
                <img src="images/Logo PNG.png" alt="Gurukul Crest" class="logo-crest">
                <span>Gurukul</span>
            </a>
            
            <!-- Desktop navigation menu links -->
            <nav class="nav-menu">
                <a href="index.php" class="nav-link <?php echo is_nav_active('index.php', $current_page); ?>">Home</a>
                <a href="about.php" class="nav-link <?php echo is_nav_active('about.php', $current_page); ?>">About Us</a>
                <a href="gallery.php" class="nav-link <?php echo is_nav_active('gallery.php', $current_page); ?>">Gallery</a>
                <a href="news.php" class="nav-link <?php echo is_nav_active('news.php', $current_page); ?>">News & Events</a>
                <a href="results.php" class="nav-link <?php echo is_nav_active('results.php', $current_page); ?>">Results</a>
                <a href="contact.php" class="nav-link <?php echo is_nav_active('contact.php', $current_page); ?>">Contact Us</a>
            </nav>
            
            <div class="header-actions">
                <!-- Theme Toggle Button -->
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark/light theme">
                    <!-- Icon loaded by JavaScript -->
                </button>
                <a href="contact.php#admissions" class="btn btn-primary">Enroll Now</a>
                
                <!-- Hamburger Trigger for mobile -->
                <button class="menu-trigger" id="menu-trigger" aria-label="Toggle mobile navigation menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- MOBILE DRAWER NAVIGATION -->
    <div class="mobile-drawer" id="mobile-drawer">
        <div class="drawer-links">
            <a href="index.php" class="drawer-link <?php echo is_nav_active('index.php', $current_page); ?>">Home</a>
            <a href="about.php" class="drawer-link <?php echo is_nav_active('about.php', $current_page); ?>">About Us</a>
            <a href="gallery.php" class="drawer-link <?php echo is_nav_active('gallery.php', $current_page); ?>">Gallery</a>
            <a href="news.php" class="drawer-link <?php echo is_nav_active('news.php', $current_page); ?>">News & Events</a>
            <a href="results.php" class="drawer-link <?php echo is_nav_active('results.php', $current_page); ?>">Results</a>
            <a href="contact.php" class="drawer-link <?php echo is_nav_active('contact.php', $current_page); ?>">Contact Us</a>
        </div>
        <div class="drawer-actions">
            <a href="contact.php#admissions" class="btn btn-primary">Enroll Now</a>
            <a href="contact.php" class="btn btn-secondary">Inquire Now</a>
        </div>
    </div>
