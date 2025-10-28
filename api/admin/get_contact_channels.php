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

if (!hasPermission($conn, 'contacts', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem kênh liên hệ']);
    exit;
}

try {
    $sql = "SELECT id, name, description, content, category, color, display_order, is_active, created_at, updated_at 
            FROM contact_channels 
            ORDER BY display_order ASC, id ASC";
    
    $result = $conn->query($sql);
    $channels = [];
    
    while ($row = $result->fetch_assoc()) {
        $channels[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'content' => $row['content'],
            'category' => $row['category'],
            'color' => $row['color'],
            'display_order' => (int)$row['display_order'],
            'is_active' => (bool)$row['is_active'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'channels' => $channels
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy danh sách kênh liên hệ: ' . $e->getMessage()
    ]);
}

$conn->close();

