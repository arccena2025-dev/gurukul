<?php
/**
 * ========================================================
 * DYNAMIC FRONTEND NEWS & EVENTS PORTAL (GURUKUL)
 * ========================================================
 */

// 1. Establish secure PDO Database connection
require_once 'config/db.php';

$page_title = 'News & Events | Gurukul Academy';
$meta_description = 'Stay updated with the latest news, announcements, academic calendars, and upcoming events at Gurukul Academy. Reserve RSVP seats online.';

// 2. Fetch Spotlight Featured Item and Archive Bulletins
try {
    // A. Fetch the most recent featured item for the big Spotlight card
    $stmt_spotlight = $pdo->prepare("SELECT * FROM `news_events` WHERE `is_featured` = 1 ORDER BY `created_at` DESC LIMIT 1");
    $stmt_spotlight->execute();
    $spotlight = $stmt_spotlight->fetch();
    
    // Fallback to most recent bulletin if none marked featured
    if (!$spotlight) {
        $spotlight = $pdo->query("SELECT * FROM `news_events` ORDER BY `created_at` DESC LIMIT 1")->fetch();
    }
    
    // B. Fetch all bulletins for the paginated registry grid
    $stmt_list = $pdo->query("SELECT * FROM `news_events` ORDER BY `created_at` DESC");
    $bulletins = $stmt_list->fetchAll();
} catch (PDOException $e) {
    die("CMS Critical Render Error: Database connectivity failure.");
}

// 3. Inject custom local stylesheet blocks in the header
$custom_css = '
    <style>
        /* Search & Filter Controls */
        .controls-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .search-box {
            position: relative;
            max-width: 400px;
            width: 100%;
        }
        .search-input {
            width: 100%;
            padding: 12px 20px 12px 48px;
            border-radius: 30px;
            background: var(--bg-white);
            border: 1px solid rgba(15, 23, 42, 0.08);
            font-size: 0.95rem;
            color: var(--primary);
            transition: var(--transition-fast);
            box-shadow: var(--shadow-sm);
        }
        body.dark-theme-active .search-input {
            background: var(--bg-card-dark);
            border-color: var(--glass-border);
            color: var(--bg-white);
        }
        .search-input:focus {
            border-color: var(--secondary-light);
            box-shadow: var(--shadow-glow);
        }
        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }
        .news-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .tab-btn {
            padding: 10px 20px;
            border-radius: 30px;
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            color: var(--primary);
            background: rgba(15, 23, 42, 0.04);
            transition: var(--transition-fast);
        }
        body.dark-theme-active .tab-btn {
            color: #cbd5e1;
            background: rgba(255, 255, 255, 0.04);
        }
        .tab-btn:hover {
            color: var(--secondary-light);
        }
        .tab-btn.active {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
            color: var(--bg-white);
            box-shadow: var(--shadow-sm);
        }

        /* Spotlight Feature Card */
        .spotlight-card {
            background: var(--bg-white);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(15, 23, 42, 0.05);
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-bottom: 50px;
            position: relative;
            transition: var(--transition-smooth);
        }
        body.dark-theme-active .spotlight-card {
            background: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .spotlight-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        .spotlight-media {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-light);
            height: 100%;
            min-height: 320px;
            overflow: hidden;
            position: relative;
        }
        .spotlight-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s var(--ease-premium);
        }
        .spotlight-card:hover .spotlight-img {
            transform: scale(1.04);
        }
        .spotlight-content {
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 16px;
        }
        .news-badge {
            align-self: flex-start;
            background: var(--accent-glow);
            color: var(--accent-light);
            padding: 6px 14px;
            border-radius: 20px;
            font-family: var(--font-heading);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .news-date {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* News Grid Card */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 32px;
        }
        .news-card {
            background: var(--bg-white);
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15, 23, 42, 0.05);
            transition: var(--transition-smooth);
            display: flex;
            flex-direction: column;
        }
        body.dark-theme-active .news-card {
            background: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .news-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(13, 148, 136, 0.12);
        }
        .news-card-media {
            height: 200px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-light);
        }
        .news-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s var(--ease-premium);
        }
        .news-card:hover .news-img {
            transform: scale(1.06);
        }
        .news-card-content {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex-grow: 1;
        }
        .news-card-content h4 {
            font-size: 1.25rem;
            line-height: 1.4;
        }
        .news-card-content p {
            font-size: 0.9rem;
            line-height: 1.6;
            color: var(--text-muted);
        }
        .news-card-footer {
            margin-top: auto;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .read-more {
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.88rem;
            color: var(--secondary-light);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            background: transparent;
            border: none;
            padding: 0;
        }
        .read-more:hover {
            color: var(--accent-light);
        }

        /* RSVP Modal Overlay */
        .rsvp-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(9, 13, 22, 0.90);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .rsvp-modal.active {
            display: flex;
        }
        .rsvp-box {
            background: var(--bg-white);
            width: 90%;
            max-width: 500px;
            border-radius: var(--border-radius-lg);
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: var(--shadow-lg);
            position: relative;
            padding: 40px;
            box-sizing: border-box;
            overflow: hidden;
        }
        body.dark-theme-active .rsvp-box {
            background: var(--bg-card-dark);
            border-color: var(--glass-border);
        }
        .rsvp-close {
            position: absolute;
            top: 20px;
            right: 24px;
            font-size: 2.2rem;
            color: var(--text-muted);
            cursor: pointer;
            line-height: 1;
            transition: var(--transition-fast);
        }
        .rsvp-close:hover {
            color: var(--accent-light);
        }
        .rsvp-box h3 {
            font-size: 1.6rem;
            margin-bottom: 8px;
        }
        .rsvp-box p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 24px;
        }
        .rsvp-success-message {
            display: none;
            text-align: center;
            padding: 20px 0;
        }
        .rsvp-success-message svg {
            color: var(--accent-light);
            margin-bottom: 16px;
        }
        .rsvp-success-message h3 {
            font-size: 1.7rem;
            color: #ffffff !important;
            margin-bottom: 8px;
        }
        .rsvp-success-message p {
            max-width: 380px;
            margin: 0 auto;
        }

        /* Pagination style elements */
        .pagination-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 50px;
            flex-wrap: wrap;
            width: 100%;
        }
        .pagination-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.04);
            border: 1px solid transparent;
            color: var(--primary);
            font-family: var(--font-heading);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        body.dark-theme-active .pagination-btn {
            background: rgba(255, 255, 255, 0.04);
            color: #cbd5e1;
        }
        .pagination-btn:hover {
            background: rgba(13, 148, 136, 0.1);
            color: var(--secondary-light);
        }
        .pagination-btn.active {
            background: var(--primary);
            color: #ffffff;
            box-shadow: var(--shadow-sm);
        }
        body.dark-theme-active .pagination-btn.active {
            background: var(--accent);
        }
        .pagination-nav-btn {
            width: auto;
            padding: 0 18px;
            border-radius: 30px;
        }
        .pagination-btn.disabled {
            opacity: 0.4;
            pointer-events: none;
            cursor: not-allowed;
        }

        @media (max-width: 992px) {
            .spotlight-card {
                grid-template-columns: 1fr;
            }
            .spotlight-content {
                padding: 32px;
            }
        }

        /* Navbar dynamic overloads */
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
                <h1 class="reveal">Academy News & Bulletins</h1>
                <div class="breadcrumbs reveal" style="transition-delay: 0.1s;">
                    <a href="index.php">Home</a>
                    <span>/</span>
                    <span class="current">News & Events</span>
                </div>
            </div>
        </section>

        <!-- SECTION 1: FEATURED SPOTLIGHT BANNER -->
        <section class="section-padding" style="padding-bottom: 0;">
            <div class="container">
                <?php if ($spotlight): ?>
                    <div class="spotlight-card reveal">
                        <div class="spotlight-media">
                            <img src="<?php echo htmlspecialchars($spotlight['filepath']); ?>" alt="<?php echo htmlspecialchars($spotlight['title']); ?>" class="spotlight-img">
                        </div>
                        <div class="spotlight-content">
                            <span class="news-badge"><?php echo ($spotlight['type'] === 'event') ? 'Featured Event' : 'Spotlight Bulletin'; ?></span>
                            <div class="news-date">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <?php 
                                    $s_date = ($spotlight['type'] === 'event' && $spotlight['event_date']) ? $spotlight['event_date'] : $spotlight['created_at'];
                                    echo date('F d, Y', strtotime($s_date));
                                ?>
                            </div>
                            <h3><?php echo htmlspecialchars($spotlight['title']); ?></h3>
                            <p><?php echo htmlspecialchars(mb_strimwidth(strip_tags($spotlight['content']), 0, 240, '...')); ?></p>
                            
                            <?php if ($spotlight['type'] === 'event'): ?>
                                <button class="btn btn-accent btn-rsvp" data-event="<?php echo htmlspecialchars($spotlight['title']); ?>" style="align-self: flex-start; margin-top: 10px;">Reserve RSVP Seat</button>
                            <?php else: ?>
                                <a href="#news-grid" class="btn btn-accent" style="align-self: flex-start; margin-top: 10px; text-decoration: none;">View Archive Bulletins</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- SECTION 2: ARCHIVES LISTINGS (WITH SEARCH & TABS PAGINATION) -->
        <section class="section-padding">
            <div class="container">
                <!-- Controls: Search & Tabs -->
                <div class="controls-row reveal">
                    <div class="search-box">
                        <input type="text" class="search-input" id="news-search" placeholder="Search articles or events...">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    
                    <div class="news-tabs">
                        <button class="tab-btn active" data-tab="all">All Bulletins</button>
                        <button class="tab-btn" data-tab="academics">Academic Updates</button>
                        <button class="tab-btn" data-tab="campus">Campus Events</button>
                        <button class="tab-btn" data-tab="achieve">Achievements</button>
                    </div>
                </div>

                <!-- Archives Grid Loop -->
                <?php if (empty($bulletins)): ?>
                    <div style="text-align: center; padding: 48px;" class="reveal">
                        <p style="color: var(--text-muted); font-size: 1.15rem;">No bulletins listed yet. Check back soon for announcements!</p>
                    </div>
                <?php else: ?>
                    <div class="news-grid" id="news-grid">
                        <?php 
                        foreach ($bulletins as $bulletin): 
                            // 4. Smart keyword heuristical classifier
                            $title_lower = strtolower($bulletin['title']);
                            $content_lower = strtolower($bulletin['content']);
                            $is_achieve = false;
                            $achieve_keywords = ['champion', 'winner', 'victory', 'gold', 'trophy', 'accolade', 'laurel', 'award', 'cup', 'secured', 'first place', 'rank #1', 'rank 1', 'choral', 'triumph', 'honored'];
                            foreach ($achieve_keywords as $keyword) {
                                if (strpos($title_lower, $keyword) !== false || strpos($content_lower, $keyword) !== false) {
                                    $is_achieve = true;
                                    break;
                                }
                            }
                            
                            if ($bulletin['type'] === 'event') {
                                $data_type = 'campus';
                                $badge_label = 'Campus Event';
                                $badge_style = '';
                            } elseif ($is_achieve) {
                                $data_type = 'achieve';
                                $badge_label = 'Achievement';
                                $badge_style = 'style="background: rgba(13, 148, 136, 0.08); color: var(--secondary-light);"';
                            } else {
                                $data_type = 'academics';
                                $badge_label = 'Academic Update';
                                $badge_style = '';
                            }
                            
                            $display_date = ($bulletin['type'] === 'event' && $bulletin['event_date']) 
                                ? date('M d, Y', strtotime($bulletin['event_date'])) 
                                : date('M d, Y', strtotime($bulletin['created_at']));
                        ?>
                        <div class="news-card reveal" data-type="<?php echo $data_type; ?>">
                            <div class="news-card-media">
                                <img src="<?php echo htmlspecialchars($bulletin['filepath']); ?>" alt="<?php echo htmlspecialchars($bulletin['title']); ?>" class="news-img">
                            </div>
                            <div class="news-card-content">
                                <span class="news-badge" <?php echo $badge_style; ?>><?php echo $badge_label; ?></span>
                                <div class="news-date"><?php echo $display_date; ?> &bull; Gurukul Desk</div>
                                <h4><?php echo htmlspecialchars($bulletin['title']); ?></h4>
                                <p><?php echo htmlspecialchars(mb_strimwidth(strip_tags($bulletin['content']), 0, 130, '...')); ?></p>
                                <div class="news-card-footer">
                                    <?php if ($bulletin['type'] === 'event'): ?>
                                        <button class="read-more btn-rsvp" data-event="<?php echo htmlspecialchars($bulletin['title']); ?>">Book RSVP
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                        </button>
                                    <?php else: ?>
                                        <button class="read-more btn-rsvp" data-event="<?php echo htmlspecialchars($bulletin['title']); ?>" style="pointer-events: none;">View Bulletin
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- PAGINATION CONTROLS -->
                <div class="pagination-container" id="pagination-controls"></div>
            </div>
        </section>
    </main>

    <!-- RSVP MOCKUP DIALOG MODAL -->
    <div class="rsvp-modal" id="rsvp-modal">
        <div class="rsvp-box">
            <span class="rsvp-close" id="rsvp-close">&times;</span>
            
            <div id="rsvp-form-container">
                <h3>Event RSVP Reservation</h3>
                <p>Register to secure your attendee entry seat for: <strong id="rsvp-event-name">Event</strong>.</p>
                
                <form id="rsvp-mock-form">
                    <div class="form-group">
                        <label class="form-label" for="rsvp-name">Full Name</label>
                        <input type="text" id="rsvp-name" class="form-field" placeholder="Enter your name" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rsvp-email">Email Address</label>
                        <input type="email" id="rsvp-email" class="form-field" placeholder="Enter your email" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rsvp-count">Number of Attendees</label>
                        <select id="rsvp-count" class="form-field">
                            <option value="1">1 Person</option>
                            <option value="2">2 Persons</option>
                            <option value="3">3 Persons</option>
                            <option value="4">4+ Persons</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-accent" style="width: 100%; margin-top: 10px;">Submit Reservation</button>
                </form>
            </div>

            <!-- Success Overlay -->
            <div class="rsvp-success-message" id="rsvp-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
                <h3>RSVP Confirmed!</h3>
                <p>We have successfully registered your seats. An entry invitation code and campus routing directions have been sent to your email.</p>
                <button class="btn btn-secondary" id="rsvp-success-close" style="margin-top: 15px;">Close Window</button>
            </div>
        </div>
    </div>

    <!-- Local Script for Instant Archives Filters & RSVP Modal -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initNewsControls();
            initRsvpModal();
        });

        // 1. Instant Client-Side Search, Categorized Tabs Filter & Dynamic Pagination Engine
        function initNewsControls() {
            const searchInput = document.getElementById('news-search');
            const tabBtns = document.querySelectorAll('.tab-btn');
            const newsCards = Array.from(document.querySelectorAll('.news-card'));
            const paginationContainer = document.getElementById('pagination-controls');

            const itemsPerPage = 4;
            let currentPage = 1;
            let filteredCards = [...newsCards];

            const renderPagination = () => {
                if (!paginationContainer) return;
                paginationContainer.innerHTML = '';
                const totalItems = filteredCards.length;
                
                if (totalItems === 0) {
                    paginationContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 1.1rem; grid-column: 1/-1; text-align: center; margin-top: 20px; width: 100%;">No articles or events match your criteria.</p>';
                    return;
                }

                const totalPages = Math.ceil(totalItems / itemsPerPage);
                if (totalPages <= 1) return; // Hide pagination if only 1 page

                // 1. Prev Button
                const prevBtn = document.createElement('button');
                prevBtn.className = `pagination-btn pagination-nav-btn ${currentPage === 1 ? 'disabled' : ''}`;
                prevBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="m15 18-6-6 6-6"/></svg>
                    Prev
                `;
                prevBtn.disabled = currentPage === 1;
                prevBtn.addEventListener('click', () => changePage(currentPage - 1));
                paginationContainer.appendChild(prevBtn);

                // 2. Number Buttons
                for (let i = 1; i <= totalPages; i++) {
                    const numBtn = document.createElement('button');
                    numBtn.className = `pagination-btn ${currentPage === i ? 'active' : ''}`;
                    numBtn.textContent = i;
                    numBtn.addEventListener('click', () => changePage(i));
                    paginationContainer.appendChild(numBtn);
                }

                // 3. Next Button
                const nextBtn = document.createElement('button');
                nextBtn.className = `pagination-btn pagination-nav-btn ${currentPage === totalPages ? 'disabled' : ''}`;
                nextBtn.innerHTML = `
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 4px;"><path d="m9 18 6-6-6-6"/></svg>
                `;
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.addEventListener('click', () => changePage(currentPage + 1));
                paginationContainer.appendChild(nextBtn);
            };

            const displayItems = () => {
                const totalItems = filteredCards.length;
                
                // Hide all cards first
                newsCards.forEach(card => {
                    card.style.display = 'none';
                    card.classList.remove('active');
                });

                if (totalItems === 0) return;

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

                for (let i = startIndex; i < endIndex; i++) {
                    const card = filteredCards[i];
                    if (card) {
                        card.style.display = 'flex';
                        setTimeout(() => {
                            card.classList.add('active');
                        }, 50);
                    }
                }
            };

            const changePage = (page) => {
                currentPage = page;
                displayItems();
                renderPagination();

                const controlsElement = document.querySelector('.controls-row');
                if (controlsElement) {
                    const offset = 120;
                    const bodyRect = document.body.getBoundingClientRect().top;
                    const elementRect = controlsElement.getBoundingClientRect().top;
                    const elementPosition = elementRect - bodyRect;
                    const offsetPosition = elementPosition - offset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            };

            const filterAndSearch = () => {
                const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const activeTabBtn = document.querySelector('.tab-btn.active');
                const activeTab = activeTabBtn ? activeTabBtn.getAttribute('data-tab') : 'all';

                filteredCards = newsCards.filter(card => {
                    const cardType = card.getAttribute('data-type');
                    const title = card.querySelector('h4').textContent.toLowerCase();
                    const desc = card.querySelector('p').textContent.toLowerCase();

                    const matchesTab = (activeTab === 'all' || cardType === activeTab);
                    const matchesSearch = (title.includes(searchVal) || desc.includes(searchVal));

                    return matchesTab && matchesSearch;
                });

                currentPage = 1;
                displayItems();
                renderPagination();
            };

            if (searchInput) searchInput.addEventListener('input', filterAndSearch);

            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    tabBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    filterAndSearch();
                });
            });

            filterAndSearch();
        }

        // 2. RSVP Interactive Modal Controls
        function initRsvpModal() {
            const modal = document.getElementById('rsvp-modal');
            const closeBtn = document.getElementById('rsvp-close');
            const successCloseBtn = document.getElementById('rsvp-success-close');
            const formContainer = document.getElementById('rsvp-form-container');
            const successOverlay = document.getElementById('rsvp-success');
            const mockForm = document.getElementById('rsvp-mock-form');
            const eventLabel = document.getElementById('rsvp-event-name');

            document.querySelectorAll('.btn-rsvp').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const eventName = btn.getAttribute('data-event');
                    if (eventLabel) eventLabel.textContent = eventName;
                    
                    if (formContainer) formContainer.style.display = 'block';
                    if (successOverlay) successOverlay.style.display = 'none';
                    if (mockForm) mockForm.reset();

                    if (modal) modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            });

            const closeModal = () => {
                if (modal) modal.classList.remove('active');
                document.body.style.overflow = '';
            };

            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (successCloseBtn) successCloseBtn.addEventListener('click', closeModal);

            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) closeModal();
                });
            }

            if (mockForm) {
                mockForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    if (formContainer) formContainer.style.display = 'none';
                    if (successOverlay) successOverlay.style.display = 'block';
                });
            }
        }
    </script>

<?php
// Load shared footer
include_once 'includes/footer.php';
?>
