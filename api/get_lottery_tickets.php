<?php
require_once 'connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    sendError('Bạn cần đăng nhập để xem vé quay', 401);
}

$userId = getCurrentUserId();

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Get active lottery tickets for user
    $sql = "SELECT id, order_id, ticket_type, status, created_at, expires_at 
            FROM lottery_tickets 
            WHERE user_id = ? AND status = 'active' 
            ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $tickets = $stmt->fetchAll();

    // Count total tickets
    $countSql = "SELECT COUNT(*) as total FROM lottery_tickets WHERE user_id = ? AND status = 'active'";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$userId]);
    $totalTickets = $countStmt->fetchColumn();

    sendSuccess([
        'tickets' => $tickets,
        'total_tickets' => (int)$totalTickets
    ]);

} catch (Exception $e) {
    error_log("Get lottery tickets error: " . $e->getMessage());
    sendError('Không thể lấy thông tin vé quay: ' . $e->getMessage(), 500);
}
?>
