<?php
/**
 * Create order from survey package
 * Handles virtual items (inverter, cabinet, accessories) that don't have product_id in database
 */

require_once 'connect.php';
require_once __DIR__ . '/helpers/order_email_helper.php';

header('Content-Type: application/json');

requireAuth();

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    sendError('Dữ liệu không hợp lệ');
    exit;
}

// Validate customer data
$customer = $data['customer'] ?? null;
if (!$customer || !isset($customer['fullname']) || !isset($customer['phone']) || !isset($customer['address'])) {
    sendError('Thiếu thông tin khách hàng');
    exit;
}

// Validate items
$items = $data['items'] ?? [];
if (empty($items)) {
    sendError('Đơn hàng không có sản phẩm');
    exit;
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Calculate total
    $total = 0;
    foreach ($items as $item) {
        $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
    }
    
    // Get user ID
    $userId = getCurrentUserId();
    
    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, 
            full_name, 
            phone, 
            email,
            city,
            district,
            ward,
            address, 
            notes,
            subtotal,
            voucher_code,
            discount_amount,
            total_amount, 
            order_status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, 0, ?, 'pending', NOW())
    ");
    
    $stmt->execute([
        $userId,
        $customer['fullname'],
        $customer['phone'],
        $customer['email'] ?? null,
        $customer['city_name'] ?? '',
        $customer['district_name'] ?? '',
        $customer['ward_name'] ?? '',
        $customer['address'],
        $customer['notes'] ?? null,
        $total,
        $total
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    // Add order items (including virtual items)
    // Try to insert with image_url, fallback to without if column doesn't exist
    foreach ($items as $item) {
        $productId = null;
        if (!empty($item['product_id']) && is_numeric($item['product_id'])) {
            $productId = intval($item['product_id']);
        }
        
        $imageUrl = $item['image_url'] ?? null;
        if ($imageUrl && is_string($imageUrl)) {
            // Clean image URL - convert absolute paths to relative
            if ($imageUrl[0] === '/') {
                $imageUrl = '..' . $imageUrl;
            }
        }
        
        error_log("Inserting item: Order={$orderId}, Product={$productId}, Name=" . ($item['title'] ?? 'Unknown') . ", Image={$imageUrl}");
        
        // Normalize numeric fields
        $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
        if ($quantity <= 0) { $quantity = 1; }
        $priceEach = isset($item['price']) ? floatval($item['price']) : 0;
        // Try to insert with image_url first
        try {
            $stmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_name,
                    quantity,
                    price,
                    image_url
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $orderId,
                $productId,
                $item['title'] ?? $item['name'] ?? 'Unknown',
                $quantity,
                $priceEach,
                $imageUrl
            ]);
            
            error_log("Successfully inserted with image_url");
        } catch (Exception $e) {
            // If image_url column doesn't exist, try without it
            error_log("Failed to insert with image_url: " . $e->getMessage() . ". Trying without image_url...");
            
            $stmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_name,
                    quantity,
                    price
                ) VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $orderId,
                $productId,
                $item['title'] ?? $item['name'] ?? 'Unknown',
                $quantity,
                $priceEach
            ]);
            
            error_log("Successfully inserted without image_url");
        }
    }
    
    // Handle vouchers if provided
    $voucherCodes = $data['voucher_codes'] ?? [];
    $appliedVoucherCodes = [];
    $totalDiscount = 0;
    
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
                $appliedVoucherCodes[] = $voucherData['code'];

                if ($voucherData['source'] === 'voucher') {
                    // Insert into order_vouchers only for legacy vouchers
                    $insertVoucherStmt = $pdo->prepare("INSERT INTO order_vouchers (order_id, voucher_id, voucher_code, discount_amount) VALUES (?, ?, ?, ?)");
                    $insertVoucherStmt->execute([
                        $orderId,
                        $voucherData['id'],
                        $voucherData['code'],
                        $voucherData['discount']
                    ]);

                    $updateVoucherSql = "UPDATE vouchers SET is_used = 1, used_by_user_id = ?, used_at = NOW() WHERE id = ?";
                    $updateVoucherStmt = $pdo->prepare($updateVoucherSql);
                    $updateVoucherStmt->execute([(int)$userId, $voucherData['id']]);
                } else {
                    // Reward-based voucher: mark lottery reward as used
                    $updateRewardSql = "UPDATE lottery_rewards SET status = 'used', used_at = NOW() WHERE id = ?";
                    $updateRewardStmt = $pdo->prepare($updateRewardSql);
                    $updateRewardStmt->execute([$voucherData['id']]);
                }
                
                $totalDiscount += $voucherData['discount'];
            }
        }
        
        // Update order with discount applied
        if ($totalDiscount > 0) {
            $finalTotal = max(0, $total - $totalDiscount);
            $updateOrderSql = "UPDATE orders SET discount_amount = ?, total_amount = ? WHERE id = ?";
            $updateOrderStmt = $pdo->prepare($updateOrderSql);
            $updateOrderStmt->execute([$totalDiscount, $finalTotal, $orderId]);
        }
    }

    $finalTotal = max(0, $total - $totalDiscount);
    $voucherCodesUsed = array_map(static function ($code) {
        return is_scalar($code) ? trim((string)$code) : '';
    }, $appliedVoucherCodes);
    $voucherCodeString = implode(', ', array_filter($voucherCodesUsed));

    if ($voucherCodeString !== '') {
        $updateVoucherCodeSql = "UPDATE orders SET voucher_code = ? WHERE id = ?";
        $updateVoucherCodeStmt = $pdo->prepare($updateVoucherCodeSql);
        $updateVoucherCodeStmt->execute([$voucherCodeString, $orderId]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Log order creation
    error_log("Survey order created - Order ID: {$orderId}, User ID: {$userId}, Total: {$total}");
    
    $responseData = [
        'order_id' => $orderId,
        'total_amount' => $finalTotal,
        'discount_amount' => $totalDiscount,
        'voucher_codes' => array_filter($voucherCodesUsed),
        'message' => 'Đặt hàng thành công! Vé quay may mắn sẽ được tặng khi đơn hàng được duyệt.'
    ];
    $responseMessage = 'Đặt hàng thành công từ gói khảo sát!';

    $emailItems = array_map(static function ($item) {
        $quantity = (int)($item['quantity'] ?? 1);
        if ($quantity <= 0) {
            $quantity = 1;
        }
        $price = (float)($item['price'] ?? 0);
        return [
            'name'     => $item['title'] ?? $item['name'] ?? 'Sản phẩm',
            'quantity' => $quantity,
            'price'    => $price,
            'subtotal' => $price * $quantity
        ];
    }, $items);

    $customerPayload = [
        'fullname' => sanitizeInput($customer['fullname']),
        'phone'    => sanitizeInput($customer['phone']),
        'email'    => sanitizeInput($customer['email'] ?? ''),
        'city'     => sanitizeInput($customer['city_name'] ?? ''),
        'district' => sanitizeInput($customer['district_name'] ?? ''),
        'ward'     => sanitizeInput($customer['ward_name'] ?? ''),
        'address'  => sanitizeInput($customer['address']),
        'notes'    => sanitizeInput($customer['notes'] ?? '')
    ];

    $sendNotification = function () use (
        $orderId,
        $customerPayload,
        $emailItems,
        $total,
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
                    'subtotal'      => $total,
                    'discount'      => $totalDiscount,
                    'total'         => $finalTotal,
                    'voucher_codes' => array_filter($voucherCodesUsed)
                ],
                'source' => 'survey'
            ]);
        } catch (Throwable $emailException) {
            error_log('Failed to send survey order notification email: ' . $emailException->getMessage());
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
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error creating survey order: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    sendError('Lỗi khi tạo đơn hàng: ' . $e->getMessage());
}

