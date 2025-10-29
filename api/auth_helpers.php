<?php
// Authentication helper functions
// Include this file only when authentication is needed

// Note: requireAuth() and getCurrentUserId() are now in session.php
// Only keep admin-specific functions here

function is_admin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    try {
        // Use Database class (PDO) for compatibility with connect.php
        require_once __DIR__ . '/connect.php';
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user && $user['is_admin'] == 1;
    } catch (Exception $e) {
        error_log("is_admin() error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if user is logged in as admin
 */
function isAdminLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}
?>
