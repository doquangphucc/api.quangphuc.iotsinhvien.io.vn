<?php
/**
 * API: Get All Users
 * Lấy danh sách tất cả users (chỉ admin)
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
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
    
    // Lấy danh sách users
    $query = "SELECT id, full_name, username, phone, is_admin, created_at, updated_at 
              FROM users 
              ORDER BY is_admin DESC, created_at DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Lỗi khi truy vấn database: ' . mysqli_error($conn));
    }
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users
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

