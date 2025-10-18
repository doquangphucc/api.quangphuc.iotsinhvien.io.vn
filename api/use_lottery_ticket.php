<?php
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

// User must be logged in to use lottery ticket
requireAuth();
$userId = getCurrentUserId();

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Get one active ticket for user
    $sql = "SELECT id FROM lottery_tickets 
            WHERE user_id = ? AND status = 'active' 
            ORDER BY created_at ASC 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        sendError('Bạn không có vé quay nào', 400);
    }

    // Mark ticket as used
    $updateSql = "UPDATE lottery_tickets SET status = 'used' WHERE id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([$ticket['id']]);

    sendSuccess([
        'ticket_id' => $ticket['id'],
        'message' => 'Vé quay đã được sử dụng'
    ]);

} catch (Exception $e) {
    error_log("Use lottery ticket error: " . $e->getMessage());
    sendError('Không thể sử dụng vé quay: ' . $e->getMessage(), 500);
}
?>
