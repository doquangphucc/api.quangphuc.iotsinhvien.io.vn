<?php
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

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!hasPermission($conn, 'promotions', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem khuyến mãi']);
    exit;
}

try {
    $sql = "SELECT id, title, image_url, target_link, target_pages, is_active, created_at, updated_at
            FROM promotions
            ORDER BY is_active DESC, updated_at DESC, id DESC";
    $result = $conn->query($sql);

    $promotions = [];
    while ($row = $result->fetch_assoc()) {
        $pages = json_decode($row['target_pages'] ?? '[]', true);
        if (!is_array($pages)) {
            $pages = [];
        }

        $promotions[] = [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'image_url' => $row['image_url'],
            'target_link' => $row['target_link'],
            'target_pages' => $pages,
            'is_active' => (bool) $row['is_active'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'promotions' => $promotions
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải danh sách khuyến mãi: ' . $e->getMessage()
    ]);
}

$conn->close();

