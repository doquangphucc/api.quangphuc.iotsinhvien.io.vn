<?php
// Đặt timezone cho PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Get PDO connection
$pdo = db_get_pdo_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only GET method allowed']);
    exit;
}

try {
    $wishId = $_GET['id'] ?? '';
    $username = $_GET['username'] ?? '';

    if (empty($wishId)) {
        throw new Exception('Wish ID is required');
    }

    // Xây dựng query với điều kiện username nếu có
    $query = "SELECT id, item_id, title, description, category, priority, 
                     price, currency, product_url, purchase_status,
                     target_date, status, user_id,
                     created_at, updated_at, purchased_at, actual_price, purchase_note
              FROM wishes 
              WHERE id = ?";
    
    $params = [$wishId];
    
    // Thêm điều kiện username nếu có
    if (!empty($username)) {
        $query .= " AND user_id = (SELECT id FROM tai_khoan WHERE user = ?)";
        $params[] = $username;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $wish = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$wish) {
        throw new Exception('Wish not found');
    }

    // Format dữ liệu
    $wish['is_completed'] = (bool)$wish['status'];
    $wish['price'] = $wish['price'] ? floatval($wish['price']) : null;
    $wish['actual_price'] = $wish['actual_price'] ? floatval($wish['actual_price']) : null;
    $wish['target_date'] = $wish['target_date'] ?: null;

    echo json_encode([
        'success' => true,
        'data' => [$wish] // Trả về array để tương thích với code hiện tại
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_type' => 'PDO_ERROR'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_type' => 'GENERAL_ERROR'
    ]);
}
?>
