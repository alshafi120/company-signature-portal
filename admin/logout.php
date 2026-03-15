<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../includes/session.php';

// Preserve language preference
$lang = $_SESSION['lang'] ?? 'ar';

// Destroy session
session_unset();
session_destroy();

// Start new session for language
session_start();
$_SESSION['lang'] = $lang;

header('Location: login.php');
exit;
