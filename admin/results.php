<?php
/**
 * ========================================================
 * STUDENT RESULTS PORTAL CMS MANAGER MODULE (GURUKUL)
 * ========================================================
 */

require_once '../config/db.php';
require_once 'includes/header.php';

$success_msg = "";
$error_msg = "";

// 1. Handle POST Actions (Add record, Edit record, Delete record, Toggle featured)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['action'];
        
        // A. ADD ACADEMIC RESULT RECORD
        if ($action === 'add_result') {
            $student_name = trim($_POST['student_name'] ?? '');
            $roll_no = trim($_POST['roll_no'] ?? '');
            $exam_category = trim($_POST['exam_category'] ?? '');
            $academic_year = trim($_POST['academic_year'] ?? '');
            $score_metric = trim($_POST['score_metric'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            if (empty($student_name) || empty($roll_no) || empty($exam_category) || empty($academic_year) || empty($score_metric)) {
                $error_msg = "All scorecard fields are required.";
            } elseif (!isset($_FILES['result_pdf']) || $_FILES['result_pdf']['error'] !== UPLOAD_ERR_OK) {
                $error_msg = "A verified results PDF document must be uploaded.";
            } else {
                $file_tmp = $_FILES['result_pdf']['tmp_name'];
                $file_name = sanitize($_FILES['result_pdf']['name']);
                $file_size = $_FILES['result_pdf']['size'];
                $file_type = $_FILES['result_pdf']['type'];
                
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                if ($ext !== 'pdf') {
                    $error_msg = "Invalid file type. Only secure PDF scorecard sheets are allowed.";
                } elseif ($file_size > 10 * 1024 * 1024) { // 10MB max limit
                    $error_msg = "PDF size exceeds the maximum limit of 10MB.";
                } else {
                    $upload_dir = '../uploads/results/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $new_filename = uniqid('result_', true) . '.pdf';
                    $target_path = $upload_dir . $new_filename;
                    $db_filepath = 'uploads/results/' . $new_filename;
                    
                    if (move_uploaded_file($file_tmp, $target_path)) {
                        try {
                            // Insert into `results` table
                            $ins_stmt = $pdo->prepare("INSERT INTO `results` (`student_name`, `roll_no`, `exam_category`, `academic_year`, `score_metric`, `pdf_path`, `is_featured`) VALUES (:name, :roll, :exam, :year, :score, :pdf, :is_feat)");
                            $ins_stmt->execute([
                                ':name' => $student_name,
                                ':roll' => $roll_no,
                                ':exam' => $exam_category,
                                ':year' => $academic_year,
                                ':score' => $score_metric,
                                ':pdf' => $db_filepath,
                                ':is_feat' => $is_featured
                            ]);
                            
                            // Register in centralized Media table
                            $stmt_media = $pdo->prepare("INSERT INTO `media` (`filename`, `filepath`, `filetype`, `filesize`) VALUES (:filename, :filepath, :filetype, :filesize)");
                            $stmt_media->execute([
                                ':filename' => $file_name,
                                ':filepath' => $db_filepath,
                                ':filetype' => $file_type,
                                ':filesize' => $file_size
                            ]);
                            
                            $success_msg = "Academic result scorecard for '{$student_name}' uploaded successfully.";
                        } catch (PDOException $e) {
                            $error_msg = "Database upload failure: " . sanitize($e->getMessage());
                        }
                    } else {
                        $error_msg = "Failed to save uploaded PDF file.";
                    }
                }
            }
        }
        
        // B. EDIT ACADEMIC RESULT RECORD (TITLE/DETAILS ONLY)
        if ($action === 'edit_result') {
            $id = intval($_POST['result_id'] ?? 0);
            $student_name = trim($_POST['student_name'] ?? '');
            $roll_no = trim($_POST['roll_no'] ?? '');
            $exam_category = trim($_POST['exam_category'] ?? '');
            $academic_year = trim($_POST['academic_year'] ?? '');
            $score_metric = trim($_POST['score_metric'] ?? '');
            
            if ($id > 0 && !empty($student_name) && !empty($roll_no) && !empty($exam_category) && !empty($academic_year) && !empty($score_metric)) {
                try {
                    $edit_stmt = $pdo->prepare("UPDATE `results` SET `student_name` = :name, `roll_no` = :roll, `exam_category` = :exam, `academic_year` = :year, `score_metric` = :score WHERE `id` = :id");
                    $edit_stmt->execute([
                        ':name' => $student_name,
                        ':roll' => $roll_no,
                        ':exam' => $exam_category,
                        ':year' => $academic_year,
                        ':score' => $score_metric,
                        ':id' => $id
                    ]);
                    $success_msg = "Student results scorecard updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database update failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // C. DELETE ACADEMIC RESULT
        if ($action === 'delete_result') {
            $id = intval($_POST['result_id'] ?? 0);
            if ($id > 0) {
                try {
                    // Fetch PDF file path first to delete it physically on disk
                    $path_stmt = $pdo->prepare("SELECT `pdf_path` FROM `results` WHERE `id` = :id");
                    $path_stmt->execute([':id' => $id]);
                    $pdf_path = $path_stmt->fetchColumn();
                    
                    if ($pdf_path && strpos($pdf_path, 'uploads/') !== false) {
                        $full_path = '../' . $pdf_path;
                        if (file_exists($full_path)) {
                            unlink($full_path);
                        }
                    }
                    
                    $del_stmt = $pdo->prepare("DELETE FROM `results` WHERE `id` = :id");
                    $del_stmt->execute([':id' => $id]);
                    $success_msg = "Results scorecard removed successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database delete failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // D. TOGGLE FEATURED STATS
        if ($action === 'toggle_featured') {
            $id = intval($_POST['asset_id'] ?? 0);
            $state = intval($_POST['featured_state'] ?? 0);
            if ($id > 0) {
                try {
                    $feat_stmt = $pdo->prepare("UPDATE `results` SET `is_featured` = :state WHERE `id` = :id");
                    $feat_stmt->execute([':state' => $state, ':id' => $id]);
                    $success_msg = "Featured results updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Featured toggle failure: " . sanitize($e->getMessage());
                }
            }
        }
    }
}

// 2. Fetch all student results scorecards
try {
    $stmt_results = $pdo->query("SELECT * FROM `results` ORDER BY `created_at` DESC");
    $result_records = $stmt_results->fetchAll();
} catch (PDOException $e) {
    die("Database Scorecard Fetch Failure: " . sanitize($e->getMessage()));
}

$token = generate_csrf_token();
?>

<!-- Tab Selector Navigation -->
<div style="display: flex; gap: 16px; margin-bottom: 28px; border-bottom: 1px solid var(--glass-border); padding-bottom: 1px;">
    <button class="btn-action <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'directory') ? 'btn-theme' : 'btn-outline'; ?>" onclick="window.location.href='results.php?tab=directory'" style="border-radius: 4px 4px 0 0; padding: 12px 24px; font-size: 0.95rem;">Registry Directory (<?php echo count($result_records); ?>)</button>
    <button class="btn-action <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'add') ? 'btn-theme' : 'btn-outline'; ?>" onclick="window.location.href='results.php?tab=add'" style="border-radius: 4px 4px 0 0; padding: 12px 24px; font-size: 0.95rem;">Add New Record</button>
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

<!-- TAB 1: REGISTRY DIRECTORY -->
<?php if (!isset($_GET['tab']) || $_GET['tab'] === 'directory'): ?>
    <div class="dashboard-block" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
            <h3 style="color: #ffffff; font-family: var(--font-heading); font-size: 1.25rem;">Academic Results Registry</h3>
            
            <div style="display: flex; gap: 12px; flex-wrap: wrap; flex-grow: 1; justify-content: flex-end;">
                <input type="text" id="result-search" class="form-control" placeholder="Search by name or roll..." style="max-width: 250px; height: 38px;">
                <select id="result-filter" class="form-control" style="max-width: 200px; height: 38px;">
                    <option value="">All Examinations</option>
                    <option value="Class XII CBSE Board">Class XII CBSE Board</option>
                    <option value="Class X CBSE Board">Class X CBSE Board</option>
                    <option value="IIT-JEE Advanced">IIT-JEE Advanced</option>
                    <option value="NEET Medical Entrance">NEET Medical Entrance</option>
                </select>
            </div>
        </div>
        
        <?php if (empty($result_records)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 36px;">No result scorecards cataloged yet. Click the upload tab to populate toppers.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table" id="results-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Roll Number</th>
                            <th>Exam Category</th>
                            <th>Academic Year</th>
                            <th>Score Metric</th>
                            <th>Featured Status</th>
                            <th style="text-align: right;">Action Overrides</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result_records as $record): ?>
                            <tr class="result-row" data-name="<?php echo strtolower(sanitize($record['student_name'])); ?>" data-roll="<?php echo strtolower(sanitize($record['roll_no'])); ?>" data-category="<?php echo sanitize($record['exam_category']); ?>">
                                <td><strong><?php echo sanitize($record['student_name']); ?></strong></td>
                                <td><code><?php echo sanitize($record['roll_no']); ?></code></td>
                                <td><span class="badge-status badge-info"><?php echo sanitize($record['exam_category']); ?></span></td>
                                <td style="text-align: center;"><strong><?php echo sanitize($record['academic_year']); ?></strong></td>
                                <td><strong style="color: var(--accent-light);"><?php echo sanitize($record['score_metric']); ?></strong></td>
                                <td>
                                    <form action="results.php?tab=directory" method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_featured">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="asset_id" value="<?php echo $record['id']; ?>">
                                        
                                        <?php if ($record['is_featured'] == 1): ?>
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
                                        <button class="btn-action btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;" onclick="openEditResultModal(<?php echo htmlspecialchars(json_encode($record)); ?>)">Edit</button>
                                        
                                        <form action="results.php?tab=directory" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this student scorecard?');" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_result">
                                            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                            <input type="hidden" name="result_id" value="<?php echo $record['id']; ?>">
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

    <!-- Edit Student Details Modal Overlay -->
    <div id="edit-result-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(12px);">
        <div class="dashboard-block" style="width: 90%; max-width: 600px; margin-bottom: 0;">
            <div class="block-title">
                <h3>Edit Student Scorecard</h3>
                <button onclick="closeEditResultModal()" class="btn-action btn-outline" style="padding: 4px 10px; border-radius: var(--border-radius-circle); font-size: 0.8rem;">X</button>
            </div>
            <form action="results.php?tab=directory" method="POST" autocomplete="off">
                <input type="hidden" name="action" value="edit_result">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <input type="hidden" name="result_id" id="edit_result_id" value="">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_student_name">Student Name</label>
                        <input type="text" name="student_name" id="edit_student_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_roll_no">Roll Number</label>
                        <input type="text" name="roll_no" id="edit_roll_no" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_exam_category">Exam Category</label>
                        <select name="exam_category" id="edit_exam_category" class="form-control" required>
                            <option value="Class XII CBSE Board">Class XII CBSE Board</option>
                            <option value="Class X CBSE Board">Class X CBSE Board</option>
                            <option value="IIT-JEE Advanced">IIT-JEE Advanced</option>
                            <option value="NEET Medical Entrance">NEET Medical Entrance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_academic_year">Academic Year</label>
                        <input type="text" name="academic_year" id="edit_academic_year" class="form-control" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_score_metric">Score / Rank Metric</label>
                        <input type="text" name="score_metric" id="edit_score_metric" class="form-control" required>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
                    <button type="button" class="btn-action btn-outline" onclick="closeEditResultModal()">Cancel</button>
                    <button type="submit" class="btn-action btn-theme">Save Scorecard Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Live client scorecard search & filter scripting
        const searchInput = document.getElementById('result-search');
        const filterSelect = document.getElementById('result-filter');
        const rows = document.querySelectorAll('.result-row');
        
        const filterGrid = () => {
            const query = searchInput.value.toLowerCase().trim();
            const filter = filterSelect.value;
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const roll = row.getAttribute('data-roll');
                const category = row.getAttribute('data-category');
                
                const queryMatch = name.includes(query) || roll.includes(query);
                const categoryMatch = !filter || category === filter;
                
                row.style.display = (queryMatch && categoryMatch) ? '' : 'none';
            });
        };
        
        if (searchInput && filterSelect) {
            searchInput.addEventListener('input', filterGrid);
            filterSelect.addEventListener('change', filterGrid);
        }
        
        function openEditResultModal(record) {
            document.getElementById('edit_result_id').value = record.id;
            document.getElementById('edit_student_name').value = record.student_name;
            document.getElementById('edit_roll_no').value = record.roll_no;
            document.getElementById('edit_exam_category').value = record.exam_category;
            document.getElementById('edit_academic_year').value = record.academic_year;
            document.getElementById('edit_score_metric').value = record.score_metric;
            document.getElementById('edit-result-modal').style.display = 'flex';
        }
        function closeEditResultModal() {
            document.getElementById('edit-result-modal').style.display = 'none';
        }
    </script>

<!-- TAB 2: ADD NEW RECORD FORM -->
<?php elseif ($_GET['tab'] === 'add'): ?>
    <div class="dashboard-block" style="max-width: 750px; margin: 0 auto 36px;">
        <div class="block-title">
            <h3>Record New Scorecard & Topper</h3>
        </div>
        
        <form action="results.php?tab=directory" method="POST" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="action" value="add_result">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="student_name">Student Full Name</label>
                    <input type="text" name="student_name" id="student_name" class="form-control" placeholder="Enter student name" required>
                </div>
                
                <div class="form-group">
                    <label for="roll_no">Student Roll Number</label>
                    <input type="text" name="roll_no" id="roll_no" class="form-control" placeholder="Enter roll number" required>
                </div>
                
                <div class="form-group">
                    <label for="exam_category">Examination / Grade category</label>
                    <select name="exam_category" id="exam_category" class="form-control" required>
                        <option value="">-- Select Exam Category --</option>
                        <option value="Class XII CBSE Board">Class XII CBSE Board</option>
                        <option value="Class X CBSE Board">Class X CBSE Board</option>
                        <option value="IIT-JEE Advanced">IIT-JEE Advanced</option>
                        <option value="NEET Medical Entrance">NEET Medical Entrance</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="academic_year">Academic Year</label>
                    <input type="text" name="academic_year" id="academic_year" class="form-control" placeholder="e.g. 2025" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="score_metric">Performance Metric (Score / Percentile / Rank)</label>
                    <input type="text" name="score_metric" id="score_metric" class="form-control" placeholder="e.g. 98.4% Aggregate / All India Rank 14" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Verified Result PDF Scorecard Sheet</label>
                <div class="file-upload-wrapper" style="padding: 24px;">
                    <input type="file" name="result_pdf" class="file-upload-input" accept="application/pdf" required onchange="updatePDFLabel(this)">
                    <div class="file-upload-info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--secondary-light);"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        <strong id="pdf-uploader-label" style="font-size: 0.95rem; color: #ffffff;">Drag & Drop or Click to choose PDF sheet</strong>
                        <span>Strictly allowed: PDF (Max size: 10MB)</span>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; text-transform: none; font-size: 0.9rem; font-weight: 500; color: #ffffff;">
                    <input type="checkbox" name="is_featured" value="1" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--secondary-light);">
                    <span>Mark as <strong>Featured Topper ★</strong> (Displays on Top Honors dashboard)</span>
                </label>
            </div>
            
            <button type="submit" class="btn-action btn-theme" style="width: 100%; height: 48px; margin-top: 10px;">
                <span>Publish Result Scorecard</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            </button>
        </form>
    </div>
    
    <script>
        function updatePDFLabel(input) {
            const label = document.getElementById('pdf-uploader-label');
            if(input.files.length > 0) {
                label.textContent = input.files[0].name;
                label.style.color = 'var(--secondary-light)';
            } else {
                label.textContent = 'Drag & Drop or Click to choose PDF sheet';
                label.style.color = '#ffffff';
            }
        }
    </script>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>
