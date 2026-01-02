<?php
/**
 * Admin API: Save Electricity Prices
 * Lưu/Cập nhật bảng giá điện (cần đăng nhập admin)
 * SECURITY FIX: Using prepared statements to prevent SQL injection
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
    
    // Prepare statements for update and insert (prevents SQL injection)
    $updateStmt = $conn->prepare("UPDATE electricity_prices SET 
        tier = ?, tier_name = ?, kwh_from = ?, kwh_to = ?, 
        price_no_vat = ?, price_with_vat = ?, effective_date = ?, 
        is_active = ?, notes = ? WHERE id = ?");
    
    $insertStmt = $conn->prepare("INSERT INTO electricity_prices 
        (tier, tier_name, kwh_from, kwh_to, price_no_vat, price_with_vat, effective_date, is_active, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$updateStmt || !$insertStmt) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL');
    }
    
    // Update each price tier
    foreach ($data['prices'] as $price) {
        $id = (int)($price['id'] ?? 0);
        $tier = (int)($price['tier'] ?? 0);
        $tier_name = $price['tier_name'] ?? '';
        $kwh_from = (int)($price['kwh_from'] ?? 0);
        $kwh_to = isset($price['kwh_to']) && $price['kwh_to'] !== null && $price['kwh_to'] !== '' 
            ? (int)$price['kwh_to'] : null;
        $price_no_vat = (float)($price['price_no_vat'] ?? 0);
        $price_with_vat = (float)($price['price_with_vat'] ?? 0);
        $effective_date = $price['effective_date'] ?? date('Y-m-d');
        $is_active = isset($price['is_active']) ? (int)$price['is_active'] : 1;
        $notes = $price['notes'] ?? '';
        
        if ($id > 0) {
            // Update existing record using prepared statement
            $updateStmt->bind_param(
                "isiiddsiis",
                $tier, $tier_name, $kwh_from, $kwh_to,
                $price_no_vat, $price_with_vat, $effective_date,
                $is_active, $notes, $id
            );
            
            if (!$updateStmt->execute()) {
                throw new Exception('Lỗi khi cập nhật bậc ' . $tier . ': ' . $updateStmt->error);
            }
        } else {
            // Insert new record using prepared statement
            $insertStmt->bind_param(
                "isiiddsis",
                $tier, $tier_name, $kwh_from, $kwh_to,
                $price_no_vat, $price_with_vat, $effective_date,
                $is_active, $notes
            );
            
            if (!$insertStmt->execute()) {
                throw new Exception('Lỗi khi thêm bậc ' . $tier . ': ' . $insertStmt->error);
            }
        }
    }
    
    // Close statements
    $updateStmt->close();
    $insertStmt->close();
    
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

