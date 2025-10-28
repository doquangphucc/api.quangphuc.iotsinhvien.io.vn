<?php
/**
 * Public API: Get Electricity Prices
 * Lấy bảng giá điện sinh hoạt EVN (không cần đăng nhập)
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_mysqli.php';

try {
    // Get active electricity prices, ordered by tier
    $query = "SELECT id, tier, tier_name, kwh_from, kwh_to, 
              price_no_vat, price_with_vat, effective_date, notes 
              FROM electricity_prices 
              WHERE is_active = 1 
              ORDER BY tier ASC";
    
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
            'notes' => $row['notes']
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

