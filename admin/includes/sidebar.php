<?php
/**
 * ========================================================
 * ADMIN PORTAL SIDEBAR NAVIGATION COMPONENT (GURUKUL)
 * ========================================================
 */

$current_file = basename($_SERVER['PHP_SELF']);

function is_active($file, $current) {
    return ($file === $current) ? 'active' : '';
}
?>
<aside class="admin-sidebar" id="admin-sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon" style="background: #FFFFFF; padding: 2px; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
            <img src="/images/Logo%20PNG.png" alt="Gurukul Crest" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
        </div>
        <div class="brand-text">Gurukul CMS</div>
    </div>
    
    <nav class="sidebar-nav">
        <!-- 1. Dashboard Overview -->
        <a href="index.php" class="nav-item <?php echo is_active('index.php', $current_file); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                <rect width="7" height="5" x="3" y="16" rx="1"></rect>
            </svg>
            <span>Overview</span>
        </a>
        
        <!-- 2. Manage Homepage -->
        <a href="homepage.php" class="nav-item <?php echo is_active('homepage.php', $current_file); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>Homepage CMS</span>
        </a>
        
        <!-- 3. Manage About Us -->
        <a href="about.php" class="nav-item <?php echo is_active('about.php', $current_file); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span>About Us CMS</span>
        </a>
        
        <!-- 4. Manage Gallery -->
        <a href="gallery.php" class="nav-item <?php echo is_active('gallery.php', $current_file); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                <circle cx="10" cy="13" r="2"></circle>
                <path d="m20 17-3.5-3.5a2 2 0 0 0-2.8 0L9 18.2"></path>
            </svg>
            <span>Gallery CMS</span>
        </a>
        
        <!-- 5. Manage News & Events -->
        <a href="news_events.php" class="nav-item <?php echo is_active('news_events.php', $current_file); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"></path>
                <path d="M18 14h-8M15 18h-5"></path>
                <path d="M10 6h8v4h-8V6Z"></path>
            </svg>
            <span>News & Events CMS</span>
        </a>
        
        <!-- 6. Manage Results -->
        <a href="results.php" class="nav-item <?php echo is_active('results.php', $current_file); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            <span>Results Portal CMS</span>
        </a>
        
        <!-- 7. Centralized Media Library -->
        <a href="media.php" class="nav-item <?php echo is_active('media.php', $current_file); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            <span>Media Library</span>
        </a>
        
        <!-- 8. Contact Form Submissions -->
        <a href="inquiries.php" class="nav-item <?php echo is_active('inquiries.php', $current_file); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
            </svg>
            <span>Inquiry Center</span>
        </a>
        
        <!-- 9. Security Profiles -->
        <a href="profile.php" class="nav-item <?php echo is_active('profile.php', $current_file); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <span>Security Settings</span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <p>&copy; Gurukul Academy</p>
        <p style="font-size: 0.7rem; margin-top: 4px; opacity: 0.6;">v1.0 Dynamic CMS</p>
    </div>
</aside>
