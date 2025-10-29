<?php
require_once 'connect.php';

header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

$orderId = $_GET['id'] ?? 3;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Check if order exists
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get all orders for current user if logged in
    $userOrders = [];
    if ($userId) {
        $stmt = $pdo->prepare("SELECT id, full_name, order_status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        $userOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'session' => [
            'logged_in' => $isLoggedIn,
            'user_id' => $userId,
            'session_data' => $_SESSION ?? []
        ],
        'order_check' => [
            'order_id' => $orderId,
            'exists' => $order ? true : false,
            'order_data' => $order,
            'belongs_to_current_user' => $order && $userId && $order['user_id'] == $userId
        ],
        'user_orders' => $userOrders
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>

