<?php
require_once 'connect.php';

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
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // --- Verify items against database ---
    $verifiedItems = [];
    $calculatedTotal = 0;

    foreach ($itemsRaw as $item) {
        $productId = filter_var($item['product_id'] ?? $item['id'], FILTER_VALIDATE_INT);
        $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);
        
        if (!$productId || $productId <= 0 || !$quantity || $quantity <= 0) {
            continue; // Skip invalid items
        }

        // Verify product exists and get current price
        $sql = "SELECT id, name, price, image_url FROM products WHERE id = ? AND is_available = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            continue; // Skip if product doesn't exist or unavailable
        }

        $price = (float)$product['price'];
        $calculatedTotal += $price * $quantity;

        $verifiedItems[] = [
            'id' => (int)$product['id'],
            'name' => $product['name'],
            'quantity' => $quantity,
            'price' => $price,
            'image_url' => $product['image_url'] ?? ''
        ];
    }

    if (empty($verifiedItems)) {
        sendError('Giỏ hàng không chứa sản phẩm hợp lệ.');
    }

    // --- Transactional Database Operations ---
    $pdo->beginTransaction();

    // 1. Insert into `orders` table
    $orderData = [
        'user_id' => (int)$userId,
        'full_name' => sanitizeInput($customer['fullname']),
        'phone' => sanitizeInput($customer['phone']),
        'email' => sanitizeInput($customer['email'] ?? ''),
        'city' => sanitizeInput($customer['city_name']),
        'district' => sanitizeInput($customer['district_name']),
        'address' => sanitizeInput($customer['address']),
        'notes' => sanitizeInput($customer['notes'] ?? ''),
        'total_amount' => $calculatedTotal
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

    // 3. Add lottery ticket for successful purchase
    // First check if ticket already exists for this order to prevent duplicates
    $checkTicketSql = "SELECT id FROM lottery_tickets WHERE order_id = ? LIMIT 1";
    $checkStmt = $pdo->prepare($checkTicketSql);
    $checkStmt->execute([$orderId]);
    $existingTicket = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    $ticketId = null;
    
    if (!$existingTicket) {
        // Only create ticket if it doesn't exist for this order
        $ticketData = [
            'user_id' => (int)$userId,
            'order_id' => $orderId,
            'ticket_type' => 'purchase',
            'status' => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')) // Expires in 30 days
        ];
        
        $ticketId = $db->insert('lottery_tickets', $ticketData);
    } else {
        $ticketId = $existingTicket['id'];
    }

    // Commit all DB changes
    $pdo->commit();

    // Respond to client
    sendSuccess([
        'order_id' => $orderId,
        'lottery_ticket_id' => $ticketId,
        'message' => 'Đặt hàng thành công! Bạn đã nhận được 1 vé quay may mắn!'
    ], 'Đặt hàng thành công!');

} catch (Exception $e) {
    // If anything fails, roll back the transaction
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Create Order From Items error: " . $e->getMessage());
    sendError('Không thể tạo đơn hàng, vui lòng thử lại sau.', 500);
}
?>
