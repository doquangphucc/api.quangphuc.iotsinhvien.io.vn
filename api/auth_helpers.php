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
?>
