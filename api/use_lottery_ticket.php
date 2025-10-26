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

    // Define available rewards with probabilities
    $rewards = [
        ['name' => 'Giảm 10%', 'type' => 'discount', 'value' => '10%', 'weight' => 30],
        ['name' => 'Giảm 20%', 'type' => 'discount', 'value' => '20%', 'weight' => 20],
        ['name' => 'Miễn phí vận chuyển', 'type' => 'free_shipping', 'value' => 'Free', 'weight' => 15],
        ['name' => 'Tặng kèm phụ kiện', 'type' => 'accessory', 'value' => 'Gift', 'weight' => 10],
        ['name' => 'Giảm 50%', 'type' => 'discount', 'value' => '50%', 'weight' => 5],
        ['name' => 'Chúc may mắn lần sau!', 'type' => 'no_prize', 'value' => 'None', 'weight' => 20]
    ];

    // Calculate total weight
    $totalWeight = array_sum(array_column($rewards, 'weight'));
    
    // Random selection based on weight
    $random = mt_rand(1, $totalWeight);
    $currentWeight = 0;
    $selectedReward = $rewards[5]; // Default to no prize
    
    foreach ($rewards as $reward) {
        $currentWeight += $reward['weight'];
        if ($random <= $currentWeight) {
            $selectedReward = $reward;
            break;
        }
    }

    // Save reward to database
    $rewardSql = "INSERT INTO lottery_rewards 
                  (user_id, reward_name, reward_type, reward_value, reward_code, status, ticket_id, won_at, expires_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))";
    
    $rewardCode = $selectedReward['type'] !== 'no_prize' ? 'REWARD_' . strtoupper(uniqid()) : null;
    $rewardStatus = $selectedReward['type'] !== 'no_prize' ? 'pending' : 'used';
    
    $rewardStmt = $pdo->prepare($rewardSql);
    $rewardStmt->execute([
        $userId,
        $selectedReward['name'],
        $selectedReward['type'],
        $selectedReward['value'],
        $rewardCode,
        $rewardStatus,
        $ticket['id']
    ]);
    
    $rewardId = $pdo->lastInsertId();

    // Get the created reward
    $getRewardSql = "SELECT * FROM lottery_rewards WHERE id = ?";
    $getRewardStmt = $pdo->prepare($getRewardSql);
    $getRewardStmt->execute([$rewardId]);
    $createdReward = $getRewardStmt->fetch();

    sendSuccess([
        'ticket_id' => $ticket['id'],
        'reward' => $createdReward,
        'message' => 'Quay thưởng thành công'
    ]);

} catch (Exception $e) {
    error_log("Use lottery ticket error: " . $e->getMessage());
    sendError('Không thể sử dụng vé quay: ' . $e->getMessage(), 500);
}
?>
