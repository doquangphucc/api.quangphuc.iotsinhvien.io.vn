<?php
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('PhÆ°Æ¡ng thá»©c khÃ´ng Ä‘Æ°á»£c há»— trá»£', 405);
}

requireAuth();

$input = json_decode(file_get_contents('php://input'), true);

$userId = getCurrentUserId();
$productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

if ($productId <= 0 || $quantity <= 0) {
    sendError('Dá»¯ liá»‡u khÃ´ng há»£p lá»‡.');
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both cases
    $sql = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $productId, $quantity]);

    // Get total items in cart
    $countStmt = $db->query("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?", [$userId]);
    $totalItems = $countStmt->fetchColumn();

    sendSuccess(['total_items' => (int)$totalItems], 'Sáº£n pháº©m Ä‘Ã£ Ä‘Æ°á»£c thÃªm/cáº­p nháº­t trong giá» hÃ ng.');

} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    sendError('KhÃ´ng thá»ƒ thÃªm vÃ o giá» hÃ ng.', 500);
}
?>
