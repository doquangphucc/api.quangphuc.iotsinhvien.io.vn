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

    // Get one active ticket for user (with pre-assigned reward if any)
    $sql = "SELECT lt.id, lt.pre_assigned_reward_id, rt.reward_name, rt.reward_type, 
                   rt.reward_value, rt.reward_description, rt.reward_quantity
            FROM lottery_tickets lt
            LEFT JOIN reward_templates rt ON lt.pre_assigned_reward_id = rt.id
            WHERE lt.user_id = ? AND lt.status = 'active' 
            ORDER BY lt.created_at ASC 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        sendError('Bạn không có vé quay nào', 400);
    }

    // Mark ticket as used
    $updateSql = "UPDATE lottery_tickets SET status = 'used' WHERE id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([$ticket['id']]);

    // Determine reward
    $selectedReward = null;
    $voucherCode = null;
    
    if ($ticket['pre_assigned_reward_id']) {
        // Use pre-assigned reward from admin
        $selectedReward = [
            'name' => $ticket['reward_name'],
            'type' => $ticket['reward_type'],
            'value' => $ticket['reward_value'],
            'description' => $ticket['reward_description'],
            'template_id' => $ticket['pre_assigned_reward_id']
        ];
    } else {
        // Random reward - use default logic
        $rewards = [
            ['name' => 'Voucher giảm 100.000đ', 'type' => 'voucher', 'value' => 100000, 'weight' => 30],
            ['name' => 'Voucher giảm 200.000đ', 'type' => 'voucher', 'value' => 200000, 'weight' => 20],
            ['name' => 'Tiền mặt 50.000đ', 'type' => 'cash', 'value' => 50000, 'weight' => 15],
            ['name' => 'Tiền mặt 100.000đ', 'type' => 'cash', 'value' => 100000, 'weight' => 10],
            ['name' => 'Quà tặng phụ kiện', 'type' => 'gift', 'value' => null, 'weight' => 10],
            ['name' => 'Chúc may mắn lần sau!', 'type' => 'gift', 'value' => null, 'weight' => 15]
        ];

        $totalWeight = array_sum(array_column($rewards, 'weight'));
        $random = mt_rand(1, $totalWeight);
        $currentWeight = 0;
        
        foreach ($rewards as $reward) {
            $currentWeight += $reward['weight'];
            if ($random <= $currentWeight) {
                $selectedReward = $reward;
                break;
            }
        }
    }

    // If reward is voucher, create voucher in vouchers table
    if ($selectedReward['type'] === 'voucher') {
        $voucherCode = 'VC' . strtoupper(uniqid());
        $voucherDescription = $selectedReward['name'] . ' - Từ vòng quay may mắn';
        $expiryDate = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $voucherSql = "INSERT INTO vouchers (code, discount_amount, description, expires_at) 
                       VALUES (?, ?, ?, ?)";
        $voucherStmt = $pdo->prepare($voucherSql);
        $voucherStmt->execute([
            $voucherCode,
            $selectedReward['value'],
            $voucherDescription,
            $expiryDate
        ]);
    }

    // Save reward to lottery_rewards
    $rewardSql = "INSERT INTO lottery_rewards 
                  (user_id, reward_template_id, reward_name, reward_type, reward_value, 
                   reward_description, voucher_code, status, ticket_id, won_at, expires_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))";
    
    $rewardStatus = 'pending';
    
    $rewardStmt = $pdo->prepare($rewardSql);
    $rewardStmt->execute([
        $userId,
        $selectedReward['template_id'] ?? null,
        $selectedReward['name'],
        $selectedReward['type'],
        $selectedReward['value'] ?? null,
        $selectedReward['description'] ?? $selectedReward['name'],
        $voucherCode,
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
