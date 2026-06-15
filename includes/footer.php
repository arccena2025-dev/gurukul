<?php
/**
 * ========================================================
 * SHARED FRONTEND FOOTER COMPONENT (GURUKUL)
 * ========================================================
 */

// Gather general contact parameters from the aboutUs CMS database if active
// Or fallback to default constants to prevent page breaks
$foot_phone   = '+91 98765 43210';
$foot_email   = 'info@gurukul.edu';
$foot_address = 'Knowledge Park III, Greater Noida, UP, India';

$footer_certs = [];
if (isset($pdo)) {
    try {
        $stmt_foot = $pdo->prepare("SELECT `leadership_author` FROM `about_content` WHERE `id` = 1 LIMIT 1");
        $stmt_foot->execute();
        $foot_data = $stmt_foot->fetch();
        
        // Fetch visible certificates of recognition & safety categories for footer list
        $stmt_foot_certs = $pdo->prepare("SELECT * FROM `certificates` WHERE `is_visible` = 1 AND `category` IN ('recognition', 'safety') ORDER BY `sort_order` ASC");
        $stmt_foot_certs->execute();
        $footer_certs = $stmt_foot_certs->fetchAll();
    } catch (PDOException $e) {
        // Safe silent fail
    }
}
?>
    <!-- ==========================================
       FOOTER SECTION
       ========================================== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-brand">
                    <a href="index.php" class="logo">
                        <img src="images/Logo PNG.png" alt="Gurukul Crest" class="logo-crest">
                        <span>Gurukul</span>
                    </a>
                    <p>Fusing traditional value ethics with advanced STEM methodologies to nurture global, creative, and enlightened leaders of tomorrow.</p>
                    <div class="social-links">
                        <a href="#" class="social-btn" aria-label="Facebook">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                        <a href="#" class="social-btn" aria-label="Twitter">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
                        </a>
                        <a href="#" class="social-btn" aria-label="LinkedIn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></svg>
                        </a>
                        <a href="#" class="social-btn" aria-label="YouTube">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17z"/><polygon points="9.7 15 9.7 9 15 12 9.7 15"/></svg>
                        </a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <div class="footer-links">
                        <div class="footer-link-item"><a href="index.php">Home Dashboard</a></div>
                        <div class="footer-link-item"><a href="about.php">About Heritage</a></div>
                        <div class="footer-link-item"><a href="gallery.php">Visual Gallery</a></div>
                        <div class="footer-link-item"><a href="news.php">News & Bulletins</a></div>
                        <div class="footer-link-item"><a href="results.php">Wall of Glory</a></div>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4>Mandatory Disclosures</h4>
                    <div class="footer-links">
                        <?php if (empty($footer_certs)): ?>
                            <div class="footer-link-item"><a href="#">No Certificates Loaded</a></div>
                        <?php else: ?>
                            <?php foreach ($footer_certs as $fc): ?>
                                <div class="footer-link-item">
                                    <a href="#" class="footer-cert-link" 
                                       data-pdf="<?php echo sanitize($fc['pdf_path']); ?>" 
                                       data-title="<?php echo sanitize($fc['title']); ?>" 
                                       data-number="<?php echo sanitize($fc['certificate_number']); ?>" 
                                       data-authority="<?php echo sanitize($fc['issue_authority']); ?>" 
                                       data-issue="<?php echo sanitize($fc['issue_date']); ?>" 
                                       data-expiry="<?php echo sanitize($fc['expiry_date']); ?>">
                                        <?php echo sanitize($fc['title']); ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4>Contact Info</h4>
                    <div class="contact-info-list">
                        <div class="contact-info-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span><?php echo $foot_address; ?></span>
                        </div>
                        <div class="contact-info-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <span><?php echo $foot_phone; ?></span>
                        </div>
                        <div class="contact-info-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            <span><?php echo $foot_email; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <span id="footer-year"></span> Gurukul Academy. All Rights Reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Sitemap</a>
                    <!-- Secure small, professional Admin Login link in the footer alongside terms -->
                    <a href="admin/login.php" style="color: var(--accent-light); font-weight: 600;">Admin Login</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ==========================================
       CERTIFICATE PDF Viewer Modal Overlay
       ========================================== -->
    <div class="cert-modal-overlay" id="cert-modal-overlay" aria-hidden="true" role="dialog">
        <div class="cert-modal-container">
            <div class="cert-modal-header">
                <h3 class="cert-modal-title" id="cert-modal-title">Document Viewer</h3>
                <button class="cert-modal-close" id="cert-modal-close" aria-label="Close modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="cert-modal-body">
                <div class="cert-modal-viewer">
                    <iframe id="cert-modal-iframe" src="" frameborder="0" title="PDF Document Viewer"></iframe>
                    <div class="cert-modal-fallback" id="cert-modal-fallback" style="display: none;">
                        <div class="fallback-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                        <p>Mobile web browsers do not support direct PDF embedding. Click below to view the file directly.</p>
                        <a id="cert-modal-download-fallback" href="#" class="btn btn-primary" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
                            View PDF Document
                        </a>
                    </div>
                </div>
                <div class="cert-modal-info">
                    <h4>Document Registry Details</h4>
                    <div class="cert-info-grid">
                        <div class="cert-info-item">
                            <span class="info-label">Issuing Authority</span>
                            <span class="info-value" id="cert-info-authority">-</span>
                        </div>
                        <div class="cert-info-item">
                            <span class="info-label">Document Number</span>
                            <span class="info-value" id="cert-info-number">-</span>
                        </div>
                        <div class="cert-info-item">
                            <span class="info-label">Issue Date</span>
                            <span class="info-value" id="cert-info-issue">-</span>
                        </div>
                        <div class="cert-info-item">
                            <span class="info-label">Expiry / Renewal</span>
                            <span class="info-value" id="cert-info-expiry">-</span>
                        </div>
                    </div>
                    <div class="cert-modal-actions">
                        <a id="cert-modal-download" href="#" class="btn btn-primary btn-view-fullscreen" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>
                            </svg>
                            View Fullscreen
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Premium JavaScript Scripts -->
    <script src="js/main.js"></script>
</body>
</html>
