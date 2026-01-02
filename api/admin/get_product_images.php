<?php
// Get all product images from assets/img/products/
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/security_middleware.php';

// Apply admin security (WAF, rate limiting, no CSRF for GET)
applySecurityMiddleware([
    'csrf' => false,
    'waf' => true,
    'rate_limit' => true,
    'rate_limit_requests' => 60,
    'rate_limit_window' => 60
]);

if (!hasPermission($conn, 'products', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem ảnh sản phẩm']);
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

