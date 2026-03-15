<?php
/**
 * Secure Signature Image Server
 * Prevents direct access to signature images
 * Only serves images to authenticated company users
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/db.php';

// Verify company is logged in
if (!isCompanyLoggedIn()) {
    http_response_code(403);
    exit('Access denied');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit('Not found');
}

$db = getDB();

// Get signatory info
$stmt = $db->prepare("SELECT * FROM signatories WHERE id = :id AND status = 'active' LIMIT 1");
$stmt->execute([':id' => $id]);
$signatory = $stmt->fetch();

if (!$signatory) {
    http_response_code(404);
    exit('Not found');
}

$filepath = UPLOAD_DIR . $signatory['signature_image'];

if (!file_exists($filepath)) {
    http_response_code(404);
    exit('File not found');
}

// Verify it's actually a PNG image
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($filepath);

if ($mimeType !== 'image/png') {
    http_response_code(403);
    exit('Invalid file type');
}

// Set headers
header('Content-Type: image/png');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

// Download mode
if (isset($_GET['download'])) {
    $downloadName = preg_replace('/[^a-zA-Z0-9_\-\x{0600}-\x{06FF}]/u', '_', $signatory['name']) . '_signature.png';
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
}

// Serve file
readfile($filepath);
exit;
