<?php
/**
 * Admin API: Get Electricity Prices
 * Lấy bảng giá điện để quản lý (cần đăng nhập admin)
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
    // Get all electricity prices, ordered by tier
    $query = "SELECT * FROM electricity_prices ORDER BY tier ASC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }
    
    $prices = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $prices[] = [
            'id' => (int)$row['id'],
            'tier' => (int)$row['tier'],
            'tier_name' => $row['tier_name'],
            'kwh_from' => (int)$row['kwh_from'],
            'kwh_to' => $row['kwh_to'] ? (int)$row['kwh_to'] : null,
            'price_no_vat' => (float)$row['price_no_vat'],
            'price_with_vat' => (float)$row['price_with_vat'],
            'effective_date' => $row['effective_date'],
            'is_active' => (bool)$row['is_active'],
            'notes' => $row['notes'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'prices' => $prices,
        'count' => count($prices)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi tải bảng giá điện: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>

