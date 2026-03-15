<?php
/**
 * Helper Functions
 */

/**
 * Sanitize input string
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to URL
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Set flash message
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Generate secure random token
 */
function generateToken(int $length = 64): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Generate secure random password
 */
function generatePassword(int $length = 12): string
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    $password = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }
    return $password;
}

/**
 * Get client IP address
 */
function getClientIP(): string
{
    $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = explode(',', $_SERVER[$header])[0];
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * Format datetime for display
 */
function formatDate(string $datetime, string $format = 'Y-m-d H:i'): string
{
    $date = new DateTime($datetime);
    return $date->format($format);
}

/**
 * Check if a token link has expired
 */
function isTokenExpired(string $expiresAt): bool
{
    return new DateTime() > new DateTime($expiresAt);
}

/**
 * Upload signature image securely
 */
function uploadSignatureImage(array $file): ?string
{
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return null;
    }

    // Check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return null;
    }

    // Check MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
        return null;
    }

    // Verify it's actually a valid image
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return null;
    }

    // Generate unique filename
    $filename = 'sig_' . bin2hex(random_bytes(16)) . '.png';
    $destination = UPLOAD_DIR . $filename;

    // Move file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }

    return null;
}

/**
 * Delete signature image
 */
function deleteSignatureImage(string $filename): bool
{
    $filepath = UPLOAD_DIR . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Log company access
 */
function logAccess(PDO $db, string $companyName, string $ipAddress): void
{
    $stmt = $db->prepare('INSERT INTO access_logs (company_name, ip_address, login_time) VALUES (:company, :ip, NOW())');
    $stmt->execute([
        ':company' => $companyName,
        ':ip' => $ipAddress,
    ]);
}
