<?php
/**
 * API: Get My Permissions
 * Lấy danh sách quyền của user hiện tại
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

require_once '../config.php';
require_once '../db_mysqli.php';
require_once '../session.php';

try {
    // Kiểm tra đã login
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Bạn chưa đăng nhập'
        ]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $is_admin = isset($_SESSION['is_admin']) ? (int)$_SESSION['is_admin'] : 0;
    
    // Nếu là full admin, trả về full quyền
    if ($is_admin == 1) {
        $modules = ['categories', 'products', 'survey', 'packages', 'orders', 'tickets', 'rewards', 'intro-posts', 'projects', 'dich-vu', 'home', 'contacts', 'wheel', 'promotions'];
        $permissions = [];
        
        foreach ($modules as $module) {
            $permissions[$module] = [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true
            ];
        }
        
        echo json_encode([
            'success' => true,
            'is_full_admin' => true,
            'permissions' => $permissions
        ]);
        exit;
    }
    
    // Lấy permissions từ database
    $query = "SELECT permission_key, can_view, can_create, can_edit, can_delete 
              FROM user_permissions 
              WHERE user_id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $permissions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $permissions[$row['permission_key']] = [
            'can_view' => (bool)$row['can_view'],
            'can_create' => (bool)$row['can_create'],
            'can_edit' => (bool)$row['can_edit'],
            'can_delete' => (bool)$row['can_delete']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'is_full_admin' => false,
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

