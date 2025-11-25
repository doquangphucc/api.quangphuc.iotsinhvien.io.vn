<?php

if (!function_exists('sendOrderNotificationEmail')) {
    /**
     * Send order notification email to admin inbox.
     *
     * @param array $payload Structured data about the order
     * @return bool
     */
    function sendOrderNotificationEmail(array $payload): bool
    {
        if (!defined('ORDER_NOTIFICATION_EMAIL') || empty(ORDER_NOTIFICATION_EMAIL)) {
            return false;
        }

        $orderId   = $payload['order_id'] ?? 'N/A';
        $customer  = $payload['customer'] ?? [];
        $items     = $payload['items'] ?? [];
        $financial = $payload['financials'] ?? [];
        $source    = strtoupper($payload['source'] ?? 'WEB');

        $customerName = trim($customer['fullname'] ?? 'Khách hàng');
        $subject = sprintf('[HC ECO] Đơn hàng mới #%s - %s', $orderId, $customerName);

        $voucherCodes = $financial['voucher_codes'] ?? [];
        if (!is_array($voucherCodes)) {
            $voucherCodes = [];
        }
        $voucherList = !empty($voucherCodes) ? implode(', ', $voucherCodes) : 'Không sử dụng';

        $subtotal = (float)($financial['subtotal'] ?? 0);
        $discount = (float)($financial['discount'] ?? 0);
        $total    = (float)($financial['total'] ?? $subtotal - $discount);

        $addressParts = array_filter([
            $customer['address'] ?? '',
            $customer['ward'] ?? '',
            $customer['district'] ?? '',
            $customer['city'] ?? ''
        ]);
        $fullAddress = htmlspecialchars(implode(', ', $addressParts));

        $itemRows = '';
        $plainItems = '';
        if (!empty($items)) {
            foreach ($items as $item) {
                $name     = htmlspecialchars($item['name'] ?? 'Sản phẩm');
                $quantity = (int)($item['quantity'] ?? 1);
                if ($quantity <= 0) {
                    $quantity = 1;
                }
                $price    = (float)($item['price'] ?? 0);
                $subtotalItem = (float)($item['subtotal'] ?? ($price * $quantity));

                $itemRows .= sprintf(
                    '<tr>
                        <td style="padding:10px;border:1px solid #e5e7eb;">%s</td>
                        <td style="padding:10px;border:1px solid #e5e7eb;text-align:center;">%d</td>
                        <td style="padding:10px;border:1px solid #e5e7eb;text-align:right;">%s₫</td>
                        <td style="padding:10px;border:1px solid #e5e7eb;text-align:right;font-weight:600;">%s₫</td>
                    </tr>',
                    $name,
                    $quantity,
                    number_format($price, 0, ',', '.'),
                    number_format($subtotalItem, 0, ',', '.')
                );

                $plainItems .= sprintf(
                    "- %s x%s: %s₫\n",
                    strip_tags($name),
                    $quantity,
                    number_format($subtotalItem, 0, ',', '.')
                );
            }
        } else {
            $itemRows = '<tr><td colspan="4" style="padding:12px;text-align:center;border:1px solid #e5e7eb;color:#6b7280;">Không có sản phẩm</td></tr>';
        }

        $orderTime = date('d/m/Y H:i');

        $body = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Đơn hàng mới</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color:#f9fafb; padding:24px;">
    <table style="max-width:720px;margin:0 auto;background:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);overflow:hidden;border:1px solid #e5e7eb;" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="background:linear-gradient(90deg,#16a34a,#22c55e);padding:24px 32px;color:#ffffff;">
                <h2 style="margin:0;font-size:22px;">HC ECO SYSTEM</h2>
                <p style="margin:4px 0 0;font-size:14px;letter-spacing:0.15em;">ĐƠN HÀNG MỚI • {$source}</p>
            </td>
        </tr>
        <tr>
            <td style="padding:28px 32px;">
                <h3 style="margin-top:0;font-size:20px;color:#111827;">Thông tin đơn hàng #{$orderId}</h3>
                <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                    <tr>
                        <td style="padding:6px 0;color:#6b7280;width:120px;">Khách hàng:</td>
                        <td style="padding:6px 0;color:#111827;font-weight:600;">{$customerName}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;color:#6b7280;">Số điện thoại:</td>
                        <td style="padding:6px 0;color:#111827;">{$customer['phone']}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;color:#6b7280;">Email:</td>
                        <td style="padding:6px 0;color:#111827;">{$customer['email']}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;color:#6b7280;">Địa chỉ:</td>
                        <td style="padding:6px 0;color:#111827;">{$fullAddress}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;color:#6b7280;">Ghi chú:</td>
                        <td style="padding:6px 0;color:#111827;">{$customer['notes']}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;color:#6b7280;">Thời gian:</td>
                        <td style="padding:6px 0;color:#111827;">{$orderTime}</td>
                    </tr>
                </table>

                <h4 style="font-size:18px;color:#111827;margin:24px 0 12px;">Chi tiết sản phẩm</h4>
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f3f4f6;">
                            <th align="left" style="padding:10px;border:1px solid #e5e7eb;color:#374151;">Sản phẩm</th>
                            <th style="padding:10px;border:1px solid #e5e7eb;color:#374151;">SL</th>
                            <th align="right" style="padding:10px;border:1px solid #e5e7eb;color:#374151;">Đơn giá</th>
                            <th align="right" style="padding:10px;border:1px solid #e5e7eb;color:#374151;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemRows}
                    </tbody>
                </table>

                <table style="width:100%;margin-top:24px;border-collapse:collapse;">
                    <tr>
                        <td style="padding:6px 0;color:#6b7280;">Tạm tính:</td>
                        <td style="padding:6px 0;text-align:right;color:#111827;font-weight:600;">{number_format($subtotal, 0, ',', '.')}₫</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;color:#6b7280;">Giảm giá/Voucher:</td>
                        <td style="padding:6px 0;text-align:right;color:#dc2626;font-weight:600;">- {number_format($discount, 0, ',', '.')}₫</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;color:#6b7280;">Mã áp dụng:</td>
                        <td style="padding:6px 0;text-align:right;color:#2563eb;">{$voucherList}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 0;color:#6b7280;font-size:16px;">Tổng cộng cần thanh toán:</td>
                        <td style="padding:12px 0;text-align:right;color:#16a34a;font-size:22px;font-weight:700;">{number_format($total, 0, ',', '.')}₫</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="background:#111827;color:#9ca3af;padding:16px 32px;font-size:12px;text-align:center;">
                Email được gửi tự động từ hệ thống HC ECO SYSTEM. Vui lòng phản hồi để xác nhận đơn hàng với khách.
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: HC Eco System <noreply@hceco.io.vn>\r\n";
        $headers .= "Reply-To: noreply@hceco.io.vn\r\n";

        $mailSent = @mail(ORDER_NOTIFICATION_EMAIL, $subject, $body, $headers);
        if ($mailSent) {
            return true;
        }

        error_log('Order notification email failed via mail(). Trying fallback...');
        return sendOrderNotificationFallback($subject, $customer, $plainItems, $subtotal, $discount, $total, $voucherList);
    }
}

if (!function_exists('sendOrderNotificationFallback')) {
    /**
     * Fallback using FormSubmit service (simple text payload).
     */
    function sendOrderNotificationFallback(
        string $subject,
        array $customer,
        string $plainItems,
        float $subtotal,
        float $discount,
        float $total,
        string $voucherList
    ): bool {
        if (
            !defined('ORDER_NOTIFICATION_FORM_SUBMIT') ||
            empty(ORDER_NOTIFICATION_FORM_SUBMIT) ||
            !function_exists('curl_init')
        ) {
            return false;
        }

        $message = "ĐƠN HÀNG MỚI\n";
        $message .= "Khách hàng: " . ($customer['fullname'] ?? 'N/A') . "\n";
        $message .= "Điện thoại: " . ($customer['phone'] ?? 'N/A') . "\n";
        $message .= "Email: " . ($customer['email'] ?? 'N/A') . "\n";
        $message .= "Địa chỉ: " . ($customer['address'] ?? 'N/A');
        if (!empty($customer['ward']) || !empty($customer['district']) || !empty($customer['city'])) {
            $message .= ", " . implode(', ', array_filter([
                $customer['ward'] ?? '',
                $customer['district'] ?? '',
                $customer['city'] ?? ''
            ]));
        }
        $message .= "\n\nSản phẩm:\n" . ($plainItems ?: 'Không có dữ liệu sản phẩm');
        $message .= "\nTạm tính: " . number_format($subtotal, 0, ',', '.') . "₫";
        $message .= "\nGiảm giá: -" . number_format($discount, 0, ',', '.') . "₫";
        $message .= "\nTổng cộng: " . number_format($total, 0, ',', '.') . "₫";
        $message .= "\nVoucher: " . $voucherList . "\n";

        $formData = [
            '_subject' => $subject,
            '_captcha' => 'false',
            'name'     => $customer['fullname'] ?? 'Khách hàng',
            'email'    => $customer['email'] ?? 'noreply@hceco.io.vn',
            'phone'    => $customer['phone'] ?? '',
            'message'  => $message
        ];

        $ch = curl_init(ORDER_NOTIFICATION_FORM_SUBMIT);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false) {
            error_log('Order notification fallback cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        if ($httpCode === 200) {
            error_log('Order notification email sent via FormSubmit fallback');
            return true;
        }

        error_log('Order notification fallback failed with status: ' . $httpCode);
        return false;
    }
}


