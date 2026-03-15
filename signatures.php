<?php
/**
 * Company Signature Access Page
 * Handles token verification and company login
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

$db = getDB();
$error = '';
$token = $_GET['token'] ?? $_POST['token'] ?? $_SESSION['company_token'] ?? '';

// If already logged in, show signatures
if (isCompanyLoggedIn() && !empty($_SESSION['company_token'])) {
    // Verify token is still valid
    $stmt = $db->prepare('SELECT * FROM companies_access WHERE token = :token AND expires_at > NOW() LIMIT 1');
    $stmt->execute([':token' => $_SESSION['company_token']]);
    $company = $stmt->fetch();

    if ($company) {
        // Show signatures page
        showSignaturesPage($db, $company);
        exit;
    } else {
        // Token expired, clear session
        unset($_SESSION['company_id'], $_SESSION['company_logged_in'], $_SESSION['company_token'], $_SESSION['company_name']);
        $error = __('token_expired');
    }
}

// No token provided
if (empty($token)) {
    showErrorPage(__('token_invalid'));
    exit;
}

// Verify token exists and is not expired
$stmt = $db->prepare('SELECT * FROM companies_access WHERE token = :token LIMIT 1');
$stmt->execute([':token' => $token]);
$company = $stmt->fetch();

if (!$company) {
    showErrorPage(__('token_invalid'));
    exit;
}

if (isTokenExpired($company['expires_at'])) {
    showErrorPage(__('token_expired'));
    exit;
}

// Check if locked
if ($company['locked_until'] && !isTokenExpired($company['locked_until'])) {
    $minutesLeft = ceil((strtotime($company['locked_until']) - time()) / 60);
    $error = sprintf(__('login_locked'), $minutesLeft);
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = __('invalid_request');
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = __('field_required');
        } else {
            // Check lockout
            if ($company['locked_until'] && !isTokenExpired($company['locked_until'])) {
                $minutesLeft = ceil((strtotime($company['locked_until']) - time()) / 60);
                $error = sprintf(__('login_locked'), $minutesLeft);
            } else {
                // Verify credentials
                if ($username === $company['username'] && password_verify($password, $company['password_hash'])) {
                    // Successful login
                    session_regenerate_id(true);
                    $_SESSION['company_id'] = $company['id'];
                    $_SESSION['company_logged_in'] = true;
                    $_SESSION['company_token'] = $company['token'];
                    $_SESSION['company_name'] = $company['company_name'];
                    $_SESSION['_last_regeneration'] = time();

                    // Reset login attempts
                    $stmt = $db->prepare('UPDATE companies_access SET login_attempts = 0, locked_until = NULL WHERE id = :id');
                    $stmt->execute([':id' => $company['id']]);

                    // Log access
                    logAccess($db, $company['company_name'], getClientIP());

                    // Redirect to same page to show signatures
                    redirect('signatures.php?token=' . $token);
                } else {
                    // Failed login
                    $newAttempts = $company['login_attempts'] + 1;
                    $lockUntil = null;

                    if ($newAttempts >= $company['max_login_attempts']) {
                        $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
                        $minutesLeft = ceil(LOCKOUT_DURATION / 60);
                        $error = sprintf(__('login_locked'), $minutesLeft);
                    } else {
                        $error = __('login_error');
                    }

                    $stmt = $db->prepare('UPDATE companies_access SET login_attempts = :attempts, locked_until = :locked WHERE id = :id');
                    $stmt->execute([
                        ':attempts' => $newAttempts,
                        ':locked' => $lockUntil,
                        ':id' => $company['id'],
                    ]);
                }
            }
        }
    }
}

// Show login page
showLoginPage($token, $error, $company['company_name']);

/**
 * Show the company login page
 */
function showLoginPage(string $token, string $error, string $companyName): void
{
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="login-page">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h1><?php echo __('site_title'); ?></h1>
                <p><?php echo sanitize($companyName); ?> - <?php echo __('company_login'); ?></p>
            </div>

            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo sanitize($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="token" value="<?php echo sanitize($token); ?>">

                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> <?php echo __('username'); ?>
                        </label>
                        <input type="text" id="username" name="username" class="form-control"
                               placeholder="<?php echo __('username'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> <?php echo __('password'); ?>
                        </label>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="<?php echo __('password'); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-sign-in-alt"></i> <?php echo __('login_btn'); ?>
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <a href="?token=<?php echo sanitize($token); ?>&lang=<?php echo __('switch_lang_code'); ?>" class="lang-link">
                    <i class="fas fa-language"></i> <?php echo __('switch_lang'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
}

/**
 * Show the signatures page
 */
function showSignaturesPage(PDO $db, array $company): void
{
    // Get active signatories
    $signatories = $db->query("SELECT * FROM signatories WHERE status = 'active' ORDER BY created_at ASC")->fetchAll();

    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="signatures-page">
        <div class="signatures-header">
            <div class="container">
                <h1><i class="fas fa-shield-halved"></i> <?php echo __('authorized_signatories_title'); ?></h1>
                <div class="header-actions">
                    <a href="?token=<?php echo sanitize($company['token']); ?>&lang=<?php echo __('switch_lang_code'); ?>"
                       class="btn btn-sm btn-outline" style="color:#fff;border-color:rgba(255,255,255,0.5);">
                        <i class="fas fa-language"></i> <?php echo __('switch_lang'); ?>
                    </a>
                    <a href="company_logout.php" class="btn btn-sm btn-danger">
                        <i class="fas fa-sign-out-alt"></i> <?php echo __('logout'); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="signatures-notice">
                <i class="fas fa-check-circle"></i>
                <?php echo __('verification_notice'); ?>
                &mdash;
                <?php echo __('session_expires'); ?>: <?php echo formatDate($company['expires_at']); ?>
            </div>

            <?php if (empty($signatories)): ?>
                <div class="empty-state" style="padding: 80px 20px;">
                    <i class="fas fa-pen-nib"></i>
                    <h3><?php echo __('no_signatories'); ?></h3>
                </div>
            <?php else: ?>
                <div class="signatures-grid">
                    <?php foreach ($signatories as $sig): ?>
                        <div class="signature-card">
                            <div class="signature-card-header">
                                <h3><?php echo sanitize($sig['name']); ?></h3>
                                <p><i class="fas fa-briefcase"></i> <?php echo sanitize($sig['position']); ?></p>
                            </div>

                            <div class="signature-image-wrapper">
                                <div class="watermark-overlay"><?php echo __('company_seal'); ?></div>
                                <img src="serve_signature.php?id=<?php echo $sig['id']; ?>"
                                     alt="<?php echo sanitize($sig['name']); ?>"
                                     loading="lazy">
                            </div>

                            <div class="signature-card-footer">
                                <div class="signature-valid-badge">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo __('signature_valid'); ?>
                                </div>
                                <div style="margin-left:auto; display:flex; gap:8px;">
                                    <button class="btn btn-sm btn-info"
                                            onclick="viewSignature('serve_signature.php?id=<?php echo $sig['id']; ?>', '<?php echo sanitize($sig['name']); ?>', '<?php echo sanitize($sig['position']); ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="serve_signature.php?id=<?php echo $sig['id']; ?>&download=1"
                                       class="btn btn-sm btn-primary" download>
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
}

/**
 * Show error page
 */
function showErrorPage(string $message): void
{
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="login-page">
        <div class="login-card">
            <div class="login-header" style="background: linear-gradient(135deg, #c0392b, #e74c3c);">
                <div class="logo-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1><?php echo __('error_403'); ?></h1>
                <p><?php echo sanitize($message); ?></p>
            </div>
            <div class="login-body" style="text-align:center;">
                <p style="color:var(--gray-600); margin-bottom:20px;">
                    <?php echo $message; ?>
                </p>
                <a href="?lang=<?php echo __('switch_lang_code'); ?>" class="lang-link">
                    <i class="fas fa-language"></i> <?php echo __('switch_lang'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
}
