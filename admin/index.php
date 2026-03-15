<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

requireAdminLogin();

$db = getDB();

// Get statistics
$totalSignatories = $db->query("SELECT COUNT(*) FROM signatories")->fetchColumn();
$activeSignatories = $db->query("SELECT COUNT(*) FROM signatories WHERE status = 'active'")->fetchColumn();
$totalCompanies = $db->query("SELECT COUNT(*) FROM companies_access")->fetchColumn();
$activeLinks = $db->query("SELECT COUNT(*) FROM companies_access WHERE expires_at > NOW()")->fetchColumn();

// Get recent access logs
$recentLogs = $db->query("SELECT * FROM access_logs ORDER BY login_time DESC LIMIT 10")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> <?php echo __('dashboard'); ?></h1>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-pen-nib"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $totalSignatories; ?></h3>
            <p><?php echo __('total_signatories'); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $activeSignatories; ?></h3>
            <p><?php echo __('active_signatories'); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $totalCompanies; ?></h3>
            <p><?php echo __('total_companies'); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-link"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $activeLinks; ?></h3>
            <p><?php echo __('active_links'); ?></p>
        </div>
    </div>
</div>

<!-- Recent Access Logs -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-clock"></i> <?php echo __('recent_access'); ?></h2>
        <a href="logs.php" class="btn btn-sm btn-outline"><?php echo __('access_logs'); ?></a>
    </div>
    <div class="card-body">
        <?php if (empty($recentLogs)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3><?php echo __('no_logs'); ?></h3>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th><?php echo __('log_company'); ?></th>
                            <th><?php echo __('log_ip'); ?></th>
                            <th><?php echo __('log_time'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td><?php echo sanitize($log['company_name']); ?></td>
                                <td><code><?php echo sanitize($log['ip_address']); ?></code></td>
                                <td><?php echo formatDate($log['login_time']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
