<?php
require_once 'connect.php';

// Check if user is logged in - if not, return empty cart
if (!isLoggedIn()) {
    sendSuccess(['cart' => [], 'logged_in' => false]);
    exit;
}

$userId = getCurrentUserId();

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $sql = "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image_url, p.specifications FROM cart_items c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();

    sendSuccess(['cart' => $cartItems, 'logged_in' => true]);

} catch (Exception $e) {
    error_log("Get cart error: " . $e->getMessage());
    sendError('KhÃ´ng thá»ƒ láº¥y thÃ´ng tin giá» hÃ ng: ' . $e->getMessage(), 500);
}
?>
