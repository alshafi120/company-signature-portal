<?php
/**
 * Secure Session Handler
 */

if (session_status() === PHP_SESSION_NONE) {
    // Secure session settings
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');

    session_start();
}

/**
 * Regenerate session ID periodically for security
 */
function regenerateSession(): void
{
    if (!isset($_SESSION['_last_regeneration'])) {
        $_SESSION['_last_regeneration'] = time();
    } elseif (time() - $_SESSION['_last_regeneration'] > 300) {
        session_regenerate_id(true);
        $_SESSION['_last_regeneration'] = time();
    }
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Check if company is logged in
 */
function isCompanyLoggedIn(): bool
{
    return isset($_SESSION['company_id']) && isset($_SESSION['company_logged_in']) && $_SESSION['company_logged_in'] === true;
}

/**
 * Require admin login
 */
function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    regenerateSession();
}

/**
 * Require company login
 */
function requireCompanyLogin(): void
{
    if (!isCompanyLoggedIn()) {
        header('Location: ../signatures.php?error=login_required');
        exit;
    }
    regenerateSession();
}

/**
 * Generate CSRF token
 */
function generateCSRFToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token field HTML
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}
