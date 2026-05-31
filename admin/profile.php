<?php
/**
 * ========================================================
 * SECURITY SETTINGS & PROFILE CONTROLLER (GURUKUL)
 * ========================================================
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';
require_once 'includes/auth.php';

// Enforce login credentials
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$error_msg = "";
$success_msg = "";
$is_forced = (isset($_SESSION['is_first_login']) && $_SESSION['is_first_login'] == 1);

// Handle POST password change submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        $current_pwd = trim($_POST['current_password'] ?? '');
        $new_pwd = trim($_POST['new_password'] ?? '');
        $confirm_pwd = trim($_POST['confirm_password'] ?? '');
        
        if (empty($current_pwd) || empty($new_pwd) || empty($confirm_pwd)) {
            $error_msg = "Please fill in all password fields.";
        } elseif ($new_pwd !== $confirm_pwd) {
            $error_msg = "New passwords do not match.";
        } elseif (strlen($new_pwd) < 8) {
            $error_msg = "New security password must be at least 8 characters long.";
        } else {
            try {
                // Fetch active administrator credentials
                $stmt = $pdo->prepare("SELECT * FROM `admins` WHERE `id` = :id LIMIT 1");
                $stmt->execute([':id' => $_SESSION['admin_id']]);
                $admin = $stmt->fetch();
                
                if ($admin && password_verify($current_pwd, $admin['password_hash'])) {
                    // Check if new password is identical to the current one
                    if (password_verify($new_pwd, $admin['password_hash'])) {
                        $error_msg = "New password cannot be identical to the current password.";
                    } else {
                        // Generate secure bcrypt hash
                        $new_hash = password_hash($new_pwd, PASSWORD_DEFAULT);
                        
                        // Update administrator database entry
                        $update_stmt = $pdo->prepare("UPDATE `admins` SET `password_hash` = :hash, `is_first_login` = 0 WHERE `id` = :id");
                        $update_stmt->execute([
                            ':hash' => $new_hash,
                            ':id'   => $_SESSION['admin_id']
                        ]);
                        
                        // Sync session indicators
                        $_SESSION['is_first_login'] = 0;
                        $is_forced = false;
                        $success_msg = "Your administrator security password was changed successfully.";
                    }
                } else {
                    $error_msg = "Current security password is invalid.";
                }
            } catch (PDOException $e) {
                $error_msg = "A database error occurred. Contact the technical team.";
            }
        }
    }
}

// Generate secure CSRF token for forms
$token = generate_csrf_token();
?>

<?php include_once 'includes/header.php'; ?>

<div style="max-width: 800px; margin: 0 auto;">

    <?php if ($is_forced): ?>
        <div class="alert-banner error" style="border-left: 5px solid #ef4444; padding: 20px;">
            <svg style="flex-shrink: 0;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <div>
                <strong style="display: block; font-size: 1.1rem; margin-bottom: 6px; color: #ffffff;">First-Time Login Security Policy</strong>
                You are logged in with the default administrator account credentials. To protect the integrity of the Gurukul Academy databases, you MUST choose a strong, custom security password before accessing the CMS dashboard features. All other pages are locked until this is complete.
            </div>
        </div>
    <?php endif; ?>

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

    <div class="dashboard-block">
        <div class="block-title">
            <h3>Change Security Password</h3>
        </div>

        <form action="profile.php" method="POST" autocomplete="off">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required placeholder="Enter current password">
            </div>

            <div class="form-group">
                <label for="new_password">New Security Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required placeholder="Enter new password (min. 8 characters)">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required placeholder="Re-enter new password">
            </div>

            <button type="submit" class="btn-action btn-theme">
                <span>Update Password</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </button>
        </form>
    </div>

    <!-- Account Recovery Documentation Block -->
    <div class="dashboard-block" style="background: rgba(217, 119, 6, 0.03); border-color: rgba(217, 119, 6, 0.15);">
        <div class="block-title" style="border-bottom-color: rgba(217, 119, 6, 0.1);">
            <h3 style="color: var(--accent-light); display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <span>Account Recovery Procedures</span>
            </h3>
        </div>
        <div style="font-size: 0.95rem; line-height: 1.6; color: var(--text-muted);">
            <p style="margin-bottom: 12px;">In case you lose access to this administrator account, you can recover or reset it securely via the Hostinger Control Panel (hPanel) by following these instructions:</p>
            <ol style="margin-left: 20px; display: flex; flex-direction: column; gap: 8px;">
                <li>Log in to your <strong>Hostinger hPanel</strong> account.</li>
                <li>Navigate to <strong>Databases &rarr; phpMyAdmin</strong> and open the database assigned to Gurukul.</li>
                <li>Find and click the <strong>`admins`</strong> table inside phpMyAdmin.</li>
                <li>Select the primary admin record (ID 1) and click <strong>Edit</strong>.</li>
                <li>Locate the <code>password_hash</code> field. Set the function dropdown to <strong>MD5</strong> or clear the field and copy the following pre-calculated secure bcrypt hash to reset the password back to <code>GurukulAdmin2026!</code>:
                    <code style="display: block; background: rgba(0,0,0,0.3); padding: 8px; border-radius: 4px; font-size: 0.8rem; font-family: monospace; word-break: break-all; margin: 6px 0; color: #ffffff;">$2y$10$wJtK2.tP0JgG7Z7h8j7HcuJj.j20N2e7h01Wb3g0T3T5F5V7V7Z5.</code>
                </li>
                <li>Set the <code>is_first_login</code> field back to <code>1</code> so the system forces another change policy when next logged in. Click <strong>Go</strong> to save the changes.</li>
            </ol>
        </div>
    </div>

</div>

<?php include_once 'includes/footer.php'; ?>
