<?php
/**
 * API: Delete User
 * Xóa user và tất cả permissions liên quan
 */

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../helpers/audit_logger.php';
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

try {
    // Kiểm tra admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không có quyền truy cập'
        ]);
        exit;
    }
    
    // Lấy dữ liệu từ request
    $input = json_decode(getRawRequestBody(), true);
    $user_id = isset($input['id']) ? intval($input['id']) : 0;
    
    if ($user_id == 0) {
        throw new Exception('User ID không hợp lệ');
    }
    
    // Không cho phép xóa chính mình
    if ($user_id == $_SESSION['user_id']) {
        throw new Exception('Không thể xóa chính tài khoản của mình');
    }
    
    // Lấy thông tin user trước khi xóa (cho audit log)
    $get_user_query = "SELECT id, full_name, username, phone, is_admin FROM users WHERE id = ?";
    $get_user_stmt = mysqli_prepare($conn, $get_user_query);
    mysqli_stmt_bind_param($get_user_stmt, 'i', $user_id);
    mysqli_stmt_execute($get_user_stmt);
    $user_result = mysqli_stmt_get_result($get_user_stmt);
    $user_data = mysqli_fetch_assoc($user_result);
    mysqli_stmt_close($get_user_stmt);
    
    if (!$user_data) {
        throw new Exception('Không tìm thấy user');
    }
    
    // Bắt đầu transaction để đảm bảo tính nhất quán
    mysqli_begin_transaction($conn);
    
    try {
        // Xóa orders của user (vì orders có foreign key đến users)
        // Các bảng khác có ON DELETE CASCADE sẽ tự động xóa:
        // - cart_items (ON DELETE CASCADE)
        // - lottery_tickets (ON DELETE CASCADE)
        // - lottery_rewards (ON DELETE CASCADE)
        // - solar_surveys (ON DELETE CASCADE) - survey_results sẽ tự xóa theo CASCADE
        // - user_permissions (ON DELETE CASCADE)
        
        // Vouchers.used_by_user_id có ON DELETE SET NULL - tự động set NULL
        // Orders.approved_by có ON DELETE SET NULL - tự động set NULL
        
        // Xóa orders trước (vì có thể có foreign key constraint nếu chưa được set CASCADE)
        $delete_orders_query = "DELETE FROM orders WHERE user_id = ?";
        $delete_orders_stmt = mysqli_prepare($conn, $delete_orders_query);
        mysqli_stmt_bind_param($delete_orders_stmt, 'i', $user_id);
        
        if (!mysqli_stmt_execute($delete_orders_stmt)) {
            throw new Exception('Lỗi khi xóa đơn hàng của user: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($delete_orders_stmt);
        
        // Xóa user
        // Các bảng có ON DELETE CASCADE sẽ tự động xóa khi user bị xóa
        $delete_query = "DELETE FROM users WHERE id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, 'i', $user_id);
        
        if (!mysqli_stmt_execute($delete_stmt)) {
            throw new Exception('Lỗi khi xóa user: ' . mysqli_error($conn));
        }
        
        if (mysqli_stmt_affected_rows($delete_stmt) == 0) {
            mysqli_rollback($conn);
            throw new Exception('Không tìm thấy user để xóa');
        }
        
        mysqli_stmt_close($delete_stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Audit log
        AuditLogger::logDelete('user', (string)$user_id, $user_data, 
            "Xóa user: " . $user_data['full_name'] . " (" . $user_data['username'] . ")");
        
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        mysqli_rollback($conn);
        throw $e;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Xóa user thành công'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>

