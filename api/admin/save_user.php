<?php
/**
 * API: Save User
 * Lưu/cập nhật user và permissions
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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
    
    // Lấy dữ liệu từ request
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = isset($input['id']) && $input['id'] ? intval($input['id']) : 0;
    $full_name = mysqli_real_escape_string($conn, trim($input['full_name']));
    $username = mysqli_real_escape_string($conn, trim($input['username']));
    $phone = mysqli_real_escape_string($conn, trim($input['phone']));
    $password = isset($input['password']) && $input['password'] ? $input['password'] : null;
    $is_admin = isset($input['is_admin']) && $input['is_admin'] ? 1 : 0;
    $permissions = isset($input['permissions']) ? $input['permissions'] : [];
    
    // Validate
    if (empty($full_name) || empty($username) || empty($phone)) {
        throw new Exception('Vui lòng điền đầy đủ thông tin');
    }
    
    // Validate password for new user
    if ($user_id == 0 && empty($password)) {
        throw new Exception('Vui lòng nhập mật khẩu');
    }
    
    if ($password && strlen($password) < 6) {
        throw new Exception('Mật khẩu phải có ít nhất 6 ký tự');
    }
    
    // Bắt đầu transaction
    mysqli_begin_transaction($conn);
    
    if ($user_id == 0) {
        // Kiểm tra username đã tồn tại
        $check_query = "SELECT id FROM users WHERE username = ? OR phone = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'ss', $username, $phone);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            throw new Exception('Username hoặc số điện thoại đã tồn tại');
        }
        
        // Thêm user mới
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO users (full_name, username, phone, password, is_admin) 
                        VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'ssssi', $full_name, $username, $phone, $hashed_password, $is_admin);
        
        if (!mysqli_stmt_execute($insert_stmt)) {
            throw new Exception('Lỗi khi tạo user: ' . mysqli_error($conn));
        }
        
        $user_id = mysqli_insert_id($conn);
        $message = 'Tạo user thành công';
        
    } else {
        // Cập nhật user
        // Kiểm tra username/phone trùng với user khác
        $check_query = "SELECT id FROM users WHERE (username = ? OR phone = ?) AND id != ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'ssi', $username, $phone, $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            throw new Exception('Username hoặc số điện thoại đã được sử dụng bởi user khác');
        }
        
        if ($password) {
            // Cập nhật với password mới
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET full_name = ?, username = ?, phone = ?, password = ?, is_admin = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'ssssii', $full_name, $username, $phone, $hashed_password, $is_admin, $user_id);
        } else {
            // Cập nhật không thay đổi password
            $update_query = "UPDATE users SET full_name = ?, username = ?, phone = ?, is_admin = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'sssii', $full_name, $username, $phone, $is_admin, $user_id);
        }
        
        if (!mysqli_stmt_execute($update_stmt)) {
            throw new Exception('Lỗi khi cập nhật user: ' . mysqli_error($conn));
        }
        
        $message = 'Cập nhật user thành công';
    }
    
    // Xóa permissions cũ
    $delete_perm_query = "DELETE FROM user_permissions WHERE user_id = ?";
    $delete_perm_stmt = mysqli_prepare($conn, $delete_perm_query);
    mysqli_stmt_bind_param($delete_perm_stmt, 'i', $user_id);
    mysqli_stmt_execute($delete_perm_stmt);
    
    // Thêm permissions mới (nếu không phải admin)
    if (!$is_admin && !empty($permissions)) {
        $insert_perm_query = "INSERT INTO user_permissions (user_id, permission_key, can_view, can_create, can_edit, can_delete) 
                             VALUES (?, ?, ?, ?, ?, ?)";
        $insert_perm_stmt = mysqli_prepare($conn, $insert_perm_query);
        
        foreach ($permissions as $module => $perms) {
            $can_view = isset($perms['can_view']) && $perms['can_view'] ? 1 : 0;
            $can_create = isset($perms['can_create']) && $perms['can_create'] ? 1 : 0;
            $can_edit = isset($perms['can_edit']) && $perms['can_edit'] ? 1 : 0;
            $can_delete = isset($perms['can_delete']) && $perms['can_delete'] ? 1 : 0;
            
            // Chỉ insert nếu có ít nhất 1 quyền
            if ($can_view || $can_create || $can_edit || $can_delete) {
                mysqli_stmt_bind_param($insert_perm_stmt, 'isiiii', 
                    $user_id, $module, $can_view, $can_create, $can_edit, $can_delete);
                mysqli_stmt_execute($insert_perm_stmt);
            }
        }
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    if (isset($conn)) {
        mysqli_rollback($conn);
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>

