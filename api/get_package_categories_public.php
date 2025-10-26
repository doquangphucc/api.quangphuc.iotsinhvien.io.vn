<?php
require_once __DIR__ . '/db_mysqli.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT id, name, logo_url, badge_text, badge_color, display_order, is_active 
            FROM package_categories 
            WHERE is_active = 1
            ORDER BY display_order ASC, id ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Lỗi query: ' . $conn->error);
    }
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        // Fix image URL path for HTML pages in /html/ subdirectory
        $logoUrl = $row['logo_url'];
        if ($logoUrl && !str_starts_with($logoUrl, 'http')) {
            $logoUrl = '../' . $logoUrl;
        }
        
        $categories[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'logo_url' => $logoUrl,
            'badge_text' => $row['badge_text'],
            'badge_color' => $row['badge_color']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
