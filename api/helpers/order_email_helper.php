<?php

if (!function_exists('sendOrderNotificationEmail')) {
    /**
     * Send order notification via FormSubmit (same approach as survey page).
     */
    function sendOrderNotificationEmail(array $payload): bool
    {
        if (
            !defined('ORDER_NOTIFICATION_FORM_SUBMIT') ||
            empty(ORDER_NOTIFICATION_FORM_SUBMIT) ||
            !function_exists('curl_init')
        ) {
            error_log('Order email helper: FormSubmit endpoint or cURL not available');
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
        $voucherList = !empty($voucherCodes) ? implode(', ', $voucherCodes) : 'Không áp dụng';

        $subtotal = (float)($financial['subtotal'] ?? 0);
        $discount = (float)($financial['discount'] ?? 0);
        $total    = (float)($financial['total'] ?? max(0, $subtotal - $discount));

        $addressParts = array_filter([
            $customer['address'] ?? '',
            $customer['ward'] ?? '',
            $customer['district'] ?? '',
            $customer['city'] ?? ''
        ]);
        $fullAddress = implode(', ', $addressParts);

        $formData = [
            '_subject'        => $subject,
            '_template'       => 'table',
            '_captcha'        => 'false',
            'Nguồn đơn'       => $source,
            'Mã đơn hàng'     => $orderId,
            'Khách hàng'      => $customerName,
            'Số điện thoại'   => $customer['phone'] ?? '',
            'Email KH'        => $customer['email'] ?? '',
            'Địa chỉ'         => $fullAddress ?: 'Không cung cấp',
            'Ghi chú của KH'  => $customer['notes'] ?? '—',
            'Tạm tính'        => number_format($subtotal, 0, ',', '.') . ' đ',
            'Giảm giá'        => number_format($discount, 0, ',', '.') . ' đ',
            'Tổng thanh toán' => number_format($total, 0, ',', '.') . ' đ',
            'Voucher áp dụng' => $voucherList
        ];

        if (!empty($items)) {
            $index = 1;
            foreach ($items as $item) {
                $name     = $item['name'] ?? ('Sản phẩm #' . $index);
                $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                if ($quantity <= 0) {
                    $quantity = 1;
                }
                $price    = isset($item['price']) ? (float)$item['price'] : 0;
                $lineTotal = isset($item['subtotal']) ? (float)$item['subtotal'] : ($price * $quantity);

                $formData[sprintf('Sản phẩm %02d', $index)] = sprintf(
                    '%s x%s = %s đ',
                    $name,
                    $quantity,
                    number_format($lineTotal, 0, ',', '.')
                );
                $index++;
            }
        } else {
            $formData['Sản phẩm'] = 'Không có dữ liệu sản phẩm';
        }

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
            error_log('Order email FormSubmit error: ' . curl_error($ch));
        }
        curl_close($ch);

        if ($httpCode === 200) {
            error_log('Order notification sent via FormSubmit successfully');
            return true;
        }

        error_log('Order notification FormSubmit failed. HTTP status: ' . $httpCode);
        return false;
    }
}

