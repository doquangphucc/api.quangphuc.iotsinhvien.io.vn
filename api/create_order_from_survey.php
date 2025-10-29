<?php
/**
 * Create order from survey package
 * Handles virtual items (inverter, cabinet, accessories) that don't have product_id in database
 */

require_once 'connect.php';

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
    // First, let's check if image_url column exists by trying to query the table structure
    try {
        $checkStmt = $pdo->query("SHOW COLUMNS FROM order_items LIKE 'image_url'");
        $hasImageUrl = $checkStmt->rowCount() > 0;
        error_log("order_items table has image_url column: " . ($hasImageUrl ? 'YES' : 'NO'));
    } catch (Exception $e) {
        error_log("Error checking for image_url column: " . $e->getMessage());
        $hasImageUrl = false;
    }
    
    if ($hasImageUrl) {
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
            
            $stmt->execute([
                $orderId,
                $productId,
                $item['title'] ?? $item['name'] ?? 'Unknown',
                $item['quantity'] ?? 1,
                $item['price'] ?? 0,
                $imageUrl
            ]);
        }
    } else {
        // Fallback: insert without image_url
        $stmt = $pdo->prepare("
            INSERT INTO order_items (
                order_id,
                product_id,
                product_name,
                quantity,
                price
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($items as $item) {
            $productId = null;
            if (!empty($item['product_id']) && is_numeric($item['product_id'])) {
                $productId = intval($item['product_id']);
            }
            
            error_log("Inserting item (no image): Order={$orderId}, Product={$productId}, Name=" . ($item['title'] ?? 'Unknown'));
            
            $stmt->execute([
                $orderId,
                $productId,
                $item['title'] ?? $item['name'] ?? 'Unknown',
                $item['quantity'] ?? 1,
                $item['price'] ?? 0
            ]);
        }
    }
    
    // Handle vouchers if provided
    $voucherCodes = $data['voucher_codes'] ?? [];
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
                // Insert into order_vouchers
                $insertVoucherStmt = $pdo->prepare("INSERT INTO order_vouchers (order_id, voucher_id, voucher_code, discount_amount) VALUES (?, ?, ?, ?)");
                $insertVoucherStmt->execute([
                    $orderId,
                    $voucherData['id'],
                    $voucherData['code'],
                    $voucherData['discount']
                ]);
                
                // Mark voucher/reward as used
                if ($voucherData['source'] === 'reward') {
                    $updateRewardSql = "UPDATE lottery_rewards SET status = 'used', used_at = NOW() WHERE id = ?";
                    $updateRewardStmt = $pdo->prepare($updateRewardSql);
                    $updateRewardStmt->execute([$voucherData['id']]);
                } else {
                    $updateVoucherSql = "UPDATE vouchers SET is_used = 1, used_by_user_id = ?, used_at = NOW() WHERE id = ?";
                    $updateVoucherStmt = $pdo->prepare($updateVoucherSql);
                    $updateVoucherStmt->execute([(int)$userId, $voucherData['id']]);
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
    
    // Commit transaction
    $pdo->commit();
    
    // Log order creation
    error_log("Survey order created - Order ID: {$orderId}, User ID: {$userId}, Total: {$total}");
    
    sendSuccess([
        'order_id' => $orderId,
        'message' => 'Đặt hàng thành công! Vé quay may mắn sẽ được tặng khi đơn hàng được duyệt.'
    ], 'Đặt hàng thành công từ gói khảo sát!');
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error creating survey order: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    sendError('Lỗi khi tạo đơn hàng: ' . $e->getMessage());
}

