<?php
/**
 * Permission Helper Functions
 * Các hàm hỗ trợ kiểm tra quyền
 */

/**
 * Check if current user has permission for a module and action
 * 
 * @param mysqli $conn Database connection
 * @param string $module Module name (categories, products, etc.)
 * @param string $action Action name (view, create, edit, delete)
 * @return bool True if has permission, false otherwise
 */
function hasPermission($conn, $module, $action = 'view') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $is_admin = isset($_SESSION['is_admin']) ? (int)$_SESSION['is_admin'] : 0;
    
    // Full admin has all permissions
    if ($is_admin == 1) {
        return true;
    }
    
    // Check specific permission from database
    $action_column = 'can_' . $action; // can_view, can_create, can_edit, can_delete
    
    $query = "SELECT {$action_column} FROM user_permissions 
              WHERE user_id = ? AND permission_key = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $module);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return (bool)$row[$action_column];
    }
    
    return false;
}

/**
 * Require permission or return error response
 * 
 * @param mysqli $conn Database connection
 * @param string $module Module name
 * @param string $action Action name
 * @return void (exits if no permission)
 */
function requirePermission($conn, $module, $action) {
    if (!hasPermission($conn, $module, $action)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => "Bạn không có quyền {$action} module này"
        ]);
        exit;
    }
}

/**
 * Check if user is full admin
 * 
 * @return bool
 */
function isFullAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Require full admin access or return error
 * 
 * @return void (exits if not full admin)
 */
function requireFullAdmin() {
    if (!isFullAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Chức năng này chỉ dành cho quản trị viên cao cấp'
        ]);
        exit;
    }
}
?>

