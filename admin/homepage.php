<?php
/**
 * ========================================================
 * HOMEPAGE CONTENT MANAGEMENT MODULE (GURUKUL)
 * ========================================================
 */

require_once '../config/db.php';
require_once 'includes/header.php';

$success_msg = "";
$error_msg = "";

// 1. Fetch current Homepage content details (Row id = 1)
try {
    $stmt = $pdo->prepare("SELECT * FROM `homepage_content` WHERE `id` = 1 LIMIT 1");
    $stmt->execute();
    $home = $stmt->fetch();
    
    if (!$home) {
        // Fallback seeder in case table is empty
        $pdo->query("INSERT INTO `homepage_content` (`id`) VALUES (1)");
        $stmt->execute();
        $home = $stmt->fetch();
    }
} catch (PDOException $e) {
    die("Database Fetch Failure: " . sanitize($e->getMessage()));
}

// 2. Process form submit updating Homepage parameters
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        // Collect and sanitize text fields
        $hero_title = trim($_POST['hero_title'] ?? '');
        $hero_subtitle = trim($_POST['hero_subtitle'] ?? '');
        $hero_btn_text_1 = trim($_POST['hero_btn_text_1'] ?? '');
        $hero_btn_link_1 = trim($_POST['hero_btn_link_1'] ?? '');
        $hero_btn_text_2 = trim($_POST['hero_btn_text_2'] ?? '');
        $hero_btn_link_2 = trim($_POST['hero_btn_link_2'] ?? '');
        
        $stat_number_1 = trim($_POST['stat_number_1'] ?? '');
        $stat_label_1 = trim($_POST['stat_label_1'] ?? '');
        $stat_number_2 = trim($_POST['stat_number_2'] ?? '');
        $stat_label_2 = trim($_POST['stat_label_2'] ?? '');
        $stat_number_3 = trim($_POST['stat_number_3'] ?? '');
        $stat_label_3 = trim($_POST['stat_label_3'] ?? '');
        $stat_number_4 = trim($_POST['stat_number_4'] ?? '');
        $stat_label_4 = trim($_POST['stat_label_4'] ?? '');
        
        $cta_banner_title = trim($_POST['cta_banner_title'] ?? '');
        $cta_banner_desc = trim($_POST['cta_banner_desc'] ?? '');
        $cta_btn_text = trim($_POST['cta_btn_text'] ?? '');
        $cta_btn_link = trim($_POST['cta_btn_link'] ?? '');
        
        // Visibility toggles (Checkbox handles: 1 if set, 0 if missing)
        $show_stats = isset($_POST['show_stats']) ? 1 : 0;
        $show_pillars = isset($_POST['show_pillars']) ? 1 : 0;
        $show_about_preview = isset($_POST['show_about_preview']) ? 1 : 0;
        $show_gallery_preview = isset($_POST['show_gallery_preview']) ? 1 : 0;
        $show_news_preview = isset($_POST['show_news_preview']) ? 1 : 0;
        $show_results_preview = isset($_POST['show_results_preview']) ? 1 : 0;
        $show_cta_banner = isset($_POST['show_cta_banner']) ? 1 : 0;
        
        $hero_image_path = $home['hero_image_path']; // Default to current image
        
        // Handle file uploader (Upload new hero banner image)
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['hero_image']['tmp_name'];
            $file_name = sanitize($_FILES['hero_image']['name']);
            $file_size = $_FILES['hero_image']['size'];
            $file_type = $_FILES['hero_image']['type'];
            
            // Extract file extension and validate
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
            
            if (!in_array($ext, $allowed_exts)) {
                $error_msg = "Invalid file type. Only JPG, PNG, WEBP, and SVG formats allowed.";
            } elseif ($file_size > 5 * 1024 * 1024) { // 5MB limit
                $error_msg = "File size exceeds the maximum limit of 5MB.";
            } else {
                // Ensure target folders exist
                $upload_dir = '../uploads/media/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Create unique filename to prevent duplicates
                $new_filename = uniqid('banner_', true) . '.' . $ext;
                $target_path = $upload_dir . $new_filename;
                $db_filepath = 'uploads/media/' . $new_filename;
                
                if (move_uploaded_file($file_tmp, $target_path)) {
                    $hero_image_path = $db_filepath;
                    
                    // Register upload in media library registry
                    $stmt_media = $pdo->prepare("INSERT INTO `media` (`filename`, `filepath`, `filetype`, `filesize`) VALUES (:filename, :filepath, :filetype, :filesize)");
                    $stmt_media->execute([
                        ':filename' => $file_name,
                        ':filepath' => $db_filepath,
                        ':filetype' => $file_type,
                        ':filesize' => $file_size
                    ]);
                } else {
                    $error_msg = "Failed to save uploaded image. Check write permissions.";
                }
            }
        }
        
        // Update database only if no upload validation errors occurred
        if (empty($error_msg)) {
            try {
                $update_stmt = $pdo->prepare("UPDATE `homepage_content` SET
                    `hero_title` = :hero_title,
                    `hero_subtitle` = :hero_subtitle,
                    `hero_btn_text_1` = :hero_btn_text_1,
                    `hero_btn_link_1` = :hero_btn_link_1,
                    `hero_btn_text_2` = :hero_btn_text_2,
                    `hero_btn_link_2` = :hero_btn_link_2,
                    `hero_image_path` = :hero_image_path,
                    
                    `stat_number_1` = :stat_number_1,
                    `stat_label_1` = :stat_label_1,
                    `stat_number_2` = :stat_number_2,
                    `stat_label_2` = :stat_label_2,
                    `stat_number_3` = :stat_number_3,
                    `stat_label_3` = :stat_label_3,
                    `stat_number_4` = :stat_number_4,
                    `stat_label_4` = :stat_label_4,
                    
                    `cta_banner_title` = :cta_banner_title,
                    `cta_banner_desc` = :cta_banner_desc,
                    `cta_btn_text` = :cta_btn_text,
                    `cta_btn_link` = :cta_btn_link,
                    
                    `show_stats` = :show_stats,
                    `show_pillars` = :show_pillars,
                    `show_about_preview` = :show_about_preview,
                    `show_gallery_preview` = :show_gallery_preview,
                    `show_news_preview` = :show_news_preview,
                    `show_results_preview` = :show_results_preview,
                    `show_cta_banner` = :show_cta_banner
                    WHERE `id` = 1");
                
                $update_stmt->execute([
                    ':hero_title' => $hero_title,
                    ':hero_subtitle' => $hero_subtitle,
                    ':hero_btn_text_1' => $hero_btn_text_1,
                    ':hero_btn_link_1' => $hero_btn_link_1,
                    ':hero_btn_text_2' => $hero_btn_text_2,
                    ':hero_btn_link_2' => $hero_btn_link_2,
                    ':hero_image_path' => $hero_image_path,
                    
                    ':stat_number_1' => $stat_number_1,
                    ':stat_label_1' => $stat_label_1,
                    ':stat_number_2' => $stat_number_2,
                    ':stat_label_2' => $stat_label_2,
                    ':stat_number_3' => $stat_number_3,
                    ':stat_label_3' => $stat_label_3,
                    ':stat_number_4' => $stat_number_4,
                    ':stat_label_4' => $stat_label_4,
                    
                    ':cta_banner_title' => $cta_banner_title,
                    ':cta_banner_desc' => $cta_banner_desc,
                    ':cta_btn_text' => $cta_btn_text,
                    ':cta_btn_link' => $cta_btn_link,
                    
                    ':show_stats' => $show_stats,
                    ':show_pillars' => $show_pillars,
                    ':show_about_preview' => $show_about_preview,
                    ':show_gallery_preview' => $show_gallery_preview,
                    ':show_news_preview' => $show_news_preview,
                    ':show_results_preview' => $show_results_preview,
                    ':show_cta_banner' => $show_cta_banner
                ]);
                
                // Refresh local cache representation of data
                $home = [
                    'hero_title' => $hero_title,
                    'hero_subtitle' => $hero_subtitle,
                    'hero_btn_text_1' => $hero_btn_text_1,
                    'hero_btn_link_1' => $hero_btn_link_1,
                    'hero_btn_text_2' => $hero_btn_text_2,
                    'hero_btn_link_2' => $hero_btn_link_2,
                    'hero_image_path' => $hero_image_path,
                    'stat_number_1' => $stat_number_1,
                    'stat_label_1' => $stat_label_1,
                    'stat_number_2' => $stat_number_2,
                    'stat_label_2' => $stat_label_2,
                    'stat_number_3' => $stat_number_3,
                    'stat_label_3' => $stat_label_3,
                    'stat_number_4' => $stat_number_4,
                    'stat_label_4' => $stat_label_4,
                    'cta_banner_title' => $cta_banner_title,
                    'cta_banner_desc' => $cta_banner_desc,
                    'cta_btn_text' => $cta_btn_text,
                    'cta_btn_link' => $cta_btn_link,
                    'show_stats' => $show_stats,
                    'show_pillars' => $show_pillars,
                    'show_about_preview' => $show_about_preview,
                    'show_gallery_preview' => $show_gallery_preview,
                    'show_news_preview' => $show_news_preview,
                    'show_results_preview' => $show_results_preview,
                    'show_cta_banner' => $show_cta_banner
                ];
                
                $success_msg = "Homepage dynamic configurations updated successfully.";
            } catch (PDOException $e) {
                $error_msg = "Database update failure: " . sanitize($e->getMessage());
            }
        }
    }
}

// Generate token protecting post submits
$token = generate_csrf_token();
?>

<!-- Action Feedback Alerts -->
<?php if (!empty($error_msg)): ?>
    <div class="alert-banner error">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><?php echo $error_msg; ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($success_msg)): ?>
    <div class="alert-banner success">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span><?php echo $success_msg; ?></span>
    </div>
<?php endif; ?>

<form action="homepage.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
    
    <!-- 1. Hero banner content section -->
    <div class="dashboard-block">
        <div class="block-title">
            <h3>Hero Banner Content Settings</h3>
        </div>
        
        <div class="form-group">
            <label for="hero_title">Hero Main Title</label>
            <input type="text" name="hero_title" id="hero_title" class="form-control" value="<?php echo sanitize($home['hero_title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="hero_subtitle">Hero Subtitle Paragraph</label>
            <textarea name="hero_subtitle" id="hero_subtitle" class="form-control" required><?php echo sanitize($home['hero_subtitle']); ?></textarea>
        </div>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="hero_btn_text_1">CTA Primary Button Text</label>
                <input type="text" name="hero_btn_text_1" id="hero_btn_text_1" class="form-control" value="<?php echo sanitize($home['hero_btn_text_1']); ?>">
            </div>
            <div class="form-group">
                <label for="hero_btn_link_1">CTA Primary Button URL</label>
                <input type="text" name="hero_btn_link_1" id="hero_btn_link_1" class="form-control" value="<?php echo sanitize($home['hero_btn_link_1']); ?>">
            </div>
            <div class="form-group">
                <label for="hero_btn_text_2">CTA Secondary Button Text</label>
                <input type="text" name="hero_btn_text_2" id="hero_btn_text_2" class="form-control" value="<?php echo sanitize($home['hero_btn_text_2']); ?>">
            </div>
            <div class="form-group">
                <label for="hero_btn_link_2">CTA Secondary Button URL</label>
                <input type="text" name="hero_btn_link_2" id="hero_btn_link_2" class="form-control" value="<?php echo sanitize($home['hero_btn_link_2']); ?>">
            </div>
        </div>
        
        <div class="form-group" style="margin-top: 10px;">
            <label>Hero Illustration / Banner Image</label>
            <div style="display: flex; gap: 24px; align-items: center; margin-bottom: 16px;">
                <div style="width: 120px; height: 100px; border-radius: var(--border-radius-sm); overflow: hidden; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                    <img src="../<?php echo $home['hero_image_path']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="flex-grow: 1;">
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 8px;">Active file: <code><?php echo sanitize($home['hero_image_path']); ?></code></p>
                    <div class="file-upload-wrapper" style="padding: 16px;">
                        <input type="file" name="hero_image" class="file-upload-input">
                        <div class="file-upload-info" style="font-size: 0.85rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span>Upload/Replace Hero Image (Allowed: JPG, PNG, WEBP, SVG)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 2. Statistics Counter configurations -->
    <div class="dashboard-block">
        <div class="block-title">
            <h3>Homepage Statistics and Counters</h3>
        </div>
        
        <div class="form-grid">
            <!-- Stat 1 -->
            <div class="form-group">
                <label for="stat_number_1">Stat 1 Count value</label>
                <input type="text" name="stat_number_1" id="stat_number_1" class="form-control" value="<?php echo sanitize($home['stat_number_1']); ?>">
            </div>
            <div class="form-group">
                <label for="stat_label_1">Stat 1 Descriptive Tag</label>
                <input type="text" name="stat_label_1" id="stat_label_1" class="form-control" value="<?php echo sanitize($home['stat_label_1']); ?>">
            </div>
            <!-- Stat 2 -->
            <div class="form-group">
                <label for="stat_number_2">Stat 2 Count value</label>
                <input type="text" name="stat_number_2" id="stat_number_2" class="form-control" value="<?php echo sanitize($home['stat_number_2']); ?>">
            </div>
            <div class="form-group">
                <label for="stat_label_2">Stat 2 Descriptive Tag</label>
                <input type="text" name="stat_label_2" id="stat_label_2" class="form-control" value="<?php echo sanitize($home['stat_label_2']); ?>">
            </div>
            <!-- Stat 3 -->
            <div class="form-group">
                <label for="stat_number_3">Stat 3 Count value</label>
                <input type="text" name="stat_number_3" id="stat_number_3" class="form-control" value="<?php echo sanitize($home['stat_number_3']); ?>">
            </div>
            <div class="form-group">
                <label for="stat_label_3">Stat 3 Descriptive Tag</label>
                <input type="text" name="stat_label_3" id="stat_label_3" class="form-control" value="<?php echo sanitize($home['stat_label_3']); ?>">
            </div>
            <!-- Stat 4 -->
            <div class="form-group">
                <label for="stat_number_4">Stat 4 Count value</label>
                <input type="text" name="stat_number_4" id="stat_number_4" class="form-control" value="<?php echo sanitize($home['stat_number_4']); ?>">
            </div>
            <div class="form-group">
                <label for="stat_label_4">Stat 4 Descriptive Tag</label>
                <input type="text" name="stat_label_4" id="stat_label_4" class="form-control" value="<?php echo sanitize($home['stat_label_4']); ?>">
            </div>
        </div>
    </div>
    
    <!-- 3. Admissions banner content -->
    <div class="dashboard-block">
        <div class="block-title">
            <h3>Admissions CTA Banner</h3>
        </div>
        
        <div class="form-group">
            <label for="cta_banner_title">Admissions Banner Title</label>
            <input type="text" name="cta_banner_title" id="cta_banner_title" class="form-control" value="<?php echo sanitize($home['cta_banner_title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="cta_banner_desc">Admissions Banner Description</label>
            <textarea name="cta_banner_desc" id="cta_banner_desc" class="form-control" required><?php echo sanitize($home['cta_banner_desc']); ?></textarea>
        </div>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="cta_btn_text">Admissions Action Text</label>
                <input type="text" name="cta_btn_text" id="cta_btn_text" class="form-control" value="<?php echo sanitize($home['cta_btn_text']); ?>">
            </div>
            <div class="form-group">
                <label for="cta_btn_link">Admissions Link URL</label>
                <input type="text" name="cta_btn_link" id="cta_btn_link" class="form-control" value="<?php echo sanitize($home['cta_btn_link']); ?>">
            </div>
        </div>
    </div>
    
    <!-- 4. Section Visibility Toggles -->
    <div class="dashboard-block">
        <div class="block-title">
            <h3>Section Visibility Toggles</h3>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Toggle 1 -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                <div>
                    <h4 style="color: #ffffff; margin-bottom: 2px;">Statistics Counter Bar</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the numerical counters (legacy, toppers, mentors, etc.)</p>
                </div>
                <label class="switch-control">
                    <input type="checkbox" name="show_stats" value="1" <?php if ($home['show_stats'] == 1) echo 'checked'; ?>>
                    <span class="slider-toggle"></span>
                </label>
            </div>
            
            <!-- Toggle 2 -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                <div>
                    <h4 style="color: #ffffff; margin-bottom: 2px;">Academic Pillars</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the academic pillars cards section (Admissions, Curriculum, etc.)</p>
                </div>
                <label class="switch-control">
                    <input type="checkbox" name="show_pillars" value="1" <?php if ($home['show_pillars'] == 1) echo 'checked'; ?>>
                    <span class="slider-toggle"></span>
                </label>
            </div>
            
            <!-- Toggle 3 -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                <div>
                    <h4 style="color: #ffffff; margin-bottom: 2px;">About Us Preview</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Displays a summary of Gurukul's heritage with the campus photo badge</p>
                </div>
                <label class="switch-control">
                    <input type="checkbox" name="show_about_preview" value="1" <?php if ($home['show_about_preview'] == 1) echo 'checked'; ?>>
                    <span class="slider-toggle"></span>
                </label>
            </div>
            
            <!-- Toggle 4 -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                <div>
                    <h4 style="color: #ffffff; margin-bottom: 2px;">Featured Gallery Preview</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the grid of featured photos/videos from the gallery registry</p>
                </div>
                <label class="switch-control">
                    <input type="checkbox" name="show_gallery_preview" value="1" <?php if ($home['show_gallery_preview'] == 1) echo 'checked'; ?>>
                    <span class="slider-toggle"></span>
                </label>
            </div>
            
            <!-- Toggle 5 -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                <div>
                    <h4 style="color: #ffffff; margin-bottom: 2px;">Latest News & Events</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the slider/grid showcasing latest updates and bulletins</p>
                </div>
                <label class="switch-control">
                    <input type="checkbox" name="show_news_preview" value="1" <?php if ($home['show_news_preview'] == 1) echo 'checked'; ?>>
                    <span class="slider-toggle"></span>
                </label>
            </div>
            
            <!-- Toggle 6 -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                <div>
                    <h4 style="color: #ffffff; margin-bottom: 2px;">Alumni Exam Results</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Displays our outstanding CBSE results aggregate percent charts</p>
                </div>
                <label class="switch-control">
                    <input type="checkbox" name="show_results_preview" value="1" <?php if ($home['show_results_preview'] == 1) echo 'checked'; ?>>
                    <span class="slider-toggle"></span>
                </label>
            </div>
            
            <!-- Toggle 7 -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                <div>
                    <h4 style="color: #ffffff; margin-bottom: 2px;">Admissions Banner Section</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the primary call to action enrollments section at the bottom</p>
                </div>
                <label class="switch-control">
                    <input type="checkbox" name="show_cta_banner" value="1" <?php if ($home['show_cta_banner'] == 1) echo 'checked'; ?>>
                    <span class="slider-toggle"></span>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Form Submit Button -->
    <div style="margin-bottom: 40px; text-align: right;">
        <button type="submit" class="btn-action btn-theme" style="padding: 12px 36px; font-size: 1rem;">
            <span>Save Homepage Changes</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        </button>
    </div>
</form>

<?php include_once 'includes/footer.php'; ?>
