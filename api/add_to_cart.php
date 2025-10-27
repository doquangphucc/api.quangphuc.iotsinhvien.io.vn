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

    // Check if item already exists in cart
    $checkSql = "SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$userId, $productId]);
    $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingItem) {
        // Update quantity
        $updateSql = "UPDATE cart_items SET quantity = quantity + ? WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$quantity, $existingItem['id']]);
        $cart_id = $existingItem['id'];
    } else {
        // Insert new item
        $insertSql = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([$userId, $productId, $quantity]);
        $cart_id = $pdo->lastInsertId();
    }

    // Get total items in cart
    $countStmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
    $countStmt->execute([$userId]);
    $totalItems = $countStmt->fetchColumn();

    sendSuccess([
        'total_items' => (int)$totalItems,
        'cart_id' => (int)$cart_id
    ], 'Sản phẩm đã được thêm/cập nhật trong giỏ hàng.');

} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    sendError('Không thể thêm vào giỏ hàng.', 500);
}
?>
