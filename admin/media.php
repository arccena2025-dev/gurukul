<?php
/**
 * ========================================================
 * CENTRALIZED MEDIA LIBRARY CONTROLLER MODULE (GURUKUL)
 * ========================================================
 */

require_once '../config/db.php';
require_once 'includes/header.php';

$success_msg = "";
$error_msg = "";

// 1. Handle POST Actions (Upload multiple files, Delete file)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['action'];
        
        // A. HANDLE MULTIPLE FILES UPLOADER
        if ($action === 'upload_files') {
            if (!isset($_FILES['media_files']) || empty($_FILES['media_files']['name'][0])) {
                $error_msg = "Please select at least one media asset to upload.";
            } else {
                $files = $_FILES['media_files'];
                $upload_count = count($files['name']);
                $success_count = 0;
                
                $upload_dir = '../uploads/media/';
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
                        $allowed_exts = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'mp4', 'mov', 'webm', 'pdf'];
                        
                        if (!in_array($ext, $allowed_exts)) {
                            $error_msg = "File type '{$ext}' is invalid. Allowed: JPG, PNG, WEBP, SVG, MP4, WEBM, PDF.";
                            continue;
                        }
                        
                        if ($size > 15 * 1024 * 1024) { // 15MB limit
                            $error_msg = "File size exceeds the maximum limit of 15MB.";
                            continue;
                        }
                        
                        // Generate safe unique filename
                        $new_filename = uniqid('media_', true) . '.' . $ext;
                        $target_path = $upload_dir . $new_filename;
                        $db_filepath = 'uploads/media/' . $new_filename;
                        
                        if (move_uploaded_file($tmp_name, $target_path)) {
                            $ins_stmt = $pdo->prepare("INSERT INTO `media` (`filename`, `filepath`, `filetype`, `filesize`) VALUES (:filename, :filepath, :filetype, :filesize)");
                            $ins_stmt->execute([
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
                    $success_msg = "Successfully uploaded {$success_count} media assets.";
                }
            }
        }
        
        // B. HANDLE DELETE FILE
        if ($action === 'delete_file') {
            $id = intval($_POST['file_id'] ?? 0);
            if ($id > 0) {
                try {
                    $stmt_filepath = $pdo->prepare("SELECT `filepath` FROM `media` WHERE `id` = :id");
                    $stmt_filepath->execute([':id' => $id]);
                    $filepath = $stmt_filepath->fetchColumn();
                    
                    if ($filepath) {
                        $full_path = '../' . $filepath;
                        if (file_exists($full_path)) {
                            unlink($full_path);
                        }
                    }
                    
                    $del_stmt = $pdo->prepare("DELETE FROM `media` WHERE `id` = :id");
                    $del_stmt->execute([':id' => $id]);
                    $success_msg = "Media asset deleted successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database delete failure: " . sanitize($e->getMessage());
                }
            }
        }
    }
}

// 2. Fetch all media assets
try {
    $stmt_media = $pdo->query("SELECT * FROM `media` ORDER BY `uploaded_at` DESC");
    $assets = $stmt_media->fetchAll();
} catch (PDOException $e) {
    die("Database Media Fetch Failure: " . sanitize($e->getMessage()));
}

$token = generate_csrf_token();
?>

<!-- 1. Centralized File Uploader widget -->
<div class="dashboard-block" style="max-width: 700px; margin: 0 auto 36px;">
    <div class="block-title">
        <h3>Upload New Media Assets</h3>
    </div>
    
    <form action="media.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_files">
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
        
        <div class="form-group">
            <div class="file-upload-wrapper" style="padding: 40px 24px;">
                <input type="file" name="media_files[]" class="file-upload-input" multiple required onchange="updateFileListLabel(this)">
                <div class="file-upload-info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <strong id="file-uploader-label" style="font-size: 1rem; color: #ffffff;">Drag & Drop or Click to choose files</strong>
                    <span>Allowed: Images (JPG, PNG, WEBP, SVG), Videos (MP4, WEBM), PDFs (Max size: 15MB)</span>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn-action btn-theme" style="width: 100%; height: 48px;">
            <span>Upload Assets to Media Library</span>
        </button>
    </form>
</div>

<!-- 2. Centralized Media grid directory overview -->
<div class="dashboard-block">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; margin-bottom: 24px; border-bottom: 1px solid var(--glass-border); padding-bottom: 16px;">
        <h3 style="color: #ffffff; font-family: var(--font-heading); font-size: 1.25rem;">Central Media Library (<?php echo count($assets); ?>)</h3>
        
        <!-- Live search filter -->
        <input type="text" id="media-search" class="form-control" placeholder="Search assets by filename..." style="max-width: 250px; height: 38px;">
    </div>
    
    <!-- Copy Path Toast popup notice -->
    <div id="copy-toast" style="display: none; position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(40px); background: #0f766e; color: #ffffff; border: 1px solid rgba(255,255,255,0.2); padding: 12px 24px; border-radius: 30px; font-weight: 600; font-size: 0.95rem; z-index: 10000; box-shadow: var(--shadow-lg); transition: transform 0.3s ease, opacity 0.3s ease; opacity: 0;">Path link copied to clipboard!</div>
    
    <?php if (empty($assets)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 48px;">No media assets inside library. Choose assets above to populate.</p>
    <?php else: ?>
        <div class="media-grid" id="media-grid">
            <?php foreach ($assets as $asset): ?>
                <div class="media-item-card" data-filename="<?php echo strtolower(sanitize($asset['filename'])); ?>" onclick="showMediaDetails(<?php echo htmlspecialchars(json_encode($asset)); ?>)">
                    
                    <?php if (strpos($asset['filetype'], 'image') !== false): ?>
                        <img src="../<?php echo $asset['filepath']; ?>" class="media-preview">
                    <?php elseif (strpos($asset['filetype'], 'video') !== false): ?>
                        <!-- Video symbol -->
                        <div class="media-icon" style="color: var(--accent-light);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect width="15" height="14" x="1" y="5" rx="2" ry="2"></rect></svg>
                        </div>
                    <?php else: ?>
                        <!-- PDF symbol -->
                        <div class="media-icon" style="color: #f87171;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        </div>
                    <?php endif; ?>
                    
                    <div class="media-meta-label">
                        <?php echo sanitize($asset['filename']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Asset Details popup Modal -->
<div id="media-details-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(12px);">
    <div class="dashboard-block" style="width: 90%; max-width: 600px; margin-bottom: 0; position: relative;">
        <div class="block-title">
            <h3>Media Asset Information</h3>
            <button onclick="closeMediaModal()" class="btn-action btn-outline" style="padding: 4px 10px; border-radius: var(--border-radius-circle); font-size: 0.8rem;">X</button>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;" class="modal-grid">
            <!-- Left preview -->
            <div style="width: 100%; height: 200px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border); overflow: hidden; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                <img id="modal-preview-img" src="" style="width: 100%; height: 100%; object-fit: contain; display: none;">
                <div id="modal-preview-icon" class="media-icon" style="font-size: 4rem; display: none;"></div>
            </div>
            
            <!-- Right Details -->
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <strong style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">File Name</strong>
                    <span id="modal-filename" style="color: #ffffff; font-weight: 600; word-break: break-all;">filename.png</span>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Relative CMS Path URL</strong>
                    <code id="modal-path" style="background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 4px; display: block; font-size: 0.8rem; border: 1px solid var(--glass-border); word-break: break-all; margin-top: 4px; color: #38bdf8;">uploads/media/file.png</code>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <strong style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">File Type</strong>
                        <span id="modal-type" style="color: #ffffff; font-size: 0.85rem;">image/png</span>
                    </div>
                    <div>
                        <strong style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">File Size</strong>
                        <span id="modal-size" style="color: #ffffff; font-size: 0.85rem;">124 KB</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; border-top: 1px solid var(--glass-border); padding-top: 16px;">
            <button type="button" class="btn-action btn-outline" onclick="closeMediaModal()">Close Details</button>
            <button type="button" class="btn-action btn-theme" onclick="copyAssetPath()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                <span>Copy Path URL</span>
            </button>
            
            <form action="media.php" method="POST" onsubmit="return confirm('Deleting this media asset will unrecoverably remove it from all page references! Proceed?');" style="display: inline;">
                <input type="hidden" name="action" value="delete_file">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <input type="hidden" name="file_id" id="modal-file-id" value="">
                <button type="submit" class="btn-action btn-danger">Delete Asset</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Live file upload labeling
    function updateFileListLabel(input) {
        const label = document.getElementById('file-uploader-label');
        if(input.files.length > 0) {
            label.textContent = `${input.files.length} file(s) selected for upload`;
            label.style.color = 'var(--secondary-light)';
        } else {
            label.textContent = 'Drag & Drop or Click to choose files';
            label.style.color = '#ffffff';
        }
    }

    // Client search filter
    const searchInput = document.getElementById('media-search');
    const items = document.querySelectorAll('.media-item-card');
    
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase().trim();
            items.forEach(item => {
                const filename = item.getAttribute('data-filename');
                item.style.display = filename.includes(query) ? '' : 'none';
            });
        });
    }

    // Modal popup triggers
    let selectedAsset = null;
    
    function showMediaDetails(asset) {
        selectedAsset = asset;
        
        document.getElementById('modal-file-id').value = asset.id;
        document.getElementById('modal-filename').textContent = asset.filename;
        document.getElementById('modal-path').textContent = asset.filepath;
        document.getElementById('modal-type').textContent = asset.filetype;
        document.getElementById('modal-size').textContent = (asset.filesize / 1024).toFixed(1) + ' KB';
        
        const imgPreview = document.getElementById('modal-preview-img');
        const iconPreview = document.getElementById('modal-preview-icon');
        
        if (asset.filetype.includes('image')) {
            imgPreview.src = '../' + asset.filepath;
            imgPreview.style.display = 'block';
            iconPreview.style.display = 'none';
        } else if (asset.filetype.includes('video')) {
            imgPreview.style.display = 'none';
            iconPreview.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent-light);"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect width="15" height="14" x="1" y="5" rx="2" ry="2"></rect></svg>`;
            iconPreview.style.display = 'flex';
        } else {
            imgPreview.style.display = 'none';
            iconPreview.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f87171;"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>`;
            iconPreview.style.display = 'flex';
        }
        
        document.getElementById('media-details-modal').style.display = 'flex';
    }
    
    function closeMediaModal() {
        document.getElementById('media-details-modal').style.display = 'none';
    }
    
    // Copy path to clipboard
    function copyAssetPath() {
        if (!selectedAsset) return;
        
        // Copy path text to clipboard using modern Clipboard API
        navigator.clipboard.writeText(selectedAsset.filepath).then(() => {
            const toast = document.getElementById('copy-toast');
            toast.style.display = 'block';
            
            // Stagger animation reveals
            setTimeout(() => {
                toast.style.transform = 'translateX(-50%) translateY(0)';
                toast.style.opacity = '1';
            }, 50);
            
            // Hide toast after 2.5s
            setTimeout(() => {
                toast.style.transform = 'translateX(-50%) translateY(40px)';
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 300);
            }, 2500);
            
            closeMediaModal();
        });
    }
</script>

<style>
    @media (max-width: 768px) {
        .modal-grid {
            grid-template-columns: 1fr !important;
            gap: 16px !important;
        }
    }
</style>

<?php include_once 'includes/footer.php'; ?>
