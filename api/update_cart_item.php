<?php
require_once 'connect.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

$input = json_decode(file_get_contents('php://input'), true);



$userId = getCurrentUserId();
$cartItemId = $input['cart_item_id'] ?? ($input['cart_id'] ?? 0); // Support both parameter names
$quantity = $input['quantity'] ?? -1; // Default to -1 to catch missing quantity

if ($cartItemId <= 0 || $quantity < 0) {
    sendError('Dữ liệu không hợp lệ.');
}

try {
    $db = Database::getInstance();
    
    if ($quantity == 0) {
        // If quantity is 0, delete the item from the cart
        $db->delete('cart_items', ['id' => $cartItemId, 'user_id' => $userId]);
        sendSuccess([], 'Đã xóa sản phẩm khỏi giỏ hàng.');
    } else {
        // Otherwise, update the quantity
        $db->update('cart_items', ['quantity' => $quantity], ['id' => $cartItemId, 'user_id' => $userId]);
        sendSuccess([], 'Cập nhật số lượng thành công.');
    }

} catch (Exception $e) {
    error_log("Update cart item error: " . $e->getMessage());
    sendError('Không thể cập nhật giỏ hàng.', 500);
}
?>
