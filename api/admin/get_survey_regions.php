<?php
/**
 * Admin API: Get Survey Regions
 * Lấy danh sách khu vực khảo sát (cần đăng nhập admin)
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');

if (!hasPermission($conn, 'survey', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

try {
    // Get all survey regions, ordered by display_order
    $query = "SELECT * FROM survey_regions ORDER BY display_order ASC, id ASC";
    
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
            'display_order' => (int)$row['display_order'],
            'is_active' => (bool)$row['is_active'],
            'notes' => $row['notes'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
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

