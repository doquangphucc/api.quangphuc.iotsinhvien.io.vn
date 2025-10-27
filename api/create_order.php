<?php
require_once 'connect.php';
// Session is already started in connect.php via session.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

// User must be logged in to create an order
requireAuth();
$userId = getCurrentUserId();

$inputRaw = file_get_contents('php://input');
error_log("Raw input: " . $inputRaw);
$input = json_decode($inputRaw, true);
error_log("Decoded input: " . print_r($input, true));

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode error: " . json_last_error_msg());
    sendError('Dữ liệu JSON không hợp lệ');
}

// --- Input Validation ---
$customer = $input['customer'] ?? null;
$itemsRaw = $input['items'] ?? null;
$voucherCode = $input['voucher_code'] ?? '';

error_log("Items raw: " . print_r($itemsRaw, true));

$requiredCustomerKeys = ['fullname', 'phone', 'address', 'city_name', 'district_name', 'ward_name'];
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
        // Support multiple field names: cart_item_id, cart_id, or id
        $cartId = $cartItem['cart_item_id'] ?? ($cartItem['cart_id'] ?? ($cartItem['id'] ?? null));
        $cartId = filter_var($cartId, FILTER_VALIDATE_INT);
        if ($cartId && $cartId > 0) {
            $cartItemIds[] = (int)$cartId;
        }
    }
    
    error_log("Extracted cart item IDs: " . print_r($cartItemIds, true));

    $cartItemIds = array_values(array_unique($cartItemIds));

    if (empty($cartItemIds)) {
        sendError('Giỏ hàng không chứa sản phẩm hợp lệ.');
    }

    $placeholders = implode(',', array_fill(0, count($cartItemIds), '?'));
    $sql = "SELECT c.id AS cart_item_id, c.user_id, c.product_id, c.quantity, p.title as name, p.market_price as price, p.image_url
            FROM cart_items c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND c.id IN ($placeholders)";
    error_log("SQL: " . $sql);
    error_log("Params: userId=" . (int)$userId . ", cartItemIds=" . print_r($cartItemIds, true));
    $stmt = $pdo->prepare($sql);
    $params = array_merge([(int)$userId], $cartItemIds);
    $stmt->execute($params);

    $cartRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Found cart rows: " . count($cartRows));
    
    // Debug: Log all cart items in database for this user
    $debugSql = "SELECT id, user_id, product_id, quantity FROM cart_items WHERE user_id = ?";
    $debugStmt = $pdo->prepare($debugSql);
    $debugStmt->execute([(int)$userId]);
    $allCartItems = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("All cart items for user " . (int)$userId . ": " . print_r($allCartItems, true));

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

        // Fix image URL path
        $imageUrl = $row['image_url'] ?? '';
        if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
            $imageUrl = '../' . $imageUrl;
        }
        
        $verifiedItems[] = [
            'id'            => (int)$row['product_id'],
            'name'          => $row['name'],
            'quantity'      => $quantity,
            'price'         => $price,
            'image_url'     => $imageUrl,
            'cart_item_id'  => $cartId
        ];
    }

    if (empty($verifiedItems)) {
        sendError('Giỏ hàng không hợp lệ.');
    }

    // --- Check and apply voucher if provided ---
    $discountAmount = 0;
    $voucherCodeToSave = null;
    $rewardId = null;
    
    if (!empty($voucherCode)) {
        // First try to find in lottery_rewards (reward-based vouchers)
        $rewardSql = "SELECT * FROM lottery_rewards WHERE (voucher_code = ? OR id = ?) AND user_id = ? AND reward_type = 'voucher' AND status = 'pending' AND (expires_at IS NULL OR expires_at > NOW())";
        $rewardStmt = $pdo->prepare($rewardSql);
        $rewardStmt->execute([trim($voucherCode), intval($voucherCode), (int)$userId]);
        $reward = $rewardStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reward) {
            $discountAmount = (float)$reward['reward_value'];
            $voucherCodeToSave = $reward['voucher_code'] ?: $reward['id'];
            $rewardId = (int)$reward['id'];
        } else {
            // Fallback to vouchers table (legacy system)
            $voucherSql = "SELECT * FROM vouchers WHERE code = ? AND is_used = 0 AND (expires_at IS NULL OR expires_at > NOW())";
            $voucherStmt = $pdo->prepare($voucherSql);
            $voucherStmt->execute([trim($voucherCode)]);
            $voucher = $voucherStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($voucher) {
                $discountAmount = (float)$voucher['discount_amount'];
                $voucherCodeToSave = $voucher['code'];
            } else {
                sendError('Mã voucher không hợp lệ hoặc đã hết hạn.');
            }
        }
    }
    
    $finalTotal = max(0, $calculatedTotal - $discountAmount);

    // --- Transactional Database Operations ---
    $pdo->beginTransaction();

    // 1. Insert into `orders` table with server-verified data
    $orderData = [
        'user_id'         => (int)$userId,
        'full_name'       => sanitizeInput($customer['fullname']),
        'phone'           => sanitizeInput($customer['phone']),
        'email'           => sanitizeInput($customer['email'] ?? ''),
        'city'            => sanitizeInput($customer['city_name']),
        'district'        => sanitizeInput($customer['district_name']),
        'ward'            => sanitizeInput($customer['ward_name']),
        'address'         => sanitizeInput($customer['address']),
        'notes'           => sanitizeInput($customer['notes'] ?? ''),
        'subtotal'        => $calculatedTotal,
        'voucher_code'    => $voucherCodeToSave,
        'discount_amount' => $discountAmount,
        'total_amount'    => $finalTotal,
        'order_status'    => 'pending' // Chờ admin duyệt
    ];

    $orderId = $db->insert('orders', $orderData);
    
    // Mark voucher/reward as used if applicable
    if ($voucherCodeToSave) {
        if ($rewardId) {
            // Mark lottery reward as used
            $updateRewardSql = "UPDATE lottery_rewards SET status = 'used', used_at = NOW() WHERE id = ?";
            $updateRewardStmt = $pdo->prepare($updateRewardSql);
            $updateRewardStmt->execute([$rewardId]);
        } else {
            // Mark voucher as used (legacy)
            $updateVoucherSql = "UPDATE vouchers SET is_used = 1, used_by_user_id = ?, used_at = NOW() WHERE code = ?";
            $updateVoucherStmt = $pdo->prepare($updateVoucherSql);
            $updateVoucherStmt->execute([(int)$userId, $voucherCodeToSave]);
        }
    }

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
    sendSuccess([
        'order_id' => $orderId,
        'total_amount' => $finalTotal,
        'discount_amount' => $discountAmount
    ], 'Đặt hàng thành công! Đơn hàng của bạn đang chờ xác nhận.');

} catch (Exception $e) {
    // If anything fails, roll back the transaction
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Create Order error: " . $e->getMessage());
    sendError('Không thể tạo đơn hàng, vui lòng thử lại sau.', 500);
}
?>
