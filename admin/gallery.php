<?php
/**
 * ========================================================
 * ACADEMY GALLERY & CATEGORIES MANAGER MODULE (GURUKUL)
 * ========================================================
 */

require_once '../config/db.php';
require_once 'includes/header.php';

$success_msg = "";
$error_msg = "";

// 1. Handle POST Actions (Upload visual asset, CRUD Categories, Delete item, Feature toggle)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['action'];
        
        // A. MULTI-FILE UPLOADER FOR IMAGES & VIDEOS
        if ($action === 'upload_asset') {
            $cat_id = intval($_POST['category_id'] ?? 0);
            $type = sanitize($_POST['type'] ?? 'image');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            if ($cat_id === 0) {
                $error_msg = "Please select a valid gallery category.";
            } elseif (!isset($_FILES['assets']) || empty($_FILES['assets']['name'][0])) {
                $error_msg = "Please choose at least one image or video file to upload.";
            } else {
                $files = $_FILES['assets'];
                $upload_count = count($files['name']);
                $success_count = 0;
                
                // Establish destination uploader paths
                $upload_dir = '../uploads/gallery/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                for ($i = 0; $i < $upload_count; $i++) {
                    $tmp_name = $files['tmp_name'][$i];
                    $original_name = sanitize($files['name'][$i]);
                    $size = $files['size'][$i];
                    $error = $files['error'][$i];
                    $mime_type = $files['type'][$i];
                    
                    if ($error === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                        $allowed_image_exts = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
                        $allowed_video_exts = ['mp4', 'mov', 'avi', 'webm'];
                        $allowed_exts = array_merge($allowed_image_exts, $allowed_video_exts);
                        
                        if (!in_array($ext, $allowed_exts)) {
                            $error_msg = "File type '{$ext}' is invalid. Allowed: JPG, PNG, WEBP, SVG, MP4, WEBM.";
                            continue;
                        }
                        
                        // Infer correct media category type if not set explicitly
                        $inferred_type = in_array($ext, $allowed_video_exts) ? 'video' : 'image';
                        
                        // Unique name
                        $uniq_name = uniqid('asset_', true) . '.' . $ext;
                        $target_path = $upload_dir . $uniq_name;
                        $db_filepath = 'uploads/gallery/' . $uniq_name;
                        
                        if (move_uploaded_file($tmp_name, $target_path)) {
                            // Format clean title from filename
                            $clean_title = ucwords(str_replace(['_', '-'], ' ', pathinfo($original_name, PATHINFO_FILENAME)));
                            
                            // Insert into `gallery` database table
                            $ins_stmt = $pdo->prepare("INSERT INTO `gallery` (`category_id`, `title`, `type`, `filepath`, `is_featured`) VALUES (:cat_id, :title, :type, :filepath, :is_feat)");
                            $ins_stmt->execute([
                                ':cat_id' => $cat_id,
                                ':title' => $clean_title,
                                ':type' => $inferred_type,
                                ':filepath' => $db_filepath,
                                ':is_feat' => $is_featured
                            ]);
                            
                            // Register in centralized Media table as well
                            $stmt_media = $pdo->prepare("INSERT INTO `media` (`filename`, `filepath`, `filetype`, `filesize`) VALUES (:filename, :filepath, :filetype, :filesize)");
                            $stmt_media->execute([
                                ':filename' => $original_name,
                                ':filepath' => $db_filepath,
                                ':filetype' => $mime_type,
                                ':filesize' => $size
                            ]);
                            
                            $success_count++;
                        }
                    }
                }
                
                if ($success_count > 0) {
                    $success_msg = "Successfully uploaded {$success_count} gallery asset(s).";
                } else {
                    $error_msg = "Upload process failed. Make sure folders exist and files conform to types.";
                }
            }
        }
        
        // B. CRUD GALLERY CATEGORIES (ADD, EDIT, DELETE)
        if ($action === 'add_category') {
            $name = trim($_POST['cat_name'] ?? '');
            if (empty($name)) {
                $error_msg = "Category name cannot be empty.";
            } else {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                try {
                    $cat_ins = $pdo->prepare("INSERT INTO `gallery_categories` (`name`, `slug`) VALUES (:name, :slug)");
                    $cat_ins->execute([':name' => $name, ':slug' => $slug]);
                    $success_msg = "Category '{$name}' created successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Category already exists or slug collision occurred.";
                }
            }
        }
        
        if ($action === 'edit_category') {
            $id = intval($_POST['cat_id'] ?? 0);
            $name = trim($_POST['cat_name'] ?? '');
            if ($id > 0 && !empty($name)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                try {
                    $cat_edit = $pdo->prepare("UPDATE `gallery_categories` SET `name` = :name, `slug` = :slug WHERE `id` = :id");
                    $cat_edit->execute([':name' => $name, ':slug' => $slug, ':id' => $id]);
                    $success_msg = "Category name updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Category name collision occurred.";
                }
            }
        }
        
        if ($action === 'delete_category') {
            $id = intval($_POST['cat_id'] ?? 0);
            if ($id > 0) {
                try {
                    $cat_del = $pdo->prepare("DELETE FROM `gallery_categories` WHERE `id` = :id");
                    $cat_del->execute([':id' => $id]);
                    $success_msg = "Category and its cascading assets deleted successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database delete failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // C. TOGGLE FEATURED STATS
        if ($action === 'toggle_featured') {
            $id = intval($_POST['asset_id'] ?? 0);
            $state = intval($_POST['featured_state'] ?? 0);
            if ($id > 0) {
                try {
                    $feat_stmt = $pdo->prepare("UPDATE `gallery` SET `is_featured` = :state WHERE `id` = :id");
                    $feat_stmt->execute([':state' => $state, ':id' => $id]);
                    $success_msg = "Asset featured status updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Feature update failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // D. EDIT ITEM TITLE
        if ($action === 'edit_asset_title') {
            $id = intval($_POST['asset_id'] ?? 0);
            $title = trim($_POST['asset_title'] ?? '');
            if ($id > 0 && !empty($title)) {
                try {
                    $edit_asset = $pdo->prepare("UPDATE `gallery` SET `title` = :title WHERE `id` = :id");
                    $edit_asset->execute([':title' => $title, ':id' => $id]);
                    $success_msg = "Gallery asset title modified successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Update failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // E. DELETE GALLERY ITEM
        if ($action === 'delete_asset') {
            $id = intval($_POST['asset_id'] ?? 0);
            if ($id > 0) {
                try {
                    // Fetch file path first to delete the file on physical disk
                    $path_stmt = $pdo->prepare("SELECT `filepath` FROM `gallery` WHERE `id` = :id");
                    $path_stmt->execute([':id' => $id]);
                    $filepath = $path_stmt->fetchColumn();
                    
                    if ($filepath) {
                        $full_path = '../' . $filepath;
                        if (file_exists($full_path)) {
                            unlink($full_path);
                        }
                    }
                    
                    $del_stmt = $pdo->prepare("DELETE FROM `gallery` WHERE `id` = :id");
                    $del_stmt->execute([':id' => $id]);
                    $success_msg = "Gallery asset removed successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database delete failure: " . sanitize($e->getMessage());
                }
            }
        }
    }
}

// 2. Fetch categories and items
try {
    $categories = $pdo->query("SELECT * FROM `gallery_categories` ORDER BY `name` ASC")->fetchAll();
    
    // Joint query to retrieve category names
    $stmt_gallery = $pdo->query("SELECT g.*, c.name AS category_name FROM `gallery` g JOIN `gallery_categories` c ON g.category_id = c.id ORDER BY g.uploaded_at DESC");
    $gallery_items = $stmt_gallery->fetchAll();
} catch (PDOException $e) {
    die("Database Fetch Failure: " . sanitize($e->getMessage()));
}

$token = generate_csrf_token();
?>

<!-- Tab Selector Navigation -->
<div style="display: flex; gap: 16px; margin-bottom: 28px; border-bottom: 1px solid var(--glass-border); padding-bottom: 1px;">
    <button class="btn-action <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'directory') ? 'btn-theme' : 'btn-outline'; ?>" onclick="window.location.href='gallery.php?tab=directory'" style="border-radius: 4px 4px 0 0; padding: 12px 24px; font-size: 0.95rem;">Assets Directory (<?php echo count($gallery_items); ?>)</button>
    <button class="btn-action <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'upload') ? 'btn-theme' : 'btn-outline'; ?>" onclick="window.location.href='gallery.php?tab=upload'" style="border-radius: 4px 4px 0 0; padding: 12px 24px; font-size: 0.95rem;">Upload Multiple Assets</button>
    <button class="btn-action <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'categories') ? 'btn-theme' : 'btn-outline'; ?>" onclick="window.location.href='gallery.php?tab=categories'" style="border-radius: 4px 4px 0 0; padding: 12px 24px; font-size: 0.95rem;">Manage Categories</button>
</div>

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

<!-- TAB 1: ASSET DIRECTORY (GRID & SEARCH TABLE) -->
<?php if (!isset($_GET['tab']) || $_GET['tab'] === 'directory'): ?>
    <div class="dashboard-block" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
            <h3 style="color: #ffffff; font-family: var(--font-heading); font-size: 1.25rem;">Visual Assets Directory</h3>
            
            <!-- Quick filters & client search -->
            <div style="display: flex; gap: 12px; flex-wrap: wrap; flex-grow: 1; justify-content: flex-end;">
                <input type="text" id="directory-search" class="form-control" placeholder="Search assets by title..." style="max-width: 250px; height: 38px;">
                <select id="directory-filter" class="form-control" style="max-width: 200px; height: 38px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo sanitize($cat['name']); ?>"><?php echo sanitize($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <?php if (empty($gallery_items)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 36px;">No gallery assets uploaded yet. Click the upload tab to populate content.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table" id="gallery-table">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Asset Title Header</th>
                            <th>Asset Category</th>
                            <th>Type</th>
                            <th>Featured Status</th>
                            <th style="text-align: right;">Action Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gallery_items as $item): ?>
                            <tr class="gallery-row" data-title="<?php echo strtolower(sanitize($item['title'])); ?>" data-category="<?php echo sanitize($item['category_name']); ?>">
                                <td style="width: 80px;">
                                    <div style="width: 50px; height: 50px; border-radius: 4px; overflow: hidden; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center;">
                                        <?php if ($item['type'] === 'image'): ?>
                                            <img src="../<?php echo $item['filepath']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <!-- Video Placeholder Symbol -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent-light);"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect width="15" height="14" x="1" y="5" rx="2" ry="2"></rect></svg>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><strong><?php echo sanitize($item['title']); ?></strong></td>
                                <td><span class="badge-status badge-info"><?php echo sanitize($item['category_name']); ?></span></td>
                                <td style="text-transform: capitalize; font-size: 0.85rem;"><?php echo sanitize($item['type']); ?></td>
                                <td>
                                    <!-- Dynamic Feature toggle form -->
                                    <form action="gallery.php?tab=directory" method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_featured">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="asset_id" value="<?php echo $item['id']; ?>">
                                        
                                        <?php if ($item['is_featured'] == 1): ?>
                                            <input type="hidden" name="featured_state" value="0">
                                            <button type="submit" class="badge-status badge-success" style="cursor: pointer; border: 1px solid rgba(34, 197, 94, 0.4); text-transform: uppercase;">Featured ★</button>
                                        <?php else: ?>
                                            <input type="hidden" name="featured_state" value="1">
                                            <button type="submit" class="badge-status badge-outline" style="cursor: pointer; border: 1px solid var(--primary-accent); background: transparent; color: var(--text-muted); text-transform: uppercase;">Standard</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <button class="btn-action btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;" onclick="openEditAssetModal(<?php echo htmlspecialchars(json_encode($item)); ?>)">Rename</button>
                                        
                                        <form action="gallery.php?tab=directory" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this gallery asset?');" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_asset">
                                            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                            <input type="hidden" name="asset_id" value="<?php echo $item['id']; ?>">
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

    <!-- Edit Asset Title Modal Overlay -->
    <div id="edit-asset-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(12px);">
        <div class="dashboard-block" style="width: 90%; max-width: 500px; margin-bottom: 0;">
            <div class="block-title">
                <h3>Rename Gallery Asset</h3>
                <button onclick="closeEditAssetModal()" class="btn-action btn-outline" style="padding: 4px 10px; border-radius: var(--border-radius-circle); font-size: 0.8rem;">X</button>
            </div>
            <form action="gallery.php?tab=directory" method="POST" autocomplete="off">
                <input type="hidden" name="action" value="edit_asset_title">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <input type="hidden" name="asset_id" id="edit_asset_id" value="">
                
                <div class="form-group">
                    <label for="edit_asset_title">Asset Title Header Text</label>
                    <input type="text" name="asset_title" id="edit_asset_title" class="form-control" required>
                </div>
                
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
                    <button type="button" class="btn-action btn-outline" onclick="closeEditAssetModal()">Cancel</button>
                    <button type="submit" class="btn-action btn-theme">Save Title changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Live client search & filter scripting
        const searchInput = document.getElementById('directory-search');
        const filterSelect = document.getElementById('directory-filter');
        const rows = document.querySelectorAll('.gallery-row');
        
        const filterGrid = () => {
            const query = searchInput.value.toLowerCase().trim();
            const filter = filterSelect.value;
            
            rows.forEach(row => {
                const title = row.getAttribute('data-title');
                const category = row.getAttribute('data-category');
                
                const titleMatch = title.includes(query);
                const categoryMatch = !filter || category === filter;
                
                row.style.display = (titleMatch && categoryMatch) ? '' : 'none';
            });
        };
        
        if(searchInput && filterSelect) {
            searchInput.addEventListener('input', filterGrid);
            filterSelect.addEventListener('change', filterGrid);
        }
        
        function openEditAssetModal(item) {
            document.getElementById('edit_asset_id').value = item.id;
            document.getElementById('edit_asset_title').value = item.title;
            document.getElementById('edit-asset-modal').style.display = 'flex';
        }
        function closeEditAssetModal() {
            document.getElementById('edit-asset-modal').style.display = 'none';
        }
    </script>

<!-- TAB 2: MULTI-FILE ASSET UPLOADER -->
<?php elseif ($_GET['tab'] === 'upload'): ?>
    <div class="dashboard-block" style="max-width: 650px; margin: 0 auto 36px;">
        <div class="block-title">
            <h3>Upload Multiple Assets</h3>
        </div>
        
        <form action="gallery.php?tab=directory" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_asset">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            
            <div class="form-group">
                <label for="category_id">Gallery Category Target</label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">-- Choose Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Select Assets Files (Allows Multiple Selection)</label>
                <div class="file-upload-wrapper" style="padding: 40px 24px;">
                    <input type="file" name="assets[]" class="file-upload-input" multiple required id="multiple-assets-input" onchange="updateFileListLabel(this)">
                    <div class="file-upload-info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <strong id="file-uploader-label" style="font-size: 1rem; color: #ffffff;">Drag & Drop or Click to choose files</strong>
                        <span>Allowed formats: JPG, PNG, WEBP, SVG, MP4, WEBM</span>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; text-transform: none; font-size: 0.9rem; font-weight: 500; color: #ffffff;">
                    <input type="checkbox" name="is_featured" value="1" style="width: 18px; height: 18px; cursor: pointer; border-radius: 4px; accent-color: var(--secondary-light);">
                    <span>Mark all uploaded assets as <strong>Featured ★</strong> (Displays on Homepage preview)</span>
                </label>
            </div>
            
            <button type="submit" class="btn-action btn-theme" style="width: 100%; height: 48px; margin-top: 10px;">
                <span>Start Upload Operations</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            </button>
        </form>
    </div>
    
    <script>
        function updateFileListLabel(input) {
            const label = document.getElementById('file-uploader-label');
            const fileCount = input.files.length;
            if(fileCount > 0) {
                label.textContent = `${fileCount} file(s) selected for upload`;
                label.style.color = 'var(--secondary-light)';
            } else {
                label.textContent = 'Drag & Drop or Click to choose files';
                label.style.color = '#ffffff';
            }
        }
    </script>

<!-- TAB 3: CATEGORIES MANAGEMENT (CRUD) -->
<?php else: ?>
    <div style="display: grid; grid-template-columns: 0.8fr 1.2fr; gap: 32px;" class="split-layout">
        
        <!-- Add Category Form -->
        <div class="dashboard-block" style="margin-bottom: 0;">
            <div class="block-title">
                <h3>Create New Category</h3>
            </div>
            <form action="gallery.php?tab=categories" method="POST" autocomplete="off">
                <input type="hidden" name="action" value="add_category">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                
                <div class="form-group">
                    <label for="cat_name">Category Name</label>
                    <input type="text" name="cat_name" id="cat_name" class="form-control" placeholder="e.g. Science Fair" required>
                </div>
                
                <button type="submit" class="btn-action btn-theme" style="width: 100%;">
                    <span>Create Category</span>
                </button>
            </form>
        </div>
        
        <!-- Categories Registry Table -->
        <div class="dashboard-block" style="margin-bottom: 0;">
            <div class="block-title">
                <h3>Active Categories</h3>
            </div>
            
            <?php if (empty($categories)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 24px;">No categories created yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category ID</th>
                                <th>Category Name</th>
                                <th>Slug Token</th>
                                <th style="text-align: right;">Action Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><strong>#<?php echo $cat['id']; ?></strong></td>
                                    <td><strong><?php echo sanitize($cat['name']); ?></strong></td>
                                    <td><code><?php echo sanitize($cat['slug']); ?></code></td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                            <button class="btn-action btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;" onclick="openEditCatModal(<?php echo htmlspecialchars(json_encode($cat)); ?>)">Rename</button>
                                            
                                            <form action="gallery.php?tab=categories" method="POST" onsubmit="return confirm('Deleting this category will permanently delete ALL assets under it! Do you wish to proceed?');" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_category">
                                                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                                <input type="hidden" name="cat_id" value="<?php echo $cat['id']; ?>">
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
    </div>

    <!-- Edit Category Name Modal Overlay -->
    <div id="edit-cat-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(12px);">
        <div class="dashboard-block" style="width: 90%; max-width: 450px; margin-bottom: 0;">
            <div class="block-title">
                <h3>Rename Category</h3>
                <button onclick="closeEditCatModal()" class="btn-action btn-outline" style="padding: 4px 10px; border-radius: var(--border-radius-circle); font-size: 0.8rem;">X</button>
            </div>
            <form action="gallery.php?tab=categories" method="POST" autocomplete="off">
                <input type="hidden" name="action" value="edit_category">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <input type="hidden" name="cat_id" id="edit_cat_id" value="">
                
                <div class="form-group">
                    <label for="edit_cat_name">Category Name</label>
                    <input type="text" name="cat_name" id="edit_cat_name" class="form-control" required>
                </div>
                
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
                    <button type="button" class="btn-action btn-outline" onclick="closeEditCatModal()">Cancel</button>
                    <button type="submit" class="btn-action btn-theme">Save Category Name</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditCatModal(cat) {
            document.getElementById('edit_cat_id').value = cat.id;
            document.getElementById('edit_cat_name').value = cat.name;
            document.getElementById('edit-cat-modal').style.display = 'flex';
        }
        function closeEditCatModal() {
            document.getElementById('edit-cat-modal').style.display = 'none';
        }
    </script>
    <style>
        @media (max-width: 992px) {
            .split-layout {
                grid-template-columns: 1fr !important;
                gap: 24px !important;
            }
        }
    </style>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>
