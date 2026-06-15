<?php
/**
 * ========================================================
 * DYNAMIC FRONTEND CONTACT US PORTAL (GURUKUL)
 * ========================================================
 */

// 1. Establish secure PDO Database connection
require_once 'config/db.php';

// 2. AJAX POST Request Endpoint for Inquiry Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $subject = "Direct Website Inquiry";
    
    // Clean and validate inputs
    if (strlen($name) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($phone) || strlen($message) < 10) {
        echo json_encode(['status' => 'error', 'message' => 'Input validation failed. Please check field requirements.']);
        exit();
    }
    
    try {
        $stmt_ins = $pdo->prepare("INSERT INTO `contact_submissions` (`name`, `email`, `phone`, `subject`, `message`, `is_read`) VALUES (:name, :email, :phone, :subject, :message, 0)");
        $stmt_ins->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':subject' => $subject,
            ':message' => $message
        ]);
        echo json_encode(['status' => 'success']);
        exit();
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'CMS Database insertion failure.']);
        exit();
    }
}

$page_title = 'Contact Us | Gurukul Academy';
$meta_description = 'Get in touch with Gurukul Academy. Contact our admissions desk, find directions, or submit an enrollment inquiry form.';

// 3. Inject custom local stylesheet blocks in the header
$custom_css = '
    <style>
        /* Contact Cards Grid */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 32px;
            margin-bottom: 60px;
        }
        .contact-item-card {
            background-color: var(--bg-white);
            border-radius: var(--border-radius-md);
            padding: 36px 30px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15, 23, 42, 0.05);
            transition: var(--transition-smooth);
        }
        body.dark-theme-active .contact-item-card {
            background-color: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .contact-item-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(13, 148, 136, 0.15);
        }
        .contact-card-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(13, 148, 136, 0.08);
            color: var(--secondary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin: 0 auto 20px;
            transition: var(--transition-smooth);
        }
        .contact-item-card:hover .contact-card-icon {
            background: var(--secondary-light);
            color: var(--bg-white);
            transform: scale(1.05);
        }
        .contact-item-card h3 {
            font-size: 1.25rem;
            margin-bottom: 8px;
        }
        .contact-item-card p {
            font-size: 0.95rem;
            margin-bottom: 16px;
        }
        .contact-action-btn {
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .contact-action-btn:hover {
            color: var(--secondary-light);
        }

        /* Form & FAQ Split Column Layout */
        .split-row {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 60px;
            align-items: flex-start;
        }
        .glass-form-card {
            background: var(--bg-white);
            border-radius: var(--border-radius-lg);
            padding: 48px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(15, 23, 42, 0.05);
            position: relative;
            overflow: hidden;
        }
        body.dark-theme-active .glass-form-card {
            background: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .glass-form-card h2 {
            margin-bottom: 8px;
        }
        .glass-form-card p {
            margin-bottom: 32px;
            font-size: 0.95rem;
        }

        /* Centralized Floating Label System holds dynamic rendering properties */
        .error-message {
            color: #ef4444;
            font-size: 0.8rem;
            margin-top: 4px;
            display: none;
        }
        .form-success-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--bg-white);
            z-index: 10;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px;
            box-sizing: border-box;
        }
        body.dark-theme-active .form-success-overlay {
            background: var(--bg-card-dark);
        }
        .form-success-overlay svg {
            color: var(--accent-light);
            margin-bottom: 20px;
        }
        .form-success-overlay h2 {
            margin-bottom: 10px;
        }
        .form-success-overlay p {
            max-width: 380px;
            color: var(--text-muted);
            margin: 0 auto 20px;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* FAQ accordions visual system */
        .faq-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .faq-item {
            background: var(--bg-white);
            border-radius: var(--border-radius-md);
            border: 1px solid rgba(15, 23, 42, 0.05);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: var(--transition-smooth);
        }
        body.dark-theme-active .faq-item {
            background: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .faq-header {
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }
        .faq-header h4 {
            font-size: 1.05rem;
            margin: 0;
            color: var(--primary);
        }
        body.dark-theme-active .faq-header h4 {
            color: #ffffff;
        }
        .faq-icon {
            color: var(--secondary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.4s var(--ease-premium);
        }
        .faq-item.active .faq-icon {
            transform: rotate(45deg);
            color: var(--accent-light);
        }
        .faq-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .faq-content p {
            padding: 0 24px 24px;
            margin: 0;
            font-size: 0.92rem;
            line-height: 1.6;
            color: var(--text-muted);
        }

        /* Institutional coordinates map section layout */
        .map-card-wrapper {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            background: var(--bg-white);
            border-radius: var(--border-radius-lg);
            border: 1px solid rgba(15, 23, 42, 0.05);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        body.dark-theme-active .map-card-wrapper {
            background: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .map-container {
            height: 480px;
            background: #e2e8f0;
        }
        .map-details-card {
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .map-details-card h3 {
            font-size: 1.6rem;
            margin: 4px 0 12px;
        }
        .coordinate-item {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        .coordinate-item:last-child {
            margin-bottom: 0;
        }
        .coordinate-icon {
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }
        body.dark-theme-active .coordinate-icon {
            color: #ffffff;
        }
        .coordinate-item strong {
            font-family: var(--font-heading);
            font-size: 0.95rem;
            color: var(--primary);
            display: block;
            margin-bottom: 4px;
        }
        body.dark-theme-active .coordinate-item strong {
            color: #ffffff;
        }
        .coordinate-item p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        @media (max-width: 992px) {
            .split-row {
                grid-template-columns: 1fr;
                gap: 56px;
            }
            .glass-form-card {
                padding: 32px;
            }
            .map-card-wrapper {
                grid-template-columns: 1fr;
            }
            .map-container {
                height: 320px;
            }
            .map-details-card {
                padding: 32px;
            }
        }

        /* Navbar active highlights overrides */
        .header .logo, 
        .header .logo span, 
        .header .nav-link, 
        .header .theme-toggle {
            color: #FFFFFF !important;
        }
        .header .menu-trigger span {
            background-color: #FFFFFF !important;
        }
        .header.scrolled .logo, 
        .header.scrolled .logo span, 
        .header.scrolled .nav-link, 
        .header.scrolled .theme-toggle {
            color: #0B1E4F !important;
        }
        .header.scrolled .theme-toggle {
            background: rgba(11, 30, 79, 0.05) !important;
        }
        .header.scrolled .menu-trigger span {
            background-color: #0B1E4F !important;
        }
        body.dark-theme-active .header .logo, 
        body.dark-theme-active .header .logo span, 
        body.dark-theme-active .header .nav-link, 
        body.dark-theme-active .header .theme-toggle,
        body.dark-theme-active .header.scrolled .logo, 
        body.dark-theme-active .header.scrolled .logo span, 
        body.dark-theme-active .header.scrolled .nav-link, 
        body.dark-theme-active .header.scrolled .theme-toggle {
            color: #FFFFFF !important;
        }
        body.dark-theme-active .header .theme-toggle,
        body.dark-theme-active .header.scrolled .theme-toggle {
            color: var(--accent-light) !important;
            background: rgba(255, 255, 255, 0.08) !important;
        }
        body.dark-theme-active .header .menu-trigger span,
        body.dark-theme-active .header.scrolled .menu-trigger span {
            background-color: #FFFFFF !important;
        }
    </style>
';

// Load shared header
include_once 'includes/header.php';
?>

    <main>
        <!-- PAGE HERO -->
        <section class="page-hero">
            <div class="parallax-layer parallax-glow-1"></div>
            <div class="parallax-layer parallax-glow-2"></div>
            
            <div class="container page-hero-container">
                <h1 class="reveal">Connect With Gurukul</h1>
                <div class="breadcrumbs reveal" style="transition-delay: 0.1s;">
                    <a href="index.php">Home</a>
                    <span>/</span>
                    <span class="current">Contact Us</span>
                </div>
            </div>
        </section>

        <!-- PORTALS SECTION 1: INSTANT CONTACT CARDS -->
        <section class="section-padding">
            <div class="container">
                <div class="contact-grid">
                    <!-- Card 1: Phone -->
                    <div class="contact-item-card reveal">
                        <div class="contact-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <h3>Phone Channels</h3>
                        <p>Speak directly to our desk counselors for quick inquiries.</p>
                        <a href="tel:+919876543210" class="contact-action-btn">+91 98765 43210</a>
                    </div>
                    <!-- Card 2: Email -->
                    <div class="contact-item-card reveal" style="transition-delay: 0.1s;">
                        <div class="contact-card-icon" style="background: rgba(217, 119, 6, 0.08); color: var(--accent);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </div>
                        <h3>Email Networks</h3>
                        <p>Draft detailed requests regarding documents or partnerships.</p>
                        <a href="mailto:info@gurukul.edu" class="contact-action-btn">info@gurukul.edu</a>
                    </div>
                    <!-- Card 3: Boarding Office -->
                    <div class="contact-item-card reveal" style="transition-delay: 0.2s;">
                        <div class="contact-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 22h20M4 12V4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8M12 18h.01M8 21h8M10 12h4"/></svg>
                        </div>
                        <h3>Boarding Office</h3>
                        <p>Schedule campus route layouts or boarding suite inspections.</p>
                        <a href="#admissions" class="contact-action-btn">Inquire Admission</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 2: ADMISSIONS INQUIRY FORM & FAQs -->
        <section class="section-padding" id="admissions" style="padding-top: 0;">
            <div class="container">
                <div class="split-row">
                    <!-- Left: Styled Form Card -->
                    <div class="glass-form-card reveal reveal-left">
                        <h2>Digital Admission Inquiry</h2>
                        <p>Submit an inquiry. Academic counselors will respond shortly with brochures, fee structures, and conceptual analysis tests.</p>
                        
                        <form id="contact-form" novalidate autocomplete="off">
                            <!-- Name -->
                            <div class="floating-group">
                                <input type="text" id="contact-name" class="floating-input" placeholder=" " required>
                                <label class="floating-label" for="contact-name">Candidate / Parent Name</label>
                                <div class="error-message" id="error-name">Please provide a valid name (at least 3 characters).</div>
                            </div>

                            <!-- Email -->
                            <div class="floating-group">
                                <input type="email" id="contact-email" class="floating-input" placeholder=" " required>
                                <label class="floating-label" for="contact-email">Email Address</label>
                                <div class="error-message" id="error-email">Please provide a valid email format.</div>
                            </div>

                            <!-- Phone -->
                            <div class="floating-group">
                                <input type="tel" id="contact-phone" class="floating-input" placeholder=" " required>
                                <label class="floating-label" for="contact-phone">Contact Number</label>
                                <div class="error-message" id="error-phone">Please provide a valid 10-digit phone number.</div>
                            </div>

                            <!-- Message -->
                            <div class="floating-group">
                                <textarea id="contact-message" class="floating-textarea" placeholder=" " required></textarea>
                                <label class="floating-label" for="contact-message">Admissions Inquiry / Message Details</label>
                                <div class="error-message" id="error-message">Please enter message details (at least 10 characters).</div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px;">Submit Digital Inquiry</button>
                        </form>

                        <!-- Success Card State -->
                        <div class="form-success-overlay" id="form-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                            <h2>Enquiry Received!</h2>
                            <p>Thank you for connecting. An academic counselor has been assigned to your query and will contact you shortly with catalog materials.</p>
                            <button class="btn btn-secondary" id="success-btn-close" style="margin-top: 10px;">New Inquiry</button>
                        </div>
                    </div>

                    <!-- Right: FAQ Accordions -->
                    <div class="reveal reveal-right">
                        <h2 style="margin-bottom: 12px;">Frequently Asked Questions</h2>
                        <p style="margin-bottom: 30px; font-size: 0.95rem;">Review quick solutions to common queries regarding registration protocols, criteria, and payments.</p>
                        
                        <div class="faq-container">
                            <!-- FAQ 1 -->
                            <div class="faq-item active">
                                <div class="faq-header">
                                    <h4>What is the admission procedure?</h4>
                                    <span class="faq-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></span>
                                </div>
                                <div class="faq-content">
                                    <p>Submit our digital enrollment inquiry form. Following this, you will schedule a campus tour, complete a primary student conceptual analysis test, and sit down for an interactive parent interview.</p>
                                </div>
                            </div>

                            <!-- FAQ 2 -->
                            <div class="faq-item">
                                <div class="faq-header">
                                    <h4>What are the academic streams offered?</h4>
                                    <span class="faq-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></span>
                                </div>
                                <div class="faq-content">
                                    <p>For senior secondary wings, we host three comprehensive pipelines: Science (PCM/PCB with advanced coaching), Commerce (Accountancy, Business Studies, Economics), and Humanities (Arts & Applied Ethics).</p>
                                </div>
                            </div>

                            <!-- FAQ 3 -->
                            <div class="faq-item">
                                <div class="faq-header">
                                    <h4>Is scholarship support available?</h4>
                                    <span class="faq-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></span>
                                </div>
                                <div class="faq-content">
                                    <p>Yes, Gurukul honors merit. We grant up to 100% tuition scholarships for regional board topper holders, Olympiad distinction holders, and national-tier sports medalists.</p>
                                </div>
                            </div>

                            <!-- FAQ 4 -->
                            <div class="faq-item">
                                <div class="faq-header">
                                    <h4>What is the teacher-student ratio?</h4>
                                    <span class="faq-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></span>
                                </div>
                                <div class="faq-content">
                                    <p>To preserve academic quality, we strictly maintain an average 1:15 classroom teacher-student ratio across all academic levels, ensuring maximum personal attention.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- INTERACTIVE CAMPUS MAP SECTION -->
        <section class="section-padding map-section" style="background: rgba(13, 148, 136, 0.02); padding-top: 0;">
            <div class="container">
                <div class="map-card-wrapper reveal">
                    <!-- Left: Styled Map Container -->
                    <div class="map-container">
                        <iframe 
                            src="https://maps.google.com/maps?q=2QR6%2B4MF%2C%20Beed%20Bypass%20Rd%2C%20Shidode%2C%20Maharashtra%20431153&amp;t=&amp;z=15&amp;ie=UTF8&amp;iwloc=&amp;output=embed" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>

                    <!-- Right: Office Hours & Coordinates -->
                    <div class="map-details-card">
                        <span class="news-badge" style="margin-bottom: 12px;">Campus Visit</span>
                        <h3>Our Institutional Coordinates</h3>
                        <p style="margin-bottom: 24px; font-size: 0.95rem;">Gurukul is nestled within a carbon-neutral parkland, designed to offer an immersive, distraction-free environment for young minds.</p>
                        
                        <div class="coordinate-item">
                            <div class="coordinate-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <div>
                                <strong>Physical Address</strong>
                                <p style="margin: 0; font-size: 0.9rem;">2QR6+4MF, Beed Bypass Rd, Shidode, Maharashtra 431153</p>
                            </div>
                        </div>

                        <div class="coordinate-item">
                            <div class="coordinate-icon" style="color: var(--accent);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </div>
                            <div>
                                <strong>Office Hours</strong>
                                <p style="margin: 0; font-size: 0.9rem;">Mon - Fri: 8:00 AM - 4:00 PM | Sat: 8:00 AM - 12:30 PM</p>
                            </div>
                        </div>

                        <div class="coordinate-item">
                            <div class="coordinate-icon" style="color: var(--secondary-light);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                            </div>
                            <div>
                                <strong>Admissions Gate Open</strong>
                                <p style="margin: 0; font-size: 0.9rem;">Term 1 registrations actively processing online & on-campus.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Local Script for Accordions & Robust AJAX Contact Form validation -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initFaqAccordions();
            initContactFormValidation();
        });

        // 1. Interactive FAQ Accordion Trigger
        function initFaqAccordions() {
            const faqItems = document.querySelectorAll('.faq-item');

            faqItems.forEach(item => {
                const header = item.querySelector('.faq-header');
                
                header.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');
                    
                    faqItems.forEach(i => {
                        i.classList.remove('active');
                        i.querySelector('.faq-content').style.maxHeight = '';
                    });

                    if (!isActive) {
                        item.classList.add('active');
                        const content = item.querySelector('.faq-content');
                        content.style.maxHeight = content.scrollHeight + 'px';
                    }
                });

                if (item.classList.contains('active')) {
                    const content = item.querySelector('.faq-content');
                    content.style.maxHeight = content.scrollHeight + 'px';
                }
            });
        }

        // 2. High-Quality Robust AJAX Form Validation & Database Insertion
        function initContactFormValidation() {
            const form = document.getElementById('contact-form');
            const successOverlay = document.getElementById('form-success');
            const successBtnClose = document.getElementById('success-btn-close');

            const fields = {
                name: {
                    input: document.getElementById('contact-name'),
                    error: document.getElementById('error-name'),
                    validate: (val) => val.trim().length >= 3
                },
                email: {
                    input: document.getElementById('contact-email'),
                    error: document.getElementById('error-email'),
                    validate: (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)
                },
                phone: {
                    input: document.getElementById('contact-phone'),
                    error: document.getElementById('error-phone'),
                    validate: (val) => /^\d{10}$/.test(val.replace(/[-+() ]/g, ''))
                },
                message: {
                    input: document.getElementById('contact-message'),
                    error: document.getElementById('error-message'),
                    validate: (val) => val.trim().length >= 10
                }
            };

            const validateField = (fieldKey) => {
                const field = fields[fieldKey];
                const isValid = field.validate(field.input.value);

                if (isValid) {
                    field.error.style.display = 'none';
                    field.input.style.borderColor = '';
                } else {
                    field.error.style.display = 'block';
                    field.input.style.borderColor = '#ef4444';
                }

                return isValid;
            };

            Object.keys(fields).forEach(key => {
                fields[key].input.addEventListener('blur', () => validateField(key));
                fields[key].input.addEventListener('input', () => {
                    if (fields[key].error.style.display === 'block') {
                        validateField(key);
                    }
                });
            });

            form.addEventListener('submit', (e) => {
                e.preventDefault();

                let formValid = true;
                Object.keys(fields).forEach(key => {
                    const fieldValid = validateField(key);
                    if (!fieldValid) formValid = false;
                });

                if (formValid) {
                    // Modern AJAX submission
                    const formData = new FormData();
                    formData.append('name', fields.name.input.value);
                    formData.append('email', fields.email.input.value);
                    formData.append('phone', fields.phone.input.value);
                    formData.append('message', fields.message.input.value);
                    
                    fetch('contact.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            successOverlay.style.display = 'flex';
                        } else {
                            alert(data.message || 'CMS Database write failure. Please try again.');
                        }
                    })
                    .catch(err => {
                        alert('Connectivity failure. Please try again.');
                    });
                }
            });

            successBtnClose.addEventListener('click', () => {
                successOverlay.style.display = 'none';
                form.reset();
                
                Object.keys(fields).forEach(key => {
                    fields[key].input.dispatchEvent(new Event('input'));
                });
            });
        }
    </script>
    <script src="js/main.js"></script>
</body>
</html>
