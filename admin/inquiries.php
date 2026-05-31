<?php
/**
 * ========================================================
 * STUDENT CONTACT INQUIRY CENTER & EXPORT MODULE (GURUKUL)
 * ========================================================
 */

require_once '../config/db.php';
require_once 'includes/auth.php';

// Enforce authentication controls
check_auth();

$success_msg = "";
$error_msg = "";

// A. HANDLE EXPORT INQUIRIES TO CSV ACTION
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    try {
        $stmt_export = $pdo->query("SELECT `id`, `name`, `email`, `phone`, `subject`, `message`, `is_read`, `submitted_at` FROM `contact_submissions` ORDER BY `submitted_at` DESC");
        $records = $stmt_export->fetchAll(PDO::FETCH_ASSOC);
        
        // Define CSV file parameters
        $filename = "Gurukul_Inquiries_" . date('Y-m-d_H-i-s') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // Write CSV column headers
        fputcsv($output, ['ID', 'Student Name', 'Email Address', 'Phone Number', 'Subject', 'Detailed Message', 'Read Status', 'Submitted At']);
        
        foreach ($records as $row) {
            $status_label = ($row['is_read'] == 1) ? 'Processed' : 'Unread';
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['email'],
                $row['phone'],
                $row['subject'],
                $row['message'],
                $status_label,
                $row['submitted_at']
            ]);
        }
        
        fclose($output);
        exit();
    } catch (PDOException $e) {
        die("Export Operation Failure: " . htmlspecialchars($e->getMessage()));
    }
}

// B. HANDLE POST CRUD ACTIONS (Mark Read/Unread, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['action'];
        
        // Toggle read/unread status
        if ($action === 'toggle_read') {
            $id = intval($_POST['inquiry_id'] ?? 0);
            $state = intval($_POST['read_state'] ?? 0);
            if ($id > 0) {
                try {
                    $up_stmt = $pdo->prepare("UPDATE `contact_submissions` SET `is_read` = :state WHERE `id` = :id");
                    $up_stmt->execute([':state' => $state, ':id' => $id]);
                    $success_msg = "Inquiry status updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database update failure: " . sanitize($e->getMessage());
                }
            }
        }
        
        // Delete inquiry record
        if ($action === 'delete_inquiry') {
            $id = intval($_POST['inquiry_id'] ?? 0);
            if ($id > 0) {
                try {
                    $del_stmt = $pdo->prepare("DELETE FROM `contact_submissions` WHERE `id` = :id");
                    $del_stmt->execute([':id' => $id]);
                    $success_msg = "Inquiry record deleted successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database delete failure: " . sanitize($e->getMessage());
                }
            }
        }
    }
}

// 3. Fetch all inquiries list
try {
    $stmt_list = $pdo->query("SELECT * FROM `contact_submissions` ORDER BY `submitted_at` DESC");
    $inquiries = $stmt_list->fetchAll();
} catch (PDOException $e) {
    die("Database Listing Fetch Failure: " . sanitize($e->getMessage()));
}

$token = generate_csrf_token();
?>

<?php include_once 'includes/header.php'; ?>

<!-- Top controls bar -->
<div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; margin-bottom: 28px;">
    <div style="display: flex; gap: 12px; align-items: center; flex-grow: 1;">
        <input type="text" id="inquiry-search" class="form-control" placeholder="Search by name, email or subject..." style="max-width: 280px; height: 38px;">
        <select id="inquiry-filter" class="form-control" style="max-width: 180px; height: 38px;">
            <option value="">All Inquiries</option>
            <option value="unread">Unread Inquiries</option>
            <option value="read">Processed Inquiries</option>
        </select>
    </div>
    
    <!-- Export CSV button link -->
    <a href="inquiries.php?action=export_csv" class="btn-action btn-accent">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        <span>Export Inquiries to CSV</span>
    </a>
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

<div class="dashboard-block">
    <div class="block-title">
        <h3>Contact Inquiry Submissions (<?php echo count($inquiries); ?>)</h3>
    </div>
    
    <?php if (empty($inquiries)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 36px;">No contact inquiry submissions recorded yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date Received</th>
                        <th>Student Name</th>
                        <th>Email & Phone</th>
                        <th>Subject Title</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action Overrides</th>
                    </tr>
                </thead>
                <tbody id="inquiry-rows">
                    <?php foreach ($inquiries as $row): ?>
                        <tr class="inquiry-item" data-search="<?php echo strtolower(sanitize($row['name'] . ' ' . $row['email'] . ' ' . $row['subject'])); ?>" data-status="<?php echo ($row['is_read'] == 1) ? 'read' : 'unread'; ?>">
                            <td style="width: 140px; font-size: 0.85rem;"><?php echo date('M d, Y h:i A', strtotime($row['submitted_at'])); ?></td>
                            <td><strong><?php echo sanitize($row['name']); ?></strong></td>
                            <td style="font-size: 0.85rem; line-height: 1.4;">
                                <a href="mailto:<?php echo sanitize($row['email']); ?>" style="color: var(--secondary-light); display: block;"><?php echo sanitize($row['email']); ?></a>
                                <span style="color: var(--text-muted);"><?php echo sanitize($row['phone']); ?></span>
                            </td>
                            <td><strong><?php echo sanitize($row['subject']); ?></strong></td>
                            <td style="width: 120px;">
                                <form action="inquiries.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_read">
                                    <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                    <input type="hidden" name="inquiry_id" value="<?php echo $row['id']; ?>">
                                    
                                    <?php if ($row['is_read'] == 1): ?>
                                        <input type="hidden" name="read_state" value="0">
                                        <button type="submit" class="badge-status badge-success" style="cursor: pointer;" title="Click to mark as Unread">Processed</button>
                                    <?php else: ?>
                                        <input type="hidden" name="read_state" value="1">
                                        <button type="submit" class="badge-status badge-warning" style="cursor: pointer;" title="Click to mark as Processed">Unread</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td style="text-align: right; width: 180px;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <!-- View Message Modal Trigger -->
                                    <button class="btn-action btn-outline" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;" onclick="openMessageModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">View Message</button>
                                    
                                    <form action="inquiries.php" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this inquiry record?');" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_inquiry">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="inquiry_id" value="<?php echo $row['id']; ?>">
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

<!-- Message Preview Modal Overlay -->
<div id="inquiry-message-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(12px);">
    <div class="dashboard-block" style="width: 90%; max-width: 600px; margin-bottom: 0;">
        <div class="block-title">
            <h3 id="modal-heading">Contact Message</h3>
            <button onclick="closeMessageModal()" class="btn-action btn-outline" style="padding: 4px 10px; border-radius: var(--border-radius-circle); font-size: 0.8rem;">X</button>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 16px; font-size: 0.95rem; line-height: 1.6;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; border-bottom: 1px solid var(--glass-border); padding-bottom: 16px;">
                <div>
                    <strong style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">From Student/Guardian</strong>
                    <span id="modal-name" style="color: #ffffff; font-weight: 600;">Full Name</span>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Email & Phone</strong>
                    <span id="modal-contacts" style="color: #ffffff;">email@mail.com</span>
                </div>
            </div>
            
            <div>
                <strong style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Message Subject</strong>
                <span id="modal-subject" style="color: #ffffff; font-weight: 600;">Subject line text</span>
            </div>
            
            <div>
                <strong style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Message content Details</strong>
                <div id="modal-body" style="background: rgba(0,0,0,0.3); padding: 18px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border); color: #e2e8f0; max-height: 250px; overflow-y: auto; white-space: pre-wrap;">
                    Message body text.
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; border-top: 1px solid var(--glass-border); padding-top: 16px;">
            <form action="inquiries.php" method="POST" id="modal-toggle-form" style="display: inline;">
                <input type="hidden" name="action" value="toggle_read">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <input type="hidden" name="inquiry_id" id="modal-inquiry-id" value="">
                <input type="hidden" name="read_state" id="modal-read-state" value="">
                <button type="submit" id="modal-toggle-btn" class="btn-action btn-theme">Mark Processed</button>
            </form>
            <button type="button" class="btn-action btn-outline" onclick="closeMessageModal()">Close Message</button>
        </div>
    </div>
</div>

<script>
    // Live client-side search & filtering
    const searchInput = document.getElementById('inquiry-search');
    const filterSelect = document.getElementById('inquiry-filter');
    const rows = document.querySelectorAll('.inquiry-item');
    
    const filterGrid = () => {
        const query = searchInput.value.toLowerCase().trim();
        const filter = filterSelect.value;
        
        rows.forEach(row => {
            const searchKey = row.getAttribute('data-search');
            const status = row.getAttribute('data-status');
            
            const queryMatch = searchKey.includes(query);
            const statusMatch = !filter || status === filter;
            
            row.style.display = (queryMatch && statusMatch) ? '' : 'none';
        });
    };
    
    if (searchInput && filterSelect) {
        searchInput.addEventListener('input', filterGrid);
        filterSelect.addEventListener('change', filterGrid);
    }
    
    // Modal controls
    function openMessageModal(row) {
        document.getElementById('modal-inquiry-id').value = row.id;
        document.getElementById('modal-name').textContent = row.name;
        document.getElementById('modal-contacts').innerHTML = `<a href="mailto:${row.email}" style="color: var(--secondary-light); text-decoration:none;">${row.email}</a><br><span style="color: var(--text-muted);">${row.phone}</span>`;
        document.getElementById('modal-subject').textContent = row.subject;
        document.getElementById('modal-body').textContent = row.message;
        
        const toggleBtn = document.getElementById('modal-toggle-btn');
        const readStateInput = document.getElementById('modal-read-state');
        
        if (row.is_read == 1) {
            toggleBtn.innerHTML = '<span>Mark as Unread</span>';
            toggleBtn.className = 'btn-action btn-outline';
            readStateInput.value = '0';
        } else {
            toggleBtn.innerHTML = '<span>Mark Processed</span>';
            toggleBtn.className = 'btn-action btn-theme';
            readStateInput.value = '1';
        }
        
        document.getElementById('inquiry-message-modal').style.display = 'flex';
    }
    
    function closeMessageModal() {
        document.getElementById('inquiry-message-modal').style.display = 'none';
    }
</script>

<?php include_once 'includes/footer.php'; ?>
