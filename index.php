<?php
/**
 * ========================================================
 * DYNAMIC FRONTEND HOMEPAGE PORTAL (GURUKUL)
 * ========================================================
 */

// 1. Establish secure PDO Database connection
require_once 'config/db.php';

$page_title = 'Gurukul Academy | Academic Excellence & Holistic Learning';
$meta_description = 'Welcome to Gurukul Academy - A premium institute of learning fostering academic excellence, moral leadership, and holistic development. Explore our admissions, results, and campus.';

// 2. Fetch Homepage content details (Row id = 1)
try {
    $stmt_home = $pdo->prepare("SELECT * FROM `homepage_content` WHERE `id` = 1 LIMIT 1");
    $stmt_home->execute();
    $home = $stmt_home->fetch();
    
    // Fetch featured gallery preview items (Max 6)
    $stmt_feat_gallery = $pdo->prepare("SELECT g.*, c.name AS category_name FROM `gallery` g JOIN `gallery_categories` c ON g.category_id = c.id WHERE g.is_featured = 1 ORDER BY g.uploaded_at DESC LIMIT 6");
    $stmt_feat_gallery->execute();
    $featured_gallery = $stmt_feat_gallery->fetchAll();
    
    // Fetch latest featured news & events highlights (Max 3)
    $stmt_feat_news = $pdo->prepare("SELECT * FROM `news_events` WHERE `is_featured` = 1 ORDER BY `created_at` DESC LIMIT 3");
    $stmt_feat_news->execute();
    $featured_news = $stmt_feat_news->fetchAll();
    
    // If featured news is empty, fallback to last 3 entries
    if (empty($featured_news)) {
        $featured_news = $pdo->query("SELECT * FROM `news_events` ORDER BY `created_at` DESC LIMIT 3")->fetchAll();
    }
    
    // Fetch featured toppers results (Max 3)
    $stmt_toppers = $pdo->prepare("SELECT * FROM `results` WHERE `is_featured` = 1 ORDER BY `created_at` DESC LIMIT 3");
    $stmt_toppers->execute();
    $featured_toppers = $stmt_toppers->fetchAll();
} catch (PDOException $e) {
    die("CMS Critical Render Error: Database connectivity failure.");
}

// 3. Inject custom local stylesheet blocks in the header
$custom_css = '
    <style>
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 150px 0 100px;
            background: linear-gradient(135deg, #090d16 0%, #111827 50%, #1e1b4b 100%);
            color: var(--bg-white);
            overflow: hidden;
        }
        .hero::before {
            content: "";
            position: absolute;
            top: -10%;
            right: -10%;
            width: 50%;
            height: 60%;
            background: radial-gradient(circle, rgba(13, 148, 136, 0.25) 0%, transparent 60%);
            filter: blur(80px);
            z-index: 1;
        }
        .hero::after {
            content: "";
            position: absolute;
            bottom: -10%;
            left: -10%;
            width: 40%;
            height: 50%;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.15) 0%, transparent 60%);
            filter: blur(80px);
            z-index: 1;
        }
        .hero-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        .hero-content {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .hero-tag {
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(13, 148, 136, 0.15);
            border: 1px solid rgba(13, 148, 136, 0.3);
            color: var(--secondary-light);
            padding: 8px 18px;
            border-radius: 30px;
            font-family: var(--font-heading);
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .hero-title {
            color: var(--bg-white);
            font-size: clamp(2.8rem, 6.5vw, 4.5rem);
            line-height: 1.1;
            letter-spacing: -0.02em;
        }
        .hero-description {
            font-size: 1.15rem;
            line-height: 1.7;
            color: #94a3b8;
            max-width: 580px;
        }
        .hero-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .hero .btn-secondary {
            color: #FFFFFF !important;
            border-color: var(--secondary-light) !important;
        }
        .hero .btn-secondary:hover {
            background: rgba(13, 148, 136, 0.15) !important;
            color: #FFFFFF !important;
        }
        .hero-visual {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .hero-badge-container {
            position: absolute;
            bottom: 30px;
            left: -30px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            padding: 20px 24px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 16px;
            z-index: 3;
        }
        .hero-badge-icon {
            width: 48px;
            height: 48px;
            background: var(--accent);
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bg-white);
            font-size: 1.4rem;
        }
        .hero-badge-text h4 {
            color: var(--bg-white);
            font-size: 1.05rem;
            margin-bottom: 2px;
        }
        .hero-badge-text p {
            font-size: 0.85rem;
            color: #94a3b8;
        }
        .hero-illustration {
            width: 100%;
            max-width: 460px;
            height: 460px;
            border-radius: var(--border-radius-lg);
            background: linear-gradient(135deg, rgba(13, 148, 136, 0.2) 0%, rgba(217, 119, 6, 0.15) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        .hero-illustration img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: inherit;
        }
        .scroll-indicator {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            font-family: var(--font-heading);
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #64748b;
            z-index: 5;
        }
        .scroll-mouse {
            width: 24px;
            height: 40px;
            border: 2px solid #475569;
            border-radius: 12px;
            position: relative;
        }
        .scroll-wheel {
            width: 4px;
            height: 8px;
            background-color: var(--secondary-light);
            border-radius: 2px;
            position: absolute;
            top: 6px;
            left: 50%;
            transform: translateX(-50%);
            animation: mouseScroll 1.8s infinite;
        }
        @keyframes mouseScroll {
            0% { opacity: 0; transform: translate(-50%, 0); }
            40% { opacity: 1; }
            100% { opacity: 0; transform: translate(-50%, 14px); }
        }
        
        .stats-section {
            background-color: var(--primary);
            padding: 60px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            z-index: 5;
        }
        body.dark-theme-active .stats-section {
            background-color: var(--bg-card-dark);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            text-align: center;
        }
        .stat-number {
            font-family: var(--font-heading);
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 800;
            color: var(--accent-light);
            line-height: 1.1;
            margin-bottom: 8px;
        }
        .stat-label {
            color: #94a3b8;
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: 0.02em;
        }
        
        .pillars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
        }
        
        .about-preview-grid {
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            gap: 80px;
            align-items: center;
        }
        .about-media {
            position: relative;
        }
        .about-badge {
            position: absolute;
            bottom: -30px;
            right: -20px;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
            color: var(--bg-white);
            padding: 24px 32px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-lg);
            z-index: 3;
        }
        .about-badge h4 {
            color: var(--bg-white);
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.1;
        }
        .about-badge p {
            color: #ccfbf1 !important;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .about-illustration {
            width: 100%;
            height: 480px;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.05);
            box-shadow: var(--shadow-md);
        }
        .about-illustration img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .about-content-box {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .about-checklist {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin: 10px 0 10px;
        }
        .about-check-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-dark);
        }
        body.dark-theme-active .about-check-item {
            color: #cbd5e1;
        }
        .about-check-icon {
            color: var(--secondary-light);
            flex-shrink: 0;
        }
        
        .featured-gallery-section {
            background-color: rgba(13, 148, 136, 0.01);
            border-top: 1px solid rgba(15, 23, 42, 0.02);
            border-bottom: 1px solid rgba(15, 23, 42, 0.02);
        }
        body.dark-theme-active .featured-gallery-section {
            background-color: rgba(9, 13, 22, 0.3);
            border-color: var(--glass-border);
        }
        .home-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
        }
        .gallery-preview-card {
            border-radius: var(--border-radius-md);
            overflow: hidden;
            position: relative;
            height: 280px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15, 23, 42, 0.04);
            cursor: pointer;
        }
        .gallery-preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s var(--ease-premium);
        }
        .gallery-preview-card:hover .gallery-preview-image {
            transform: scale(1.06);
        }
        .gallery-preview-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.90) 0%, rgba(15, 23, 42, 0.4) 60%, transparent 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 30px;
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 2;
        }
        .gallery-preview-card:hover .gallery-preview-overlay {
            opacity: 1;
        }
        .gallery-preview-overlay span {
            color: var(--accent-light);
            font-family: var(--font-heading);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .gallery-preview-overlay h4 {
            color: var(--bg-white);
            font-size: 1.25rem;
            font-weight: 700;
        }
        .play-indicator-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(13, 148, 136, 0.9);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
            z-index: 1;
            transition: all 0.3s ease;
        }
        .gallery-preview-card:hover .play-indicator-overlay {
            background: var(--accent);
            transform: translate(-50%, -50%) scale(1.1);
        }
        
        .home-news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
        }
        .home-news-card {
            background-color: var(--bg-white);
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15, 23, 42, 0.04);
            display: flex;
            flex-direction: column;
            transition: var(--transition-smooth);
        }
        body.dark-theme-active .home-news-card {
            background-color: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .home-news-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(13, 148, 136, 0.15);
        }
        .home-news-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        .home-news-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s var(--ease-premium);
        }
        .home-news-card:hover .home-news-image img {
            transform: scale(1.06);
        }
        .home-news-badge {
            position: absolute;
            top: 16px;
            left: 16px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(8px);
            padding: 6px 14px;
            border-radius: 30px;
            font-family: var(--font-heading);
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--accent-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: 1px solid var(--glass-border);
        }
        .home-news-info {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex-grow: 1;
        }
        .home-news-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            gap: 12px;
        }
        .home-news-info h3 {
            font-size: 1.2rem;
            line-height: 1.4;
        }
        .home-news-info p {
            font-size: 0.92rem;
            line-height: 1.6;
        }
        .home-news-link {
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--secondary-light);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
        }
        .home-news-link:hover {
            color: var(--accent-light);
        }
        
        .results-highlight-section {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.02) 0%, rgba(13, 148, 136, 0.02) 100%);
            border-top: 1px solid rgba(15, 23, 42, 0.02);
            border-bottom: 1px solid rgba(15, 23, 42, 0.02);
        }
        body.dark-theme-active .results-highlight-section {
            background: linear-gradient(135deg, #090d16 0%, #131b2e 100%);
            border-color: var(--glass-border);
        }
        .results-preview-grid {
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            gap: 80px;
            align-items: center;
        }
        .results-indicators {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .results-bar-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .results-bar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .results-bar-label {
            font-family: var(--font-heading);
            font-weight: 600;
            color: var(--primary);
            font-size: 0.95rem;
        }
        .results-bar-value {
            font-family: var(--font-heading);
            font-weight: 700;
            color: var(--accent);
        }
        .results-bar-track {
            width: 100%;
            height: 10px;
            background: rgba(15, 23, 42, 0.06);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.02);
        }
        body.dark-theme-active .results-bar-track {
            background: rgba(255,255,255,0.05);
        }
        .results-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--secondary-light), var(--accent-light));
            border-radius: 10px;
            width: 0%;
            transition: width 1.2s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .results-visual-block {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
        }
        .spotlight-topper-card {
            background-color: var(--bg-white);
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 2px solid rgba(217, 119, 6, 0.1);
            position: relative;
            text-align: center;
            transition: var(--transition-smooth);
            max-width: 320px;
            margin: 0 auto;
        }
        body.dark-theme-active .spotlight-topper-card {
            background-color: var(--bg-card-dark);
            border-color: rgba(217, 119, 6, 0.15);
        }
        .spotlight-topper-card::before {
            content: "";
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            bottom: 12px;
            border: 1px dashed rgba(217, 119, 6, 0.3);
            border-radius: var(--border-radius-sm);
            pointer-events: none;
            z-index: 2;
        }
        .spotlight-topper-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-glow-accent);
            border-color: var(--accent);
        }
        .topper-score-label {
            position: absolute;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            padding: 6px 18px;
            border-radius: 30px;
            color: var(--accent-light);
            font-family: var(--font-heading);
            font-weight: 800;
            font-size: 1.05rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 3;
        }
        .spotlight-topper-photo {
            height: 200px;
            background: linear-gradient(135deg, #131b2e 0%, #1e1b4b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-light);
            overflow: hidden;
        }
        .spotlight-topper-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .spotlight-topper-info {
            padding: 20px;
            position: relative;
            z-index: 3;
        }
        .spotlight-topper-info h3 {
            font-size: 1.15rem;
            margin-bottom: 2px;
        }
        .spotlight-topper-rank {
            font-family: var(--font-heading);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--secondary-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
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
            color: var(--bg-white);
            margin-bottom: 16px;
            font-size: clamp(1.8rem, 3.5vw, 2.4rem);
        }
        .admissions-content p {
            color: #ccfbf1;
            font-size: 1.05rem;
            line-height: 1.6;
        }
        .admissions-cta {
            flex-shrink: 0;
            position: relative;
            z-index: 2;
        }
        
        @media (max-width: 992px) {
            .hero {
                padding: 120px 0 80px;
                min-height: auto;
            }
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 56px;
                text-align: center;
            }
            .hero-tag {
                align-self: center;
            }
            .hero-content {
                align-items: center;
            }
            .hero-description {
                margin: 0 auto;
            }
            .hero-actions {
                justify-content: center;
            }
            .hero-illustration {
                height: 380px;
                margin: 0 auto;
            }
            .hero-badge-container {
                left: 50%;
                transform: translateX(-50%);
                bottom: -20px;
                width: 90%;
            }
            .scroll-indicator {
                display: none;
            }
            .about-preview-grid {
                grid-template-columns: 1fr;
                gap: 48px;
            }
            .about-media {
                max-width: 480px;
                margin: 0 auto;
                width: 100%;
            }
            .about-badge {
                right: 10px;
            }
            .about-checklist {
                grid-template-columns: 1fr;
            }
            .results-preview-grid {
                grid-template-columns: 1fr;
                gap: 48px;
            }
            .admissions-banner {
                padding: 40px;
                flex-direction: column;
                text-align: center;
            }
            .admissions-content {
                max-width: 100%;
            }
        }
    </style>
';

// Load shared header
include_once 'includes/header.php';
?>

    <main>
        <!-- ==========================================
           1. HERO BANNER SECTION (CMS Dynamic)
           ========================================== -->
        <section class="hero">
            <!-- Floating Parallax Ambient Glows -->
            <div class="parallax-layer parallax-glow-1"></div>
            <div class="parallax-layer parallax-glow-2"></div>
            
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-content reveal reveal-left">
                        <div class="hero-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: -2px;">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                <path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/>
                            </svg>
                            Admissions Open 2026-27
                        </div>
                        
                        <h1 class="hero-title"><?php echo sanitize($home['hero_title']); ?></h1>
                        <p class="hero-description"><?php echo sanitize($home['hero_subtitle']); ?></p>
                        
                        <div class="hero-actions">
                            <?php if (!empty($home['hero_btn_text_1'])): ?>
                                <a href="<?php echo sanitize($home['hero_btn_link_1']); ?>" class="btn btn-accent"><?php echo sanitize($home['hero_btn_text_1']); ?></a>
                            <?php endif; ?>
                            <?php if (!empty($home['hero_btn_text_2'])): ?>
                                <a href="<?php echo sanitize($home['hero_btn_link_2']); ?>" class="btn btn-secondary"><?php echo sanitize($home['hero_btn_text_2']); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="hero-visual reveal reveal-right">
                        <div class="hero-badge-container float-anim">
                            <div class="hero-badge-icon">★</div>
                            <div class="hero-badge-text">
                                <h4>National Rankings</h4>
                                <p>Grooming Global Leaders</p>
                            </div>
                        </div>
                        <div class="hero-illustration">
                            <img src="<?php echo sanitize($home['hero_image_path']); ?>" alt="Gurukul Hero Banner">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mouse scroll animator -->
            <div class="scroll-indicator">
                <span>Scroll Down</span>
                <div class="scroll-mouse">
                    <div class="scroll-wheel"></div>
                </div>
            </div>
        </section>

        <!-- ==========================================
           2. STATISTICS COUNTER SECTION (CMS Dynamic)
           ========================================== -->
        <?php if ($home['show_stats'] == 1): ?>
            <section class="stats-section">
                <div class="container">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number"><span class="counter-value" data-target="<?php echo intval($home['stat_number_1']); ?>">0</span>+</div>
                            <div class="stat-label"><?php echo sanitize($home['stat_label_1']); ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><span class="counter-value" data-target="<?php echo intval($home['stat_number_2']); ?>">0</span>+</div>
                            <div class="stat-label"><?php echo sanitize($home['stat_label_2']); ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><span class="counter-value" data-target="<?php echo intval($home['stat_number_3']); ?>">0</span>+</div>
                            <div class="stat-label"><?php echo sanitize($home['stat_label_3']); ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><span class="counter-value" data-target="<?php echo intval($home['stat_number_4']); ?>">0</span>%</div>
                            <div class="stat-label"><?php echo sanitize($home['stat_label_4']); ?></div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- ==========================================
           3. EDUCATIONAL PILLARS SECTION (CMS Dynamic)
           ========================================== -->
        <?php if ($home['show_pillars'] == 1): ?>
            <section class="section-padding">
                <div class="container">
                    <div class="section-title reveal">
                        <h2>Academic Foundations</h2>
                        <p>Bridging traditional ethical values with modern research-driven stem structures.</p>
                    </div>
                    
                    <div class="pillars-grid">
                        <!-- Pillar 1 -->
                        <div class="premium-card reveal reveal-up">
                            <div class="card-icon">📚</div>
                            <h3>Rigorous Curriculum</h3>
                            <p>Rigorous CBSE academic disciplines designed to trigger intellectual independence, logic, and reasoning.</p>
                        </div>
                        <!-- Pillar 2 -->
                        <div class="premium-card reveal reveal-up" style="transition-delay: 0.1s;">
                            <div class="card-icon">🤖</div>
                            <h3>STEM Robotics</h3>
                            <p>Advanced practical labs featuring micro-controller fabrications, artificial intelligence models, and CAD design.</p>
                        </div>
                        <!-- Pillar 3 -->
                        <div class="premium-card reveal reveal-up" style="transition-delay: 0.2s;">
                            <div class="card-icon">🏃</div>
                            <h3>Athletics Arena</h3>
                            <p>Elite sporting facilities supporting comprehensive physical training, sportsmanship, and mental resilience.</p>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- ==========================================
           4. ABOUT PREVIEW SECTION (CMS Dynamic)
           ========================================== -->
        <?php if ($home['show_about_preview'] == 1): ?>
            <section class="section-padding" style="background: rgba(13, 148, 136, 0.02);">
                <div class="container">
                    <div class="about-preview-grid">
                        <div class="about-media reveal reveal-left">
                            <div class="about-badge">
                                <h4>7+</h4>
                                <p>Years Legacy</p>
                            </div>
                            <div class="about-illustration">
                                <img src="images/library.png" alt="About Gurukul Library">
                            </div>
                        </div>
                        
                        <div class="about-content-box reveal reveal-right">
                            <div class="hero-tag">Legacy of Trust</div>
                            <h2>A Heritage of Moral Ethics & STEM Excellence</h2>
                            <p>Founded with the core goal of reshaping comprehensive learning, Gurukul Academy bridges ancient holistic value systems with modern scientific methodologies.</p>
                            
                            <div class="about-checklist">
                                <div class="about-check-item">
                                    <span class="about-check-icon">✔</span>
                                    <span>Elite STEM Laboratories</span>
                                </div>
                                <div class="about-check-item">
                                    <span class="about-check-icon">✔</span>
                                    <span>CBSE Growth Records</span>
                                </div>
                                <div class="about-check-item">
                                    <span class="about-check-icon">✔</span>
                                    <span>Moral Leadership Seminars</span>
                                </div>
                                <div class="about-check-item">
                                    <span class="about-check-icon">✔</span>
                                    <span>A+ Sports Infrastructure</span>
                                </div>
                            </div>
                            
                            <div>
                                <a href="about.php" class="btn btn-primary">Read Our Heritage &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- ==========================================
           5. FEATURED GALLERY PREVIEW (CMS Dynamic)
           ========================================== -->
        <?php if ($home['show_gallery_preview'] == 1 && !empty($featured_gallery)): ?>
            <section class="section-padding featured-gallery-section">
                <div class="container">
                    <div class="section-title reveal">
                        <h2>Life at Gurukul Campus</h2>
                        <p>Take a virtual tour through our modern, beautiful campus environments.</p>
                    </div>
                    
                    <div class="home-gallery-grid">
                        <?php foreach ($featured_gallery as $item): ?>
                            <div class="gallery-preview-card reveal reveal-scale" onclick="window.location.href='gallery.php'">
                                <img src="<?php echo sanitize($item['filepath']); ?>" class="gallery-preview-image" alt="<?php echo sanitize($item['title']); ?>">
                                
                                <?php if ($item['type'] === 'video'): ?>
                                    <div class="play-indicator-overlay">▶</div>
                                <?php endif; ?>
                                
                                <div class="gallery-preview-overlay">
                                    <span><?php echo sanitize($item['category_name']); ?></span>
                                    <h4><?php echo sanitize($item['title']); ?></h4>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- ==========================================
           6. LATEST NEWS & EVENTS SECTION (CMS Dynamic)
           ========================================== -->
        <?php if ($home['show_news_preview'] == 1 && !empty($featured_news)): ?>
            <section class="section-padding">
                <div class="container">
                    <div class="section-title reveal">
                        <h2>Latest Updates & Bulletins</h2>
                        <p>Stay informed with the latest academic publications, announcements, and fests.</p>
                    </div>
                    
                    <div class="home-news-grid">
                        <?php foreach ($featured_news as $news): ?>
                            <div class="home-news-card reveal reveal-up">
                                <div class="home-news-image">
                                    <div class="home-news-badge"><?php echo sanitize($news['type']); ?></div>
                                    <img src="<?php echo sanitize($news['filepath']); ?>" alt="<?php echo sanitize($news['title']); ?>">
                                </div>
                                <div class="home-news-info">
                                    <div class="home-news-meta">
                                        <span>📅 <?php echo date('M d, Y', strtotime($news['created_at'])); ?></span>
                                        <?php if ($news['type'] === 'event' && $news['event_date']): ?>
                                            <span style="color: var(--accent-light);">🕒 Event: <?php echo date('M d', strtotime($news['event_date'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <h3><?php echo sanitize($news['title']); ?></h3>
                                    <p><?php echo substr(sanitize(strip_tags($news['content'])), 0, 110) . '...'; ?></p>
                                    <a href="news.php" class="home-news-link">Read Bulletin &rarr;</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- ==========================================
           7. RESULTS HIGHLIGHT SECTION (CMS Dynamic)
           ========================================== -->
        <?php if ($home['show_results_preview'] == 1): ?>
            <section class="section-padding results-highlight-section">
                <div class="container">
                    <div class="results-preview-grid">
                        <div class="results-content reveal reveal-left">
                            <div class="hero-tag">Wall of Glory</div>
                            <h2>Exemplifying Elite Board & National Entrance Success</h2>
                            <p style="margin-bottom: 24px;">Our alumni continually break national records, scoring highest distinction brackets in board examinations and securing positions in global STEM academies.</p>
                            
                            <div class="results-indicators">
                                <div class="results-bar-item">
                                    <div class="results-bar-header">
                                        <span class="results-bar-label">CBSE XII board aggregate selections</span>
                                        <span class="results-bar-value">98.4%</span>
                                    </div>
                                    <div class="results-bar-track">
                                        <div class="results-bar-fill" data-percent="98.4%"></div>
                                    </div>
                                </div>
                                <div class="results-bar-item">
                                    <div class="results-bar-header">
                                        <span class="results-bar-label">National IIT-JEE / NEET selection ratios</span>
                                        <span class="results-bar-value">92.2%</span>
                                    </div>
                                    <div class="results-bar-track">
                                        <div class="results-bar-fill" data-percent="92.2%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="results-visual-block reveal reveal-right">
                            <?php if (!empty($featured_toppers)): ?>
                                <?php foreach ($featured_toppers as $topper): ?>
                                    <div class="spotlight-topper-card" onclick="window.location.href='results.php'">
                                        <div class="topper-score-label"><?php echo sanitize($topper['score_metric']); ?></div>
                                        <div class="spotlight-topper-photo">
                                            <img src="images/heritage_dance_1780230915767.png" alt="Topper Picture">
                                        </div>
                                        <div class="spotlight-topper-info">
                                            <h3><?php echo sanitize($topper['student_name']); ?></h3>
                                            <span class="spotlight-topper-rank"><?php echo sanitize($topper['exam_category']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Standard hardcoded fallback topper card if empty -->
                                <div class="spotlight-topper-card" onclick="window.location.href='results.php'">
                                    <div class="topper-score-label">99.6% CBSE</div>
                                    <div class="spotlight-topper-photo">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M18 21a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/></svg>
                                    </div>
                                    <div class="spotlight-topper-info">
                                        <h3>Karan Malhotra</h3>
                                        <span class="spotlight-topper-rank">District Rank 1</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- ==========================================
           8. CALL-TO-ACTION SECTION (CMS Dynamic admissions)
           ========================================== -->
        <?php if ($home['show_cta_banner'] == 1): ?>
            <section class="admissions-cta-section">
                <div class="container">
                    <div class="admissions-banner reveal reveal-scale">
                        <div class="admissions-content">
                            <h2><?php echo sanitize($home['cta_banner_title']); ?></h2>
                            <p><?php echo sanitize($home['cta_banner_desc']); ?></p>
                        </div>
                        <div class="admissions-cta">
                            <a href="<?php echo sanitize($home['cta_btn_link']); ?>" class="btn btn-accent" style="padding: 14px 36px; font-size: 1rem;"><?php echo sanitize($home['cta_btn_text']); ?></a>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <!-- Local scroll fills animations trigger script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const resultsBars = document.querySelectorAll('.results-bar-fill');
            const resultsObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const targetWidth = entry.target.getAttribute('data-percent');
                        entry.target.style.width = targetWidth;
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });
            resultsBars.forEach(bar => resultsObserver.observe(bar));
        });
    </script>

<?php 
// Load shared footer
include_once 'includes/footer.php'; 
?>
