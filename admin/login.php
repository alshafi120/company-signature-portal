<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/helpers.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = __('invalid_request');
    } else {
        require_once __DIR__ . '/../includes/db.php';
        $db = getDB();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = __('field_required');
        } else {
            // Check login attempts
            $attemptKey = 'admin_login_attempts';
            $lockKey = 'admin_locked_until';

            if (isset($_SESSION[$lockKey]) && time() < $_SESSION[$lockKey]) {
                $minutesLeft = ceil(($_SESSION[$lockKey] - time()) / 60);
                $error = sprintf(__('login_locked'), $minutesLeft);
            } else {
                // Reset lock if expired
                if (isset($_SESSION[$lockKey]) && time() >= $_SESSION[$lockKey]) {
                    unset($_SESSION[$attemptKey], $_SESSION[$lockKey]);
                }

                $stmt = $db->prepare('SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1');
                $stmt->execute([':username' => $username]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password_hash'])) {
                    // Successful login
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['_last_regeneration'] = time();

                    // Clear attempts
                    unset($_SESSION[$attemptKey], $_SESSION[$lockKey]);

                    redirect('index.php');
                } else {
                    // Failed login
                    $_SESSION[$attemptKey] = ($_SESSION[$attemptKey] ?? 0) + 1;

                    if ($_SESSION[$attemptKey] >= MAX_LOGIN_ATTEMPTS) {
                        $_SESSION[$lockKey] = time() + LOCKOUT_DURATION;
                        $minutesLeft = ceil(LOCKOUT_DURATION / 60);
                        $error = sprintf(__('login_locked'), $minutesLeft);
                    } else {
                        $error = __('login_error');
                    }
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="login-page">
    <div class="login-card">
        <div class="login-header">
            <div class="logo-icon">
                <i class="fas fa-shield-halved"></i>
            </div>
            <h1><?php echo __('site_title'); ?></h1>
            <p><?php echo __('admin_login'); ?></p>
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

                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> <?php echo __('username'); ?>
                    </label>
                    <input type="text" id="username" name="username" class="form-control"
                           placeholder="<?php echo __('username'); ?>" required
                           value="<?php echo sanitize($_POST['username'] ?? ''); ?>">
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
            <a href="?lang=<?php echo __('switch_lang_code'); ?>" class="lang-link">
                <i class="fas fa-language"></i> <?php echo __('switch_lang'); ?>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
