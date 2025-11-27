<?php
require_once 'connect.php';
require_once __DIR__ . '/helpers/order_email_helper.php';
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

    // --- Verify items against database ---
    $verifiedItems = [];
    $calculatedTotal = 0;
    $cartItemIds = [];
    $productIds = []; // For direct orders (not from cart)

    foreach ($itemsRaw as $cartItem) {
        // Support multiple field names: cart_item_id, cart_id, or id
        $cartId = $cartItem['cart_item_id'] ?? ($cartItem['cart_id'] ?? ($cartItem['id'] ?? null));
        $cartId = filter_var($cartId, FILTER_VALIDATE_INT);
        
        // Check if this is a direct order (has product_id but no cart_item_id)
        $productId = $cartItem['product_id'] ?? null;
        $productId = filter_var($productId, FILTER_VALIDATE_INT);
        
        if ($cartId && $cartId > 0) {
            // Item from cart
            $cartItemIds[] = (int)$cartId;
        } elseif ($productId && $productId > 0) {
            // Direct order item (not from cart)
            $productIds[] = [
                'product_id' => (int)$productId,
                'quantity' => isset($cartItem['quantity']) ? (int)$cartItem['quantity'] : 1,
                'price' => isset($cartItem['price']) ? (float)$cartItem['price'] : null
            ];
        }
    }
    
    error_log("Extracted cart item IDs: " . print_r($cartItemIds, true));
    error_log("Extracted product IDs (direct order): " . print_r($productIds, true));

    $cartItemIds = array_values(array_unique($cartItemIds));

    // If we have direct order items, process them first
    if (!empty($productIds)) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $productIdList = array_column($productIds, 'product_id');
        
        $sql = "SELECT p.id AS product_id, p.title as name, 
                       COALESCE(NULLIF(p.category_price, 0), p.market_price) as price, 
                       p.image_url
                FROM products p
                WHERE p.id IN ($placeholders) AND p.is_active = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($productIdList);
        $productRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $productRowsById = [];
        foreach ($productRows as $row) {
            $productRowsById[(int)$row['product_id']] = $row;
        }
        
        foreach ($productIds as $item) {
            $prodId = $item['product_id'];
            if (!isset($productRowsById[$prodId])) {
                sendError('Sản phẩm không tồn tại hoặc đã bị xóa: ID ' . $prodId, 400);
            }
            
            $row = $productRowsById[$prodId];
            $quantity = $item['quantity'] ?? 1;
            if ($quantity <= 0) {
                sendError('Số lượng sản phẩm không hợp lệ.', 400);
            }
            
            // Use price from request if provided, otherwise from database
            $price = $item['price'] !== null ? (float)$item['price'] : (float)$row['price'];
            $calculatedTotal += $price * $quantity;
            
            // Fix image URL path
            $imageUrl = $row['image_url'] ?? '';
            if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                $imageUrl = '../' . $imageUrl;
            }
            
            $verifiedItems[] = [
                'id'            => (int)$prodId,
                'name'          => $row['name'],
                'quantity'      => $quantity,
                'price'         => $price,
                'image_url'     => $imageUrl,
                'cart_item_id'  => null // Direct order, no cart_item_id
            ];
        }
    }
    
    // Process cart items if any
    if (!empty($cartItemIds)) {
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
    }

    if (empty($verifiedItems)) {
        sendError('Không có sản phẩm hợp lệ để đặt hàng.');
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
    $voucherCodesUsed = array_map(static function ($voucher) {
        return $voucher['code'] ?? '';
    }, $validatedVouchers);
    $voucherCodeString = implode(', ', array_filter($voucherCodesUsed));

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
        'voucher_code'    => $voucherCodeString ?: null,  // Store comma separated codes for history display
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

    // Remove ordered items from cart (only if items were from cart, not direct orders)
    if (!empty($cartItemIds)) {
        $deletePlaceholders = implode(',', array_fill(0, count($cartItemIds), '?'));
        $deleteSql = "DELETE FROM cart_items WHERE user_id = ? AND id IN ($deletePlaceholders)";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteParams = array_merge([(int)$userId], $cartItemIds);
        $deleteStmt->execute($deleteParams);
    }

    // Commit all DB changes
    $pdo->commit();

    $responseData = [
        'order_id' => $orderId,
        'total_amount' => $finalTotal,
        'discount_amount' => $totalDiscount,
        'vouchers_used' => count($validatedVouchers)
    ];
    $responseMessage = 'Đặt hàng thành công! Đơn hàng của bạn đang chờ xác nhận.';

    $emailItems = array_map(static function ($item) {
        $quantity = (int)($item['quantity'] ?? 1);
        if ($quantity <= 0) {
            $quantity = 1;
        }
        $price = (float)($item['price'] ?? 0);
        return [
            'name'     => $item['name'] ?? 'Sản phẩm',
            'quantity' => $quantity,
            'price'    => $price,
            'subtotal' => $price * $quantity
        ];
    }, $verifiedItems);

    $customerPayload = [
        'fullname' => $orderData['full_name'],
        'phone'    => $orderData['phone'],
        'email'    => $orderData['email'],
        'city'     => $orderData['city'],
        'district' => $orderData['district'],
        'ward'     => $orderData['ward'],
        'address'  => $orderData['address'],
        'notes'    => $orderData['notes']
    ];

    $sendNotification = function () use (
        $orderId,
        $customerPayload,
        $emailItems,
        $calculatedTotal,
        $totalDiscount,
        $finalTotal,
        $voucherCodesUsed
    ) {
        try {
            sendOrderNotificationEmail([
                'order_id'   => $orderId,
                'customer'   => $customerPayload,
                'items'      => $emailItems,
                'financials' => [
                    'subtotal'      => $calculatedTotal,
                    'discount'      => $totalDiscount,
                    'total'         => $finalTotal,
                    'voucher_codes' => array_filter($voucherCodesUsed)
                ],
                'source' => 'cart'
            ]);
        } catch (Throwable $emailException) {
            error_log('Failed to send order notification email: ' . $emailException->getMessage());
        }
    };

    if (function_exists('fastcgi_finish_request')) {
        $payload = [
            'success' => true,
            'message' => $responseMessage,
            'data'    => $responseData
        ];
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        flush();
        fastcgi_finish_request();
        $sendNotification();
        exit;
    }

    ignore_user_abort(true);
    $sendNotification();
    sendSuccess($responseData, $responseMessage);

} catch (Exception $e) {
    // If anything fails, roll back the transaction
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Create Order error: " . $e->getMessage());
    sendError('Không thể tạo đơn hàng, vui lòng thử lại sau.', 500);
}
?>
