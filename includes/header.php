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

// Fetch visible certificates for Mega Menu
$all_certs = [];
$featured_certs = [];
if (isset($pdo)) {
    try {
        $stmt_certs = $pdo->prepare("SELECT * FROM `certificates` WHERE `is_visible` = 1 ORDER BY `sort_order` ASC, `title` ASC");
        $stmt_certs->execute();
        $all_certs = $stmt_certs->fetchAll();
        
        // Filter featured certificates (max 3)
        $featured_count = 0;
        foreach ($all_certs as $cert) {
            if ($cert['is_featured'] == 1 && $featured_count < 3) {
                $featured_certs[] = $cert;
                $featured_count++;
            }
        }
    } catch (PDOException $e) {
        // Safe silent fail
    }
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
    <link rel="icon" type="image/png" href="/images/Logo%20PNG.png">
    
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
            <div class="preloader-crest" style="background: #FFFFFF; padding: 5px; border-radius: 50%; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                <img src="/images/Logo%20PNG.png" alt="Gurukul Crest" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
            </div>
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
                
                <!-- Mandatory Public Disclosure Mega Menu Dropdown -->
                <div class="nav-item-dropdown disclosure-nav-item">
                    <a href="#" class="nav-link dropdown-trigger" aria-haspopup="true" aria-expanded="false">
                        Mandatory Disclosure
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="dropdown-chevron">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </a>
                    <div class="mega-menu-panel">
                        <!-- Featured badges row at the top (if any featured certs) -->
                        <?php if (!empty($featured_certs)): ?>
                        <div class="mega-menu-featured">
                            <span class="featured-label">Key Credentials:</span>
                            <div class="featured-badges">
                                <?php foreach ($featured_certs as $fcert): ?>
                                    <span class="featured-badge" 
                                          data-pdf="<?php echo sanitize($fcert['pdf_path']); ?>" 
                                          data-title="<?php echo sanitize($fcert['title']); ?>" 
                                          data-number="<?php echo sanitize($fcert['certificate_number']); ?>" 
                                          data-authority="<?php echo sanitize($fcert['issue_authority']); ?>" 
                                          data-issue="<?php echo sanitize($fcert['issue_date']); ?>" 
                                          data-expiry="<?php echo sanitize($fcert['expiry_date']); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        <?php echo sanitize($fcert['title']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mega-menu-grid">
                            <!-- Left Column: Institution Information -->
                            <div class="mega-menu-col info-col">
                                <div class="col-header">
                                    <h3>Institution Profile</h3>
                                </div>
                                <div class="school-profile-card">
                                    <div class="profile-logo-wrapper">
                                        <img src="/images/Logo%20PNG.png" alt="Gurukul Logo" class="profile-logo">
                                    </div>
                                    <div class="profile-details">
                                        <h4>Gurukul Academy</h4>
                                        <p class="school-trust">Managed by Gurukul Educational Trust</p>
                                        
                                        <div class="profile-meta-list">
                                            <div class="profile-meta-item">
                                                <span class="meta-label">CBSE Affiliation:</span>
                                                <span class="meta-value">330882</span>
                                            </div>
                                            <div class="profile-meta-item">
                                                <span class="meta-label">School Code:</span>
                                                <span class="meta-value">65882</span>
                                            </div>
                                            <div class="profile-meta-item">
                                                <span class="meta-label">Established:</span>
                                                <span class="meta-value">2010</span>
                                            </div>
                                            <div class="profile-meta-item">
                                                <span class="meta-label">Status:</span>
                                                <span class="meta-value badge-active">Active</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Center Column: Affiliation & NOC -->
                            <div class="mega-menu-col docs-col">
                                <div class="col-header">
                                    <h3>Affiliation & NOC</h3>
                                </div>
                                <div class="cert-cards-container">
                                    <?php 
                                    $recognition_certs = array_filter($all_certs, function($c) {
                                        return $c['category'] === 'recognition';
                                    });
                                    if (empty($recognition_certs)):
                                    ?>
                                        <p class="no-certs">No affiliation documents uploaded.</p>
                                    <?php else: ?>
                                        <?php foreach ($recognition_certs as $cert): ?>
                                            <div class="cert-card" 
                                                 data-pdf="<?php echo sanitize($cert['pdf_path']); ?>" 
                                                 data-title="<?php echo sanitize($cert['title']); ?>" 
                                                 data-number="<?php echo sanitize($cert['certificate_number']); ?>" 
                                                 data-authority="<?php echo sanitize($cert['issue_authority']); ?>" 
                                                 data-issue="<?php echo sanitize($cert['issue_date']); ?>" 
                                                 data-expiry="<?php echo sanitize($cert['expiry_date']); ?>">
                                                <div class="cert-card-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                                        <polyline points="14 2 14 8 20 8"></polyline>
                                                    </svg>
                                                </div>
                                                <div class="cert-card-body">
                                                    <h4 class="cert-card-title"><?php echo sanitize($cert['title']); ?></h4>
                                                    <span class="cert-card-authority"><?php echo sanitize($cert['issue_authority'] ?: 'Verification Authority'); ?></span>
                                                    <div class="cert-card-footer">
                                                        <span class="cert-status-badge <?php echo ($cert['expiry_date'] && strtotime($cert['expiry_date']) < time()) ? 'expired' : 'active'; ?>">
                                                            <?php echo ($cert['expiry_date'] && strtotime($cert['expiry_date']) < time()) ? 'Expired' : 'Verified'; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Right Column: Safety & Compliance -->
                            <div class="mega-menu-col policies-col">
                                <div class="col-header">
                                    <h3>Safety & Compliance</h3>
                                </div>
                                <div class="cert-cards-container">
                                    <?php 
                                    $safety_certs = array_filter($all_certs, function($c) {
                                        return $c['category'] === 'safety';
                                    });
                                    if (empty($safety_certs)):
                                    ?>
                                        <p class="no-certs">No safety certificates uploaded.</p>
                                    <?php else: ?>
                                        <?php foreach ($safety_certs as $cert): ?>
                                            <div class="cert-card" 
                                                 data-pdf="<?php echo sanitize($cert['pdf_path']); ?>" 
                                                 data-title="<?php echo sanitize($cert['title']); ?>" 
                                                 data-number="<?php echo sanitize($cert['certificate_number']); ?>" 
                                                 data-authority="<?php echo sanitize($cert['issue_authority']); ?>" 
                                                 data-issue="<?php echo sanitize($cert['issue_date']); ?>" 
                                                 data-expiry="<?php echo sanitize($cert['expiry_date']); ?>">
                                                <div class="cert-card-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                                        <polyline points="14 2 14 8 20 8"></polyline>
                                                    </svg>
                                                </div>
                                                <div class="cert-card-body">
                                                    <h4 class="cert-card-title"><?php echo sanitize($cert['title']); ?></h4>
                                                    <span class="cert-card-authority"><?php echo sanitize($cert['issue_authority'] ?: 'Verification Authority'); ?></span>
                                                    <div class="cert-card-footer">
                                                        <span class="cert-status-badge <?php echo ($cert['expiry_date'] && strtotime($cert['expiry_date']) < time()) ? 'expired' : 'active'; ?>">
                                                            <?php echo ($cert['expiry_date'] && strtotime($cert['expiry_date']) < time()) ? 'Expired' : 'Verified'; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
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
            
            <!-- Mobile Accordion for Mandatory Public Disclosure -->
            <div class="drawer-accordion-item">
                <button class="drawer-accordion-trigger">
                    Mandatory Disclosure
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="accordion-chevron">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="drawer-accordion-content">
                    <div class="drawer-school-details">
                        <p><strong>CBSE Affiliation:</strong> 330882</p>
                        <p><strong>School Code:</strong> 65882</p>
                        <p class="trust-info">Managed by Gurukul Educational Trust</p>
                    </div>
                    
                    <div class="drawer-sub-category">
                        <h5>Affiliation & NOC</h5>
                        <?php if (empty($recognition_certs)): ?>
                            <p class="no-certs-mobile">None available</p>
                        <?php else: ?>
                            <div class="drawer-cert-list">
                                <?php foreach ($recognition_certs as $cert): ?>
                                    <div class="drawer-cert-item" 
                                         data-pdf="<?php echo sanitize($cert['pdf_path']); ?>" 
                                         data-title="<?php echo sanitize($cert['title']); ?>" 
                                         data-number="<?php echo sanitize($cert['certificate_number']); ?>" 
                                         data-authority="<?php echo sanitize($cert['issue_authority']); ?>" 
                                         data-issue="<?php echo sanitize($cert['issue_date']); ?>" 
                                         data-expiry="<?php echo sanitize($cert['expiry_date']); ?>">
                                        <span><?php echo sanitize($cert['title']); ?></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="drawer-sub-category">
                        <h5>Safety & Compliance</h5>
                        <?php if (empty($safety_certs)): ?>
                            <p class="no-certs-mobile">None available</p>
                        <?php else: ?>
                            <div class="drawer-cert-list">
                                <?php foreach ($safety_certs as $cert): ?>
                                    <div class="drawer-cert-item" 
                                         data-pdf="<?php echo sanitize($cert['pdf_path']); ?>" 
                                         data-title="<?php echo sanitize($cert['title']); ?>" 
                                         data-number="<?php echo sanitize($cert['certificate_number']); ?>" 
                                         data-authority="<?php echo sanitize($cert['issue_authority']); ?>" 
                                         data-issue="<?php echo sanitize($cert['issue_date']); ?>" 
                                         data-expiry="<?php echo sanitize($cert['expiry_date']); ?>">
                                        <span><?php echo sanitize($cert['title']); ?></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
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
