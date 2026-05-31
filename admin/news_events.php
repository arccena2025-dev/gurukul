<?php
/**
 * ========================================================
 * NEWS & CAMPUS EVENTS CMS MANAGER MODULE (GURUKUL)
 * ========================================================
 */

require_once '../config/db.php';
require_once 'includes/header.php';

$success_msg = "";
$error_msg = "";
$action_view = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);
$item = null;

// 1. Fetch item if in EDIT mode
if ($action_view === 'edit' && $edit_id > 0) {
    try {
        $stmt_item = $pdo->prepare("SELECT * FROM `news_events` WHERE `id` = :id LIMIT 1");
        $stmt_item->execute([':id' => $edit_id]);
        $item = $stmt_item->fetch();
        
        if (!$item) {
            header("Location: news_events.php");
            exit();
        }
    } catch (PDOException $e) {
        $error_msg = "Database fetch failure: " . sanitize($e->getMessage());
    }
}

// 2. Handle POST CRUD Submissions (ADD, EDIT, DELETE, TOGGLE FEATURED)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['submit_action'] ?? '';
        
        // A. ADD OR EDIT ITEM SUBMISSION
        if ($action === 'add' || $action === 'edit') {
            $type = sanitize($_POST['type'] ?? 'news');
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $event_date = ($type === 'event') ? sanitize($_POST['event_date'] ?? '') : null;
            
            // Image handling (Default to current if edit)
            $db_filepath = ($action === 'edit') ? $item['filepath'] : 'images/heritage_dance_1780230915767.png';
            
            if (empty($title) || empty($content)) {
                $error_msg = "Please fill in all bulletin details.";
            } elseif ($type === 'event' && empty($event_date)) {
                $error_msg = "Event date is required for campus events.";
            } else {
                // Process poster upload
                if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['poster']['tmp_name'];
                    $file_name = sanitize($_FILES['poster']['name']);
                    $file_size = $_FILES['poster']['size'];
                    $file_type = $_FILES['poster']['type'];
                    
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
                    
                    if (!in_array($ext, $allowed_exts)) {
                        $error_msg = "Invalid file type. Only JPG, PNG, WEBP, and SVG formats allowed.";
                    } elseif ($file_size > 5 * 1024 * 1024) {
                        $error_msg = "File size exceeds the limit of 5MB.";
                    } else {
                        $upload_dir = '../uploads/news/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $new_filename = uniqid('news_', true) . '.' . $ext;
                        $target_path = $upload_dir . $new_filename;
                        $db_filepath = 'uploads/news/' . $new_filename;
                        
                        if (move_uploaded_file($file_tmp, $target_path)) {
                            // Register in centralized Media library
                            $stmt_media = $pdo->prepare("INSERT INTO `media` (`filename`, `filepath`, `filetype`, `filesize`) VALUES (:filename, :filepath, :filetype, :filesize)");
                            $stmt_media->execute([
                                ':filename' => $file_name,
                                ':filepath' => $db_filepath,
                                ':filetype' => $file_type,
                                ':filesize' => $file_size
                            ]);
                        } else {
                            $error_msg = "Failed to save uploaded banner image.";
                        }
                    }
                }
                
                if (empty($error_msg)) {
                    try {
                        if ($action === 'add') {
                            $stmt_add = $pdo->prepare("INSERT INTO `news_events` (`title`, `type`, `content`, `event_date`, `filepath`, `is_featured`) VALUES (:title, :type, :content, :event_date, :filepath, :is_feat)");
                            $stmt_add->execute([
                                ':title' => $title,
                                ':type' => $type,
                                ':content' => $content,
                                ':event_date' => $event_date,
                                ':filepath' => $db_filepath,
                                ':is_feat' => $is_featured
                            ]);
                            $success_msg = "New dynamic bulletin added successfully.";
                            $action_view = 'list'; // return
                        } else { // edit
                            $stmt_edit = $pdo->prepare("UPDATE `news_events` SET `title` = :title, `type` = :type, `content` = :content, `event_date` = :event_date, `filepath` = :filepath, `is_featured` = :is_feat WHERE `id` = :id");
                            $stmt_edit->execute([
                                ':title' => $title,
                                ':type' => $type,
                                ':content' => $content,
                                ':event_date' => $event_date,
                                ':filepath' => $db_filepath,
                                ':is_feat' => $is_featured,
                                ':id' => $edit_id
                            ]);
                            $success_msg = "Campus bulletin updated successfully.";
                            $action_view = 'list'; // return
                        }
                    } catch (PDOException $e) {
                        $error_msg = "Database bulletin operation failure: " . sanitize($e->getMessage());
                    }
                }
            }
        }
        
        // B. DELETE BULLETIN
        if ($action === 'delete') {
            $id = intval($_POST['delete_id'] ?? 0);
            if ($id > 0) {
                try {
                    // Fetch file path first to delete physically on disk
                    $path_stmt = $pdo->prepare("SELECT `filepath` FROM `news_events` WHERE `id` = :id");
                    $path_stmt->execute([':id' => $id]);
                    $filepath = $path_stmt->fetchColumn();
                    
                    if ($filepath && strpos($filepath, 'uploads/') !== false) {
                        $full_path = '../' . $filepath;
                        if (file_exists($full_path)) {
                            unlink($full_path);
                        }
                    }
                    
                    $del_stmt = $pdo->prepare("DELETE FROM `news_events` WHERE `id` = :id");
                    $del_stmt->execute([':id' => $id]);
                    $success_msg = "Bulletin removed successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database delete failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // C. FEATURED SWITCH
        if ($action === 'toggle_featured') {
            $id = intval($_POST['asset_id'] ?? 0);
            $state = intval($_POST['featured_state'] ?? 0);
            if ($id > 0) {
                try {
                    $feat_stmt = $pdo->prepare("UPDATE `news_events` SET `is_featured` = :state WHERE `id` = :id");
                    $feat_stmt->execute([':state' => $state, ':id' => $id]);
                    $success_msg = "Featured updates saved successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Featured toggle failure: " . sanitize($e->getMessage());
                }
            }
        }
    }
}

// 3. Fetch all news & events directory records
try {
    $stmt_list = $pdo->query("SELECT * FROM `news_events` ORDER BY `created_at` DESC");
    $bulletins = $stmt_list->fetchAll();
} catch (PDOException $e) {
    die("Database Listing Fetch Failure: " . sanitize($e->getMessage()));
}

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

<!-- VIEW A: BULLETINS DIRECTORY TABLE -->
<?php if ($action_view === 'list'): ?>
    <div style="display: flex; justify-content: space-between; margin-bottom: 28px;">
        <div style="display: flex; gap: 12px; align-items: center; flex-grow: 1;">
            <input type="text" id="bulletin-search" class="form-control" placeholder="Search bulletins by title..." style="max-width: 250px; height: 38px;">
            <select id="bulletin-filter" class="form-control" style="max-width: 180px; height: 38px;">
                <option value="">All Types</option>
                <option value="news">News Bulletins</option>
                <option value="event">Campus Events</option>
            </select>
        </div>
        <a href="news_events.php?action=add" class="btn-action btn-theme">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5v14"/></svg>
            <span>Create Bulletin</span>
        </a>
    </div>
    
    <div class="dashboard-block">
        <div class="block-title">
            <h3>Academy News & Events Directory</h3>
        </div>
        
        <?php if (empty($bulletins)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 36px;">No articles or events listed. Click "Create Bulletin" to add new content.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Poster</th>
                            <th>Title Header</th>
                            <th>Type</th>
                            <th>Scheduled Date</th>
                            <th>Spotlight / Featured</th>
                            <th style="text-align: right;">Action Overrides</th>
                        </tr>
                    </thead>
                    <tbody id="bulletin-rows">
                        <?php foreach ($bulletins as $bulletin): ?>
                            <tr class="bulletin-item" data-title="<?php echo strtolower(sanitize($bulletin['title'])); ?>" data-type="<?php echo sanitize($bulletin['type']); ?>">
                                <td style="width: 80px;">
                                    <div style="width: 50px; height: 50px; border-radius: 4px; overflow: hidden; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center;">
                                        <img src="../<?php echo $bulletin['filepath']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                </td>
                                <td><strong><?php echo sanitize($bulletin['title']); ?></strong></td>
                                <td>
                                    <?php if ($bulletin['type'] === 'news'): ?>
                                        <span class="badge-status badge-info">News</span>
                                    <?php else: ?>
                                        <span class="badge-status badge-success" style="background: rgba(13,148,136,0.15); color: var(--secondary-light); border-color: rgba(13,148,136,0.25);">Event</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem;">
                                    <?php echo ($bulletin['type'] === 'event' && $bulletin['event_date']) ? date('M d, Y', strtotime($bulletin['event_date'])) : '<span style="color: var(--text-muted);">&mdash;</span>'; ?>
                                </td>
                                <td>
                                    <form action="news_events.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="submit_action" value="toggle_featured">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="asset_id" value="<?php echo $bulletin['id']; ?>">
                                        
                                        <?php if ($bulletin['is_featured'] == 1): ?>
                                            <input type="hidden" name="featured_state" value="0">
                                            <button type="submit" class="badge-status badge-success" style="cursor: pointer;">Featured ★</button>
                                        <?php else: ?>
                                            <input type="hidden" name="featured_state" value="1">
                                            <button type="submit" class="badge-status badge-outline" style="cursor: pointer; background: transparent; border-color: var(--primary-accent); color: var(--text-muted);">Standard</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <a href="news_events.php?action=edit&id=<?php echo $bulletin['id']; ?>" class="btn-action btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;">Edit</a>
                                        
                                        <form action="news_events.php" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this bulletin entry?');" style="display: inline;">
                                            <input type="hidden" name="submit_action" value="delete">
                                            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                            <input type="hidden" name="delete_id" value="<?php echo $bulletin['id']; ?>">
                                            <button type="submit" class="btn-action btn-danger" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        const searchInput = document.getElementById('bulletin-search');
        const filterSelect = document.getElementById('bulletin-filter');
        const rows = document.querySelectorAll('.bulletin-item');
        
        const filterRows = () => {
            const query = searchInput.value.toLowerCase().trim();
            const filter = filterSelect.value;
            
            rows.forEach(row => {
                const title = row.getAttribute('data-title');
                const type = row.getAttribute('data-type');
                
                const titleMatch = title.includes(query);
                const typeMatch = !filter || type === filter;
                
                row.style.display = (titleMatch && typeMatch) ? '' : 'none';
            });
        };
        
        if (searchInput && filterSelect) {
            searchInput.addEventListener('input', filterRows);
            filterSelect.addEventListener('change', filterRows);
        }
    </script>

<!-- VIEW B: CREATE OR EDIT BULLETIN BLOCK -->
<?php else: ?>
    <div class="dashboard-block" style="max-width: 800px; margin: 0 auto 36px;">
        <div class="block-title">
            <h3><?php echo ($action_view === 'add') ? 'Create New Bulletin Entry' : 'Edit Bulletin Entry'; ?></h3>
            <a href="news_events.php" class="btn-action btn-outline" style="font-size: 0.85rem; padding: 6px 14px; border-radius: var(--border-radius-sm);">&larr; Return to List</a>
        </div>
        
        <form action="news_events.php<?php echo ($action_view === 'edit') ? '?action=edit&id=' . $edit_id : ''; ?>" method="POST" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="submit_action" value="<?php echo ($action_view === 'add') ? 'add' : 'edit'; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="type">Bulletin Entry Type</label>
                    <select name="type" id="type" class="form-control" required onchange="toggleDateGroup(this.value)">
                        <option value="news" <?php if ($item && $item['type'] === 'news') echo 'selected'; ?>>News Bulletin / Announcement</option>
                        <option value="event" <?php if ($item && $item['type'] === 'event') echo 'selected'; ?>>Campus Scheduled Event</option>
                    </select>
                </div>
                
                <div class="form-group" id="date-group" style="<?php echo ($item && $item['type'] === 'event') ? 'display: block;' : 'display: none;'; ?>">
                    <label for="event_date">Event Date</label>
                    <input type="date" name="event_date" id="event_date" class="form-control" value="<?php echo $item ? sanitize($item['event_date']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="title">Bulletin Headline / Title</label>
                <input type="text" name="title" id="title" class="form-control" value="<?php echo $item ? sanitize($item['title']) : ''; ?>" placeholder="Enter headline title text" required>
            </div>
            
            <div class="form-group">
                <label for="content">Detailed Article Content / Event Agenda</label>
                <textarea name="content" id="content" class="form-control" placeholder="Enter article body paragraphs..." required><?php echo $item ? sanitize($item['content']) : ''; ?></textarea>
            </div>
            
            <div class="form-group" style="margin-top: 10px;">
                <label>Featured Image / Event Poster Banner</label>
                <div style="display: flex; gap: 24px; align-items: center; margin-bottom: 16px;">
                    <?php if ($item): ?>
                        <div style="width: 100px; height: 80px; border-radius: var(--border-radius-sm); overflow: hidden; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                            <img src="../<?php echo $item['filepath']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    <div style="flex-grow: 1;">
                        <div class="file-upload-wrapper" style="padding: 16px;">
                            <input type="file" name="poster" class="file-upload-input">
                            <div class="file-upload-info" style="font-size: 0.85rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span>Choose Poster Image (Allowed: JPG, PNG, WEBP, SVG)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; text-transform: none; font-size: 0.9rem; font-weight: 500; color: #ffffff;">
                    <input type="checkbox" name="is_featured" value="1" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--secondary-light);" <?php if ($item && $item['is_featured'] == 1) echo 'checked'; ?>>
                    <span>Mark this bulletin as <strong>Featured ★</strong> (Highlights inside the Hero/Spotlight layouts)</span>
                </label>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                <a href="news_events.php" class="btn-action btn-outline">Cancel</a>
                <button type="submit" class="btn-action btn-theme">
                    <span><?php echo ($action_view === 'add') ? 'Publish Bulletin' : 'Save Bulletin Changes'; ?></span>
                </button>
            </div>
        </form>
    </div>
    
    <script>
        function toggleDateGroup(val) {
            const dateGroup = document.getElementById('date-group');
            if (val === 'event') {
                dateGroup.style.display = 'block';
                document.getElementById('event_date').setAttribute('required', 'required');
            } else {
                dateGroup.style.display = 'none';
                document.getElementById('event_date').removeAttribute('required');
            }
        }
    </script>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>
