<?php
/**
 * Admin - Manage Company Access
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

requireAdminLogin();

$db = getDB();
$newCredentials = null;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', __('invalid_request'));
        redirect('companies.php');
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $companyName = trim($_POST['company_name'] ?? '');
            $expirationHours = (int)($_POST['expiration_hours'] ?? 48);

            if (empty($companyName)) {
                setFlash('danger', __('field_required'));
                break;
            }

            // Clamp expiration hours
            $expirationHours = max(TOKEN_MIN_HOURS, min(TOKEN_MAX_HOURS, $expirationHours));

            // Generate credentials
            $username = 'company_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $companyName)) . '_' . random_int(100, 999);
            $password = generatePassword(12);
            $token = generateToken(32);
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expirationHours} hours"));

            $stmt = $db->prepare('INSERT INTO companies_access (company_name, username, password_hash, token, expires_at) VALUES (:name, :username, :password, :token, :expires)');
            $stmt->execute([
                ':name' => $companyName,
                ':username' => $username,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':token' => $token,
                ':expires' => $expiresAt,
            ]);

            // Store credentials temporarily for display
            $newCredentials = [
                'company_name' => $companyName,
                'username' => $username,
                'password' => $password,
                'token' => $token,
                'expires_at' => $expiresAt,
                'link' => rtrim(SITE_URL, '/') . '/signatures.php?token=' . $token,
            ];

            setFlash('success', __('company_added'));
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $db->prepare('DELETE FROM companies_access WHERE id = :id');
                $stmt->execute([':id' => $id]);
                setFlash('success', __('company_deleted'));
            }
            redirect('companies.php');
            break;

        case 'regenerate':
            $id = (int)($_POST['id'] ?? 0);
            $expirationHours = (int)($_POST['expiration_hours'] ?? 48);
            $expirationHours = max(TOKEN_MIN_HOURS, min(TOKEN_MAX_HOURS, $expirationHours));

            if ($id > 0) {
                $password = generatePassword(12);
                $token = generateToken(32);
                $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expirationHours} hours"));

                $stmt = $db->prepare('UPDATE companies_access SET password_hash = :password, token = :token, expires_at = :expires, login_attempts = 0, locked_until = NULL WHERE id = :id');
                $stmt->execute([
                    ':password' => password_hash($password, PASSWORD_DEFAULT),
                    ':token' => $token,
                    ':expires' => $expiresAt,
                    ':id' => $id,
                ]);

                // Get company info for display
                $stmt = $db->prepare('SELECT company_name, username FROM companies_access WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $company = $stmt->fetch();

                if ($company) {
                    $newCredentials = [
                        'company_name' => $company['company_name'],
                        'username' => $company['username'],
                        'password' => $password,
                        'token' => $token,
                        'expires_at' => $expiresAt,
                        'link' => rtrim(SITE_URL, '/') . '/signatures.php?token=' . $token,
                    ];
                }

                setFlash('success', __('company_added'));
            }
            break;
    }
}

// Get all companies
$companies = $db->query('SELECT * FROM companies_access ORDER BY created_at DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-building"></i> <?php echo __('companies'); ?></h1>
    <button class="btn btn-primary" onclick="openModal('addModal')">
        <i class="fas fa-plus"></i> <?php echo __('add_company'); ?>
    </button>
</div>

<?php
$flash = getFlash();
if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type']; ?>">
        <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo sanitize($flash['message']); ?>
    </div>
<?php endif; ?>

<?php if ($newCredentials): ?>
    <div class="card" style="margin-bottom: 24px; border: 2px solid var(--success);">
        <div class="card-header" style="background: #d4edda;">
            <h2><i class="fas fa-key"></i> <?php echo __('credentials_info'); ?></h2>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $currentLang === 'ar' ? 'يرجى نسخ بيانات الدخول الآن. لن تتمكن من رؤية كلمة المرور مرة أخرى.' : 'Please copy these credentials now. You will not be able to see the password again.'; ?>
            </div>
            <div class="credentials-box">
                <div class="cred-item">
                    <span class="cred-label"><?php echo __('company_name'); ?></span>
                    <span class="cred-value"><?php echo sanitize($newCredentials['company_name']); ?></span>
                </div>
                <div class="cred-item">
                    <span class="cred-label"><?php echo __('company_username'); ?></span>
                    <span class="cred-value"><?php echo sanitize($newCredentials['username']); ?></span>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo $newCredentials['username']; ?>', this)">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div class="cred-item">
                    <span class="cred-label"><?php echo __('company_password'); ?></span>
                    <span class="cred-value"><?php echo sanitize($newCredentials['password']); ?></span>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo $newCredentials['password']; ?>', this)">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div class="cred-item">
                    <span class="cred-label"><?php echo __('access_link'); ?></span>
                    <span class="cred-value" style="font-size:0.8rem;"><?php echo sanitize($newCredentials['link']); ?></span>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo $newCredentials['link']; ?>', this)">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div class="cred-item">
                    <span class="cred-label"><?php echo __('expires_at'); ?></span>
                    <span class="cred-value"><?php echo formatDate($newCredentials['expires_at']); ?></span>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($companies)): ?>
            <div class="empty-state">
                <i class="fas fa-building"></i>
                <h3><?php echo __('no_companies'); ?></h3>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo __('company_name'); ?></th>
                            <th><?php echo __('company_username'); ?></th>
                            <th><?php echo __('expires_at'); ?></th>
                            <th><?php echo __('status_label'); ?></th>
                            <th><?php echo __('created_at'); ?></th>
                            <th><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $i => $company): ?>
                            <?php $isExpired = isTokenExpired($company['expires_at']); ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><strong><?php echo sanitize($company['company_name']); ?></strong></td>
                                <td><code><?php echo sanitize($company['username']); ?></code></td>
                                <td><?php echo formatDate($company['expires_at']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $isExpired ? 'expired' : 'valid'; ?>">
                                        <?php echo $isExpired ? __('expired') : __('valid'); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($company['created_at']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <?php if (!$isExpired): ?>
                                            <button class="copy-btn"
                                                    onclick="copyToClipboard('<?php echo rtrim(SITE_URL, '/') . '/signatures.php?token=' . $company['token']; ?>', this)"
                                                    title="<?php echo __('copy_link'); ?>">
                                                <i class="fas fa-link"></i>
                                            </button>
                                        <?php endif; ?>

                                        <form method="POST" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="regenerate">
                                            <input type="hidden" name="id" value="<?php echo $company['id']; ?>">
                                            <input type="hidden" name="expiration_hours" value="48">
                                            <button type="submit" class="btn btn-sm btn-warning"
                                                    title="<?php echo __('regenerate'); ?>">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>

                                        <form method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $company['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

<!-- Add Company Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <h2><?php echo __('add_company'); ?></h2>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label for="company_name"><?php echo __('company_name'); ?></label>
                    <input type="text" id="company_name" name="company_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="expiration_hours"><?php echo __('expiration_hours'); ?> (24-72)</label>
                    <input type="number" id="expiration_hours" name="expiration_hours" class="form-control"
                           value="48" min="24" max="72" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">
                    <?php echo __('cancel'); ?>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key"></i> <?php echo __('generate_credentials'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
