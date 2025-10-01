<?php
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

requireAuth();

$input = json_decode(file_get_contents('php://input'), true);

$userId = getCurrentUserId();
$productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

if ($productId <= 0 || $quantity <= 0) {
    sendError('Dữ liệu không hợp lệ.');
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

    sendSuccess(['total_items' => (int)$totalItems], 'Sản phẩm đã được thêm/cập nhật trong giỏ hàng.');

} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    sendError('Không thể thêm vào giỏ hàng.', 500);
}
?>