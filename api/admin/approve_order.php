<?php
// Approve order and give lottery ticket
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

if (!hasPermission($conn, 'orders', 'edit')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền duyệt đơn hàng']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = intval($data['order_id'] ?? 0);
$admin_id = $_SESSION['user_id'];

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Get order info
    $stmt = $conn->prepare("SELECT user_id, order_status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if (!$order) {
        throw new Exception('Không tìm thấy đơn hàng');
    }
    
    if ($order['order_status'] !== 'pending') {
        throw new Exception('Đơn hàng đã được xử lý');
    }
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET order_status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $admin_id, $order_id);
    $stmt->execute();
    
    // Calculate total product quantity in order
    $stmt = $conn->prepare("SELECT SUM(quantity) as total_quantity FROM order_items WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quantity_data = $result->fetch_assoc();
    $total_quantity = intval($quantity_data['total_quantity'] ?? 0);
    
    // Create lottery tickets based on total product quantity
    // Each product quantity = 1 lottery ticket
    $user_id = $order['user_id'];
    $tickets_created = 0;
    
    if ($total_quantity > 0) {
        $stmt = $conn->prepare("INSERT INTO lottery_tickets (user_id, order_id, ticket_type, status) VALUES (?, ?, 'purchase', 'active')");
        $stmt->bind_param("ii", $user_id, $order_id);
        
        for ($i = 0; $i < $total_quantity; $i++) {
            $stmt->execute();
            $tickets_created++;
        }
    }
    
    $conn->commit();
    
    $ticket_message = $tickets_created > 0 
        ? "và đã tặng {$tickets_created} vé quay may mắn" 
        : "";
    
    echo json_encode([
        'success' => true,
        'message' => "Duyệt đơn hàng thành công {$ticket_message}",
        'tickets_created' => $tickets_created
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>

