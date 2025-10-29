<?php
/**
 * Admin API: Save Survey Regions
 * Lưu/Cập nhật danh sách khu vực khảo sát (cần đăng nhập admin)
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
    
    if (!$data || !isset($data['regions'])) {
        throw new Exception('Dữ liệu không hợp lệ');
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    // Update each region
    foreach ($data['regions'] as $region) {
        $id = (int)$region['id'];
        $region_code = mysqli_real_escape_string($conn, $region['region_code']);
        $region_name = mysqli_real_escape_string($conn, $region['region_name']);
        $display_content = mysqli_real_escape_string($conn, $region['display_content']);
        $sun_hours = (float)$region['sun_hours'];
        $display_order = (int)$region['display_order'];
        $is_active = isset($region['is_active']) ? (int)$region['is_active'] : 1;
        $notes = isset($region['notes']) ? mysqli_real_escape_string($conn, $region['notes']) : '';
        
        if ($id > 0) {
            // Update existing record
            $query = "UPDATE survey_regions SET 
                     region_code = '$region_code',
                     region_name = '$region_name',
                     display_content = '$display_content',
                     sun_hours = $sun_hours,
                     display_order = $display_order,
                     is_active = $is_active,
                     notes = '$notes'
                     WHERE id = $id";
        } else {
            // Insert new record
            $query = "INSERT INTO survey_regions 
                     (region_code, region_name, display_content, sun_hours, display_order, is_active, notes) 
                     VALUES 
                     ('$region_code', '$region_name', '$display_content', $sun_hours, $display_order, $is_active, '$notes')";
        }
        
        if (!mysqli_query($conn, $query)) {
            throw new Exception('Lỗi khi lưu khu vực ' . $region_name . ': ' . mysqli_error($conn));
        }
    }
    
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

