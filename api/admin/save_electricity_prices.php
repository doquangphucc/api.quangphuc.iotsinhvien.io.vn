<?php
/**
 * Admin API: Save Electricity Prices
 * Lưu/Cập nhật bảng giá điện (cần đăng nhập admin)
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['prices'])) {
        throw new Exception('Dữ liệu không hợp lệ');
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    // Update each price tier
    foreach ($data['prices'] as $price) {
        $id = (int)$price['id'];
        $tier = (int)$price['tier'];
        $tier_name = mysqli_real_escape_string($conn, $price['tier_name']);
        $kwh_from = (int)$price['kwh_from'];
        $kwh_to = isset($price['kwh_to']) && $price['kwh_to'] !== null ? (int)$price['kwh_to'] : null;
        $price_no_vat = (float)$price['price_no_vat'];
        $price_with_vat = (float)$price['price_with_vat'];
        $effective_date = mysqli_real_escape_string($conn, $price['effective_date']);
        $is_active = isset($price['is_active']) ? (int)$price['is_active'] : 1;
        $notes = isset($price['notes']) ? mysqli_real_escape_string($conn, $price['notes']) : '';
        
        if ($id > 0) {
            // Update existing record
            $query = "UPDATE electricity_prices SET 
                     tier = $tier,
                     tier_name = '$tier_name',
                     kwh_from = $kwh_from,
                     kwh_to = " . ($kwh_to !== null ? $kwh_to : 'NULL') . ",
                     price_no_vat = $price_no_vat,
                     price_with_vat = $price_with_vat,
                     effective_date = '$effective_date',
                     is_active = $is_active,
                     notes = '$notes'
                     WHERE id = $id";
        } else {
            // Insert new record
            $query = "INSERT INTO electricity_prices 
                     (tier, tier_name, kwh_from, kwh_to, price_no_vat, price_with_vat, effective_date, is_active, notes) 
                     VALUES 
                     ($tier, '$tier_name', $kwh_from, " . ($kwh_to !== null ? $kwh_to : 'NULL') . ", $price_no_vat, $price_with_vat, '$effective_date', $is_active, '$notes')";
        }
        
        if (!mysqli_query($conn, $query)) {
            throw new Exception('Lỗi khi lưu bậc ' . $tier . ': ' . mysqli_error($conn));
        }
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã lưu bảng giá điện thành công!'
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>

