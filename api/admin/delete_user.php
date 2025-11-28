<?php
/**
 * API: Delete User
 * Xóa user và tất cả permissions liên quan
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Credentials: true');

require_once '../config.php';
require_once '../db_mysqli.php';
require_once '../session.php';

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
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = isset($input['id']) ? intval($input['id']) : 0;
    
    if ($user_id == 0) {
        throw new Exception('User ID không hợp lệ');
    }
    
    // Không cho phép xóa chính mình
    if ($user_id == $_SESSION['user_id']) {
        throw new Exception('Không thể xóa chính tài khoản của mình');
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

