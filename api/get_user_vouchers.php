<?php
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Phương thức không được hỗ trợ', 405);
}

requireAuth();
$userId = getCurrentUserId();

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get available voucher rewards for the user
    // Only get voucher type rewards that are pending and not expired
    $sql = "SELECT 
                id,
                reward_name,
                reward_type,
                reward_value,
                reward_description,
                voucher_code,
                reward_image,
                expires_at,
                won_at
            FROM lottery_rewards
            WHERE user_id = :user_id
                AND reward_type = 'voucher'
                AND status = 'pending'
                AND (expires_at IS NULL OR expires_at > NOW())
            ORDER BY won_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform data for easier usage
    $vouchers = [];
    foreach ($rewards as $reward) {
        $vouchers[] = [
            'id' => (int)$reward['id'],
            'code' => $reward['voucher_code'] ?: '',
            'name' => $reward['reward_name'],
            'type' => $reward['reward_type'],
            'discount_amount' => floatval($reward['reward_value']),
            'description' => $reward['reward_description'] ?: $reward['reward_name'],
            'expires_at' => $reward['expires_at'],
            'won_at' => $reward['won_at']
        ];
    }
    
    sendSuccess([
        'vouchers' => $vouchers,
        'count' => count($vouchers)
    ], 'Lấy danh sách voucher thành công');
    
} catch (Exception $e) {
    error_log("Get user vouchers error: " . $e->getMessage());
    sendError('Không thể lấy danh sách voucher: ' . $e->getMessage(), 500);
}
?>
