<?php
// Get all product images from assets/img/products/
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$images_dir = __DIR__ . '/../../assets/img/products/';
$images = [];

if (is_dir($images_dir)) {
    $files = scandir($images_dir);
    foreach ($files as $file) {
        // Skip . and .. and hidden files and README
        if ($file !== '.' && $file !== '..' && $file[0] !== '.' && $file !== 'README.md') {
            // Check if it's an image file
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $images[] = [
                    'filename' => $file,
                    'path' => '/assets/img/products/' . $file,
                    'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/assets/img/products/' . $file
                ];
            }
        }
    }
}

// Sort by filename descending (newest first)
usort($images, function($a, $b) {
    return strcmp($b['filename'], $a['filename']);
});

echo json_encode([
    'success' => true,
    'images' => $images,
    'count' => count($images)
]);

