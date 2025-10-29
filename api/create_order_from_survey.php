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
        // For virtual items (isVirtual = true or id is string), use NULL for product_id
        $productId = null;
        if (!empty($item['product_id']) && is_numeric($item['product_id'])) {
            $productId = intval($item['product_id']);
        }
        
        $stmt->execute([
            $orderId,
            $productId,
            $item['title'] ?? $item['name'] ?? 'Unknown',
            $item['quantity'] ?? 1,
            $item['price'] ?? 0,
            $item['image_url'] ?? null
        ]);
    }
    
    // Handle vouchers if provided
    $voucherCodes = $data['voucher_codes'] ?? [];
    if (!empty($voucherCodes)) {
        $stmt = $pdo->prepare("
            INSERT INTO order_vouchers (order_id, voucher_id, discount_amount)
            SELECT ?, v.id, v.discount_amount
            FROM vouchers v
            WHERE v.code = ? AND v.is_active = 1
        ");
        
        foreach ($voucherCodes as $code) {
            $stmt->execute([$orderId, $code]);
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

