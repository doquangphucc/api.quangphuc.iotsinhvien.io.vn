<?php
/**
 * API: Get User Permissions
 * Lấy danh sách quyền của 1 user
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

require_once '../config.php';
require_once '../connect.php';
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
    
    // Lấy user_id từ query string
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    if ($user_id == 0) {
        throw new Exception('User ID không hợp lệ');
    }
    
    // Lấy permissions
    $query = "SELECT permission_key, can_view, can_create, can_edit, can_delete 
              FROM user_permissions 
              WHERE user_id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $permissions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $permissions[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'permissions' => $permissions
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

