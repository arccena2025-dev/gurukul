<?php
/**
 * ========================================================
 * CERTIFICATES & COMPLIANCE MANDATORY DISCLOSURE CMS MODULE (GURUKUL)
 * ========================================================
 */

require_once '../config/db.php';
require_once 'includes/header.php';

$success_msg = "";
$error_msg = "";

/**
 * Reusable PDF Upload Helper
 */
function upload_pdf_document($file_key, $prefix = 'cert_') {
    global $pdo;
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'error' => null];
    }
    
    $file_tmp = $_FILES[$file_key]['tmp_name'];
    $file_name = sanitize($_FILES[$file_key]['name']);
    $file_size = $_FILES[$file_key]['size'];
    $file_type = $_FILES[$file_key]['type'];
    
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    if ($ext !== 'pdf') {
        return ['status' => false, 'error' => "Invalid file type. Only PDF format allowed for documents."];
    }
    if ($file_size > 15 * 1024 * 1024) { // 15MB limit
        return ['status' => false, 'error' => "PDF size exceeds the maximum limit of 15MB."];
    }
    
    $upload_dir = '../uploads/certificates/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $new_filename = uniqid($prefix, true) . '.' . $ext;
    $target_path = $upload_dir . $new_filename;
    $db_filepath = 'uploads/certificates/' . $new_filename;
    
    if (move_uploaded_file($file_tmp, $target_path)) {
        // Also insert into central media table for consistency
        $stmt_media = $pdo->prepare("INSERT INTO `media` (`filename`, `filepath`, `filetype`, `filesize`) VALUES (:filename, :filepath, :filetype, :filesize)");
        $stmt_media->execute([
            ':filename' => $file_name,
            ':filepath' => $db_filepath,
            ':filetype' => 'application/pdf',
            ':filesize' => $file_size
        ]);
        return ['status' => true, 'filepath' => $db_filepath];
    }
    
    return ['status' => false, 'error' => "Failed to save uploaded PDF."];
}

/**
 * Reusable Image Upload Helper (for optional thumbnails)
 */
function upload_thumbnail_image($file_key, $prefix = 'thumb_') {
    global $pdo;
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'error' => null];
    }
    
    $file_tmp = $_FILES[$file_key]['tmp_name'];
    $file_name = sanitize($_FILES[$file_key]['name']);
    $file_size = $_FILES[$file_key]['size'];
    $file_type = $_FILES[$file_key]['type'];
    
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    
    if (!in_array($ext, $allowed_exts)) {
        return ['status' => false, 'error' => "Invalid file type. Only JPG, PNG, WEBP, and SVG formats allowed for thumbnails."];
    }
    if ($file_size > 5 * 1024 * 1024) { // 5MB limit
        return ['status' => false, 'error' => "Thumbnail size exceeds the maximum limit of 5MB."];
    }
    
    $upload_dir = '../uploads/certificates/thumbnails/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $new_filename = uniqid($prefix, true) . '.' . $ext;
    $target_path = $upload_dir . $new_filename;
    $db_filepath = 'uploads/certificates/thumbnails/' . $new_filename;
    
    if (move_uploaded_file($file_tmp, $target_path)) {
        $stmt_media = $pdo->prepare("INSERT INTO `media` (`filename`, `filepath`, `filetype`, `filesize`) VALUES (:filename, :filepath, :filetype, :filesize)");
        $stmt_media->execute([
            ':filename' => $file_name,
            ':filepath' => $db_filepath,
            ':filetype' => $file_type,
            ':filesize' => $file_size
        ]);
        return ['status' => true, 'filepath' => $db_filepath];
    }
    
    return ['status' => false, 'error' => "Failed to save uploaded thumbnail image."];
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['action'];
        
        // 1. ADD CERTIFICATE
        if ($action === 'add_certificate') {
            $title = trim($_POST['title'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $issue_authority = trim($_POST['issue_authority'] ?? '');
            $certificate_number = trim($_POST['certificate_number'] ?? '');
            $issue_date = !empty($_POST['issue_date']) ? $_POST['issue_date'] : null;
            $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            if (empty($title) || empty($category)) {
                $error_msg = "Title and Category fields are required.";
            } elseif (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
                $error_msg = "Please choose a valid PDF document to upload.";
            } else {
                // Upload PDF
                $pdf_res = upload_pdf_document('pdf_file');
                if (!$pdf_res['status']) {
                    $error_msg = $pdf_res['error'] ?? "Failed to upload document.";
                } else {
                    $pdf_path = $pdf_res['filepath'];
                    $thumbnail_path = null;
                    
                    // Upload optional thumbnail
                    if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
                        $thumb_res = upload_thumbnail_image('thumbnail_file');
                        if ($thumb_res['status']) {
                            $thumbnail_path = $thumb_res['filepath'];
                        }
                    }
                    
                    try {
                        $stmt_ins = $pdo->prepare("INSERT INTO `certificates` 
                            (`title`, `category`, `pdf_path`, `thumbnail_path`, `issue_authority`, `certificate_number`, `issue_date`, `expiry_date`, `is_visible`, `is_featured`, `sort_order`) 
                            VALUES (:title, :category, :pdf_path, :thumbnail_path, :issue_authority, :certificate_number, :issue_date, :expiry_date, :is_visible, :is_featured, :sort_order)");
                        $stmt_ins->execute([
                            ':title' => $title,
                            ':category' => $category,
                            ':pdf_path' => $pdf_path,
                            ':thumbnail_path' => $thumbnail_path,
                            ':issue_authority' => $issue_authority,
                            ':certificate_number' => $certificate_number,
                            ':issue_date' => $issue_date,
                            ':expiry_date' => $expiry_date,
                            ':is_visible' => $is_visible,
                            ':is_featured' => $is_featured,
                            ':sort_order' => $sort_order
                        ]);
                        $success_msg = "Certificate '{$title}' successfully uploaded and registered.";
                    } catch (PDOException $e) {
                        $error_msg = "Database write error: " . sanitize($e->getMessage());
                    }
                }
            }
        }
        
        // 2. EDIT CERTIFICATE
        elseif ($action === 'edit_certificate') {
            $id = intval($_POST['cert_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $issue_authority = trim($_POST['issue_authority'] ?? '');
            $certificate_number = trim($_POST['certificate_number'] ?? '');
            $issue_date = !empty($_POST['issue_date']) ? $_POST['issue_date'] : null;
            $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            if ($id <= 0 || empty($title) || empty($category)) {
                $error_msg = "Invalid inputs. Required fields must not be empty.";
            } else {
                try {
                    // Fetch existing record
                    $stmt_fetch = $pdo->prepare("SELECT * FROM `certificates` WHERE `id` = :id LIMIT 1");
                    $stmt_fetch->execute([':id' => $id]);
                    $cert = $stmt_fetch->fetch();
                    
                    if (!$cert) {
                        $error_msg = "Target certificate record not found.";
                    } else {
                        $pdf_path = $cert['pdf_path'];
                        $thumbnail_path = $cert['thumbnail_path'];
                        
                        // Check if new PDF uploaded
                        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                            $pdf_res = upload_pdf_document('pdf_file');
                            if ($pdf_res['status']) {
                                // Delete old PDF file if it exists
                                if (!empty($pdf_path) && file_exists('../' . $pdf_path)) {
                                    @unlink('../' . $pdf_path);
                                }
                                $pdf_path = $pdf_res['filepath'];
                            } else {
                                throw new Exception($pdf_res['error'] ?? "Failed to upload new PDF.");
                            }
                        }
                        
                        // Check if new Thumbnail uploaded
                        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
                            $thumb_res = upload_thumbnail_image('thumbnail_file');
                            if ($thumb_res['status']) {
                                // Delete old thumbnail file if exists
                                if (!empty($thumbnail_path) && file_exists('../' . $thumbnail_path)) {
                                    @unlink('../' . $thumbnail_path);
                                }
                                $thumbnail_path = $thumb_res['filepath'];
                            }
                        }
                        
                        $stmt_upd = $pdo->prepare("UPDATE `certificates` SET 
                            `title` = :title, 
                            `category` = :category, 
                            `pdf_path` = :pdf_path, 
                            `thumbnail_path` = :thumbnail_path, 
                            `issue_authority` = :issue_authority, 
                            `certificate_number` = :certificate_number, 
                            `issue_date` = :issue_date, 
                            `expiry_date` = :expiry_date, 
                            `is_visible` = :is_visible, 
                            `is_featured` = :is_featured, 
                            `sort_order` = :sort_order 
                            WHERE `id` = :id");
                        
                        $stmt_upd->execute([
                            ':title' => $title,
                            ':category' => $category,
                            ':pdf_path' => $pdf_path,
                            ':thumbnail_path' => $thumbnail_path,
                            ':issue_authority' => $issue_authority,
                            ':certificate_number' => $certificate_number,
                            ':issue_date' => $issue_date,
                            ':expiry_date' => $expiry_date,
                            ':is_visible' => $is_visible,
                            ':is_featured' => $is_featured,
                            ':sort_order' => $sort_order,
                            ':id' => $id
                        ]);
                        
                        $success_msg = "Certificate '{$title}' updated successfully.";
                    }
                } catch (Exception $e) {
                    $error_msg = "Error updating record: " . sanitize($e->getMessage());
                }
            }
        }
        
        // 3. DELETE CERTIFICATE
        elseif ($action === 'delete_certificate') {
            $id = intval($_POST['cert_id'] ?? 0);
            
            if ($id > 0) {
                try {
                    $stmt_fetch = $pdo->prepare("SELECT * FROM `certificates` WHERE `id` = :id LIMIT 1");
                    $stmt_fetch->execute([':id' => $id]);
                    $cert = $stmt_fetch->fetch();
                    
                    if ($cert) {
                        // Delete PDF on disk
                        if (!empty($cert['pdf_path']) && file_exists('../' . $cert['pdf_path'])) {
                            @unlink('../' . $cert['pdf_path']);
                        }
                        // Delete Thumbnail on disk
                        if (!empty($cert['thumbnail_path']) && file_exists('../' . $cert['thumbnail_path'])) {
                            @unlink('../' . $cert['thumbnail_path']);
                        }
                        
                        $stmt_del = $pdo->prepare("DELETE FROM `certificates` WHERE `id` = :id");
                        $stmt_del->execute([':id' => $id]);
                        
                        $success_msg = "Certificate successfully deleted.";
                    } else {
                        $error_msg = "Certificate record not found.";
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error during deletion: " . sanitize($e->getMessage());
                }
            }
        }
    }
}

// Fetch all certificates from the database
try {
    $stmt_list = $pdo->query("SELECT * FROM `certificates` ORDER BY `category` ASC, `sort_order` ASC, `title` ASC");
    $certificates = $stmt_list->fetchAll();
} catch (PDOException $e) {
    die("Database Listing Fetch Failure: " . sanitize($e->getMessage()));
}

$token = generate_csrf_token();

// Categorization helper
$categories_meta = [
    'recognition'    => ['label' => 'Recognition & Affiliation', 'class' => 'badge-info'],
    'safety'         => ['label' => 'Safety & Compliance', 'class' => 'badge-warning'],
    'academic'       => ['label' => 'Academic Information', 'class' => 'badge-success'],
    'awards'         => ['label' => 'Awards & Accreditations', 'class' => 'badge-primary'],
    'student_safety' => ['label' => 'Student Safety', 'class' => 'badge-danger']
];
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px;">
    <div>
        <h1 style="color: #ffffff; font-family: var(--font-heading); margin: 0;">Certificates & Compliance CMS</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Upload and manage accreditation certificates, safety compliance documents, and mandatory disclosures</p>
    </div>
    <button class="btn-action btn-theme" onclick="openAddModal()">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5v14"/></svg>
        <span>Add Document</span>
    </button>
</div>

<!-- Feedback Alerts -->
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

<!-- Documents Registry Table -->
<div class="dashboard-block">
    <div class="block-title">
        <h3>Compliance Certificates (<?php echo count($certificates); ?>)</h3>
    </div>
    
    <?php if (empty($certificates)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 36px;">No certificates uploaded yet. Click "Add Document" at the top to start.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">Order</th>
                        <th>Category</th>
                        <th>Document Title</th>
                        <th>Authority & Reference</th>
                        <th>Dates (Issue / Expiry)</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Featured</th>
                        <th style="text-align: right;">Action Overrides</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificates as $cert): ?>
                        <tr>
                            <!-- 1. Sort Order -->
                            <td style="width: 80px; text-align: center;"><strong><?php echo intval($cert['sort_order']); ?></strong></td>
                            
                            <!-- 2. Category Badge -->
                            <td style="width: 180px;">
                                <span class="badge <?php echo $categories_meta[$cert['category']]['class'] ?? 'badge-outline'; ?>" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px;">
                                    <?php echo $categories_meta[$cert['category']]['label'] ?? 'Unknown'; ?>
                                </span>
                            </td>
                            
                            <!-- 3. Title & PDF Link -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                    <a href="../<?php echo $cert['pdf_path']; ?>" target="_blank" style="color: #ffffff; font-weight: 600; text-decoration: underline;" title="Open PDF in new tab">
                                        <?php echo sanitize($cert['title']); ?>
                                    </a>
                                </div>
                            </td>
                            
                            <!-- 4. Authority & Number -->
                            <td style="font-size: 0.85rem;">
                                <?php if (!empty($cert['issue_authority'])): ?>
                                    <span style="display: block; font-weight: 500;"><?php echo sanitize($cert['issue_authority']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($cert['certificate_number'])): ?>
                                    <span style="display: block; opacity: 0.6; font-size: 0.8rem;">Ref: <?php echo sanitize($cert['certificate_number']); ?></span>
                                <?php else: ?>
                                    <span style="opacity: 0.4;">No number registered</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- 5. Issue & Expiry Dates -->
                            <td style="font-size: 0.85rem; width: 160px;">
                                <span style="display: block; font-weight: 500;">Issued: <?php echo !empty($cert['issue_date']) ? date('d-M-Y', strtotime($cert['issue_date'])) : 'N/A'; ?></span>
                                <?php if (!empty($cert['expiry_date'])): 
                                    $expired = strtotime($cert['expiry_date']) < time();
                                    $exp_color = $expired ? '#ef4444' : '#10b981';
                                ?>
                                    <span style="display: block; font-size: 0.8rem; color: <?php echo $exp_color; ?>;">
                                        Expires: <?php echo date('d-M-Y', strtotime($cert['expiry_date'])); ?> <?php echo $expired ? '(Expired)' : ''; ?>
                                    </span>
                                <?php else: ?>
                                    <span style="display: block; opacity: 0.6; font-size: 0.8rem; color: #10b981;">Permanent Validity</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- 6. Visible Status -->
                            <td style="text-align: center; width: 100px;">
                                <?php if ($cert['is_visible'] == 1): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 4px; color: #10b981; font-weight: 600; font-size: 0.85rem;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span> Active
                                    </span>
                                <?php else: ?>
                                    <span style="display: inline-flex; align-items: center; gap: 4px; color: var(--text-muted); font-size: 0.85rem;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #64748b; display: inline-block;"></span> Hidden
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- 7. Featured Status -->
                            <td style="text-align: center; width: 100px;">
                                <?php if ($cert['is_featured'] == 1): ?>
                                    <span class="badge badge-primary" style="font-size: 0.7rem; padding: 2px 6px; border-radius: 3px;">Featured</span>
                                <?php else: ?>
                                    <span style="opacity: 0.3; font-size: 0.85rem;">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- 8. Actions Override -->
                            <td style="text-align: right; width: 180px;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <button class="btn-action btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($cert)); ?>)">Edit</button>
                                    
                                    <form action="certificates.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this compliance certificate? This cannot be undone.');" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_certificate">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="cert_id" value="<?php echo $cert['id']; ?>">
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

<!-- ==========================================
   ADD DOCUMENT MODAL OVERLAY
   ========================================== -->
<div id="add-cert-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; overflow-y: auto; padding: 40px 16px; box-sizing: border-box; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
    <div class="dashboard-block" style="width: 100%; max-width: 650px; margin: 0 auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
        <div class="block-title">
            <h3>Add Accreditation & Compliance Certificate</h3>
            <button onclick="closeAddModal()" class="btn-action btn-outline" style="padding: 4px 10px; border-radius: var(--border-radius-circle); font-size: 0.8rem;">X</button>
        </div>
        <form action="certificates.php" method="POST" enctype="multipart/form-data" autocomplete="off" class="no-float-form">
            <input type="hidden" name="action" value="add_certificate">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            
            <div class="form-group">
                <label for="add_title">Certificate Title / Name</label>
                <input type="text" name="title" id="add_title" class="form-control" placeholder="e.g. CBSE Board Affiliation Extension Certificate" required>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="add_category">Document Category Selection</label>
                    <select name="category" id="add_category" class="form-control" required>
                        <option value="">-- Choose Category --</option>
                        <option value="recognition">Recognition & Affiliation</option>
                        <option value="safety">Safety & Compliance</option>
                        <option value="academic">Academic Information</option>
                        <option value="awards">Awards & Accreditations</option>
                        <option value="student_safety">Student Safety & Policies</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="add_sort_order">Sort Display Order</label>
                    <input type="number" name="sort_order" id="add_sort_order" class="form-control" value="0" min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="add_pdf">Accreditation PDF Document (Required, Max 15MB)</label>
                <div class="file-upload-wrapper" style="padding: 16px;">
                    <input type="file" name="pdf_file" id="add_pdf" class="file-upload-input" accept="application/pdf" required onchange="handleFileSelectLabel(this)">
                    <div class="file-upload-info" style="font-size: 0.85rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <span class="upload-label-text">Select CBSE/Accreditation PDF File</span>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="add_thumbnail">Certificate Thumbnail Preview Graphic (Optional)</label>
                <div class="file-upload-wrapper" style="padding: 16px;">
                    <input type="file" name="thumbnail_file" id="add_thumbnail" class="file-upload-input" accept="image/*" onchange="handleFileSelectLabel(this)">
                    <div class="file-upload-info" style="font-size: 0.85rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        <span class="upload-label-text">Upload Preview Image Graphic (JPG/PNG/WEBP)</span>
                    </div>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="add_authority">Issuing Authority / Body</label>
                    <input type="text" name="issue_authority" id="add_authority" class="form-control" placeholder="e.g. State Fire Department">
                </div>
                <div class="form-group">
                    <label for="add_number">Certificate ID / Reference Number</label>
                    <input type="text" name="certificate_number" id="add_number" class="form-control" placeholder="e.g. FD/SAFE-990-2026">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="add_issue_date">Issue Date</label>
                    <input type="date" name="issue_date" id="add_issue_date" class="form-control">
                </div>
                <div class="form-group">
                    <label for="add_expiry_date">Expiry Date (Leave blank if permanent)</label>
                    <input type="date" name="expiry_date" id="add_expiry_date" class="form-control">
                </div>
            </div>
            
            <div style="display: flex; gap: 24px; margin: 16px 0;">
                <label style="display: flex; align-items: center; gap: 8px; color: #ffffff; cursor: pointer;">
                    <input type="checkbox" name="is_visible" value="1" checked style="width: 18px; height: 18px;">
                    <span>Visible in Navigation (Show)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; color: #ffffff; cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1" style="width: 18px; height: 18px;">
                    <span>Featured Badge (Top of menu)</span>
                </label>
            </div>
            
            <div class="modal-actions-row">
                <button type="button" onclick="closeAddModal()" class="btn-action btn-outline">Cancel</button>
                <button type="submit" class="btn-action btn-theme">Register & Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
   EDIT DOCUMENT MODAL OVERLAY
   ========================================== -->
<div id="edit-cert-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; overflow-y: auto; padding: 40px 16px; box-sizing: border-box; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
    <div class="dashboard-block" style="width: 100%; max-width: 650px; margin: 0 auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
        <div class="block-title">
            <h3>Edit Accreditation & Compliance Certificate</h3>
            <button onclick="closeEditModal()" class="btn-action btn-outline" style="padding: 4px 10px; border-radius: var(--border-radius-circle); font-size: 0.8rem;">X</button>
        </div>
        <form action="certificates.php" method="POST" enctype="multipart/form-data" autocomplete="off" class="no-float-form">
            <input type="hidden" name="action" value="edit_certificate">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            <input type="hidden" name="cert_id" id="edit_id">
            
            <div class="form-group">
                <label for="edit_title">Certificate Title / Name</label>
                <input type="text" name="title" id="edit_title" class="form-control" required>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="edit_category">Document Category Selection</label>
                    <select name="category" id="edit_category" class="form-control" required>
                        <option value="recognition">Recognition & Affiliation</option>
                        <option value="safety">Safety & Compliance</option>
                        <option value="academic">Academic Information</option>
                        <option value="awards">Awards & Accreditations</option>
                        <option value="student_safety">Student Safety & Policies</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_sort_order">Sort Display Order</label>
                    <input type="number" name="sort_order" id="edit_sort_order" class="form-control" min="0">
                </div>
            </div>
            
            <div style="display: flex; gap: 16px; margin: 12px 0; align-items: center; background: rgba(255,255,255,0.02); padding: 12px; border-radius: 6px; border: 1px solid var(--glass-border);">
                <div style="font-size: 0.8rem; background: rgba(239, 68, 68, 0.15); color: #f87171; padding: 4px 8px; border-radius: 4px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Current PDF</div>
                <div id="edit_pdf_link_container" style="font-size: 0.9rem; font-weight: 500; color: #ffffff; text-decoration: underline; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></div>
            </div>
            
            <div class="form-group">
                <label for="edit_pdf">Replace PDF Document (Optional, Max 15MB)</label>
                <div class="file-upload-wrapper" style="padding: 16px;">
                    <input type="file" name="pdf_file" id="edit_pdf" class="file-upload-input" accept="application/pdf" onchange="handleFileSelectLabel(this)">
                    <div class="file-upload-info" style="font-size: 0.85rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <span class="upload-label-text">Select new PDF document file to overwrite</span>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Current Thumbnail Preview Graphic</label>
                <div class="modal-image-row" style="margin-bottom: 12px;">
                    <div id="edit_thumbnail_preview" style="width: 80px; height: 60px; border-radius: 4px; overflow: hidden; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <!-- JS Loaded thumbnail preview -->
                    </div>
                    <div class="file-upload-wrapper" style="padding: 12px; flex-grow: 1;">
                        <input type="file" name="thumbnail_file" id="edit_thumbnail" class="file-upload-input" accept="image/*" onchange="handleFileSelectLabel(this)">
                        <div class="file-upload-info" style="font-size: 0.8rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <span class="upload-label-text">Replace preview graphic (JPG/PNG/WEBP)</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="edit_authority">Issuing Authority / Body</label>
                    <input type="text" name="issue_authority" id="edit_authority" class="form-control">
                </div>
                <div class="form-group">
                    <label for="edit_number">Certificate ID / Reference Number</label>
                    <input type="text" name="certificate_number" id="edit_number" class="form-control">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="edit_issue_date">Issue Date</label>
                    <input type="date" name="issue_date" id="edit_issue_date" class="form-control">
                </div>
                <div class="form-group">
                    <label for="edit_expiry_date">Expiry Date (Leave blank if permanent)</label>
                    <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control">
                </div>
            </div>
            
            <div style="display: flex; gap: 24px; margin: 16px 0;">
                <label style="display: flex; align-items: center; gap: 8px; color: #ffffff; cursor: pointer;">
                    <input type="checkbox" name="is_visible" id="edit_visible" value="1" style="width: 18px; height: 18px;">
                    <span>Visible in Navigation (Show)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; color: #ffffff; cursor: pointer;">
                    <input type="checkbox" name="is_featured" id="edit_featured" value="1" style="width: 18px; height: 18px;">
                    <span>Featured Badge (Top of menu)</span>
                </label>
            </div>
            
            <div class="modal-actions-row">
                <button type="button" onclick="closeEditModal()" class="btn-action btn-outline">Cancel</button>
                <button type="submit" class="btn-action btn-theme">Save Modifications</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Controls
function openAddModal() {
    document.getElementById('add-cert-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeAddModal() {
    document.getElementById('add-cert-modal').style.display = 'none';
    document.body.style.overflow = '';
}

function openEditModal(cert) {
    document.getElementById('edit_id').value = cert.id;
    document.getElementById('edit_title').value = cert.title;
    document.getElementById('edit_category').value = cert.category;
    document.getElementById('edit_sort_order').value = cert.sort_order;
    document.getElementById('edit_authority').value = cert.issue_authority || '';
    document.getElementById('edit_number').value = cert.certificate_number || '';
    document.getElementById('edit_issue_date').value = cert.issue_date || '';
    document.getElementById('edit_expiry_date').value = cert.expiry_date || '';
    document.getElementById('edit_visible').checked = (cert.is_visible == 1);
    document.getElementById('edit_featured').checked = (cert.is_featured == 1);
    
    // Display current PDF name
    const pdfBasename = cert.pdf_path.split('/').pop();
    document.getElementById('edit_pdf_link_container').innerHTML = `<a href="../${cert.pdf_path}" target="_blank" style="color: #6366f1; text-decoration: underline;">${pdfBasename}</a>`;
    
    // Display thumbnail preview
    const thumbContainer = document.getElementById('edit_thumbnail_preview');
    if (cert.thumbnail_path) {
        thumbContainer.innerHTML = `<img src="../${cert.thumbnail_path}" style="width: 100%; height: 100%; object-fit: cover;">`;
    } else {
        thumbContainer.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.4;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`;
    }
    
    document.getElementById('edit-cert-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('edit-cert-modal').style.display = 'none';
    document.body.style.overflow = '';
}

// Display selected file name in custom uploader label
function handleFileSelectLabel(input) {
    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        const labelText = file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + 'MB)';
        const wrapper = input.closest('.file-upload-wrapper');
        const textElement = wrapper.querySelector('.upload-label-text');
        if (textElement) {
            textElement.textContent = labelText;
            textElement.style.color = '#38bdf8'; // Blue highlight color on selection
        }
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>
