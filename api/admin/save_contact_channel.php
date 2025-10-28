<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check authentication and permissions
if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = isset($_POST['id']) && $_POST['id'] ? 'edit' : 'create';
if (!hasPermission($conn, 'contacts', $action)) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền ' . ($action === 'edit' ? 'sửa' : 'tạo') . ' kênh liên hệ']);
    exit;
}

try {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $color = trim($_POST['color'] ?? '#16a34a');
    $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
    
    // Validate required fields
    if (empty($name) || empty($content) || empty($category)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc']);
        exit;
    }
    
    // Validate category
    $valid_categories = ['phone', 'zalo', 'email', 'facebook', 'tiktok', 'youtube', 'website'];
    if (!in_array($category, $valid_categories)) {
        echo json_encode(['success' => false, 'message' => 'Danh mục không hợp lệ']);
        exit;
    }
    
    if ($id) {
        // Update existing channel
        $sql = "UPDATE contact_channels SET 
                name = ?, description = ?, content = ?, category = ?, 
                color = ?, display_order = ?, is_active = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssiii', $name, $description, $content, $category, $color, $display_order, $is_active, $id);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật kênh liên hệ thành công',
            'id' => $id
        ]);
    } else {
        // Create new channel
        $sql = "INSERT INTO contact_channels (name, description, content, category, color, display_order, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssii', $name, $description, $content, $category, $color, $display_order, $is_active);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Tạo kênh liên hệ thành công',
            'id' => $conn->insert_id
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lưu kênh liên hệ: ' . $e->getMessage()
    ]);
}

$conn->close();

