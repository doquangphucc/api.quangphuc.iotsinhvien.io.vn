<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id = isset($data['id']) ? intval($data['id']) : 0;
$category_id = intval($data['category_id'] ?? 0);
$name = $data['name'] ?? '';
$description = $data['description'] ?? '';
$price = floatval($data['price'] ?? 0);
$badge_text = $data['badge_text'] ?? '';
$badge_color = $data['badge_color'] ?? 'blue';
$savings_per_month = $data['savings_per_month'] ?? '';
$payback_period = $data['payback_period'] ?? '';
$display_order = isset($data['display_order']) ? intval($data['display_order']) : 0;
$is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;
$items = $data['items'] ?? [];

// Validation
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Tên gói không được để trống']);
    exit;
}

if ($category_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn danh mục gói']);
    exit;
}

if ($price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Giá gói phải lớn hơn 0']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    if ($id > 0) {
        // Update existing package
        $stmt = $conn->prepare("UPDATE packages SET category_id = ?, name = ?, description = ?, price = ?, badge_text = ?, badge_color = ?, savings_per_month = ?, payback_period = ?, display_order = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("issdssssiii", $category_id, $name, $description, $price, $badge_text, $badge_color, $savings_per_month, $payback_period, $display_order, $is_active, $id);
        $stmt->execute();
        $package_id = $id;
        
        // Delete old items
        $delete_items_stmt = $conn->prepare("DELETE FROM package_items WHERE package_id = ?");
        $delete_items_stmt->bind_param("i", $package_id);
        $delete_items_stmt->execute();
        $delete_items_stmt->close();
    } else {
        // Insert new package
        $stmt = $conn->prepare("INSERT INTO packages (category_id, name, description, price, badge_text, badge_color, savings_per_month, payback_period, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdssssii", $category_id, $name, $description, $price, $badge_text, $badge_color, $savings_per_month, $payback_period, $display_order, $is_active);
        $stmt->execute();
        $package_id = $conn->insert_id;
    }
    $stmt->close();
    
    // Insert package items
    if (!empty($items) && is_array($items)) {
        $item_stmt = $conn->prepare("INSERT INTO package_items (package_id, item_name, item_description, display_order) VALUES (?, ?, ?, ?)");
        $item_order = 0;
        foreach ($items as $item) {
            $item_name = $item['item_name'] ?? $item;
            $item_description = $item['item_description'] ?? '';
            $item_order++;
            $item_stmt->bind_param("issi", $package_id, $item_name, $item_description, $item_order);
            $item_stmt->execute();
        }
        $item_stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Lưu gói sản phẩm thành công',
        'package_id' => $package_id
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

