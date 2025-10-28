<?php
// Authentication helper functions
// Include this file only when authentication is needed

// Note: requireAuth() and getCurrentUserId() are now in session.php
// Only keep admin-specific functions here

function is_admin() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user && $user['is_admin'];
}

/**
 * Check if user is logged in as admin
 */
function isAdminLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Check if user has specific permission for a module
 * @param string $module - Module name (e.g., 'contacts', 'home', 'products')
 * @param string $action - Action type ('view', 'create', 'edit', 'delete')
 * @return bool
 */
function hasPermission($module, $action = 'view') {
    global $conn;
    
    // If not logged in, no permission
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Full admin has all permissions
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        return true;
    }
    
    $user_id = $_SESSION['user_id'];
    $column_map = [
        'view' => 'can_view',
        'create' => 'can_create',
        'edit' => 'can_edit',
        'delete' => 'can_delete'
    ];
    
    $column = $column_map[$action] ?? 'can_view';
    
    $stmt = $conn->prepare("SELECT {$column} FROM user_permissions WHERE user_id = ? AND permission_key = ?");
    $stmt->bind_param("is", $user_id, $module);
    $stmt->execute();
    $result = $stmt->get_result();
    $perm = $result->fetch_assoc();
    $stmt->close();
    
    return $perm && $perm[$column] == 1;
}
?>
