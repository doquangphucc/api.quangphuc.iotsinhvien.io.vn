<?php
// Suppress warnings to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? intval($data['id']) : 0;
$required_action = $id > 0 ? 'edit' : 'create';

if (!hasPermission($conn, 'packages', $required_action)) {
    echo json_encode(['success' => false, 'message' => "Bạn không có quyền {$required_action} gói sản phẩm"]);
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
$display_order = isset($data['display_order']) ? intval($data['display_order']) : 0;
$is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;
$items = $data['items'] ?? [];
$highlights = $data['highlights'] ?? [];

// Convert highlights to JSON
$highlights_json = !empty($highlights) ? json_encode($highlights) : null;

// Validation
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Tên gói không được để trống']);
    exit;
}

if ($category_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn danh mục gói']);
    exit;
}

// Price can be 0 if auto-calculated from items, but we'll allow manual override
// if ($price <= 0) {
//     echo json_encode(['success' => false, 'message' => 'Giá gói phải lớn hơn 0']);
//     exit;
// }

// Check for duplicate display_order (except current package)
$check_stmt = $conn->prepare("SELECT id, name FROM packages WHERE display_order = ? AND id != ?");
$check_stmt->bind_param("ii", $display_order, $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
if ($row = $check_result->fetch_assoc()) {
    echo json_encode([
        'success' => false, 
        'message' => "Thứ tự hiển thị {$display_order} đã được sử dụng bởi gói khác: \"{$row['name']}\". Vui lòng chọn thứ tự khác."
    ]);
    exit;
}
$check_stmt->close();

// Start transaction
$conn->begin_transaction();

try {
    if ($id > 0) {
        // Update existing package
        $stmt = $conn->prepare("UPDATE packages SET category_id = ?, name = ?, description = ?, price = ?, badge_text = ?, badge_color = ?, highlights = ?, display_order = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("issdsssiii", $category_id, $name, $description, $price, $badge_text, $badge_color, $highlights_json, $display_order, $is_active, $id);
        $stmt->execute();
        $package_id = $id;
        
        // Delete old items
        $delete_items_stmt = $conn->prepare("DELETE FROM package_items WHERE package_id = ?");
        $delete_items_stmt->bind_param("i", $package_id);
        $delete_items_stmt->execute();
        $delete_items_stmt->close();
    } else {
        // Insert new package
        $stmt = $conn->prepare("INSERT INTO packages (category_id, name, description, price, badge_text, badge_color, highlights, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdsssii", $category_id, $name, $description, $price, $badge_text, $badge_color, $highlights_json, $display_order, $is_active);
        $stmt->execute();
        $package_id = $conn->insert_id;
    }
    $stmt->close();
    
    // Insert package items
    if (!empty($items) && is_array($items)) {
        $item_stmt = $conn->prepare("INSERT INTO package_items (package_id, product_id, item_name, item_description, quantity, price_type, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $item_order = 0;
        foreach ($items as $item) {
            $product_id = isset($item['product_id']) && $item['product_id'] > 0 ? intval($item['product_id']) : null;
            $item_name = $item['item_name'] ?? '';
            $item_description = $item['item_description'] ?? '';
            $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
            $price_type = $item['price_type'] ?? 'market_price';
            $item_order++;
            $item_stmt->bind_param("iissisi", $package_id, $product_id, $item_name, $item_description, $quantity, $price_type, $item_order);
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

