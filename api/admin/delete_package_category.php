<?php
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/security_middleware.php';

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Apply admin security (WAF, rate limiting)
applySecurityMiddleware([
    'csrf' => false,  // TODO: Enable after frontend update
    'waf' => true,
    'rate_limit' => true,
    'rate_limit_requests' => 30,
    'rate_limit_window' => 60,
    'audit' => true
]);

if (!hasPermission($conn, 'packages', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa gói sản phẩm']);
    exit;
}

$data = json_decode(getRawRequestBody(), true);
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

// Check if package category has packages
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM packages WHERE category_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Không thể xóa danh mục gói đang có gói sản phẩm']);
    exit;
}

// Get category logo URL before deleting
$stmt = $conn->prepare("SELECT logo_url FROM package_categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM package_categories WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Delete logo file if exists
    if ($category && !empty($category['logo_url'])) {
        $logo_path = $category['logo_url'];
        
        // Try relative path first (if starts with assets/)
        if (strpos($logo_path, 'assets/') === 0) {
            $logo_file = __DIR__ . '/../../' . $logo_path;
        } else {
            // Try absolute path
            $logo_file = __DIR__ . '/../../' . ltrim($logo_path, '/');
        }
        
        if (file_exists($logo_file)) {
            unlink($logo_file);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Xóa danh mục gói thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>

