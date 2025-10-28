<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

// Check authentication and permissions
if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!hasPermission($conn, 'contacts', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa kênh liên hệ']);
    exit;
}

try {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        exit;
    }
    
    $sql = "DELETE FROM contact_channels WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Xóa kênh liên hệ thành công'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy kênh liên hệ'
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi xóa kênh liên hệ: ' . $e->getMessage()
    ]);
}

$conn->close();

