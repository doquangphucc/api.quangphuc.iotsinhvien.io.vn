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
$voucherCodes = $input['voucher_codes'] ?? [];  // Changed to array

// Support legacy single voucher_code
if (empty($voucherCodes) && !empty($input['voucher_code'])) {
    $voucherCodes = [$input['voucher_code']];
}

error_log("Items raw: " . print_r($itemsRaw, true));
error_log("Voucher codes: " . print_r($voucherCodes, true));

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
    $sql = "SELECT c.id AS cart_item_id, c.user_id, c.product_id, c.quantity, p.title as name, 
                   COALESCE(NULLIF(p.category_price, 0), p.market_price) as price, 
                   p.image_url
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
    
    // Debug: Log all cart items in database (all users) to check if cart_item_id exists
    $debugSql2 = "SELECT id, user_id, product_id, quantity FROM cart_items ORDER BY id DESC LIMIT 10";
    $debugStmt2 = $pdo->prepare($debugSql2);
    $debugStmt2->execute();
    $allCartItemsAll = $debugStmt2->fetchAll(PDO::FETCH_ASSOC);
    error_log("Last 10 cart items (all users): " . print_r($allCartItemsAll, true));

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

    // --- Check and apply multiple vouchers if provided ---
    $totalDiscount = 0;
    $validatedVouchers = [];
    
    if (!empty($voucherCodes) && is_array($voucherCodes)) {
        foreach ($voucherCodes as $voucherCode) {
            if (empty($voucherCode)) continue;
            
            $voucherCode = trim($voucherCode);
            $voucherData = null;
            
            // First try to find in lottery_rewards (reward-based vouchers)
            $rewardSql = "SELECT * FROM lottery_rewards WHERE (voucher_code = ? OR id = ?) AND user_id = ? AND reward_type = 'voucher' AND status = 'pending' AND (expires_at IS NULL OR expires_at > NOW())";
            $rewardStmt = $pdo->prepare($rewardSql);
            $rewardStmt->execute([$voucherCode, intval($voucherCode), (int)$userId]);
            $reward = $rewardStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reward) {
                $voucherData = [
                    'source' => 'reward',
                    'id' => (int)$reward['id'],
                    'code' => $reward['voucher_code'] ?: $reward['id'],
                    'discount' => (float)$reward['reward_value']
                ];
            } else {
                // Fallback to vouchers table (legacy system)
                $voucherSql = "SELECT * FROM vouchers WHERE code = ? AND is_used = 0 AND (expires_at IS NULL OR expires_at > NOW())";
                $voucherStmt = $pdo->prepare($voucherSql);
                $voucherStmt->execute([$voucherCode]);
                $voucher = $voucherStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($voucher) {
                    $voucherData = [
                        'source' => 'voucher',
                        'id' => (int)$voucher['id'],
                        'code' => $voucher['code'],
                        'discount' => (float)$voucher['discount_amount']
                    ];
                }
            }
            
            if ($voucherData) {
                $validatedVouchers[] = $voucherData;
                $totalDiscount += $voucherData['discount'];
            } else {
                sendError("Mã voucher '{$voucherCode}' không hợp lệ hoặc đã hết hạn.");
            }
        }
    }
    
    $finalTotal = max(0, $calculatedTotal - $totalDiscount);

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
        'voucher_code'    => null,  // Keep null for backward compatibility
        'discount_amount' => $totalDiscount,
        'total_amount'    => $finalTotal,
        'order_status'    => 'pending' // Chờ admin duyệt
    ];

    $orderId = $db->insert('orders', $orderData);
    
    // Insert validated vouchers into order_vouchers table and mark as used
    if (!empty($validatedVouchers)) {
        // Only insert vouchers from vouchers table (not lottery_rewards)
        $insertVoucherSql = "INSERT INTO order_vouchers (order_id, voucher_id, voucher_code, discount_amount) VALUES (?, ?, ?, ?)";
        $insertVoucherStmt = $pdo->prepare($insertVoucherSql);
        
        foreach ($validatedVouchers as $voucher) {
            // Only insert into order_vouchers if source is 'voucher' (from vouchers table)
            // Reward-based vouchers (from lottery_rewards) are tracked separately and don't need to be in order_vouchers
            if ($voucher['source'] === 'voucher') {
                $insertVoucherStmt->execute([
                    $orderId,
                    $voucher['id'],
                    $voucher['code'],
                    $voucher['discount']
                ]);
                
                // Mark voucher as used
                $updateVoucherSql = "UPDATE vouchers SET is_used = 1, used_by_user_id = ?, used_at = NOW() WHERE id = ?";
                $updateVoucherStmt = $pdo->prepare($updateVoucherSql);
                $updateVoucherStmt->execute([(int)$userId, $voucher['id']]);
            } else {
                // For reward-based vouchers, just mark the reward as used
                // They don't need to be in order_vouchers since they're tracked in lottery_rewards
                $updateRewardSql = "UPDATE lottery_rewards SET status = 'used', used_at = NOW() WHERE id = ?";
                $updateRewardStmt = $pdo->prepare($updateRewardSql);
                $updateRewardStmt->execute([$voucher['id']]);
            }
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
        'discount_amount' => $totalDiscount,
        'vouchers_used' => count($validatedVouchers)
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
