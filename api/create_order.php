<?php
require_once 'connect.php';
// Session is already started in connect.php via session.php

/* ================== ĐOẠN 1: HÀM GỬI MAIL (đơn giản, không cần SMTP) ================== */

function _e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/**
 * Gửi email multipart (text + html) bằng hàm mail() có sẵn trên hầu hết shared hosting.
 * Trả về true/false, KHÔNG throw → không làm hỏng flow API.
 */
function sendSimpleMail($to, $subject, $html, $alt = '') {
    $boundary = md5(uniqid(time(), true));
    $headers  = [];
    $fromDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $headers[] = 'From: Order Notifier <no-reply@' . $fromDomain . '>';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/alternative; boundary="'.$boundary.'"';

    if ($alt === '' || $alt === null) {
        // AltBody: bóc text từ html
        $alt = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $html));
    }

    $body  = "--$boundary\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\n\n";
    $body .= $alt . "\n";
    $body .= "--$boundary\n";
    $body .= "Content-Type: text/html; charset=UTF-8\n\n";
    $body .= $html . "\n";
    $body .= "--$boundary--";

    // Subject UTF-8
    $encodedSubject = '=?UTF-8?B?'.base64_encode($subject).'?=';

    return @mail($to, $encodedSubject, $body, implode("\n", $headers));
}

/** Dựng nội dung email HTML: gồm dữ liệu bảng orders + order_items */
function buildOrderEmailHtml($orderId, array $order, array $items) {
    $rows = '';
    foreach ($items as $i) {
        $rows .= '<tr>\n            <td>'._e($i['id']).'</td>\n            <td>'._e($i['name']).'</td>\n            <td align="right">'._e((int)$i['quantity']).'</td>\n            <td align="right">'.number_format((float)$i['price'], 0, ',', '.').'</td>\n            <td>'.(!empty($i['image_url']) ? '<a href="'._e($i['image_url']).'">image</a>' : '-').'</td>\n        </tr>';
    }

    return '
    <div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#222">
      <h3>Đơn hàng mới #'._e($orderId).'</h3>
      <table border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse">
        <tr><th align="left">Mã đơn</th><td>#'._e($orderId).'</td></tr>
        <tr><th align="left">Khách hàng</th><td>'._e($order['full_name']).'</td></tr>
        <tr><th align="left">Điện thoại</th><td>'._e($order['phone']).'</td></tr>
        <tr><th align="left">Email</th><td>'._e($order['email']).'</td></tr>
        <tr><th align="left">Tỉnh/Thành</th><td>'._e($order['city']).'</td></tr>
        <tr><th align="left">Quận/Huyện</th><td>'._e($order['district']).'</td></tr>
        <tr><th align="left">Địa chỉ</th><td>'._e($order['address']).'</td></tr>
        <tr><th align="left">Ghi chú</th><td>'._e($order['notes']).'</td></tr>
        <tr><th align="left">Tổng tiền</th><td>'.number_format((float)$order['total_amount'], 0, ',', '.').' đ</td></tr>
      </table>

      <h4 style="margin-top:14px">Sản phẩm</h4>
      <table border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse">
        <thead>
          <tr style="background:#f2f2f2">
            <th>Product ID</th><th>Tên sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Ảnh</th>
          </tr>
        </thead>
        <tbody>'.$rows.'</tbody>
      </table>
      <p style="color:#666">Email này được tạo tự động từ hệ thống.</p>
    </div>';
}

/** Dựng nội dung email text thuần */
function buildOrderEmailText($orderId, array $order, array $items) {
    $lines = [];
    $lines[] = "Đơn hàng mới #{$orderId}";
    $lines[] = "Khách: {$order['full_name']}";
    $lines[] = "Điện thoại: {$order['phone']}";
    $lines[] = "Email: {$order['email']}";
    $lines[] = "Địa chỉ: {$order['address']}, {$order['district']}, {$order['city']}";
    $lines[] = "Ghi chú: {$order['notes']}";
    $lines[] = "Tổng tiền: " . number_format((float)$order['total_amount'], 0, ',', '.') . " đ";
    $lines[] = "";
    $lines[] = "Sản phẩm:";
    foreach ($items as $i) {
        $lines[] = "- [{$i['id']}] {$i['name']} x{$i['quantity']} @ " . number_format((float)$i['price'], 0, ',', '.') . " đ";
    }
    return implode("\n", $lines);
}
/* ================== HẾT ĐOẠN 1 ================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

// User must be logged in to create an order

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

    /* ================== SEND NOTIFICATION EMAIL (after successful commit) ================== */
    try {
        $to       = 'doquangphuc21@gmail.com'; // Should be a configurable admin email
        $subject  = '[ĐƠN HÀNG MỚI] #' . $orderId . ' - ' . ($orderData['full_name'] ?? '');
        $htmlBody = buildOrderEmailHtml($orderId, $orderData, $verifiedItems);
        $txtBody  = buildOrderEmailText($orderId, $orderData, $verifiedItems);

        $ok = sendSimpleMail($to, $subject, $htmlBody, $txtBody);
        if (!$ok) {
            // Log warning if mail sending fails, but don't fail the API response
            error_log('MAIL WARN: mail() returned false for order #' . $orderId);
        }
    } catch (Throwable $ex) {
        // Absolutely do not re-throw, to avoid breaking the API response
        error_log('MAIL ERROR: ' . $ex->getMessage());
    }
    /* ================== END OF EMAIL LOGIC ================== */

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