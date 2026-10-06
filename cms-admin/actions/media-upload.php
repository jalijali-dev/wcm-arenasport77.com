<?php
declare(strict_types=1);

// AJAX endpoint: upload ONE image into the Media Library (used by the drop-zone
// in includes/tinymce-media-picker.php). auth.php enforces login + CSRF
// (token via X-CSRF-Token header); validation lives in cms_handle_media_upload().
require_once __DIR__ . '/../includes/auth.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/schema-guard.php';

header('Content-Type: application/json; charset=utf-8');

$mu_fail = static function (string $message, int $status = 400): void {
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $mu_fail('Method not allowed.', 405);
}

$file = $_FILES['media_file'] ?? null;
if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    $mu_fail('No file received.');
}
$err = (int) $file['error'];
if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
    $mu_fail('File exceeds the 5 MB limit for this file type.');
}
if ($err !== UPLOAD_ERR_OK) {
    $mu_fail('File upload failed (error code ' . $err . ').');
}

try {
    $saved = cms_handle_media_upload(
        (string) ($file['tmp_name'] ?? ''),
        (string) ($file['name'] ?? ''),
        (int) ($file['size'] ?? 0)
    );
} catch (RuntimeException $e) {
    $mu_fail($e->getMessage());
}

// The picker only lists images; PDFs belong in media-library.php.
if ($saved['file_type'] !== 'image') {
    @unlink(CMS_PROJECT_ROOT . $saved['file_path']);
    $mu_fail('Only image files can be uploaded here.');
}

try {
    $insert = $pdo->prepare(
        'INSERT INTO media_library (file_name, file_path, file_type, mime_type, file_size_kb, is_active, created_at, updated_at)
         VALUES (:file_name, :file_path, :file_type, :mime_type, :file_size_kb, 1, NOW(), NOW())'
    );
    $insert->execute($saved);
    $newId = (int) $pdo->lastInsertId();
} catch (PDOException $e) {
    @unlink(CMS_PROJECT_ROOT . $saved['file_path']);
    $mu_fail('Could not register the file in the Media Library.', 500);
}

$w = 0;
$h = 0;
$dim = @getimagesize(CMS_PROJECT_ROOT . $saved['file_path']);
if (is_array($dim) && $dim[0] > 0 && $dim[1] > 0) {
    $w = (int) $dim[0];
    $h = (int) $dim[1];
}

echo json_encode([
    'ok'        => true,
    'id'        => $newId,
    'file_name' => $saved['file_name'],
    'file_path' => $saved['file_path'],
    'url'       => app_asset_preview_url($saved['file_path']),
    'mime_type' => $saved['mime_type'],
    'width'     => $w,
    'height'    => $h,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
