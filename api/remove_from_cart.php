<?php
require_once 'connect.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

$input = json_decode(file_get_contents('php://input'), true);



$userId = getCurrentUserId();
$cartItemId = $input['cart_item_id'] ?? 0;

if ($cartItemId <= 0) {
    sendError('Dữ liệu không hợp lệ.');
}

try {
    $db = Database::getInstance();
    $db->delete('cart_items', ['id' => $cartItemId, 'user_id' => $userId]);
    sendSuccess([], 'Đã xóa sản phẩm khỏi giỏ hàng.');
} catch (Exception $e) {
    error_log("Remove from cart error: " . $e->getMessage());
    sendError('Không thể xóa sản phẩm.', 500);
}

?>