<?php
/**
 * Company Logout
 */

require_once __DIR__ . '/includes/session.php';

// Preserve language preference
$lang = $_SESSION['lang'] ?? 'ar';

// Clear company session data
unset(
    $_SESSION['company_id'],
    $_SESSION['company_logged_in'],
    $_SESSION['company_token'],
    $_SESSION['company_name']
);

// Start fresh for language
$_SESSION['lang'] = $lang;

header('Location: signatures.php');
exit;
