<?php
/**
 * Application Configuration
 */

// Site settings
define('SITE_NAME', 'Company Signature Portal');
define('SITE_URL', 'http://localhost/company-signature-portal');
define('DEFAULT_LANG', 'ar');

// Security settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes in seconds
define('TOKEN_MIN_HOURS', 24);
define('TOKEN_MAX_HOURS', 72);
define('SESSION_LIFETIME', 3600); // 1 hour

// Upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/signatures/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['png']);
define('ALLOWED_MIME_TYPES', ['image/png']);
