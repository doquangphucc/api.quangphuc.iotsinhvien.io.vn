<?php
/**
 * Public API: Get Home Posts
 * Lấy danh sách bài đăng trang chủ (không cần đăng nhập)
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_mysqli.php';

try {
    // Get only active posts, ordered by display_order
    $query = "SELECT id, title, description, highlight_text, highlight_color, 
              image_url, image_position, button_text, button_url, button_color, 
              features, display_order, section_id 
              FROM home_posts 
              WHERE is_active = 1 
              ORDER BY display_order ASC, id DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
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
            'section_id' => $row['section_id']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'posts' => $posts,
        'count' => count($posts)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi tải bài đăng: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>

