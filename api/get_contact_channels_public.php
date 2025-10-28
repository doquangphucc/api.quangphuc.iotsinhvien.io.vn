<?php
// Public API to get active contact channels
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_mysqli.php';

try {
    $sql = "SELECT id, name, description, content, category, color, display_order 
            FROM contact_channels 
            WHERE is_active = 1 
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
            'display_order' => (int)$row['display_order']
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

