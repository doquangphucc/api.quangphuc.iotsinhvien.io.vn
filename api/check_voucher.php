<?php
// Check if voucher is valid
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require_once __DIR__ . '/db_mysqli.php';

$data = json_decode(file_get_contents('php://input'), true);
$code = trim($data['code'] ?? '');

if (empty($code)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập mã voucher'
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM vouchers WHERE code = ? AND is_used = 0 AND (expires_at IS NULL OR expires_at > NOW())");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();
$voucher = $result->fetch_assoc();

if ($voucher) {
    echo json_encode([
        'success' => true,
        'voucher' => [
            'code' => $voucher['code'],
            'discount_amount' => floatval($voucher['discount_amount']),
            'description' => $voucher['description']
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Mã voucher không hợp lệ hoặc đã hết hạn'
    ]);
}

$stmt->close();
$conn->close();
?>

