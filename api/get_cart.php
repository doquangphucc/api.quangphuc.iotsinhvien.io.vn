<?php
require_once 'connect.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    sendError('Bạn cần đăng nhập để xem giỏ hàng.', 401);
}

$userId = (int)$_SESSION['user_id'];

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