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

    $sql = "SELECT c.id, c.product_id, c.quantity, p.title as name, 
                   COALESCE(NULLIF(p.category_price, 0), p.market_price) as price, 
                   p.image_url, p.technical_description as specifications,
                   p.is_active
            FROM cart_items c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? AND p.is_active = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();
    
    // Fix image URL paths
    foreach ($cartItems as &$item) {
        if (!empty($item['image_url']) && !str_starts_with($item['image_url'], 'http')) {
            $item['image_url'] = '../' . $item['image_url'];
        }
    }

    sendSuccess(['cart' => $cartItems, 'logged_in' => true]);

} catch (Exception $e) {
    error_log("Get cart error: " . $e->getMessage());
    sendError('Không thể lấy thông tin giỏ hàng: ' . $e->getMessage(), 500);
}
?>
