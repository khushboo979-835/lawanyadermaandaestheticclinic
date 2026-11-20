<?php
/**
 * Admin: AJAX Image Upload Handler
 * Lawanya Derma & Aesthetic Clinic
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['upload'])) {
    echo json_encode(['error' => ['message' => 'No file uploaded.']]);
    exit;
}

$file = $_FILES['upload'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => ['message' => 'Upload error code: ' . $file['error']]]);
    exit;
}

if (!in_array($fileExt, $allowedExtensions)) {
    echo json_encode(['error' => ['message' => 'Invalid file extension. Only JPG, PNG, WEBP, GIF allowed.']]);
    exit;
}

if ($file['size'] > 6 * 1024 * 1024) {
    echo json_encode(['error' => ['message' => 'File exceeds 6MB limit.']]);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['error' => ['message' => 'File is not a valid image.']]);
    exit;
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$newFileName = 'img_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $fileExt;
$targetPath = UPLOAD_DIR . $newFileName;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    $url = BASE_URL . 'uploads/blogs/' . $newFileName;
    echo json_encode([
        'url' => $url,
        'uploaded' => 1,
        'fileName' => $newFileName
    ]);
} else {
    echo json_encode(['error' => ['message' => 'Failed to save file on server.']]);
}
