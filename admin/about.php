<?php
/**
 * ========================================================
 * ABOUT US CONTENT, LEADERSHIP & TIMELINE CMS MODULE (GURUKUL)
 * ========================================================
 */

require_once '../config/db.php';
require_once 'includes/header.php';

$success_msg = "";
$error_msg = "";

/**
 * Reusable Image Upload Helper Integration
 */
function upload_image_asset($file_key, $prefix = 'about_') {
    global $pdo;
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'error' => null];
    }
    
    $file_tmp = $_FILES[$file_key]['tmp_name'];
    $file_name = sanitize($_FILES[$file_key]['name']);
    $file_size = $_FILES[$file_key]['size'];
    $file_type = $_FILES[$file_key]['type'];
    
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
    
    if (!in_array($ext, $allowed_exts)) {
        return ['status' => false, 'error' => "Invalid file type. Only JPG, PNG, WEBP, and SVG formats allowed."];
    }
    if ($file_size > 5 * 1024 * 1024) {
        return ['status' => false, 'error' => "File size exceeds the maximum limit of 5MB."];
    }
    
    $upload_dir = '../uploads/media/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $new_filename = uniqid($prefix, true) . '.' . $ext;
    $target_path = $upload_dir . $new_filename;
    $db_filepath = 'uploads/media/' . $new_filename;
    
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
    
    return ['status' => false, 'error' => "Failed to save uploaded image."];
}

/**
 * Reusable Faculty Image Upload Helper (uploads to uploads/faculty/)
 */
function upload_faculty_photo($file_key, $prefix = 'faculty_') {
    global $pdo;
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'error' => null];
    }
    
    $file_tmp = $_FILES[$file_key]['tmp_name'];
    $file_name = sanitize($_FILES[$file_key]['name']);
    $file_size = $_FILES[$file_key]['size'];
    $file_type = $_FILES[$file_key]['type'];
    
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
    
    if (!in_array($ext, $allowed_exts)) {
        return ['status' => false, 'error' => "Invalid file type. Only JPG, PNG, WEBP, and SVG formats allowed."];
    }
    if ($file_size > 5 * 1024 * 1024) {
        return ['status' => false, 'error' => "File size exceeds the maximum limit of 5MB."];
    }
    
    $upload_dir = '../uploads/faculty/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $new_filename = uniqid($prefix, true) . '.' . $ext;
    $target_path = $upload_dir . $new_filename;
    $db_filepath = 'uploads/faculty/' . $new_filename;
    
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
    
    return ['status' => false, 'error' => "Failed to save uploaded image."];
}

// 1. Fetch current About Us general settings (Row id = 1)
try {
    $stmt = $pdo->prepare("SELECT * FROM `about_content` WHERE `id` = 1 LIMIT 1");
    $stmt->execute();
    $about = $stmt->fetch();
    
    if (!$about) {
        $pdo->query("INSERT INTO `about_content` (`id`) VALUES (1)");
        $stmt->execute();
        $about = $stmt->fetch();
    }
} catch (PDOException $e) {
    die("Database General Fetch Failure: " . sanitize($e->getMessage()));
}

// 2. Handle POST General Settings Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_general') {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        $intro_heading = trim($_POST['intro_heading'] ?? '');
        $intro_desc_1 = trim($_POST['intro_desc_1'] ?? '');
        $intro_desc_2 = trim($_POST['intro_desc_2'] ?? '');
        
        $vision_title = trim($_POST['vision_title'] ?? '');
        $vision_desc = trim($_POST['vision_desc'] ?? '');
        $mission_title = trim($_POST['mission_title'] ?? '');
        $mission_desc = trim($_POST['mission_desc'] ?? '');
        $philosophy_title = trim($_POST['philosophy_title'] ?? '');
        $philosophy_desc = trim($_POST['philosophy_desc'] ?? '');
        
        $achievement_1_title = trim($_POST['achievement_1_title'] ?? '');
        $achievement_1_metric = trim($_POST['achievement_1_metric'] ?? '');
        $achievement_1_desc = trim($_POST['achievement_1_desc'] ?? '');
        $achievement_2_title = trim($_POST['achievement_2_title'] ?? '');
        $achievement_2_metric = trim($_POST['achievement_2_metric'] ?? '');
        $achievement_2_desc = trim($_POST['achievement_2_desc'] ?? '');
        $achievement_3_title = trim($_POST['achievement_3_title'] ?? '');
        $achievement_3_metric = trim($_POST['achievement_3_metric'] ?? '');
        $achievement_3_desc = trim($_POST['achievement_3_desc'] ?? '');
        
        $show_intro = isset($_POST['show_intro']) ? 1 : 0;
        $show_vision_mission = isset($_POST['show_vision_mission']) ? 1 : 0;
        $show_leadership = isset($_POST['show_leadership']) ? 1 : 0;
        $show_achievements = isset($_POST['show_achievements']) ? 1 : 0;
        $show_timeline = isset($_POST['show_timeline']) ? 1 : 0;
        $show_cta = isset($_POST['show_cta']) ? 1 : 0;
        $show_faculty = isset($_POST['show_faculty']) ? 1 : 0;
        
        $intro_image_path = $about['intro_image_path'];
        
        // Handle file uploader
        $upload = upload_image_asset('intro_image', 'about_intro_');
        if ($upload['status']) {
            $intro_image_path = $upload['filepath'];
        } elseif ($upload['error']) {
            $error_msg = $upload['error'];
        }
        
        if (empty($error_msg)) {
            try {
                $update_stmt = $pdo->prepare("UPDATE `about_content` SET
                    `intro_heading` = :intro_heading,
                    `intro_desc_1` = :intro_desc_1,
                    `intro_desc_2` = :intro_desc_2,
                    `intro_image_path` = :intro_image_path,
                    
                    `vision_title` = :vision_title,
                    `vision_desc` = :vision_desc,
                    `mission_title` = :mission_title,
                    `mission_desc` = :mission_desc,
                    `philosophy_title` = :philosophy_title,
                    `philosophy_desc` = :philosophy_desc,
                    
                    `achievement_1_title` = :achievement_1_title,
                    `achievement_1_metric` = :achievement_1_metric,
                    `achievement_1_desc` = :achievement_1_desc,
                    `achievement_2_title` = :achievement_2_title,
                    `achievement_2_metric` = :achievement_2_metric,
                    `achievement_2_desc` = :achievement_2_desc,
                    `achievement_3_title` = :achievement_3_title,
                    `achievement_3_metric` = :achievement_3_metric,
                    `achievement_3_desc` = :achievement_3_desc,
                    
                    `show_intro` = :show_intro,
                    `show_vision_mission` = :show_vision_mission,
                    `show_leadership` = :show_leadership,
                    `show_achievements` = :show_achievements,
                    `show_timeline` = :show_timeline,
                    `show_cta` = :show_cta,
                    `show_faculty` = :show_faculty
                    WHERE `id` = 1");
                
                $update_stmt->execute([
                    ':intro_heading' => $intro_heading,
                    ':intro_desc_1' => $intro_desc_1,
                    ':intro_desc_2' => $intro_desc_2,
                    ':intro_image_path' => $intro_image_path,
                    ':vision_title' => $vision_title,
                    ':vision_desc' => $vision_desc,
                    ':mission_title' => $mission_title,
                    ':mission_desc' => $mission_desc,
                    ':philosophy_title' => $philosophy_title,
                    ':philosophy_desc' => $philosophy_desc,
                    ':achievement_1_title' => $achievement_1_title,
                    ':achievement_1_metric' => $achievement_1_metric,
                    ':achievement_1_desc' => $achievement_1_desc,
                    ':achievement_2_title' => $achievement_2_title,
                    ':achievement_2_metric' => $achievement_2_metric,
                    ':achievement_2_desc' => $achievement_2_desc,
                    ':achievement_3_title' => $achievement_3_title,
                    ':achievement_3_metric' => $achievement_3_metric,
                    ':achievement_3_desc' => $achievement_3_desc,
                    ':show_intro' => $show_intro,
                    ':show_vision_mission' => $show_vision_mission,
                    ':show_leadership' => $show_leadership,
                    ':show_achievements' => $show_achievements,
                    ':show_timeline' => $show_timeline,
                    ':show_cta' => $show_cta,
                    ':show_faculty' => $show_faculty
                ]);
                
                // Refresh local representation
                $about['intro_heading'] = $intro_heading;
                $about['intro_desc_1'] = $intro_desc_1;
                $about['intro_desc_2'] = $intro_desc_2;
                $about['intro_image_path'] = $intro_image_path;
                $about['vision_title'] = $vision_title;
                $about['vision_desc'] = $vision_desc;
                $about['mission_title'] = $mission_title;
                $about['mission_desc'] = $mission_desc;
                $about['philosophy_title'] = $philosophy_title;
                $about['philosophy_desc'] = $philosophy_desc;
                $about['achievement_1_title'] = $achievement_1_title;
                $about['achievement_1_metric'] = $achievement_1_metric;
                $about['achievement_1_desc'] = $achievement_1_desc;
                $about['achievement_2_title'] = $achievement_2_title;
                $about['achievement_2_metric'] = $achievement_2_metric;
                $about['achievement_2_desc'] = $achievement_2_desc;
                $about['achievement_3_title'] = $achievement_3_title;
                $about['achievement_3_metric'] = $achievement_3_metric;
                $about['achievement_3_desc'] = $achievement_3_desc;
                 $about['show_intro'] = $show_intro;
                $about['show_vision_mission'] = $show_vision_mission;
                $about['show_leadership'] = $show_leadership;
                $about['show_achievements'] = $show_achievements;
                $about['show_timeline'] = $show_timeline;
                $about['show_cta'] = $show_cta;
                $about['show_faculty'] = $show_faculty;
                
                $success_msg = "About Us general content sections updated successfully.";
            } catch (PDOException $e) {
                $error_msg = "Database general update failure: " . sanitize($e->getMessage());
            }
        }
    }
}

// 3. Handle POST Timeline Milestones Submit Actions (ADD, EDIT, DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['add_milestone', 'edit_milestone', 'delete_milestone'])) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['action'];
        
        // A. Add timeline milestone
        if ($action === 'add_milestone') {
            $year = trim($_POST['milestone_year'] ?? '');
            $title = trim($_POST['milestone_title'] ?? '');
            $desc = trim($_POST['milestone_desc'] ?? '');
            $sort = intval($_POST['sort_order'] ?? 0);
            $image_path = null;
            
            // Handle optional timeline uploader
            $upload = upload_image_asset('milestone_image', 'about_timeline_');
            if ($upload['status']) {
                $image_path = $upload['filepath'];
            } elseif ($upload['error']) {
                $error_msg = $upload['error'];
            }
            
            if (empty($year) || empty($title) || empty($desc)) {
                $error_msg = "Please fill in all milestone columns.";
            } elseif (empty($error_msg)) {
                try {
                    $add_stmt = $pdo->prepare("INSERT INTO `about_timeline` (`milestone_year`, `milestone_title`, `milestone_desc`, `sort_order`, `image_path`) VALUES (:year, :title, :desc, :sort, :image_path)");
                    $add_stmt->execute([
                        ':year' => $year,
                        ':title' => $title,
                        ':desc' => $desc,
                        ':sort' => $sort,
                        ':image_path' => $image_path
                    ]);
                    $success_msg = "New timeline milestone added successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Timeline insertion failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // B. Edit timeline milestone
        if ($action === 'edit_milestone') {
            $id = intval($_POST['milestone_id'] ?? 0);
            $year = trim($_POST['milestone_year'] ?? '');
            $title = trim($_POST['milestone_title'] ?? '');
            $desc = trim($_POST['milestone_desc'] ?? '');
            $sort = intval($_POST['sort_order'] ?? 0);
            $image_path = $_POST['current_image_path'] ?? null;
            
            // Handle optional replacement uploader
            $upload = upload_image_asset('milestone_image', 'about_timeline_');
            if ($upload['status']) {
                $image_path = $upload['filepath'];
            } elseif ($upload['error']) {
                $error_msg = $upload['error'];
            }
            
            if (empty($year) || empty($title) || empty($desc) || $id === 0) {
                $error_msg = "Please fill in all milestone columns.";
            } elseif (empty($error_msg)) {
                try {
                    $edit_stmt = $pdo->prepare("UPDATE `about_timeline` SET `milestone_year` = :year, `milestone_title` = :title, `milestone_desc` = :desc, `sort_order` = :sort, `image_path` = :image_path WHERE `id` = :id");
                    $edit_stmt->execute([
                        ':year' => $year,
                        ':title' => $title,
                        ':desc' => $desc,
                        ':sort' => $sort,
                        ':image_path' => $image_path,
                        ':id' => $id
                    ]);
                    $success_msg = "Timeline milestone updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Timeline modification failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // C. Delete timeline milestone
        if ($action === 'delete_milestone') {
            $id = intval($_POST['milestone_id'] ?? 0);
            if ($id > 0) {
                try {
                    $del_stmt = $pdo->prepare("DELETE FROM `about_timeline` WHERE `id` = :id");
                    $del_stmt->execute([':id' => $id]);
                    $success_msg = "Timeline milestone deleted successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Timeline deletion failure: " . sanitize($e->getMessage());
                }
            }
        }
    }
}

// 4. Handle POST Leadership Profile Submit Actions (ADD, EDIT, DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['add_leadership', 'edit_leadership', 'delete_leadership'])) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['action'];
        
        // A. Add Leadership
        if ($action === 'add_leadership') {
            $name = trim($_POST['leader_name'] ?? '');
            $designation = trim($_POST['leader_designation'] ?? '');
            $message = trim($_POST['leader_message'] ?? '');
            $profile_description = trim($_POST['leader_profile_description'] ?? '');
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $image_path = null;
            
            $upload = upload_image_asset('leader_image', 'about_leader_');
            if ($upload['status']) {
                $image_path = $upload['filepath'];
            } elseif ($upload['error']) {
                $error_msg = $upload['error'];
            }
            
            if (empty($name) || empty($designation) || empty($profile_description)) {
                $error_msg = "Please fill in Name, Designation, and Profile Description.";
            } elseif (empty($error_msg)) {
                try {
                    $add_stmt = $pdo->prepare("INSERT INTO `about_leadership` (`name`, `designation`, `message`, `profile_description`, `image_path`, `sort_order`) VALUES (:name, :designation, :message, :profile_description, :image_path, :sort_order)");
                    $add_stmt->execute([
                        ':name' => $name,
                        ':designation' => $designation,
                        ':message' => $message,
                        ':profile_description' => $profile_description,
                        ':image_path' => $image_path,
                        ':sort_order' => $sort_order
                    ]);
                    $success_msg = "New leadership profile added successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Leadership profile insertion failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // B. Edit Leadership
        if ($action === 'edit_leadership') {
            $id = intval($_POST['leader_id'] ?? 0);
            $name = trim($_POST['leader_name'] ?? '');
            $designation = trim($_POST['leader_designation'] ?? '');
            $message = trim($_POST['leader_message'] ?? '');
            $profile_description = trim($_POST['leader_profile_description'] ?? '');
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $image_path = $_POST['current_image_path'] ?? null;
            
            $upload = upload_image_asset('leader_image', 'about_leader_');
            if ($upload['status']) {
                $image_path = $upload['filepath'];
            } elseif ($upload['error']) {
                $error_msg = $upload['error'];
            }
            
            if (empty($name) || empty($designation) || empty($profile_description) || $id === 0) {
                $error_msg = "Please fill in Name, Designation, and Profile Description.";
            } elseif (empty($error_msg)) {
                try {
                    $edit_stmt = $pdo->prepare("UPDATE `about_leadership` SET `name` = :name, `designation` = :designation, `message` = :message, `profile_description` = :profile_description, `image_path` = :image_path, `sort_order` = :sort_order WHERE `id` = :id");
                    $edit_stmt->execute([
                        ':name' => $name,
                        ':designation' => $designation,
                        ':message' => $message,
                        ':profile_description' => $profile_description,
                        ':image_path' => $image_path,
                        ':sort_order' => $sort_order,
                        ':id' => $id
                    ]);
                    $success_msg = "Leadership profile updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Leadership profile modification failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // C. Delete Leadership
        if ($action === 'delete_leadership') {
            $id = intval($_POST['leader_id'] ?? 0);
            if ($id > 0) {
                try {
                    $del_stmt = $pdo->prepare("DELETE FROM `about_leadership` WHERE `id` = :id");
                    $del_stmt->execute([':id' => $id]);
                    $success_msg = "Leadership profile deleted successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Leadership profile deletion failure: " . sanitize($e->getMessage());
                }
            }
        }
    }
}

// Handle POST Faculty Submit Actions (ADD, EDIT, DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['add_faculty', 'edit_faculty', 'delete_faculty'])) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['action'];
        
        // A. Add Faculty
        if ($action === 'add_faculty') {
            $name = trim($_POST['fac_name'] ?? '');
            $designation = trim($_POST['fac_designation'] ?? '');
            $qualification = trim($_POST['fac_qualification'] ?? '');
            $subject = trim($_POST['fac_subject'] ?? '');
            $experience = trim($_POST['fac_experience'] ?? '');
            $expertise = trim($_POST['fac_expertise'] ?? '');
            $meaning = trim($_POST['fac_meaning'] ?? '');
            $philosophy = trim($_POST['fac_philosophy'] ?? '');
            $message = trim($_POST['fac_message'] ?? '');
            $quote = trim($_POST['fac_quote'] ?? '');
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $image_path = null;
            
            $upload = upload_faculty_photo('fac_image', 'fac_');
            if ($upload['status']) {
                $image_path = $upload['filepath'];
            } elseif ($upload['error']) {
                $error_msg = $upload['error'];
            }
            
            if (empty($name) || empty($designation)) {
                $error_msg = "Please fill in Name and Designation.";
            } elseif (empty($error_msg)) {
                try {
                    $add_stmt = $pdo->prepare("INSERT INTO `faculty` (`name`, `designation`, `qualification`, `subject`, `experience`, `expertise`, `meaning_of_education`, `teaching_philosophy`, `student_message`, `quote`, `image_path`, `sort_order`) VALUES (:name, :designation, :qualification, :subject, :experience, :expertise, :meaning, :philosophy, :message, :quote, :image_path, :sort_order)");
                    $add_stmt->execute([
                        ':name' => $name,
                        ':designation' => $designation,
                        ':qualification' => $qualification,
                        ':subject' => $subject,
                        ':experience' => $experience,
                        ':expertise' => $expertise,
                        ':meaning' => $meaning,
                        ':philosophy' => $philosophy,
                        ':message' => $message,
                        ':quote' => $quote,
                        ':image_path' => $image_path,
                        ':sort_order' => $sort_order
                    ]);
                    $success_msg = "New faculty profile added successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Faculty profile insertion failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // B. Edit Faculty
        if ($action === 'edit_faculty') {
            $id = intval($_POST['fac_id'] ?? 0);
            $name = trim($_POST['fac_name'] ?? '');
            $designation = trim($_POST['fac_designation'] ?? '');
            $qualification = trim($_POST['fac_qualification'] ?? '');
            $subject = trim($_POST['fac_subject'] ?? '');
            $experience = trim($_POST['fac_experience'] ?? '');
            $expertise = trim($_POST['fac_expertise'] ?? '');
            $meaning = trim($_POST['fac_meaning'] ?? '');
            $philosophy = trim($_POST['fac_philosophy'] ?? '');
            $message = trim($_POST['fac_message'] ?? '');
            $quote = trim($_POST['fac_quote'] ?? '');
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $image_path = $_POST['current_image_path'] ?? null;
            
            $upload = upload_faculty_photo('fac_image', 'fac_');
            if ($upload['status']) {
                $image_path = $upload['filepath'];
            } elseif ($upload['error']) {
                $error_msg = $upload['error'];
            }
            
            if (empty($name) || empty($designation) || $id === 0) {
                $error_msg = "Please fill in Name and Designation.";
            } elseif (empty($error_msg)) {
                try {
                    $edit_stmt = $pdo->prepare("UPDATE `faculty` SET `name` = :name, `designation` = :designation, `qualification` = :qualification, `subject` = :subject, `experience` = :experience, `expertise` = :expertise, `meaning_of_education` = :meaning, `teaching_philosophy` = :philosophy, `student_message` = :message, `quote` = :quote, `image_path` = :image_path, `sort_order` = :sort_order WHERE `id` = :id");
                    $edit_stmt->execute([
                        ':name' => $name,
                        ':designation' => $designation,
                        ':qualification' => $qualification,
                        ':subject' => $subject,
                        ':experience' => $experience,
                        ':expertise' => $expertise,
                        ':meaning' => $meaning,
                        ':philosophy' => $philosophy,
                        ':message' => $message,
                        ':quote' => $quote,
                        ':image_path' => $image_path,
                        ':sort_order' => $sort_order,
                        ':id' => $id
                    ]);
                    $success_msg = "Faculty profile updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Faculty profile modification failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // C. Delete Faculty
        if ($action === 'delete_faculty') {
            $id = intval($_POST['fac_id'] ?? 0);
            if ($id > 0) {
                try {
                    $del_stmt = $pdo->prepare("DELETE FROM `faculty` WHERE `id` = :id");
                    $del_stmt->execute([':id' => $id]);
                    $success_msg = "Faculty profile deleted successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Faculty profile deletion failure: " . sanitize($e->getMessage());
                }
            }
        }
    }
}

// 5. Fetch Milestones, Leaders, and Faculty for displaying
try {
    $timeline_stmt = $pdo->query("SELECT * FROM `about_timeline` ORDER BY `sort_order` ASC, `milestone_year` ASC");
    $milestones = $timeline_stmt->fetchAll();
    
    $leadership_stmt = $pdo->query("SELECT * FROM `about_leadership` ORDER BY `sort_order` ASC, `id` ASC");
    $leaders = $leadership_stmt->fetchAll();

    $faculty_stmt = $pdo->query("SELECT * FROM `faculty` ORDER BY `sort_order` ASC, `id` ASC");
    $faculties = $faculty_stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Listing Fetch Failure: " . sanitize($e->getMessage()));
}

$token = generate_csrf_token();
$active_tab = $_GET['tab'] ?? 'general';
if (!in_array($active_tab, ['general', 'timeline', 'leadership', 'faculty'])) {
    $active_tab = 'general';
}
?>

<!-- Tab switcher structure for CMS categories -->
<div style="display: flex; gap: 16px; margin-bottom: 28px; border-bottom: 1px solid var(--glass-border); padding-bottom: 1px; flex-wrap: wrap;">
    <button class="btn-action <?php echo ($active_tab === 'general') ? 'btn-theme' : 'btn-outline'; ?>" onclick="window.location.href='about.php?tab=general'" style="border-radius: 4px 4px 0 0; padding: 12px 24px; font-size: 0.95rem;">General CMS Settings</button>
    <button class="btn-action <?php echo ($active_tab === 'timeline') ? 'btn-theme' : 'btn-outline'; ?>" onclick="window.location.href='about.php?tab=timeline'" style="border-radius: 4px 4px 0 0; padding: 12px 24px; font-size: 0.95rem;">Timeline Milestones (<?php echo count($milestones); ?>)</button>
    <button class="btn-action <?php echo ($active_tab === 'leadership') ? 'btn-theme' : 'btn-outline'; ?>" onclick="window.location.href='about.php?tab=leadership'" style="border-radius: 4px 4px 0 0; padding: 12px 24px; font-size: 0.95rem;">Leadership Profiles (<?php echo count($leaders); ?>)</button>
    <button class="btn-action <?php echo ($active_tab === 'faculty') ? 'btn-theme' : 'btn-outline'; ?>" onclick="window.location.href='about.php?tab=faculty'" style="border-radius: 4px 4px 0 0; padding: 12px 24px; font-size: 0.95rem;">Faculty Profiles (<?php echo count($faculties); ?>)</button>
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

<!-- Wrap in Option A layout classes to align static labels above fields -->
<div class="no-float-form">

<!-- VIEW A: GENERAL CONTENT SETTINGS -->
<?php if ($active_tab === 'general'): ?>
    <form action="about.php?tab=general" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_general">
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
        
        <!-- 1. Introduction section General settings -->
        <div class="dashboard-block">
            <div class="block-title">
                <h3>Introduction Section</h3>
            </div>
            
            <div class="form-group">
                <label for="intro_heading">Introduction Header Title</label>
                <input type="text" name="intro_heading" id="intro_heading" class="form-control" value="<?php echo sanitize($about['intro_heading']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="intro_desc_1">Narrative Paragraph 1</label>
                <textarea name="intro_desc_1" id="intro_desc_1" class="form-control" required><?php echo sanitize($about['intro_desc_1']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="intro_desc_2">Narrative Paragraph 2</label>
                <textarea name="intro_desc_2" id="intro_desc_2" class="form-control" required><?php echo sanitize($about['intro_desc_2']); ?></textarea>
            </div>
            
            <div class="form-group" style="margin-top: 10px;">
                <label>Intro Section Crest Illustration</label>
                <div style="display: flex; gap: 24px; align-items: center; margin-bottom: 16px;">
                    <div style="width: 120px; height: 100px; border-radius: var(--border-radius-sm); overflow: hidden; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                        <img src="../<?php echo $about['intro_image_path']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="flex-grow: 1;">
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 8px;">Active file: <code><?php echo sanitize($about['intro_image_path']); ?></code></p>
                        <div class="file-upload-wrapper" style="padding: 16px;">
                            <input type="file" name="intro_image" class="file-upload-input">
                            <div class="file-upload-info" style="font-size: 0.85rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span>Replace Introduction Section Image (Allowed: JPG, PNG, WEBP, SVG)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 2. Vision & Mission Statement configurations -->
        <div class="dashboard-block">
            <div class="block-title">
                <h3>Vision, Mission & Core Philosophy</h3>
            </div>
            
            <div class="form-group">
                <label for="vision_title">Vision Section Title</label>
                <input type="text" name="vision_title" id="vision_title" class="form-control" value="<?php echo sanitize($about['vision_title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="vision_desc">Vision Narrative Statement</label>
                <textarea name="vision_desc" id="vision_desc" class="form-control" required><?php echo sanitize($about['vision_desc']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="mission_title">Mission Section Title</label>
                <input type="text" name="mission_title" id="mission_title" class="form-control" value="<?php echo sanitize($about['mission_title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="mission_desc">Mission Narrative Statement</label>
                <textarea name="mission_desc" id="mission_desc" class="form-control" required><?php echo sanitize($about['mission_desc']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="philosophy_title">Philosophy Section Title</label>
                <input type="text" name="philosophy_title" id="philosophy_title" class="form-control" value="<?php echo sanitize($about['philosophy_title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="philosophy_desc">Philosophy Narrative Statement</label>
                <textarea name="philosophy_desc" id="philosophy_desc" class="form-control" required><?php echo sanitize($about['philosophy_desc']); ?></textarea>
            </div>
        </div>
        
        <!-- 4. Achievements parameters -->
        <div class="dashboard-block">
            <div class="block-title">
                <h3>Achievements and National Accolades</h3>
            </div>
            
            <div class="form-grid">
                <!-- Achievement 1 -->
                <div class="form-group" style="border-bottom: 1px dashed var(--glass-border); padding-bottom: 20px;">
                    <label for="achievement_1_title">Accolade 1 Title</label>
                    <input type="text" name="achievement_1_title" id="achievement_1_title" class="form-control" value="<?php echo sanitize($about['achievement_1_title']); ?>">
                </div>
                <div class="form-group" style="border-bottom: 1px dashed var(--glass-border); padding-bottom: 20px;">
                    <label for="achievement_1_metric">Accolade 1 Score Metric</label>
                    <input type="text" name="achievement_1_metric" id="achievement_1_metric" class="form-control" value="<?php echo sanitize($about['achievement_1_metric']); ?>">
                </div>
                <div class="form-group full-width" style="border-bottom: 1px dashed var(--glass-border); padding-bottom: 20px;">
                    <label for="achievement_1_desc">Accolade 1 Brief Description</label>
                    <input type="text" name="achievement_1_desc" id="achievement_1_desc" class="form-control" value="<?php echo sanitize($about['achievement_1_desc']); ?>">
                </div>
                
                <!-- Achievement 2 -->
                <div class="form-group" style="border-bottom: 1px dashed var(--glass-border); padding-bottom: 20px;">
                    <label for="achievement_2_title">Accolade 2 Title</label>
                    <input type="text" name="achievement_2_title" id="achievement_2_title" class="form-control" value="<?php echo sanitize($about['achievement_2_title']); ?>">
                </div>
                <div class="form-group" style="border-bottom: 1px dashed var(--glass-border); padding-bottom: 20px;">
                    <label for="achievement_2_metric">Accolade 2 Score Metric</label>
                    <input type="text" name="achievement_2_metric" id="achievement_2_metric" class="form-control" value="<?php echo sanitize($about['achievement_2_metric']); ?>">
                </div>
                <div class="form-group full-width" style="border-bottom: 1px dashed var(--glass-border); padding-bottom: 20px;">
                    <label for="achievement_2_desc">Accolade 2 Brief Description</label>
                    <input type="text" name="achievement_2_desc" id="achievement_2_desc" class="form-control" value="<?php echo sanitize($about['achievement_2_desc']); ?>">
                </div>
                
                <!-- Achievement 3 -->
                <div class="form-group">
                    <label for="achievement_3_title">Accolade 3 Title</label>
                    <input type="text" name="achievement_3_title" id="achievement_3_title" class="form-control" value="<?php echo sanitize($about['achievement_3_title']); ?>">
                </div>
                <div class="form-group">
                    <label for="achievement_3_metric">Accolade 3 Score Metric</label>
                    <input type="text" name="achievement_3_metric" id="achievement_3_metric" class="form-control" value="<?php echo sanitize($about['achievement_3_metric']); ?>">
                </div>
                <div class="form-group full-width">
                    <label for="achievement_3_desc">Accolade 3 Brief Description</label>
                    <input type="text" name="achievement_3_desc" id="achievement_3_desc" class="form-control" value="<?php echo sanitize($about['achievement_3_desc']); ?>">
                </div>
            </div>
        </div>
        
        <!-- 5. Section Visibility Toggles -->
        <div class="dashboard-block">
            <div class="block-title">
                <h3>About Page Section Visibility</h3>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                    <div>
                        <h4 style="color: #ffffff; margin-bottom: 2px;">Academy Introduction Section</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the prestigious Letter of Welcome and institutional crest badge</p>
                    </div>
                    <label class="switch-control">
                        <input type="checkbox" name="show_intro" value="1" <?php if ($about['show_intro'] == 1) echo 'checked'; ?>>
                        <span class="slider-toggle"></span>
                    </label>
                </div>
                
                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                    <div>
                        <h4 style="color: #ffffff; margin-bottom: 2px;">Vision, Mission & Philosophy Cards</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the 3-column card deck laying out our core institutional values</p>
                    </div>
                    <label class="switch-control">
                        <input type="checkbox" name="show_vision_mission" value="1" <?php if ($about['show_vision_mission'] == 1) echo 'checked'; ?>>
                        <span class="slider-toggle"></span>
                    </label>
                </div>
                
                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                    <div>
                        <h4 style="color: #ffffff; margin-bottom: 2px;">Leadership Message & Advisors</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the dynamic leadership bio grids and quotes layout</p>
                    </div>
                    <label class="switch-control">
                        <input type="checkbox" name="show_leadership" value="1" <?php if ($about['show_leadership'] == 1) echo 'checked'; ?>>
                        <span class="slider-toggle"></span>
                    </label>
                </div>
                
                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                    <div>
                        <h4 style="color: #ffffff; margin-bottom: 2px;">Achievements Accolades Bar</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the 3-column board growth metric, robotics rank, and personality honors</p>
                    </div>
                    <label class="switch-control">
                        <input type="checkbox" name="show_achievements" value="1" <?php if ($about['show_achievements'] == 1) echo 'checked'; ?>>
                        <span class="slider-toggle"></span>
                    </label>
                </div>
                
                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                    <div>
                        <h4 style="color: #ffffff; margin-bottom: 2px;">Chronological Milestones Timeline</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the alternating center-lined history milestones (2003 to 2026)</p>
                    </div>
                    <label class="switch-control">
                        <input type="checkbox" name="show_timeline" value="1" <?php if ($about['show_timeline'] == 1) echo 'checked'; ?>>
                        <span class="slider-toggle"></span>
                    </label>
                </div>
                
                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                    <div>
                        <h4 style="color: #ffffff; margin-bottom: 2px;">Enrollment admissions CTA Banner</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the bottom full-width admissions CTA enrollment banner</p>
                    </div>
                    <label class="switch-control">
                        <input type="checkbox" name="show_cta" value="1" <?php if ($about['show_cta'] == 1) echo 'checked'; ?>>
                        <span class="slider-toggle"></span>
                    </label>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.01); padding: 12px 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                    <div>
                        <h4 style="color: #ffffff; margin-bottom: 2px;">Faculty Profiles Directory Section</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Displays the dynamic faculty grid section filterable by subject/designation</p>
                    </div>
                    <label class="switch-control">
                        <input type="checkbox" name="show_faculty" value="1" <?php if (($about['show_faculty'] ?? 1) == 1) echo 'checked'; ?>>
                        <span class="slider-toggle"></span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Form Submit Button -->
        <div style="margin-bottom: 40px; text-align: right;">
            <button type="submit" class="btn-action btn-theme" style="padding: 12px 36px; font-size: 1rem;">
                <span>Save About Page Changes</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            </button>
        </div>
    </form>

<!-- VIEW B: TIMELINE MILESTONES MANAGEMENT -->
<?php elseif ($active_tab === 'timeline'): ?>
    <!-- 1. Form to Add a New Timeline Milestone -->
    <div class="dashboard-block">
        <div class="block-title">
            <h3>Add New Timeline Milestone</h3>
        </div>
        <form action="about.php?tab=timeline" method="POST" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="action" value="add_milestone">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="milestone_year">Milestone Year (e.g. 2003)</label>
                    <input type="text" name="milestone_year" id="milestone_year" class="form-control" placeholder="Enter year" required>
                </div>
                <div class="form-group">
                    <label for="milestone_title">Milestone Brief Title</label>
                    <input type="text" name="milestone_title" id="milestone_title" class="form-control" placeholder="Enter title" required>
                </div>
                <div class="form-group">
                    <label for="sort_order">Sort Order Index</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="milestone_desc">Detailed Milestone Description Paragraph</label>
                <textarea name="milestone_desc" id="milestone_desc" class="form-control" placeholder="Enter description details" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Milestone Image Graphic (Optional)</label>
                <div class="file-upload-wrapper" style="padding: 16px;">
                    <input type="file" name="milestone_image" class="file-upload-input">
                    <div class="file-upload-info" style="font-size: 0.85rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <span>Choose Milestone Image Graphic (Allowed: JPG, PNG, WEBP, SVG)</span>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-action btn-theme">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5v14"/></svg>
                <span>Add Milestone</span>
            </button>
        </form>
    </div>

    <!-- 2. Active Timeline Milestones list table -->
    <div class="dashboard-block">
        <div class="block-title">
            <h3>Active Chronological Milestones</h3>
        </div>
        
        <?php if (empty($milestones)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 24px;">No timeline milestones added yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                     <thead>
                        <tr>
                            <th>Order</th>
                            <th>Graphic</th>
                            <th>Year</th>
                            <th>Milestone Title Header</th>
                            <th>Milestone Description details</th>
                            <th style="text-align: right;">Action Overrides</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($milestones as $milestone): ?>
                            <tr>
                                <td style="width: 80px; text-align: center;"><strong><?php echo intval($milestone['sort_order']); ?></strong></td>
                                <td style="width: 90px;">
                                    <div style="width: 70px; height: 50px; border-radius: 4px; overflow: hidden; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center;">
                                        <?php if (!empty($milestone['image_path'])): ?>
                                            <img src="../<?php echo $milestone['image_path']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.4;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="width: 100px; color: var(--accent-light); font-family: var(--font-heading); font-size: 1.1rem; font-weight: 700;"><?php echo sanitize($milestone['milestone_year']); ?></td>
                                <td style="width: 200px;"><strong><?php echo sanitize($milestone['milestone_title']); ?></strong></td>
                                <td style="font-size: 0.85rem; line-height: 1.5; max-width: 320px;"><?php echo sanitize($milestone['milestone_desc']); ?></td>
                                <td style="text-align: right; width: 180px;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <button class="btn-action btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($milestone)); ?>)">Edit</button>
                                        
                                        <form action="about.php?tab=timeline" method="POST" onsubmit="return confirm('Are you sure you want to delete this historical milestone?');" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_milestone">
                                            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                            <input type="hidden" name="milestone_id" value="<?php echo $milestone['id']; ?>">
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

    <!-- 3. Timeline Milestone Edit Modal Overlay -->
    <div id="edit-milestone-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; overflow-y: auto; padding: 40px 16px; box-sizing: border-box; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
        <div class="dashboard-block" style="width: 100%; max-width: 600px; margin: 0 auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
            <div class="block-title">
                <h3>Edit Historical Milestone</h3>
                <button onclick="closeEditModal()" class="btn-action btn-outline" style="padding: 4px 10px; border-radius: var(--border-radius-circle); font-size: 0.8rem;">X</button>
            </div>
            <form action="about.php?tab=timeline" method="POST" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="action" value="edit_milestone">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <input type="hidden" name="milestone_id" id="edit_milestone_id" value="">
                <input type="hidden" name="current_image_path" id="edit_current_image_path" value="">
                
                <div class="form-grid-modal">
                    <div class="form-group">
                        <label for="edit_milestone_year">Milestone Year</label>
                        <input type="text" name="milestone_year" id="edit_milestone_year" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_milestone_title">Milestone Title</label>
                        <input type="text" name="milestone_title" id="edit_milestone_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_sort_order">Sort Order Index</label>
                        <input type="number" name="sort_order" id="edit_sort_order" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_milestone_desc">Detailed Milestone Description</label>
                    <textarea name="milestone_desc" id="edit_milestone_desc" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label>Replace Milestone Image Graphic (Optional)</label>
                    <div class="modal-image-row">
                        <div id="edit-milestone-img-preview" class="modal-image-row-preview" style="width: 80px; height: 60px; border-radius: 4px; overflow: hidden; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center;">
                            <!-- Populated dynamically via modal JS -->
                        </div>
                        <div class="modal-image-row-upload">
                            <div class="file-upload-wrapper" style="padding: 12px;">
                                <input type="file" name="milestone_image" class="file-upload-input">
                                <div class="file-upload-info" style="font-size: 0.8rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <span>Upload new graphic file</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-actions-row">
                    <button type="button" class="btn-action btn-outline" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-action btn-theme">Save Milestone changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script facilitating Modal toggle -->
    <script>
        function openEditModal(milestone) {
            document.getElementById('edit_milestone_id').value = milestone.id;
            document.getElementById('edit_milestone_year').value = milestone.milestone_year;
            document.getElementById('edit_milestone_title').value = milestone.milestone_title;
            document.getElementById('edit_sort_order').value = milestone.sort_order;
            document.getElementById('edit_milestone_desc').value = milestone.milestone_desc;
            document.getElementById('edit_current_image_path').value = milestone.image_path || '';
            
            const previewBox = document.getElementById('edit-milestone-img-preview');
            if (milestone.image_path) {
                previewBox.innerHTML = `<img src="../${milestone.image_path}" style="width:100%;height:100%;object-fit:cover;">`;
            } else {
                previewBox.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.4;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`;
            }
            
            const modal = document.getElementById('edit-milestone-modal');
            modal.style.display = 'block';
        }
        function closeEditModal() {
            const modal = document.getElementById('edit-milestone-modal');
            modal.style.display = 'none';
        }
    </script>

<!-- VIEW C: LEADERSHIP PROFILE MANAGEMENT -->
<?php elseif ($active_tab === 'leadership'): ?>
    <!-- 1. Form to Add a New Leadership Profile -->
    <div class="dashboard-block">
        <div class="block-title">
            <h3>Add New Leadership Profile</h3>
        </div>
        <form action="about.php?tab=leadership" method="POST" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="action" value="add_leadership">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="leader_name">Full Name</label>
                    <input type="text" name="leader_name" id="leader_name" class="form-control" placeholder="e.g. Dr. Rajesh Mukhopadhyay" required>
                </div>
                <div class="form-group">
                    <label for="leader_designation">Designation</label>
                    <input type="text" name="leader_designation" id="leader_designation" class="form-control" placeholder="e.g. Principal" required>
                </div>
                <div class="form-group">
                    <label for="leader_sort_order">Sort Order Index</label>
                    <input type="number" name="sort_order" id="leader_sort_order" class="form-control" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="leader_message">Visionary Quote / Personal Message (Optional)</label>
                <textarea name="leader_message" id="leader_message" class="form-control" placeholder="Enter quote message displayed on main banner..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="leader_profile_description">Brief Profile Description / Biography Details</label>
                <textarea name="leader_profile_description" id="leader_profile_description" class="form-control" placeholder="Enter qualifications, experience, and bio details..." required></textarea>
            </div>
            
            <div class="form-group">
                <label>Profile Photograph</label>
                <div class="file-upload-wrapper" style="padding: 16px;">
                    <input type="file" name="leader_image" class="file-upload-input" required>
                    <div class="file-upload-info" style="font-size: 0.85rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <span>Choose Photograph (Allowed: JPG, PNG, WEBP)</span>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-action btn-theme">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5v14"/></svg>
                <span>Add Leadership Profile</span>
            </button>
        </form>
    </div>

    <!-- 2. Active Leadership Profiles list table -->
    <div class="dashboard-block">
        <div class="block-title">
            <h3>Active Leadership & Mentors</h3>
        </div>
        
        <?php if (empty($leaders)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 24px;">No leadership profiles registered yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Photo</th>
                            <th>Name & Designation</th>
                            <th>Bio Details</th>
                            <th>Personal Message Quote</th>
                            <th style="text-align: right;">Action Overrides</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaders as $leader): ?>
                            <tr>
                                <td style="width: 70px; text-align: center;"><strong><?php echo intval($leader['sort_order']); ?></strong></td>
                                <td style="width: 80px;">
                                    <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center;">
                                        <?php if (!empty($leader['image_path'])): ?>
                                            <img src="../<?php echo $leader['image_path']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.4;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="width: 220px;">
                                    <strong style="font-size: 1rem; color: #ffffff; display: block;"><?php echo sanitize($leader['name']); ?></strong>
                                    <span style="font-size: 0.8rem; color: var(--accent-light); font-weight: 600; text-transform: uppercase;"><?php echo sanitize($leader['designation']); ?></span>
                                </td>
                                <td style="font-size: 0.85rem; line-height: 1.5; max-width: 280px;"><?php echo sanitize($leader['profile_description']); ?></td>
                                <td style="font-size: 0.85rem; font-style: italic; color: #cbd5e1; max-width: 260px;"><?php echo !empty($leader['message']) ? '"' . sanitize($leader['message']) . '"' : '<span style="opacity:0.3;">None</span>'; ?></td>
                                <td style="text-align: right; width: 180px;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <button class="btn-action btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;" onclick="openEditLeaderModal(<?php echo htmlspecialchars(json_encode($leader)); ?>)">Edit</button>
                                        
                                        <form action="about.php?tab=leadership" method="POST" onsubmit="return confirm('Are you sure you want to remove this leadership profile?');" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_leadership">
                                            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                            <input type="hidden" name="leader_id" value="<?php echo $leader['id']; ?>">
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

    <!-- 3. Leadership Profile Edit Modal Overlay -->
    <div id="edit-leader-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; overflow-y: auto; padding: 40px 16px; box-sizing: border-box; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
        <div class="dashboard-block" style="width: 100%; max-width: 650px; margin: 0 auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
            <div class="block-title">
                <h3>Edit Leadership Profile</h3>
                <button onclick="closeEditLeaderModal()" class="btn-action btn-outline" style="padding: 4px 10px; border-radius: var(--border-radius-circle); font-size: 0.8rem;">X</button>
            </div>
            <form action="about.php?tab=leadership" method="POST" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="action" value="edit_leadership">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <input type="hidden" name="leader_id" id="edit_leader_id" value="">
                <input type="hidden" name="current_image_path" id="edit_leader_current_image_path" value="">
                
                <div class="form-grid-modal">
                    <div class="form-group">
                        <label for="edit_leader_name">Full Name</label>
                        <input type="text" name="leader_name" id="edit_leader_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_leader_designation">Designation</label>
                        <input type="text" name="leader_designation" id="edit_leader_designation" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_leader_sort_order">Sort Order Index</label>
                        <input type="number" name="sort_order" id="edit_leader_sort_order" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_leader_message">Visionary Quote / Personal Message (Optional)</label>
                    <textarea name="leader_message" id="edit_leader_message" class="form-control"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_leader_profile_description">Brief Profile Description / Biography Details</label>
                    <textarea name="leader_profile_description" id="edit_leader_profile_description" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label>Replace Profile Photograph (Optional)</label>
                    <div class="modal-image-row">
                        <div id="edit-leader-img-preview" class="modal-image-row-preview" style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center;">
                            <!-- Populated dynamically via modal JS -->
                        </div>
                        <div class="modal-image-row-upload">
                            <div class="file-upload-wrapper" style="padding: 12px;">
                                <input type="file" name="leader_image" class="file-upload-input">
                                <div class="file-upload-info" style="font-size: 0.8rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <span>Upload new photograph</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-actions-row">
                    <button type="button" class="btn-action btn-outline" onclick="closeEditLeaderModal()">Cancel</button>
                    <button type="submit" class="btn-action btn-theme">Save Leadership changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script facilitating Modal toggle -->
    <script>
        function openEditLeaderModal(leader) {
            document.getElementById('edit_leader_id').value = leader.id;
            document.getElementById('edit_leader_name').value = leader.name;
            document.getElementById('edit_leader_designation').value = leader.designation;
            document.getElementById('edit_leader_sort_order').value = leader.sort_order;
            document.getElementById('edit_leader_message').value = leader.message || '';
            document.getElementById('edit_leader_profile_description').value = leader.profile_description;
            document.getElementById('edit_leader_current_image_path').value = leader.image_path || '';
            
            const previewBox = document.getElementById('edit-leader-img-preview');
            if (leader.image_path) {
                previewBox.innerHTML = `<img src="../${leader.image_path}" style="width:100%;height:100%;object-fit:cover;">`;
            } else {
                previewBox.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.4;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`;
            }
            
            const modal = document.getElementById('edit-leader-modal');
            modal.style.display = 'block';
        }
        function closeEditLeaderModal() {
            const modal = document.getElementById('edit-leader-modal');
            modal.style.display = 'none';
        }
    </script>
<?php elseif ($active_tab === 'faculty'): ?>
    <!-- 1. Form to Add a New Faculty Profile -->
    <div class="dashboard-block">
        <div class="block-title">
            <h3>Add New Faculty Profile</h3>
        </div>
        <form action="about.php?tab=faculty" method="POST" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="action" value="add_faculty">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="fac_name">Full Name</label>
                    <input type="text" name="fac_name" id="fac_name" class="form-control" placeholder="e.g. Anuradha Dinkarao Nandurakar" required>
                </div>
                <div class="form-group">
                    <label for="fac_designation">Designation</label>
                    <input type="text" name="fac_designation" id="fac_designation" class="form-control" placeholder="e.g. Teacher, Co-ordinator" required>
                </div>
                <div class="form-group">
                    <label for="fac_qualification">Qualification</label>
                    <input type="text" name="fac_qualification" id="fac_qualification" class="form-control" placeholder="e.g. M.Sc, B.Ed">
                </div>
                <div class="form-group">
                    <label for="fac_subject">Subject (Optional)</label>
                    <input type="text" name="fac_subject" id="fac_subject" class="form-control" placeholder="e.g. Maths, Science, Languages">
                </div>
                <div class="form-group">
                    <label for="fac_experience">Experience</label>
                    <input type="text" name="fac_experience" id="fac_experience" class="form-control" placeholder="e.g. 5 Years">
                </div>
                <div class="form-group">
                    <label for="fac_sort_order">Sort Order Index</label>
                    <input type="number" name="sort_order" id="fac_sort_order" class="form-control" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="fac_expertise">Area of Expertise (Optional)</label>
                <textarea name="fac_expertise" id="fac_expertise" class="form-control" placeholder="Enter key strengths, domains..."></textarea>
            </div>
            
            <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <div class="form-group">
                    <label for="fac_meaning">Meaning of Education (Optional)</label>
                    <textarea name="fac_meaning" id="fac_meaning" class="form-control" placeholder="What education means to them..."></textarea>
                </div>
                <div class="form-group">
                    <label for="fac_philosophy">Teaching Philosophy (Optional)</label>
                    <textarea name="fac_philosophy" id="fac_philosophy" class="form-control" placeholder="Their teaching approach..."></textarea>
                </div>
            </div>

            <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <div class="form-group">
                    <label for="fac_message">Student Message (Optional)</label>
                    <textarea name="fac_message" id="fac_message" class="form-control" placeholder="Words of advice for students..."></textarea>
                </div>
                <div class="form-group">
                    <label for="fac_quote">Inspirational Quote (Optional)</label>
                    <textarea name="fac_quote" id="fac_quote" class="form-control" placeholder="Their favorite quote..."></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label>Profile Photograph (Optional)</label>
                <div class="file-upload-wrapper" style="padding: 16px;">
                    <input type="file" name="fac_image" class="file-upload-input">
                    <div class="file-upload-info" style="font-size: 0.85rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <span>Choose Photograph (Allowed: JPG, PNG, WEBP)</span>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-action btn-theme">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5v14"/></svg>
                <span>Add Faculty Profile</span>
            </button>
        </form>
    </div>

    <!-- 2. Active Faculty Profiles list table -->
    <div class="dashboard-block">
        <div class="block-title">
            <h3>Active Faculty Directory</h3>
        </div>
        
        <?php if (empty($faculties)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 24px;">No faculty profiles registered yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Photo</th>
                            <th>Name & Designation</th>
                            <th>Qualifications</th>
                            <th>Experience</th>
                            <th style="text-align: right;">Action Overrides</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faculties as $fac): ?>
                            <tr>
                                <td style="width: 70px; text-align: center;"><strong><?php echo intval($fac['sort_order']); ?></strong></td>
                                <td style="width: 80px;">
                                    <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center;">
                                        <?php if (!empty($fac['image_path'])): ?>
                                            <img src="../<?php echo $fac['image_path']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.4;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <strong style="font-size: 1rem; color: #ffffff; display: block;"><?php echo sanitize($fac['name']); ?></strong>
                                    <span style="font-size: 0.8rem; color: var(--accent-light); font-weight: 600; text-transform: uppercase;">
                                        <?php echo sanitize($fac['designation']); ?>
                                        <?php if (!empty($fac['subject'])): ?>
                                            — <?php echo sanitize($fac['subject']); ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td style="font-size: 0.85rem; color: #e2e8f0;"><?php echo !empty($fac['qualification']) ? sanitize($fac['qualification']) : '<span style="opacity:0.3;">N/A</span>'; ?></td>
                                <td style="font-size: 0.85rem; color: #e2e8f0;"><?php echo !empty($fac['experience']) ? sanitize($fac['experience']) : '<span style="opacity:0.3;">N/A</span>'; ?></td>
                                <td style="text-align: right; width: 180px;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <button class="btn-action btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;" onclick="openEditFacultyModal(<?php echo htmlspecialchars(json_encode($fac)); ?>)">Edit</button>
                                        
                                        <form action="about.php?tab=faculty" method="POST" onsubmit="return confirm('Are you sure you want to remove this faculty profile?');" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_faculty">
                                            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                            <input type="hidden" name="fac_id" value="<?php echo $fac['id']; ?>">
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

    <!-- 3. Faculty Profile Edit Modal Overlay -->
    <div id="edit-faculty-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; overflow-y: auto; padding: 40px 16px; box-sizing: border-box; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
        <div class="dashboard-block" style="width: 100%; max-width: 700px; margin: 0 auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
            <div class="block-title">
                <h3>Edit Faculty Profile</h3>
                <button onclick="closeEditFacultyModal()" class="btn-action btn-outline" style="padding: 4px 10px; border-radius: var(--border-radius-circle); font-size: 0.8rem;">X</button>
            </div>
            <form action="about.php?tab=faculty" method="POST" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="action" value="edit_faculty">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <input type="hidden" name="fac_id" id="edit_fac_id" value="">
                <input type="hidden" name="current_image_path" id="edit_fac_current_image_path" value="">
                
                <div class="form-grid-modal">
                    <div class="form-group">
                        <label for="edit_fac_name">Full Name</label>
                        <input type="text" name="fac_name" id="edit_fac_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_fac_designation">Designation</label>
                        <input type="text" name="fac_designation" id="edit_fac_designation" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_fac_qualification">Qualification</label>
                        <input type="text" name="fac_qualification" id="edit_fac_qualification" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_fac_subject">Subject</label>
                        <input type="text" name="fac_subject" id="edit_fac_subject" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_fac_experience">Experience</label>
                        <input type="text" name="fac_experience" id="edit_fac_experience" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_fac_sort_order">Sort Order Index</label>
                        <input type="number" name="sort_order" id="edit_fac_sort_order" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_fac_expertise">Area of Expertise (Optional)</label>
                    <textarea name="fac_expertise" id="edit_fac_expertise" class="form-control"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_fac_meaning">Meaning of Education (Optional)</label>
                    <textarea name="fac_meaning" id="edit_fac_meaning" class="form-control"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_fac_philosophy">Teaching Philosophy (Optional)</label>
                    <textarea name="fac_philosophy" id="edit_fac_philosophy" class="form-control"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_fac_message">Student Message (Optional)</label>
                    <textarea name="fac_message" id="edit_fac_message" class="form-control"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_fac_quote">Inspirational Quote (Optional)</label>
                    <textarea name="fac_quote" id="edit_fac_quote" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Replace Profile Photograph (Optional)</label>
                    <div class="modal-image-row">
                        <div id="edit-faculty-img-preview" class="modal-image-row-preview" style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center;">
                            <!-- Populated dynamically via modal JS -->
                        </div>
                        <div class="modal-image-row-upload">
                            <div class="file-upload-wrapper" style="padding: 12px;">
                                <input type="file" name="fac_image" class="file-upload-input">
                                <div class="file-upload-info" style="font-size: 0.8rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <span>Upload new photograph</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-actions-row">
                    <button type="button" class="btn-action btn-outline" onclick="closeEditFacultyModal()">Cancel</button>
                    <button type="submit" class="btn-action btn-theme">Save Faculty changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script facilitating Faculty Modal toggle -->
    <script>
        function openEditFacultyModal(fac) {
            document.getElementById('edit_fac_id').value = fac.id;
            document.getElementById('edit_fac_name').value = fac.name;
            document.getElementById('edit_fac_designation').value = fac.designation;
            document.getElementById('edit_fac_qualification').value = fac.qualification || '';
            document.getElementById('edit_fac_subject').value = fac.subject || '';
            document.getElementById('edit_fac_experience').value = fac.experience || '';
            document.getElementById('edit_fac_sort_order').value = fac.sort_order;
            document.getElementById('edit_fac_expertise').value = fac.expertise || '';
            document.getElementById('edit_fac_meaning').value = fac.meaning_of_education || '';
            document.getElementById('edit_fac_philosophy').value = fac.teaching_philosophy || '';
            document.getElementById('edit_fac_message').value = fac.student_message || '';
            document.getElementById('edit_fac_quote').value = fac.quote || '';
            document.getElementById('edit_fac_current_image_path').value = fac.image_path || '';
            
            const previewBox = document.getElementById('edit-faculty-img-preview');
            if (fac.image_path) {
                previewBox.innerHTML = `<img src="../${fac.image_path}" style="width:100%;height:100%;object-fit:cover;">`;
            } else {
                previewBox.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.4;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`;
            }
            
            const modal = document.getElementById('edit-faculty-modal');
            modal.style.display = 'block';
        }
        function closeEditFacultyModal() {
            const modal = document.getElementById('edit-faculty-modal');
            modal.style.display = 'none';
        }
    </script>
<?php endif; ?>

</div> <!-- Close Option A layout wrappers -->

<?php include_once 'includes/footer.php'; ?>
