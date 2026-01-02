<?php
/**
 * Admin API: Save Survey Regions
 * Lưu/Cập nhật danh sách khu vực khảo sát (cần đăng nhập admin)
 * SECURITY FIX: Using prepared statements to prevent SQL injection
 */

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/security_middleware.php';

// Apply admin security (WAF, rate limiting)
applySecurityMiddleware([
    'csrf' => false,  // TODO: Enable after frontend update
    'waf' => true,
    'rate_limit' => true,
    'rate_limit_requests' => 30,
    'rate_limit_window' => 60,
    'audit' => true
]);

if (!hasPermission($conn, 'survey', 'edit')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa cấu hình khu vực khảo sát']);
    exit;
}

try {
    // Get JSON input
    $input = getRawRequestBody();
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['regions'])) {
        throw new Exception('Dữ liệu không hợp lệ');
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    // Prepare statements for update and insert (prevents SQL injection)
    $updateStmt = $conn->prepare("UPDATE survey_regions SET 
        region_code = ?, region_name = ?, display_content = ?, 
        sun_hours = ?, display_order = ?, is_active = ?, notes = ? 
        WHERE id = ?");
    
    $insertStmt = $conn->prepare("INSERT INTO survey_regions 
        (region_code, region_name, display_content, sun_hours, display_order, is_active, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if (!$updateStmt || !$insertStmt) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL');
    }
    
    // Update each region
    foreach ($data['regions'] as $region) {
        $id = (int)($region['id'] ?? 0);
        $region_code = $region['region_code'] ?? '';
        $region_name = $region['region_name'] ?? '';
        $display_content = $region['display_content'] ?? '';
        $sun_hours = (float)($region['sun_hours'] ?? 0);
        $display_order = (int)($region['display_order'] ?? 0);
        $is_active = isset($region['is_active']) ? (int)$region['is_active'] : 1;
        $notes = $region['notes'] ?? '';
        
        if ($id > 0) {
            // Update existing record using prepared statement
            $updateStmt->bind_param(
                "sssdiisi",
                $region_code, $region_name, $display_content,
                $sun_hours, $display_order, $is_active, $notes, $id
            );
            
            if (!$updateStmt->execute()) {
                throw new Exception('Lỗi khi cập nhật khu vực ' . $region_name . ': ' . $updateStmt->error);
            }
        } else {
            // Insert new record using prepared statement
            $insertStmt->bind_param(
                "sssdiis",
                $region_code, $region_name, $display_content,
                $sun_hours, $display_order, $is_active, $notes
            );
            
            if (!$insertStmt->execute()) {
                throw new Exception('Lỗi khi thêm khu vực ' . $region_name . ': ' . $insertStmt->error);
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
        'message' => 'Đã lưu danh sách khu vực thành công!'
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

