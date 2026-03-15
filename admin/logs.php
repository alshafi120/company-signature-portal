<?php
/**
 * Admin - Access Logs
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

requireAdminLogin();

$db = getDB();

// Handle clear logs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', __('invalid_request'));
        redirect('logs.php');
    }

    if (($_POST['action'] ?? '') === 'clear') {
        $db->exec('DELETE FROM access_logs');
        setFlash('success', __('logs_cleared'));
        redirect('logs.php');
    }
}

// Get all logs
$logs = $db->query('SELECT * FROM access_logs ORDER BY login_time DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-clipboard-list"></i> <?php echo __('access_logs'); ?></h1>
    <?php if (!empty($logs)): ?>
        <form method="POST" onsubmit="return confirm('<?php echo __('confirm_delete'); ?>')">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> <?php echo __('clear_logs'); ?>
            </button>
        </form>
    <?php endif; ?>
</div>

<?php
$flash = getFlash();
if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type']; ?>">
        <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo sanitize($flash['message']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3><?php echo __('no_logs'); ?></h3>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo __('log_company'); ?></th>
                            <th><?php echo __('log_ip'); ?></th>
                            <th><?php echo __('log_time'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $i => $log): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><strong><?php echo sanitize($log['company_name']); ?></strong></td>
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
