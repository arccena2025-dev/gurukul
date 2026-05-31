<?php
/**
 * ========================================================
 * SECURE MAIN DASHBOARD OVERVIEW & ANALYTICS (GURUKUL)
 * ========================================================
 */

// Include system configurations
require_once '../config/db.php';
require_once 'includes/header.php';

try {
    // 1. Gather Analytics counts from MySQL tables
    $count_gallery = $pdo->query("SELECT COUNT(*) FROM `gallery`")->fetchColumn();
    $count_news    = $pdo->query("SELECT COUNT(*) FROM `news_events` WHERE `type` = 'news'")->fetchColumn();
    $count_events  = $pdo->query("SELECT COUNT(*) FROM `news_events` WHERE `type` = 'event'")->fetchColumn();
    $count_results = $pdo->query("SELECT COUNT(*) FROM `results`")->fetchColumn();
    $count_media   = $pdo->query("SELECT COUNT(*) FROM `media`")->fetchColumn();
    $count_unread  = $pdo->query("SELECT COUNT(*) FROM `contact_submissions` WHERE `is_read` = 0")->fetchColumn();
    
    // 2. Fetch the 5 most recent inquiry submissions
    $stmt_inquiries = $pdo->query("SELECT * FROM `contact_submissions` ORDER BY `submitted_at` DESC LIMIT 5");
    $recent_inquiries = $stmt_inquiries->fetchAll();
    
    // 3. Fetch the 5 most recent media uploads
    $stmt_media = $pdo->query("SELECT * FROM `media` ORDER BY `uploaded_at` DESC LIMIT 5");
    $recent_media = $stmt_media->fetchAll();
} catch (PDOException $e) {
    echo "<div class='alert-banner error'>Database retrieval failure: " . sanitize($e->getMessage()) . "</div>";
    exit();
}
?>

<!-- Grid Analytics Cards Section -->
<div class="analytics-grid">
    <!-- Card 1: Gallery items -->
    <div class="analytics-card">
        <div class="card-metrics">
            <h3>Gallery Assets</h3>
            <div class="metric-value"><?php echo $count_gallery; ?></div>
        </div>
        <div class="card-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                <circle cx="9" cy="9" r="2"></circle>
                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
            </svg>
        </div>
    </div>
    
    <!-- Card 2: News items -->
    <div class="analytics-card">
        <div class="card-metrics">
            <h3>News Articles</h3>
            <div class="metric-value"><?php echo $count_news; ?></div>
        </div>
        <div class="card-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"></path>
                <path d="M18 14h-8M15 18h-5"></path>
                <path d="M10 6h8v4h-8V6Z"></path>
            </svg>
        </div>
    </div>

    <!-- Card 3: Events -->
    <div class="analytics-card">
        <div class="card-metrics">
            <h3>Active Events</h3>
            <div class="metric-value"><?php echo $count_events; ?></div>
        </div>
        <div class="card-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
    </div>

    <!-- Card 4: Results -->
    <div class="analytics-card">
        <div class="card-metrics">
            <h3>Exam Results</h3>
            <div class="metric-value"><?php echo $count_results; ?></div>
        </div>
        <div class="card-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
    </div>

    <!-- Card 5: Media Files -->
    <div class="analytics-card">
        <div class="card-metrics">
            <h3>Library Media</h3>
            <div class="metric-value"><?php echo $count_media; ?></div>
        </div>
        <div class="card-icon-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
        </div>
    </div>

    <!-- Card 6: Contact Submissions -->
    <div class="analytics-card" <?php if ($count_unread > 0) echo 'style="border-color: rgba(217,119,6,0.3); background: rgba(217,119,6,0.03);"'; ?>>
        <div class="card-metrics">
            <h3>Unread Inquiries</h3>
            <div class="metric-value" <?php if ($count_unread > 0) echo 'style="color: var(--accent-light);"'; ?>><?php echo $count_unread; ?></div>
        </div>
        <div class="card-icon-box" <?php if ($count_unread > 0) echo 'style="color: var(--accent-light); background: rgba(217,119,6,0.15);"'; ?>>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
            </svg>
        </div>
    </div>
</div>

<!-- Quick Actions Panel -->
<div class="dashboard-block">
    <div class="block-title">
        <h3>Quick Action Shortcuts</h3>
    </div>
    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
        <a href="homepage.php" class="btn-action btn-theme">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/><polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/></svg>
            <span>Update Homepage Banner</span>
        </a>
        <a href="gallery.php" class="btn-action btn-theme">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
            <span>Upload Gallery Asset</span>
        </a>
        <a href="news_events.php" class="btn-action btn-accent">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5v14"/></svg>
            <span>Add News / Event</span>
        </a>
        <a href="results.php" class="btn-action btn-accent">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <span>Upload Exam Result</span>
        </a>
        <a href="media.php" class="btn-action btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            <span>Media Library Upload</span>
        </a>
    </div>
</div>

<!-- Bottom Split Block: Inquiries & Media -->
<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 32px; margin-bottom: 36px;" class="split-layout">
    
    <!-- 1. Recent Inquiries Submissions -->
    <div class="dashboard-block" style="margin-bottom: 0;">
        <div class="block-title">
            <h3>Recent Student Inquiries</h3>
            <a href="inquiries.php" style="font-size: 0.85rem; color: var(--secondary-light); text-decoration: none; font-weight: 600;">View All Inquiries &rarr;</a>
        </div>
        
        <?php if (empty($recent_inquiries)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 24px;">No inquiries submitted yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Contact Phone</th>
                            <th>Subject Info</th>
                            <th>Status Indicator</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_inquiries as $inquiry): ?>
                            <tr>
                                <td><strong><?php echo sanitize($inquiry['name']); ?></strong></td>
                                <td><?php echo sanitize($inquiry['phone']); ?></td>
                                <td><?php echo sanitize($inquiry['subject']); ?></td>
                                <td>
                                    <?php if ($inquiry['is_read'] == 1): ?>
                                        <span class="badge-status badge-success">Processed</span>
                                    <?php else: ?>
                                        <span class="badge-status badge-warning">Unread</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- 2. Recent Media Assets uploads -->
    <div class="dashboard-block" style="margin-bottom: 0;">
        <div class="block-title">
            <h3>Recently Uploaded Media</h3>
            <a href="media.php" style="font-size: 0.85rem; color: var(--secondary-light); text-decoration: none; font-weight: 600;">Manage Assets &rarr;</a>
        </div>
        
        <?php if (empty($recent_media)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 24px;">No media uploaded yet.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <?php foreach ($recent_media as $media): ?>
                    <div style="display: flex; align-items: center; gap: 16px; background: rgba(255, 255, 255, 0.02); padding: 12px; border-radius: var(--border-radius-sm); border: 1px solid var(--glass-border);">
                        <div style="width: 50px; height: 50px; border-radius: 4px; overflow: hidden; flex-shrink: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                            <?php if (strpos($media['filetype'], 'image') !== false): ?>
                                <img src="../<?php echo $media['filepath']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            <?php endif; ?>
                        </div>
                        <div style="overflow: hidden; flex-grow: 1;">
                            <h4 style="font-size: 0.9rem; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px;" title="<?php echo sanitize($media['filename']); ?>"><?php echo sanitize($media['filename']); ?></h4>
                            <p style="font-size: 0.75rem; color: var(--text-muted);"><?php echo sanitize($media['filetype']); ?> &bull; <?php echo round($media['filesize'] / 1024, 1); ?> KB</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    @media (max-width: 992px) {
        .split-layout {
            grid-template-columns: 1fr !important;
            gap: 24px !important;
        }
    }
</style>

<?php include_once 'includes/footer.php'; ?>
