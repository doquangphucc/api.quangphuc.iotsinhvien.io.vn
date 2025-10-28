<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Check permission to view home posts
if (!hasPermission($conn, 'home', 'view')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem quản lý trang chủ']);
    exit;
}

try {
    $sql = "SELECT * FROM home_posts ORDER BY display_order ASC, id DESC";
    $result = $conn->query($sql);
    
    $posts = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Parse features JSON
            $features = [];
            if (!empty($row['features'])) {
                $features = json_decode($row['features'], true) ?? [];
            }
            
            $posts[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'highlight_text' => $row['highlight_text'],
                'highlight_color' => $row['highlight_color'],
                'image_url' => $row['image_url'],
                'image_position' => $row['image_position'],
                'button_text' => $row['button_text'],
                'button_url' => $row['button_url'],
                'button_color' => $row['button_color'],
                'features' => $features,
                'display_order' => (int)$row['display_order'],
                'is_active' => (bool)$row['is_active'],
                'section_id' => $row['section_id'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'posts' => $posts
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy danh sách bài đăng: ' . $e->getMessage()
    ]);
}

$conn->close();

