<?php
require_once 'connect.php';
// Session is already started in connect.php via session.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

// User must be logged in to create an order
requireAuth();
$userId = getCurrentUserId();

$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Dữ liệu JSON không hợp lệ');
}

// --- Input Validation ---
$customer = $input['customer'] ?? null;
$itemsRaw = $input['items'] ?? null;

$requiredCustomerKeys = ['fullname', 'phone', 'address', 'city_name', 'district_name'];
if (!$customer || count(array_diff($requiredCustomerKeys, array_keys($customer))) > 0) {
    sendError('Thông tin khách hàng không đầy đủ (thiếu họ tên, SĐT, địa chỉ, v.v...).');
}

if (empty($itemsRaw) || !is_array($itemsRaw)) {
    sendError('Giỏ hàng không được để trống.');
}

try {
    $db  = Database::getInstance();
    $pdo = $db->getConnection();

    // --- Verify cart items against database ---
    $verifiedItems = [];
    $calculatedTotal = 0;
    $cartItemIds = [];

    foreach ($itemsRaw as $cartItem) {
        $cartId = $cartItem['cart_item_id'] ?? ($cartItem['id'] ?? null);
        $cartId = filter_var($cartId, FILTER_VALIDATE_INT);
        if ($cartId && $cartId > 0) {
            $cartItemIds[] = (int)$cartId;
        }
    }

    $cartItemIds = array_values(array_unique($cartItemIds));

    if (empty($cartItemIds)) {
        sendError('Giỏ hàng không chứa sản phẩm hợp lệ.');
    }

    $placeholders = implode(',', array_fill(0, count($cartItemIds), '?'));
    $sql = "SELECT c.id AS cart_item_id, c.product_id, c.quantity, p.name, p.price, p.image_url
            FROM cart_items c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND c.id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $params = array_merge([(int)$userId], $cartItemIds);
    $stmt->execute($params);

    $cartRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartRows)) {
        sendError('Giỏ hàng không chứa sản phẩm hợp lệ.');
    }

    $cartRowsById = [];
    foreach ($cartRows as $row) {
        $cartRowsById[(int)$row['cart_item_id']] = $row;
    }

    foreach ($cartItemIds as $cartId) {
        if (!isset($cartRowsById[$cartId])) {
            sendError('Một số sản phẩm không còn trong giỏ hàng. Vui lòng tải lại giỏ hàng.', 400);
        }

        $row = $cartRowsById[$cartId];
        $quantity = (int)$row['quantity'];
        if ($quantity <= 0) {
            sendError('Số lượng sản phẩm không hợp lệ, vui lòng kiểm tra lại giỏ hàng.', 400);
        }

        $price = (float)$row['price'];
        $calculatedTotal += $price * $quantity;

        $verifiedItems[] = [
            'id'            => (int)$row['product_id'],
            'name'          => $row['name'],
            'quantity'      => $quantity,
            'price'         => $price,
            'image_url'     => $row['image_url'] ?? '',
            'cart_item_id'  => $cartId
        ];
    }

    if (empty($verifiedItems)) {
        sendError('Giỏ hàng không hợp lệ.');
    }

    // --- Transactional Database Operations ---
    $pdo->beginTransaction();

    // 1. Insert into `orders` table with server-verified data
    $orderData = [
        'user_id'      => (int)$userId,
        'full_name'    => sanitizeInput($customer['fullname']),
        'phone'        => sanitizeInput($customer['phone']),
        'email'        => sanitizeInput($customer['email'] ?? ''),
        'city'         => sanitizeInput($customer['city_name']),
        'district'     => sanitizeInput($customer['district_name']),
        'address'      => sanitizeInput($customer['address']),
        'notes'        => sanitizeInput($customer['notes'] ?? ''),
        'total_amount' => $calculatedTotal // CRITICAL: Use server-calculated total
    ];

    $orderId = $db->insert('orders', $orderData);

    // 2. Insert into `order_items` table
    $itemInsertSql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, image_url) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($itemInsertSql);

    foreach ($verifiedItems as $item) {
        $stmt->execute([
            $orderId,
            $item['id'],
            $item['name'],
            $item['quantity'],
            $item['price'],
            $item['image_url']
        ]);
    }

    // Remove ordered items from cart
    $deletePlaceholders = implode(',', array_fill(0, count($cartItemIds), '?'));
    $deleteSql = "DELETE FROM cart_items WHERE user_id = ? AND id IN ($deletePlaceholders)";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteParams = array_merge([(int)$userId], $cartItemIds);
    $deleteStmt->execute($deleteParams);

    // Commit all DB changes
    $pdo->commit();

    // Respond to client
    sendSuccess(['order_id' => $orderId], 'Đặt hàng thành công!');

} catch (Exception $e) {
    // If anything fails, roll back the transaction
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Create Order error: " . $e->getMessage());
    sendError('Không thể tạo đơn hàng, vui lòng thử lại sau.', 500);
}
?>
