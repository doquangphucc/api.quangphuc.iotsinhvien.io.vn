<?php
require_once 'connect.php';

// This API returns cart data even if user is not logged in
// It will try to get from session first, then return empty if not found

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Check if user is logged in
    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        
        $sql = "SELECT c.id, c.product_id, c.quantity, p.title as name, 
                       COALESCE(NULLIF(p.category_price, 0), p.market_price) as price, 
                       p.image_url, p.technical_description as specifications 
                FROM cart_items c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?";
        
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
    } else {
        // User not logged in, return empty cart
        sendSuccess(['cart' => [], 'logged_in' => false]);
    }

} catch (Exception $e) {
    error_log("Get cart without auth error: " . $e->getMessage());
    sendSuccess(['cart' => [], 'logged_in' => false]);
}
?>
