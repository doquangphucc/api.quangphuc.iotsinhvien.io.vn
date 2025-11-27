<?php
// Add or update product
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = isset($data['id']) ? intval($data['id']) : 0;
$required_action = $id > 0 ? 'edit' : 'create';

if (!hasPermission($conn, 'products', $required_action)) {
    echo json_encode(['success' => false, 'message' => "Bạn không có quyền {$required_action} sản phẩm"]);
    exit;
}
$category_id = intval($data['category_id'] ?? 0);
$title = $data['title'] ?? '';
$market_price = floatval($data['market_price'] ?? 0);
$category_price = !empty($data['category_price']) ? floatval($data['category_price']) : null;
$technical_description = $data['technical_description'] ?? '';
$image_url = $data['image_url'] ?? '';
$is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;
// Lấy display_order từ request - nếu không có hoặc = 0, sẽ được xử lý sau
$display_order_input = isset($data['display_order']) ? intval($data['display_order']) : 0;

// Validation
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Tiêu đề sản phẩm không được để trống']);
    exit;
}

if ($category_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn danh mục']);
    exit;
}

if ($market_price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Giá thị trường phải lớn hơn 0']);
    exit;
}

// Xử lý display_order
$display_order = 0;
if ($display_order_input > 0) {
    // Người dùng đã nhập giá trị hợp lệ, dùng giá trị đó
    $display_order = $display_order_input;
} else {
    // Không có giá trị hợp lệ từ người dùng
    if ($id > 0) {
        // Khi sửa: giữ nguyên giá trị cũ nếu có
        $oldOrderStmt = $conn->prepare("SELECT display_order FROM products WHERE id = ?");
        $oldOrderStmt->bind_param("i", $id);
        $oldOrderStmt->execute();
        $oldOrderResult = $oldOrderStmt->get_result();
        if ($oldOrderRow = $oldOrderResult->fetch_assoc()) {
            $oldOrderValue = intval($oldOrderRow['display_order'] ?? 0);
            $display_order = $oldOrderValue > 0 ? $oldOrderValue : 0;
        }
        $oldOrderStmt->close();
    }
    
    // Nếu vẫn không có giá trị hợp lệ (thêm mới hoặc giá trị cũ = 0), tính mới
    if ($display_order < 1) {
        $maxOrderStmt = $conn->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM products WHERE category_id = ?");
        $maxOrderStmt->bind_param("i", $category_id);
        $maxOrderStmt->execute();
        $maxOrderResult = $maxOrderStmt->get_result();
        if ($maxOrderRow = $maxOrderResult->fetch_assoc()) {
            $display_order = intval($maxOrderRow['next_order']);
        } else {
            $display_order = 1;
        }
        $maxOrderStmt->close();
    }
}

// Bắt đầu transaction
$conn->begin_transaction();

try {
    $old_display_order = null;
    
    if ($id > 0) {
        // Lấy display_order cũ của sản phẩm đang sửa
        $oldOrderStmt = $conn->prepare("SELECT display_order FROM products WHERE id = ?");
        $oldOrderStmt->bind_param("i", $id);
        $oldOrderStmt->execute();
        $oldOrderResult = $oldOrderStmt->get_result();
        if ($oldOrderRow = $oldOrderResult->fetch_assoc()) {
            $old_display_order = intval($oldOrderRow['display_order']);
        }
        $oldOrderStmt->close();
        
        // Xử lý logic tự động tăng/giảm thứ tự
        if ($old_display_order !== null) {
            if ($display_order < $old_display_order) {
                // Di chuyển lên trên: tăng display_order của các sản phẩm từ display_order đến old_display_order-1
                $shiftStmt = $conn->prepare("UPDATE products SET display_order = display_order + 1 WHERE category_id = ? AND display_order >= ? AND display_order < ? AND id != ?");
                $shiftStmt->bind_param("iiii", $category_id, $display_order, $old_display_order, $id);
                $shiftStmt->execute();
                $shiftStmt->close();
            } elseif ($display_order > $old_display_order) {
                // Di chuyển xuống dưới: giảm display_order của các sản phẩm từ old_display_order+1 đến display_order
                $shiftStmt = $conn->prepare("UPDATE products SET display_order = display_order - 1 WHERE category_id = ? AND display_order > ? AND display_order <= ? AND id != ?");
                $shiftStmt->bind_param("iiii", $category_id, $old_display_order, $display_order, $id);
                $shiftStmt->execute();
                $shiftStmt->close();
            }
        }
        
        // Update existing product
        $stmt = $conn->prepare("UPDATE products SET category_id = ?, title = ?, market_price = ?, category_price = ?, technical_description = ?, image_url = ?, is_active = ?, display_order = ? WHERE id = ?");
        $stmt->bind_param("isddssiii", $category_id, $title, $market_price, $category_price, $technical_description, $image_url, $is_active, $display_order, $id);
    } else {
        // Thêm mới: tăng display_order của tất cả sản phẩm có display_order >= display_order
        $shiftStmt = $conn->prepare("UPDATE products SET display_order = display_order + 1 WHERE category_id = ? AND display_order >= ?");
        $shiftStmt->bind_param("ii", $category_id, $display_order);
        $shiftStmt->execute();
        $shiftStmt->close();
        
        // Insert new product
        $stmt = $conn->prepare("INSERT INTO products (category_id, title, market_price, category_price, technical_description, image_url, is_active, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdssii", $category_id, $title, $market_price, $category_price, $technical_description, $image_url, $is_active, $display_order);
    }

    if (!$stmt->execute()) {
        throw new Exception('Lỗi khi lưu sản phẩm: ' . $conn->error);
    }
    
    $product_id = $id > 0 ? $id : $conn->insert_id;
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Lưu sản phẩm thành công',
        'product_id' => $product_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction nếu có lỗi
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>

