<?php
/**
 * API: Check Admin Access
 * Kiểm tra user có quyền truy cập admin không (is_admin hoặc có permissions)
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

require_once 'config.php';
require_once 'db_mysqli.php';
require_once 'session.php';

try {
    // Kiểm tra đã login chưa
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'has_access' => false,
            'message' => 'Chưa đăng nhập'
        ]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Kiểm tra is_admin
    $query = "SELECT is_admin FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if (!$user) {
        throw new Exception('User không tồn tại');
    }
    
    $has_access = false;
    $reason = '';
    
    // Check is_admin = 1
    if ($user['is_admin'] == 1) {
        $has_access = true;
        $reason = 'full_admin';
    } else {
        // Check có permissions không
        $perm_query = "SELECT COUNT(*) as count FROM user_permissions WHERE user_id = ?";
        $perm_stmt = mysqli_prepare($conn, $perm_query);
        mysqli_stmt_bind_param($perm_stmt, 'i', $user_id);
        mysqli_stmt_execute($perm_stmt);
        $perm_result = mysqli_stmt_get_result($perm_stmt);
        $perm_data = mysqli_fetch_assoc($perm_result);
        
        if ($perm_data['count'] > 0) {
            $has_access = true;
            $reason = 'has_permissions';
        }
    }
    
    echo json_encode([
        'success' => true,
        'has_access' => $has_access,
        'reason' => $reason,
        'is_admin' => (int)$user['is_admin']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'has_access' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>

