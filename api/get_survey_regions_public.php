<?php
/**
 * Public API: Get Survey Regions
 * Lấy danh sách khu vực khảo sát (không cần đăng nhập)
 */

require_once __DIR__ . '/db_mysqli.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Get only active regions, ordered by display_order
    $query = "SELECT 
                id,
                region_code,
                region_name,
                display_content,
                sun_hours,
                display_order
              FROM survey_regions 
              WHERE is_active = TRUE
              ORDER BY display_order ASC, id ASC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }
    
    $regions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $regions[] = [
            'id' => (int)$row['id'],
            'region_code' => $row['region_code'],
            'region_name' => $row['region_name'],
            'display_content' => $row['display_content'],
            'sun_hours' => (float)$row['sun_hours'],
            'display_order' => (int)$row['display_order']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'regions' => $regions,
        'count' => count($regions)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi tải danh sách khu vực: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>

