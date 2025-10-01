<?php
require_once 'connect.php';

// Debug logging
error_log("Get Cart Debug - Session ID: " . session_id());
error_log("Get Cart Debug - Session Data: " . print_r($_SESSION, true));
error_log("Get Cart Debug - Cookie Data: " . print_r($_COOKIE, true));

// Check if user is logged in
requireAuth();

$userId = getCurrentUserId();
error_log("Get Cart Debug - User ID: " . $userId);

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $sql = "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image_url, p.specifications FROM cart_items c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();

    sendSuccess(['cart' => $cartItems]);

} catch (Exception $e) {
    error_log("Get cart error: " . $e->getMessage());
    sendError('Không thể lấy thông tin giỏ hàng: ' . $e->getMessage(), 500);
}
?>