<?php
/**
 * ========================================================
 * DYNAMIC FRONTEND ABOUT US PORTAL (GURUKUL)
 * ========================================================
 */

// 1. Establish secure PDO Database connection
require_once 'config/db.php';

$page_title = 'About Us | Gurukul Academy';
$meta_description = 'Explore the noble heritage, vision, mission, and achievements of Gurukul Academy. Learn about our leadership, expert faculty, and student growth timeline.';

// 2. Fetch About page content details (Row id = 1)
try {
    $stmt_about = $pdo->prepare("SELECT * FROM `about_content` WHERE `id` = 1 LIMIT 1");
    $stmt_about->execute();
    $about = $stmt_about->fetch();

    // Fetch timeline milestones ordered by sort_order
    $stmt_timeline = $pdo->prepare("SELECT * FROM `about_timeline` ORDER BY `sort_order` ASC, `milestone_year` ASC");
    $stmt_timeline->execute();
    $timeline = $stmt_timeline->fetchAll();

    // Fetch dynamic leadership profiles
    $stmt_leaders = $pdo->prepare("SELECT * FROM `about_leadership` ORDER BY `sort_order` ASC, `id` ASC");
    $stmt_leaders->execute();
    $leaders = $stmt_leaders->fetchAll();

    // Fetch dynamic faculty profiles
    $stmt_faculty = $pdo->prepare("SELECT * FROM `faculty` ORDER BY `sort_order` ASC, `id` ASC");
    $stmt_faculty->execute();
    $faculties = $stmt_faculty->fetchAll();
} catch (PDOException $e) {
    die("CMS Critical Render Error: Database connectivity failure. " . $e->getMessage());
}

// 3. Inject custom local stylesheet blocks in the header
$custom_css = '
    <style>
        /* ==========================================
           1. INTRODUCTION SECTION
           ========================================== */
        .intro-grid {
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            gap: 60px;
            align-items: center;
        }
        .intro-media {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .intro-shield-card {
            width: 100%;
            max-width: 400px;
            height: 400px;
            border-radius: var(--border-radius-lg);
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        .intro-shield-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: relative;
            z-index: 2;
        }
        .intro-shield-card::before {
            content: "";
            position: absolute;
            width: 80%;
            height: 80%;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(13, 148, 136, 0.3) 0%, transparent 70%);
            z-index: 1;
        }
        .intro-shield-svg {
            width: 50%;
            height: auto;
            color: var(--accent-light);
            position: relative;
            z-index: 2;
        }
        .intro-badge {
            position: absolute;
            bottom: 20px;
            right: -20px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            color: var(--bg-white);
            padding: 16px 24px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-md);
            font-family: var(--font-heading);
            font-weight: 700;
            z-index: 3;
            text-align: center;
        }
        .intro-badge h4 {
            color: var(--bg-white) !important;
            font-size: 1.4rem;
            margin: 0;
            line-height: 1;
        }
        .intro-badge p {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.85) !important;
            margin: 4px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }
        .intro-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .intro-content p {
            font-size: 1.1rem;
            line-height: 1.7;
        }
        @media (max-width: 992px) {
            .intro-grid {
                grid-template-columns: 1fr;
                gap: 48px;
                text-align: center;
            }
            .intro-media {
                max-width: 400px;
                margin: 0 auto;
                width: 100%;
            }
            .intro-badge {
                right: 10px;
            }
        }

        /* ==========================================
           2. VISION, MISSION & PHILOSOPHY
           ========================================== */
        .vision-mission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
        }
        .vm-card {
            background-color: var(--bg-white);
            border-radius: var(--border-radius-md);
            padding: 40px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15, 23, 42, 0.05);
            transition: var(--transition-smooth);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        body.dark-theme-active .vm-card {
            background-color: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .vm-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(13, 148, 136, 0.03) 0%, rgba(217, 119, 6, 0.03) 100%);
            z-index: -1;
            opacity: 0;
            transition: var(--transition-smooth);
        }
        .vm-card:hover::before {
            opacity: 1;
        }
        .vm-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(13, 148, 136, 0.1);
        }
        .vm-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--border-radius-sm);
            background: rgba(217, 119, 6, 0.08);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 24px;
            transition: var(--transition-smooth);
        }
        .vm-card:hover .vm-icon {
            background: var(--accent);
            color: var(--bg-white);
            transform: scale(1.05) rotate(-3deg);
        }
        .vm-card h3 {
            margin-bottom: 16px;
            font-size: 1.4rem;
        }

        /* ==========================================
           3. LEADERSHIP MESSAGE & ADVISORY MENTORS
           ========================================== */
        .leadership-message-section {
            background-color: rgba(13, 148, 136, 0.02);
            border-top: 1px solid rgba(15, 23, 42, 0.03);
            border-bottom: 1px solid rgba(15, 23, 42, 0.03);
        }
        body.dark-theme-active .leadership-message-section {
            background-color: rgba(9, 13, 22, 0.3);
            border-color: var(--glass-border);
        }
        .leadership-grid {
            display: grid;
            grid-template-columns: 0.8fr 1.2fr;
            gap: 60px;
            align-items: center;
            margin-bottom: 80px;
        }
        .leader-card {
            background-color: var(--bg-white);
            border-radius: var(--border-radius-md);
            padding: 28px;
            border: 1px solid rgba(15, 23, 42, 0.05);
            box-shadow: var(--shadow-md);
            text-align: center;
            transition: var(--transition-smooth);
        }
        body.dark-theme-active .leader-card {
            background-color: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .leader-photo-box {
            height: 300px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-light);
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .leader-photo-box svg {
            width: 35%;
            height: auto;
        }
        .leader-card h3 {
            font-size: 1.3rem;
            margin-bottom: 4px;
        }
        .leader-card .leader-role {
            font-family: var(--font-heading);
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--secondary-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .leader-message-box {
            position: relative;
            padding: 48px;
            background: var(--bg-white);
            border-radius: var(--border-radius-md);
            border: 1px solid rgba(15, 23, 42, 0.04);
            box-shadow: var(--shadow-sm);
        }
        body.dark-theme-active .leader-message-box {
            background: rgba(19, 27, 46, 0.4);
            border-color: var(--glass-border);
        }
        .leader-quote {
            font-family: var(--font-body);
            font-size: 1.25rem;
            line-height: 1.7;
            font-style: italic;
            color: var(--primary);
            position: relative;
            z-index: 2;
        }
        body.dark-theme-active .leader-quote {
            color: #cbd5e1;
        }
        .leader-quote::before {
            content: "“";
            position: absolute;
            top: -45px;
            left: -20px;
            font-size: 8rem;
            font-family: Georgia, serif;
            color: rgba(217, 119, 6, 0.12);
            line-height: 1;
            z-index: 1;
        }
        .leader-signature {
            margin-top: 24px;
            text-align: right;
            font-family: var(--font-heading);
            font-weight: 700;
            color: var(--accent);
        }
        body.dark-theme-active .leader-signature {
            color: var(--accent-light);
        }
        @media (max-width: 992px) {
            .leadership-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            .leader-card {
                max-width: 400px;
                margin: 0 auto;
                width: 100%;
            }
        }

        /* Faculty Advisory Board Grid */
        .mentor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
        }
        .mentor-card {
            background-color: var(--bg-white);
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15, 23, 42, 0.05);
            transition: var(--transition-smooth);
            text-align: center;
        }
        body.dark-theme-active .mentor-card {
            background-color: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .mentor-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }
        .mentor-photo-wrapper {
            position: relative;
            height: 300px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(15, 23, 42, 0.05);
        }
        body.dark-theme-active .mentor-photo-wrapper {
            border-bottom-color: var(--glass-border);
        }
        .mentor-photo-svg {
            width: 35%;
            height: auto;
            color: var(--accent-light);
            transition: transform 0.4s var(--ease-premium);
        }
        .mentor-card:hover .mentor-photo-svg {
            transform: scale(1.08) rotate(3deg);
        }
        .mentor-info {
            padding: 24px;
        }
        .mentor-role {
            font-family: var(--font-heading);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--secondary-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }
        .mentor-info h3 {
            font-size: 1.2rem;
            margin-bottom: 8px;
        }
        .mentor-bio {
            font-size: 0.9rem;
            line-height: 1.5;
            color: var(--text-muted);
            margin-bottom: 18px;
        }
        .mentor-socials {
            display: flex;
            justify-content: center;
            gap: 12px;
        }
        .mentor-social-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.05);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: var(--transition-smooth);
        }
        body.dark-theme-active .mentor-social-btn {
            background: rgba(255, 255, 255, 0.05);
            color: var(--bg-white);
        }
        .mentor-social-btn:hover {
            background: var(--accent);
            color: var(--bg-white);
            transform: translateY(-2px);
        }

        /* ==========================================
           4. ACHIEVEMENTS & ACCOLADES
           ========================================== */
        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }
        .achievement-card {
            background-color: var(--bg-white);
            border-radius: var(--border-radius-md);
            padding: 40px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15, 23, 42, 0.05);
            transition: var(--transition-smooth);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        body.dark-theme-active .achievement-card {
            background-color: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .achievement-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(13, 148, 136, 0.1);
        }
        .achievement-icon {
            width: 70px;
            height: 70px;
            border-radius: var(--border-radius-sm);
            background: rgba(217, 119, 6, 0.08);
            color: var(--accent);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-heading);
            font-weight: 800;
            font-size: 1.15rem !important;
            margin: 0 auto 24px;
            transition: var(--transition-smooth);
            border: 1px solid rgba(217, 119, 6, 0.2);
        }
        .achievement-card:hover .achievement-icon {
            background: var(--accent);
            color: var(--bg-white);
            transform: scale(1.08) rotate(3deg);
            border-color: var(--accent);
        }
        .achievement-card h3 {
            font-size: 1.3rem;
            margin-bottom: 12px;
        }
        .achievement-card p {
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* ==========================================
           5. GLORIOUS JOURNEY TIMELINE
           ========================================== */
        .timeline-section {
            background-color: rgba(15, 23, 42, 0.01);
            position: relative;
        }
        body.dark-theme-active .timeline-section {
            background-color: rgba(9, 13, 22, 0.5);
        }
        .timeline-container {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 0;
        }
        .timeline-container::before {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 4px;
            background: linear-gradient(to bottom, var(--secondary-light) 0%, var(--accent-light) 100%);
            transform: translateX(-50%);
            border-radius: 2px;
        }
        .timeline-item {
            position: relative;
            width: 50%;
            padding: 20px 40px;
            box-sizing: border-box;
        }
        .timeline-item-left {
            left: 0;
            text-align: right;
        }
        .timeline-item-right {
            left: 50%;
            text-align: left;
        }
        .timeline-dot {
            position: absolute;
            top: 28px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--bg-white);
            border: 4px solid var(--secondary);
            z-index: 10;
            transform: translateX(-50%);
            left: 100%;
            transition: var(--transition-smooth);
        }
        body.dark-theme-active .timeline-dot {
            background: var(--bg-dark);
            border-color: var(--accent-light);
        }
        .timeline-item-right .timeline-dot {
            left: 0%;
        }
        .timeline-item:hover .timeline-dot {
            transform: translateX(-50%) scale(1.3);
            background: var(--accent);
            box-shadow: var(--shadow-glow-accent);
        }
        .timeline-box {
            background-color: var(--bg-white);
            border-radius: var(--border-radius-md);
            padding: 30px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15, 23, 42, 0.05);
            transition: var(--transition-smooth);
        }
        body.dark-theme-active .timeline-box {
            background-color: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .timeline-box:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
        }
        .timeline-year {
            font-family: var(--font-heading);
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--secondary-light);
            margin-bottom: 8px;
        }
        .timeline-item-right .timeline-year {
            color: var(--accent);
        }
        .timeline-box h4 {
            margin-bottom: 10px;
            font-size: 1.15rem;
        }
        @media (max-width: 768px) {
            .timeline-container::before {
                left: 20px;
            }
            .timeline-item {
                width: 100%;
                left: 0;
                padding-left: 50px;
                padding-right: 0;
                text-align: left;
            }
            .timeline-dot {
                left: 20px !important;
            }
            .timeline-item-left {
                text-align: left;
            }
        }

        /* ==========================================
           6. CALL TO ACTION (CTA)
           ========================================== */
        .admissions-cta-section {
            padding: 100px 0;
            position: relative;
        }
        .admissions-banner {
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 50%, #0f172a 100%);
            border-radius: var(--border-radius-lg);
            padding: 60px 80px;
            color: var(--bg-white);
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 40px;
        }
        .admissions-banner::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 60%);
            border-radius: 50%;
        }
        .admissions-content {
            max-width: 650px;
            position: relative;
            z-index: 2;
        }
        .admissions-content h2 {
            color: var(--bg-white) !important;
            margin-bottom: 16px;
            font-size: clamp(1.8rem, 3.5vw, 2.4rem);
        }
        .admissions-content p {
            color: #ccfbf1 !important;
            font-size: 1.05rem;
            line-height: 1.6;
        }
        .admissions-cta {
            flex-shrink: 0;
            position: relative;
            z-index: 2;
        }
        @media (max-width: 992px) {
            .admissions-banner {
                padding: 40px;
                flex-direction: column;
                text-align: center;
            }
            .admissions-content {
                max-width: 100%;
            }
        }

        /* ==========================================
           7. GLOBAL DARK THEME OVERRIDES
           ========================================== */
        body.dark-theme-active h1,
        body.dark-theme-active h2,
        body.dark-theme-active h3,
        body.dark-theme-active h4,
        body.dark-theme-active h5,
        body.dark-theme-active h6,
        body.dark-theme-active .mentor-role,
        body.dark-theme-active .timeline-year,
        body.dark-theme-active .leader-role {
            color: #ffffff !important;
        }
        body.dark-theme-active p {
            color: #94a3b8 !important;
        }
        body.dark-theme-active .mentor-info h3 {
            color: var(--accent-light) !important;
        }

        /* ==========================================
           NAVBAR OVERRIDES
           ========================================== */
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

        /* ==========================================
           FACULTY DIRECTORY CONTROLS
           ========================================== */
        .faculty-controls {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 40px;
            align-items: center;
        }
        .search-box-wrapper {
            position: relative;
            width: 100%;
            max-width: 500px;
        }
        .faculty-search {
            width: 100%;
            padding: 12px 20px 12px 48px !important;
            border-radius: 30px !important;
            border: 1px solid var(--glass-border) !important;
            background: rgba(255, 255, 255, 0.03) !important;
            color: var(--text-color) !important;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        body.dark-theme-active .faculty-search {
            background: rgba(255, 255, 255, 0.01) !important;
            color: #ffffff !important;
        }
        .faculty-search:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.15) !important;
            outline: none;
        }
        .search-box-wrapper .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
            transition: color 0.3s ease;
        }
        .faculty-search:focus + .search-icon {
            color: var(--accent);
        }
        .faculty-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .filter-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            color: var(--text-muted);
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body.dark-theme-active .filter-btn {
            background: rgba(255, 255, 255, 0.01);
            color: var(--text-muted);
        }
        .filter-btn:hover {
            border-color: var(--accent);
            color: var(--accent);
            transform: translateY(-2px);
        }
        .filter-btn.active {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            color: var(--text-color-inverse) !important;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.25);
        }
        body.dark-theme-active .filter-btn.active {
            background: var(--accent-light) !important;
            border-color: var(--accent-light) !important;
            color: #0b1e4f !important;
        }
        .faculty-card {
            transition: opacity 0.4s ease, transform 0.4s ease;
        }
        .faculty-card.hidden {
            display: none !important;
        }

        /* Collapsible Faculty Cards */
        .faculty-card.collapsed-card {
            display: none !important;
        }
        .faculty-grid.expanded .faculty-card.collapsed-card {
            display: block !important;
            animation: fadeInUpFaculty 0.5s ease forwards;
        }
        @keyframes fadeInUpFaculty {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .faculty-toggle-btn {
            margin-top: 15px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--glass-border);
            color: var(--accent);
            padding: 10px 24px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        body.dark-theme-active .faculty-toggle-btn {
            background: rgba(255, 255, 255, 0.02);
            color: var(--accent-light);
        }
        .faculty-toggle-btn:hover {
            background: var(--accent);
            color: var(--text-color-inverse) !important;
            border-color: var(--accent);
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
            transform: translateY(-2px);
        }
        body.dark-theme-active .faculty-toggle-btn:hover {
            background: var(--accent-light);
            color: #0b1e4f !important;
            border-color: var(--accent-light);
        }
        .faculty-toggle-btn svg {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .faculty-toggle-btn.expanded svg {
            transform: rotate(180deg);
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
                <h1 class="reveal">About Our Academy</h1>
                <div class="breadcrumbs reveal" style="transition-delay: 0.1s;">
                    <a href="index.php">Home</a>
                    <span>/</span>
                    <span class="current">About Us</span>
                </div>
            </div>
        </section>

        <!-- SECTION 1: INTRODUCTION -->
        <?php if ($about['show_intro']): ?>
        <section class="section-padding">
            <div class="container">
                <div class="intro-grid">
                    <div class="intro-media reveal reveal-left">
                        <div class="intro-shield-card">
                            <?php if (!empty($about['intro_image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($about['intro_image_path']); ?>" alt="Gurukul Campus Area">
                            <?php else: ?>
                                <svg class="intro-shield-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    <path d="M12 6v12M8 12h8"/>
                                </svg>
                            <?php endif; ?>
                            <div class="intro-badge">
                                <h4>7+</h4>
                                <p>Years Legacy</p>
                            </div>
                        </div>
                    </div>
                    <div class="intro-content reveal reveal-right" style="transition-delay: 0.1s;">
                        <span class="news-badge" style="background: rgba(13, 148, 136, 0.08); color: var(--secondary-light);">Established 2019</span>
                        <h2><?php echo htmlspecialchars($about['intro_heading'] ?? 'Fusing Heritage Values with Technical Innovation'); ?></h2>
                        <p><?php echo nl2br(htmlspecialchars($about['intro_desc_1'] ?? '')); ?></p>
                        <p><?php echo nl2br(htmlspecialchars($about['intro_desc_2'] ?? '')); ?></p>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- SECTION 2 & 3: VISION, MISSION & PHILOSOPHY -->
        <?php if ($about['show_vision_mission']): ?>
        <section class="section-padding" style="background-color: rgba(15, 23, 42, 0.015);">
            <div class="container">
                <div class="section-title reveal">
                    <h2>Our Noble Foundations</h2>
                    <p>Gurukul Academy is established upon robust academic paradigms, ethical principles, and an unwavering commitment to modeling future-ready global leaders.</p>
                </div>
                
                <div class="vision-mission-grid">
                    <!-- Vision Card -->
                    <div class="vm-card reveal">
                        <div class="vm-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </div>
                        <h3><?php echo htmlspecialchars($about['vision_title'] ?? 'Our Noble Vision'); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($about['vision_desc'] ?? '')); ?></p>
                    </div>
                    <!-- Mission Card -->
                    <div class="vm-card reveal" style="transition-delay: 0.1s;">
                        <div class="vm-icon" style="background: rgba(13, 148, 136, 0.08); color: var(--secondary-light);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                        </div>
                        <h3><?php echo htmlspecialchars($about['mission_title'] ?? 'Our Pure Mission'); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($about['mission_desc'] ?? '')); ?></p>
                    </div>
                    <!-- Core philosophy Card -->
                    <div class="vm-card reveal" style="transition-delay: 0.2s;">
                        <div class="vm-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h3><?php echo htmlspecialchars($about['philosophy_title'] ?? 'Our Core Philosophy'); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($about['philosophy_desc'] ?? '')); ?></p>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>        <!-- SECTION 4: LEADERSHIP MESSAGE & EXPERT MENTORS -->
        <?php if ($about['show_leadership'] && !empty($leaders)): ?>
        <section class="section-padding leadership-message-section">
            <div class="container">
                <div class="section-title reveal">
                    <h2>Elite Leadership & Mentors</h2>
                    <p>Meet our advisory board and lead educators who guide, inspire, and drive excellence across every wing of Gurukul Academy.</p>
                </div>

                <?php
                // Extract key quote leader (usually first leader with a non-empty message)
                $quote_leader = null;
                foreach ($leaders as $leader) {
                    if (!empty($leader['message'])) {
                        $quote_leader = $leader;
                        break;
                    }
                }
                if (!$quote_leader && !empty($leaders)) {
                    $quote_leader = $leaders[0];
                }
                
                if ($quote_leader):
                ?>
                <!-- Dynamic Leadership Message Box Row -->
                <div class="leadership-grid">
                    <div class="leader-card reveal reveal-left">
                        <div class="leader-photo-box" style="overflow: hidden; border-radius: var(--border-radius-sm);">
                            <?php if (!empty($quote_leader['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($quote_leader['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="<?php echo htmlspecialchars($quote_leader['name']); ?>">
                            <?php else: ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width: 35%; height: auto;">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="leader-info">
                            <span class="leader-role"><?php echo htmlspecialchars($quote_leader['designation']); ?></span>
                            <h3><?php echo htmlspecialchars($quote_leader['name']); ?></h3>
                            <p style="font-size: 0.85rem; margin-top: 4px;"><?php echo htmlspecialchars($quote_leader['profile_description']); ?></p>
                        </div>
                    </div>
                    <div class="leader-message-box reveal reveal-right" style="transition-delay: 0.1s;">
                        <div class="leader-quote">
                            <?php echo nl2br(htmlspecialchars($quote_leader['message'] ?? 'Inspiring comprehensive ethical vision, leadership training, and critical problem-solving skills across the entire student life cycle.')); ?>
                        </div>
                        <div class="leader-signature">
                            — <?php echo htmlspecialchars($quote_leader['name']); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Dynamic Faculty & Advisors Grid -->
                <div class="mentor-grid">
                    <?php 
                    $delay_count = 0;
                    foreach ($leaders as $leader): 
                        // Skip the quote leader as they are already highlighted in the message box above
                        if ($quote_leader && $leader['id'] == $quote_leader['id']) {
                            continue;
                        }
                        $delay = $delay_count * 0.1;
                        $delay_count++;
                    ?>
                    <div class="mentor-card reveal" style="transition-delay: <?php echo $delay; ?>s;">
                        <div class="mentor-photo-wrapper" style="overflow: hidden;">
                            <?php if (!empty($leader['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($leader['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="<?php echo htmlspecialchars($leader['name']); ?>">
                            <?php else: ?>
                                <svg class="mentor-photo-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="mentor-info">
                            <div class="mentor-role"><?php echo htmlspecialchars($leader['designation']); ?></div>
                            <h3><?php echo htmlspecialchars($leader['name']); ?></h3>
                            <p class="mentor-bio"><?php echo htmlspecialchars($leader['profile_description']); ?></p>
                            <div class="mentor-socials">
                                <a href="#" class="mentor-social-btn"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></svg></a>
                                <a href="#" class="mentor-social-btn"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- SECTION 5: ACHIEVEMENTS & ACCOLADES -->
        <?php if ($about['show_achievements']): ?>
        <section class="section-padding">
            <div class="container">
                <div class="section-title reveal">
                    <h2>Key Academic Accolades</h2>
                    <p>Recognitions and benchmark scores highlighting our unwavering commitment to educational and leadership success.</p>
                </div>

                <div class="achievements-grid">
                    <!-- Achievement 1 -->
                    <div class="achievement-card reveal">
                        <div class="achievement-icon">
                            <?php echo htmlspecialchars($about['achievement_1_metric'] ?? '100%'); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($about['achievement_1_title'] ?? ''); ?></h3>
                        <p><?php echo htmlspecialchars($about['achievement_1_desc'] ?? ''); ?></p>
                    </div>
                    <!-- Achievement 2 -->
                    <div class="achievement-card reveal" style="transition-delay: 0.1s;">
                        <div class="achievement-icon" style="background: rgba(13, 148, 136, 0.08); color: var(--secondary-light); border-color: rgba(13, 148, 136, 0.2);">
                            <?php echo htmlspecialchars($about['achievement_2_metric'] ?? 'Rank #1'); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($about['achievement_2_title'] ?? ''); ?></h3>
                        <p><?php echo htmlspecialchars($about['achievement_2_desc'] ?? ''); ?></p>
                    </div>
                    <!-- Achievement 3 -->
                    <div class="achievement-card reveal" style="transition-delay: 0.2s;">
                        <div class="achievement-icon">
                            <?php echo htmlspecialchars($about['achievement_3_metric'] ?? 'A+'); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($about['achievement_3_title'] ?? ''); ?></h3>
                        <p><?php echo htmlspecialchars($about['achievement_3_desc'] ?? ''); ?></p>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- SECTION 6: HERITAGE TIMELINE -->
        <?php if ($about['show_timeline'] && !empty($timeline)): ?>
        <section class="section-padding timeline-section">
            <div class="container">
                <div class="section-title reveal">
                    <h2>Our Glorious Journey</h2>
                    <p>Trace the milestones of Gurukul Academy as we have expanded from a modest schoolhouse to a state-of-the-art modern educational harbor.</p>
                </div>
                
                <div class="timeline-container">
                    <?php 
                    $count = 0;
                    foreach ($timeline as $milestone): 
                        $count++;
                        $align_class = ($count % 2 === 1) ? 'timeline-item-left' : 'timeline-item-right';
                        $reveal_class = ($count % 2 === 1) ? 'reveal-left' : 'reveal-right';
                        $delay = ($count - 1) * 0.1;
                    ?>
                    <div class="timeline-item <?php echo $align_class; ?> reveal <?php echo $reveal_class; ?>" style="transition-delay: <?php echo $delay; ?>s;">
                        <div class="timeline-dot"></div>
                        <div class="timeline-box">
                            <div class="timeline-year"><?php echo htmlspecialchars($milestone['milestone_year']); ?></div>
                            <h4><?php echo htmlspecialchars($milestone['milestone_title']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($milestone['milestone_desc'])); ?></p>
                            <?php if (!empty($milestone['image_path'])): ?>
                                <div class="timeline-img-box" style="margin-top: 16px; border-radius: var(--border-radius-sm); overflow: hidden; border: 1px solid rgba(255,255,255,0.08); aspect-ratio: 1.6; background: rgba(0,0,0,0.2);">
                                    <img src="<?php echo htmlspecialchars($milestone['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="<?php echo htmlspecialchars($milestone['milestone_title']); ?>">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- SECTION 4B: EXPERT FACULTY DIRECTORY -->
        <?php if (($about['show_faculty'] ?? 1) && !empty($faculties)): ?>
        <section class="section-padding faculty-section" style="background-color: rgba(15, 23, 42, 0.005); border-top: 1px solid var(--glass-border);">
            <div class="container">
                <div class="section-title reveal" style="margin-bottom: 35px;">
                    <h2>Expert Faculty & Educators</h2>
                    <p>Fostering academic excellence, moral leadership, and logical thinking through experienced, subject-matter specialists.</p>
                </div>

                <!-- Faculty Search and Filter Controls -->
                <div class="faculty-controls reveal">
                    <div class="search-box-wrapper">
                        <input type="text" id="faculty-search-input" placeholder="Search faculty by name or subject..." class="form-control faculty-search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                    
                    <div class="faculty-filters">
                        <button class="filter-btn active" data-filter="all">All Faculty</button>
                        <button class="filter-btn" data-filter="maths">Mathematics</button>
                        <button class="filter-btn" data-filter="science">Science</button>
                        <button class="filter-btn" data-filter="languages">Languages</button>
                        <button class="filter-btn" data-filter="social">Social Sciences</button>
                        <button class="filter-btn" data-filter="pre-primary">Pre-Primary</button>
                        <button class="filter-btn" data-filter="others">Others</button>
                    </div>
                </div>

                <!-- Faculty Members Grid -->
                <div class="mentor-grid faculty-grid" id="faculty-grid">
                    <?php 
                    $delay_idx = 0;
                    foreach ($faculties as $fac): 
                        $delay = ($delay_idx % 4) * 0.05;
                        
                        // Map subject to filter categories
                        $sub = strtolower($fac['subject'] ?? '');
                        $des = strtolower($fac['designation'] ?? '');
                        
                        $category = 'others';
                        if (strpos($sub, 'math') !== false || strpos($sub, 'alg') !== false || strpos($sub, 'geom') !== false) {
                            $category = 'maths';
                        } elseif (strpos($sub, 'sci') !== false || strpos($sub, 'phys') !== false || strpos($sub, 'chem') !== false || strpos($sub, 'bio') !== false) {
                            $category = 'science';
                        } elseif (strpos($sub, 'eng') !== false || strpos($sub, 'marath') !== false || strpos($sub, 'hind') !== false || strpos($sub, 'lang') !== false) {
                            $category = 'languages';
                        } elseif (strpos($sub, 'social') !== false || strpos($sub, 's.st') !== false || strpos($sub, 'hist') !== false || strpos($sub, 'geo') !== false || strpos($sub, 'pol') !== false) {
                            $category = 'social';
                        } elseif (strpos($sub, 'pre-primary') !== false || strpos($des, 'pre-primary') !== false || strpos($sub, 'nursery') !== false || strpos($sub, 'kg') !== false) {
                            $category = 'pre-primary';
                        }

                        $card_class = 'mentor-card faculty-card reveal';
                        if ($delay_idx >= 8) {
                            $card_class .= ' collapsed-card';
                        }
                        $delay_idx++;
                    ?>
                    <div class="<?php echo $card_class; ?>" 
                         style="transition-delay: <?php echo $delay; ?>s;"
                         data-name="<?php echo strtolower(sanitize($fac['name'])); ?>"
                         data-subject="<?php echo strtolower(sanitize($fac['subject'])); ?>"
                         data-category="<?php echo $category; ?>">
                        <div class="mentor-photo-wrapper" style="overflow: hidden;">
                            <?php if (!empty($fac['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($fac['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="<?php echo htmlspecialchars($fac['name']); ?>">
                            <?php else: ?>
                                <svg class="mentor-photo-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width: 35%; height: auto; margin: auto; display: block; padding-top: 20px; color: var(--text-muted);">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="mentor-info">
                            <div class="mentor-role">
                                <?php echo htmlspecialchars($fac['designation']); ?>
                                <?php if (!empty($fac['subject'])): ?>
                                    — <?php echo htmlspecialchars($fac['subject']); ?>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($fac['name']); ?></h3>
                            <?php if (!empty($fac['qualification'])): ?>
                                <div class="mentor-qualification" style="font-size: 0.8rem; color: var(--accent-light); font-weight: 600; margin-bottom: 6px;"><?php echo htmlspecialchars($fac['qualification']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($fac['experience'])): ?>
                                <div class="mentor-experience" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">Experience: <?php echo htmlspecialchars($fac['experience']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($fac['quote']) || !empty($fac['teaching_philosophy'])): ?>
                                <p class="mentor-bio" style="font-style: italic; font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">
                                    "<?php echo htmlspecialchars($fac['quote'] ?: $fac['teaching_philosophy']); ?>"
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Zero Search Results State -->
                <div id="no-faculty-results" style="display: none; text-align: center; padding: 48px; background: rgba(255, 255, 255, 0.02); border-radius: var(--border-radius-sm); border: 1px dashed var(--glass-border); margin-top: 24px;">
                    <p style="color: var(--text-muted); font-size: 1.1rem; margin: 0;">No faculty members found matching your search criteria.</p>
                </div>

                <!-- Toggle Button for Collapsed Cards -->
                <?php if (count($faculties) > 8): ?>
                <div class="text-center reveal" id="faculty-toggle-container" style="margin-top: 35px;">
                    <button class="faculty-toggle-btn" id="faculty-toggle-btn" aria-expanded="false" aria-controls="faculty-grid">
                        <span>Show More Educators</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                </div>
                <?php endif; ?>

            </div>
        </section>
        <?php endif; ?>

        <script>
        let facultyExpanded = false;

        document.addEventListener('DOMContentLoaded', () => {
            initFacultyFilters();
            initFacultyCollapsible();
        });

        function initFacultyCollapsible() {
            const toggleBtn = document.getElementById('faculty-toggle-btn');
            const gridElement = document.getElementById('faculty-grid');
            
            if (!toggleBtn || !gridElement) return;
            
            toggleBtn.addEventListener('click', () => {
                facultyExpanded = !facultyExpanded;
                
                if (facultyExpanded) {
                    gridElement.classList.add('expanded');
                    toggleBtn.classList.add('expanded');
                    toggleBtn.querySelector('span').textContent = 'Show Less';
                    toggleBtn.setAttribute('aria-expanded', 'true');
                } else {
                    gridElement.classList.remove('expanded');
                    toggleBtn.classList.remove('expanded');
                    toggleBtn.querySelector('span').textContent = 'Show More Educators';
                    toggleBtn.setAttribute('aria-expanded', 'false');
                    
                    // Smooth scroll to the top of the faculty section when collapsing back
                    const section = document.querySelector('.faculty-section');
                    if (section) {
                        section.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        }

        function initFacultyFilters() {
            const searchInput = document.getElementById('faculty-search-input');
            const filterBtns = document.querySelectorAll('.filter-btn');
            const facultyCards = document.querySelectorAll('.faculty-card');
            const noResults = document.getElementById('no-faculty-results');
            const gridElement = document.getElementById('faculty-grid');
            const toggleContainer = document.getElementById('faculty-toggle-container');

            if (!searchInput || facultyCards.length === 0) return;

            let currentFilter = 'all';
            let searchQuery = '';

            function filterFaculty() {
                let visibleCount = 0;
                const isSearchingOrFiltering = (searchQuery !== '' || currentFilter !== 'all');

                if (isSearchingOrFiltering) {
                    if (gridElement) gridElement.classList.add('expanded');
                    if (toggleContainer) toggleContainer.style.display = 'none';
                } else {
                    if (gridElement) {
                        if (facultyExpanded) {
                            gridElement.classList.add('expanded');
                        } else {
                            gridElement.classList.remove('expanded');
                        }
                    }
                    if (toggleContainer) toggleContainer.style.display = 'block';
                }

                facultyCards.forEach(card => {
                    const name = card.getAttribute('data-name') || '';
                    const subject = card.getAttribute('data-subject') || '';
                    const category = card.getAttribute('data-category') || '';

                    const matchesSearch = name.includes(searchQuery) || subject.includes(searchQuery);
                    const matchesFilter = currentFilter === 'all' || category === currentFilter;

                    if (matchesSearch && matchesFilter) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }
                });

                if (visibleCount === 0) {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }
            }

            // Search input handler
            searchInput.addEventListener('input', (e) => {
                searchQuery = e.target.value.toLowerCase().trim();
                filterFaculty();
            });

            // Filter button handlers
            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    currentFilter = btn.getAttribute('data-filter') || 'all';
                    filterFaculty();
                });
            });
        }
        </script>

        <!-- SECTION 7: CALL TO ACTION -->
        <?php if ($about['show_cta']): ?>
        <section class="admissions-cta-section section-padding">
            <div class="container">
                <div class="admissions-banner reveal reveal-scale">
                    <div class="admissions-content">
                        <h2>Begin Your Child's Journey Today</h2>
                        <p>Enrollment inquiries are now active for the upcoming academic session. Connect with our admissions desk to request catalogs or campus routing directions.</p>
                    </div>
                    <div class="admissions-cta">
                        <a href="contact.php#admissions" class="btn btn-accent">Apply For Admissions</a>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

<?php
// Load shared footer
include_once 'includes/footer.php';
?>
