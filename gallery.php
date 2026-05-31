<?php
/**
 * ========================================================
 * DYNAMIC FRONTEND GALLERY PORTAL (GURUKUL)
 * ========================================================
 */

// 1. Establish secure PDO Database connection
require_once 'config/db.php';

$page_title = 'Gallery & Campus Life | Gurukul Academy';
$meta_description = 'Explore Gurukul Academy through our interactive media gallery. Browse classrooms, labs, athletic stadiums, and cultural assemblies in photos and videos.';

// 2. Fetch categories and items
try {
    $categories = $pdo->query("SELECT * FROM `gallery_categories` ORDER BY `name` ASC")->fetchAll();
    
    // Join query to retrieve category names and slugs
    $stmt_gallery = $pdo->query("SELECT g.*, c.name AS category_name, c.slug AS category_slug FROM `gallery` g JOIN `gallery_categories` c ON g.category_id = c.id ORDER BY g.uploaded_at DESC");
    $gallery_items = $stmt_gallery->fetchAll();
} catch (PDOException $e) {
    die("CMS Critical Render Error: Database connectivity failure.");
}

// 3. Inject custom local stylesheet blocks in the header
$custom_css = '
    <style>
        /* ==========================================
           1. CONTROLS: SEARCH & MULTI-FILTERS
           ========================================== */
        .controls-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }
        .search-box {
            position: relative;
            max-width: 380px;
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
        .filter-groups-wrapper {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        /* Media Type Selector */
        .type-filters {
            display: flex;
            background: rgba(15, 23, 42, 0.04);
            padding: 4px;
            border-radius: 30px;
            border: 1px solid rgba(15, 23, 42, 0.02);
        }
        body.dark-theme-active .type-filters {
            background: rgba(255, 255, 255, 0.04);
            border-color: var(--glass-border);
        }
        .type-btn {
            padding: 8px 18px;
            border-radius: 20px;
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: var(--transition-fast);
            color: var(--text-muted);
        }
        .type-btn.active {
            background: var(--primary);
            color: var(--bg-white);
        }
        body.dark-theme-active .type-btn.active {
            background: var(--accent);
            color: var(--bg-white);
        }
        /* Category Tabs */
        .filter-container {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 10px 20px;
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--primary);
            background: rgba(15, 23, 42, 0.04);
            border-radius: 30px;
            cursor: pointer;
            transition: var(--transition-fast);
            border: 1px solid transparent;
        }
        body.dark-theme-active .filter-btn {
            color: #cbd5e1;
            background: rgba(255, 255, 255, 0.04);
        }
        .filter-btn:hover {
            background: rgba(13, 148, 136, 0.08);
            color: var(--secondary-light);
        }
        .filter-btn.active {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
            color: var(--bg-white);
            box-shadow: var(--shadow-sm);
        }
        @media (max-width: 992px) {
            .controls-row {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box {
                max-width: 100%;
            }
            .filter-groups-wrapper {
                flex-direction: column;
                align-items: stretch;
            }
        }

        /* ==========================================
           2. TRUE CSS MASONRY LAYOUT
           ========================================== */
        .gallery-grid {
            column-count: 3;
            column-gap: 24px;
            width: 100%;
            transition: all 0.5s ease;
        }
        .gallery-item {
            break-inside: avoid;
            margin-bottom: 24px;
            position: relative;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15, 23, 42, 0.05);
            transition: transform 0.4s var(--ease-premium), opacity 0.4s ease, box-shadow 0.4s ease;
            z-index: 1;
            display: inline-block;
            width: 100%;
        }
        body.dark-theme-active .gallery-item {
            border-color: var(--glass-border);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        /* Staggered Masonry Heights */
        .height-sm { height: 260px; }
        .height-md { height: 320px; }
        .height-lg { height: 380px; }

        .gallery-item.hidden {
            opacity: 0;
            transform: scale(0.9);
            position: absolute;
            pointer-events: none;
            width: 0;
            height: 0;
            padding: 0;
            margin: 0;
            border: none;
        }
        .gallery-visual-box {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #131b2e 0%, #090d16 100%);
        }
        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s var(--ease-premium);
        }
        .gallery-item:hover .gallery-img {
            transform: scale(1.08);
        }
        .media-badge {
            position: absolute;
            top: 16px;
            left: 16px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 6px 14px;
            border-radius: 30px;
            font-family: var(--font-heading);
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--accent-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            z-index: 3;
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .play-overlay-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(13, 148, 136, 0.9);
            color: var(--bg-white);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
            z-index: 2;
            transition: all 0.3s ease;
        }
        .gallery-item:hover .play-overlay-icon {
            background: var(--accent);
            transform: translate(-50%, -50%) scale(1.1);
        }
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.92) 0%, rgba(15, 23, 42, 0.4) 60%, transparent 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 24px;
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 3;
        }
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        .gallery-tag {
            color: var(--accent-light);
            font-family: var(--font-heading);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .gallery-title {
            color: var(--bg-white);
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 4px;
            line-height: 1.3;
        }
        .gallery-caption {
            color: #cbd5e1;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        @media (max-width: 992px) {
            .gallery-grid {
                column-count: 2;
            }
        }
        @media (max-width: 600px) {
            .gallery-grid {
                column-count: 1;
            }
            .gallery-item {
                height: 260px !important;
            }
        }

        /* ==========================================
           3. PREMIUM LIGHTBOX STYLING
           ========================================== */
        .lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(9, 13, 22, 0.95);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .lightbox.active {
            display: flex;
        }
        .lightbox-close {
            position: absolute;
            top: 30px;
            right: 40px;
            font-size: 2.8rem;
            color: #ffffff;
            cursor: pointer;
            transition: var(--transition-fast);
            z-index: 10010;
        }
        .lightbox-close:hover {
            color: var(--accent-light);
            transform: scale(1.1);
        }
        .lightbox-container {
            width: 90%;
            max-width: 960px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            position: relative;
        }
        .lightbox-view {
            width: 100%;
            height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lightbox-info {
            width: 100%;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .lightbox-info h3 {
            color: #ffffff !important;
            font-size: 1.6rem;
            margin: 4px 0;
        }
        .lightbox-info p {
            color: #94a3b8 !important;
            font-size: 0.95rem;
            max-width: 600px;
        }
        .lightbox-nav {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            transform: translateY(-50%);
            display: flex;
            justify-content: space-between;
            pointer-events: none;
            width: 100%;
            padding: 0 20px;
            box-sizing: border-box;
            z-index: 10005;
        }
        .lightbox-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition-fast);
            pointer-events: auto;
        }
        .lightbox-btn:hover {
            background: var(--accent);
            border-color: var(--accent);
            transform: scale(1.1);
        }
        
        /* Simulated video player inside Lightbox */
        .mock-video-player {
            width: 100%;
            height: 100%;
            background: #000000;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            box-shadow: var(--shadow-lg);
        }
        .mock-video-poster {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease;
        }
        .mock-player-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.4) 70%, transparent 100%);
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 4;
        }
        .mock-progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            cursor: pointer;
            overflow: hidden;
        }
        .mock-progress-fill {
            width: 0%;
            height: 100%;
            background: var(--accent-light);
            border-radius: 3px;
        }
        .mock-controls-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .mock-controls-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .mock-play-btn, .mock-volume-btn {
            background: transparent;
            border: none;
            color: #ffffff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-fast);
        }
        .mock-play-btn:hover, .mock-volume-btn:hover {
            color: var(--accent-light);
        }
        .mock-time {
            color: #94a3b8;
            font-family: var(--font-heading);
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Navbar active overriding styles */
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
                <h1 class="reveal">Academy Media Gallery</h1>
                <div class="breadcrumbs reveal" style="transition-delay: 0.1s;">
                    <a href="index.php">Home</a>
                    <span>/</span>
                    <span class="current">Visual Gallery</span>
                </div>
            </div>
        </section>

        <!-- PORTFOLIO CONTROLLER: SEARCH & CATEGORY FILTERS -->
        <section class="section-padding" style="padding-bottom: 0;">
            <div class="container">
                <div class="controls-row reveal">
                    <!-- Search Input Box -->
                    <div class="search-box">
                        <input type="text" id="gallery-search" class="search-input" placeholder="Search classrooms, fests, fete fests...">
                        <div class="search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Multi Filters Tab Groups -->
                    <div class="filter-groups-wrapper">
                        <!-- Media Type Filter (Photos / Videos Toggle) -->
                        <div class="type-filters">
                            <div class="type-btn active" data-type="all">All Media</div>
                            <div class="type-btn" data-type="photo">Photos</div>
                            <div class="type-btn" data-type="video">Videos</div>
                        </div>

                        <!-- Category Filters -->
                        <div class="filter-container">
                            <div class="filter-btn active" data-filter="all">All Categories</div>
                            <?php foreach ($categories as $cat): ?>
                                <div class="filter-btn" data-filter="<?php echo htmlspecialchars($cat['slug']); ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 2: CSS MASONRY MEDIA GRID LOOP -->
        <section class="section-padding">
            <div class="container">
                <?php if (empty($gallery_items)): ?>
                    <div style="text-align: center; padding: 60px 20px;" class="reveal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent); margin-bottom: 16px;">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                        <h3 style="color: var(--text-dark); margin-bottom: 8px;">No media assets found</h3>
                        <p style="color: var(--text-muted);">Please upload visual items inside the secure Admin Dashboard Panel to view highlights.</p>
                    </div>
                <?php else: ?>
                    <div class="gallery-grid">
                        <?php 
                        $i = 0;
                        $height_classes = ['height-sm', 'height-md', 'height-lg'];
                        foreach ($gallery_items as $item): 
                            $i++;
                            $stagger_height = $height_classes[$i % 3];
                            $media_type = ($item['type'] === 'image') ? 'photo' : 'video';
                        ?>
                        <div class="gallery-item reveal <?php echo $stagger_height; ?>" data-category="<?php echo htmlspecialchars($item['category_slug']); ?>" data-media-type="<?php echo $media_type; ?>">
                            <!-- Media Badge Indicator -->
                            <div class="media-badge">
                                <?php if ($item['type'] === 'image'): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    Photo
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/></svg>
                                    Video highlights
                                <?php endif; ?>
                            </div>

                            <div class="gallery-visual-box">
                                <?php if ($item['type'] === 'video'): ?>
                                    <div class="play-overlay-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                    </div>
                                <?php endif; ?>
                                <img src="<?php echo htmlspecialchars($item['filepath']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="gallery-img">
                            </div>

                            <div class="gallery-overlay">
                                <span class="gallery-tag"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                <h4 class="gallery-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                                <p class="gallery-caption">Campus visual showcase highlight.</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- LIGHTBOX CUSTOM MEDIA MODAL -->
    <div class="lightbox" id="lightbox">
        <span class="lightbox-close" id="lightbox-close">&times;</span>
        
        <!-- Arrow Nav controllers -->
        <div class="lightbox-nav">
            <div class="lightbox-btn" id="lightbox-prev" title="Previous Image">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </div>
            <div class="lightbox-btn" id="lightbox-next" title="Next Image">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </div>
        </div>

        <div class="lightbox-container">
            <div class="lightbox-view" id="lightbox-view"></div>
            
            <div class="lightbox-info">
                <span class="gallery-tag" id="lightbox-tag">Tag</span>
                <h3 id="lightbox-title">Title</h3>
                <p id="lightbox-caption">Caption description text here.</p>
            </div>
        </div>
    </div>

    <!-- Interactive Search, Filters & Staggered Lightbox Video Controller -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initGalleryInteractivity();
        });

        function initGalleryInteractivity() {
            // Elements
            const searchInput = document.getElementById('gallery-search');
            const typeBtns = document.querySelectorAll('.type-btn');
            const filterBtns = document.querySelectorAll('.filter-btn');
            const galleryItems = document.querySelectorAll('.gallery-item');
            
            const lightbox = document.getElementById('lightbox');
            const lightboxClose = document.getElementById('lightbox-close');
            const lightboxView = document.getElementById('lightbox-view');
            const lightboxTag = document.getElementById('lightbox-tag');
            const lightboxTitle = document.getElementById('lightbox-title');
            const lightboxCaption = document.getElementById('lightbox-caption');
            const lightboxPrev = document.getElementById('lightbox-prev');
            const lightboxNext = document.getElementById('lightbox-next');

            let activeItems = [];
            let currentIndex = 0;
            let videoPlayInterval = null;

            // 1. Live Multidimensional Filter Engine
            const applyFilters = () => {
                const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const activeTypeBtn = document.querySelector('.type-btn.active');
                const activeType = activeTypeBtn ? activeTypeBtn.getAttribute('data-type') : 'all';
                const activeCatBtn = document.querySelector('.filter-btn.active');
                const activeCategory = activeCatBtn ? activeCatBtn.getAttribute('data-filter') : 'all';

                galleryItems.forEach(item => {
                    const itemType = item.getAttribute('data-media-type');
                    const itemCategory = item.getAttribute('data-category');
                    const title = item.querySelector('.gallery-title').textContent.toLowerCase();
                    const caption = item.querySelector('.gallery-caption').textContent.toLowerCase();

                    const matchesSearch = title.includes(searchVal) || caption.includes(searchVal);
                    const matchesType = (activeType === 'all' || itemType === activeType);
                    const matchesCategory = (activeCategory === 'all' || itemCategory === activeCategory);

                    if (matchesSearch && matchesType && matchesCategory) {
                        item.classList.remove('hidden');
                        item.style.display = 'inline-block';
                    } else {
                        item.classList.add('hidden');
                        item.style.display = 'none';
                    }
                });
            };

            // Bind Search Input
            if (searchInput) searchInput.addEventListener('input', applyFilters);

            // Bind Media Type buttons
            typeBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    typeBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    applyFilters();
                });
            });

            // Bind Category Tab buttons
            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    applyFilters();
                });
            });

            // 2. High-Performance Custom Lightbox & Video Player Controller
            const stopVideoSimulation = () => {
                if (videoPlayInterval) {
                    clearInterval(videoPlayInterval);
                    videoPlayInterval = null;
                }
            };

            const runVideoSimulation = (progressBarFill, playBtnText, timeText) => {
                let progress = 0;
                let isPlaying = true;
                
                const updateProgress = () => {
                    if (!isPlaying) return;
                    progress += 0.8; 
                    if (progress >= 100) {
                        progress = 0;
                    }
                    if (progressBarFill) progressBarFill.style.width = `${progress}%`;
                    
                    const totalSecs = 160;
                    const curSecs = Math.round((progress / 100) * totalSecs);
                    const curMin = Math.floor(curSecs / 60);
                    const curSec = curSecs % 60;
                    if (timeText) timeText.textContent = `${curMin}:${curSec < 10 ? '0' : ''}${curSec} / 2:40`;
                };

                // Trigger Interval at 50fps
                videoPlayInterval = setInterval(updateProgress, 50);

                const playBtn = document.querySelector('.mock-play-btn');
                if (playBtn) {
                    playBtn.addEventListener('click', () => {
                        isPlaying = !isPlaying;
                        if (isPlaying) {
                            playBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><rect width="4" height="16" x="6" y="4" rx="1"/><rect width="4" height="16" x="14" y="4" rx="1"/></svg>';
                        } else {
                            playBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>';
                        }
                    });
                }
            };

            const openLightbox = (index) => {
                stopVideoSimulation();
                currentIndex = index;
                const activeItem = activeItems[currentIndex];
                if (!activeItem) return;

                // Extract details from visual card
                const category = activeItem.getAttribute('data-category');
                const mediaType = activeItem.getAttribute('data-media-type');
                const title = activeItem.querySelector('.gallery-title').textContent;
                const caption = activeItem.querySelector('.gallery-caption').textContent;

                // Populate Text Details
                if (lightboxTag) lightboxTag.textContent = category;
                if (lightboxTitle) lightboxTitle.textContent = title;
                if (lightboxCaption) lightboxCaption.textContent = caption;

                if (lightboxView) {
                    lightboxView.innerHTML = '';

                    if (mediaType === 'photo') {
                        const img = document.createElement('img');
                        img.src = activeItem.querySelector('.gallery-img').src;
                        img.alt = activeItem.querySelector('.gallery-title').textContent;
                        img.style.maxWidth = '100%';
                        img.style.maxHeight = '100%';
                        img.style.objectFit = 'contain';
                        img.style.borderRadius = 'var(--border-radius-md)';
                        lightboxView.appendChild(img);
                    } else {
                        const posterSrc = activeItem.querySelector('.gallery-img').src;
                        lightboxView.innerHTML = `
                            <div class="mock-video-player">
                                <div class="mock-video-poster" id="mock-poster" style="background-image: url('${posterSrc}'); background-size: cover; background-position: center;">
                                    <div class="play-overlay-icon" style="position: static; transform: scale(1.1); cursor: pointer;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                    </div>
                                </div>
                                <div class="mock-player-controls">
                                    <div class="mock-progress-bar" id="mock-progress-bar">
                                        <div class="mock-progress-fill" id="mock-progress-fill"></div>
                                    </div>
                                    <div class="mock-controls-row">
                                        <div class="mock-controls-left">
                                            <button class="mock-play-btn">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><rect width="4" height="16" x="6" y="4" rx="1"/><rect width="4" height="16" x="14" y="4" rx="1"/></svg>
                                            </button>
                                            <div class="mock-time" id="mock-time">0:00 / 2:40</div>
                                        </div>
                                        <div class="mock-controls-right">
                                            <button class="mock-volume-btn">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        setTimeout(() => {
                            const poster = document.getElementById('mock-poster');
                            const progressFill = document.getElementById('mock-progress-fill');
                            const timeText = document.getElementById('mock-time');
                            if (poster) poster.style.opacity = '0';
                            runVideoSimulation(progressFill, null, timeText);
                        }, 800);
                    }
                }

                if (lightbox) lightbox.classList.add('active');
                document.body.style.overflow = 'hidden';
            };

            const closeLightbox = () => {
                stopVideoSimulation();
                if (lightbox) lightbox.classList.remove('active');
                document.body.style.overflow = '';
            };

            const navigateLightbox = (direction) => {
                currentIndex += direction;
                if (currentIndex < 0) currentIndex = activeItems.length - 1;
                if (currentIndex >= activeItems.length) currentIndex = 0;
                openLightbox(currentIndex);
            };

            // Register items click
            galleryItems.forEach(item => {
                item.addEventListener('click', () => {
                    activeItems = Array.from(galleryItems).filter(i => !i.classList.contains('hidden'));
                    const index = activeItems.indexOf(item);
                    openLightbox(index);
                });
            });

            if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
            if (lightboxPrev) lightboxPrev.addEventListener('click', () => navigateLightbox(-1));
            if (lightboxNext) lightboxNext.addEventListener('click', () => navigateLightbox(1));

            if (lightbox) {
                lightbox.addEventListener('click', (e) => {
                    if (e.target === lightbox) closeLightbox();
                });
            }

            document.addEventListener('keydown', (e) => {
                if (lightbox && !lightbox.classList.contains('active')) return;
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') navigateLightbox(-1);
                if (e.key === 'ArrowRight') navigateLightbox(1);
            });
        }
    </script>

<?php
// Load shared footer
include_once 'includes/footer.php';
?>
