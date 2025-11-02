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

    // Get request data
    $requestData = json_decode(file_get_contents('php://input'), true);
    $quantity = isset($requestData['quantity']) ? intval($requestData['quantity']) : 1;
    
    if ($quantity < 1 || $quantity > 1000) {
        sendError('Số lượng vé quay không hợp lệ (1-1000)', 400);
    }
    
    // Get active tickets count for user
    $countSql = "SELECT COUNT(*) as total FROM lottery_tickets WHERE user_id = ? AND status = 'active'";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$userId]);
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $availableTickets = intval($countResult['total'] || 0);
    
    if ($availableTickets < $quantity) {
        sendError("Bạn chỉ có {$availableTickets} vé quay, không đủ để quay {$quantity} vé", 400);
    }
    
    // Get tickets to use (with pre-assigned reward if any)
    // Sort by ID DESC (quay vé ID lớn trước, giảm dần)
    $sql = "SELECT lt.id, lt.pre_assigned_reward_id, rt.reward_name, rt.reward_type, 
                   rt.reward_value, rt.reward_description, rt.reward_quantity
            FROM lottery_tickets lt
            LEFT JOIN reward_templates rt ON lt.pre_assigned_reward_id = rt.id
            WHERE lt.user_id = ? AND lt.status = 'active' 
            ORDER BY lt.id DESC 
            LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $quantity]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($tickets) < $quantity) {
        sendError("Không đủ vé để quay {$quantity} lần", 400);
    }

    // Default rewards for random selection
    $defaultRewards = [
        ['name' => 'Voucher giảm 100.000đ', 'type' => 'voucher', 'value' => 100000, 'weight' => 30],
        ['name' => 'Voucher giảm 200.000đ', 'type' => 'voucher', 'value' => 200000, 'weight' => 20],
        ['name' => 'Tiền mặt 50.000đ', 'type' => 'cash', 'value' => 50000, 'weight' => 15],
        ['name' => 'Tiền mặt 100.000đ', 'type' => 'cash', 'value' => 100000, 'weight' => 10],
        ['name' => 'Quà tặng phụ kiện', 'type' => 'gift', 'value' => null, 'weight' => 10],
        ['name' => 'Chúc may mắn lần sau!', 'type' => 'gift', 'value' => null, 'weight' => 15]
    ];
    
    $totalWeight = array_sum(array_column($defaultRewards, 'weight'));
    
    // Process each ticket
    $rewards = [];
    $ticketIds = [];
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        foreach ($tickets as $ticket) {
            $ticketIds[] = $ticket['id'];
            $selectedReward = null;
            $voucherCode = null;
            
            // Determine reward for this ticket
            if ($ticket['pre_assigned_reward_id'] && $ticket['reward_name']) {
                // Use pre-assigned reward from admin
                $selectedReward = [
                    'name' => $ticket['reward_name'],
                    'type' => $ticket['reward_type'],
                    'value' => $ticket['reward_value'],
                    'description' => $ticket['reward_description'],
                    'template_id' => $ticket['pre_assigned_reward_id']
                ];
            } else {
                // Random reward
                $random = mt_rand(1, $totalWeight);
                $currentWeight = 0;
                
                foreach ($defaultRewards as $reward) {
                    $currentWeight += $reward['weight'];
                    if ($random <= $currentWeight) {
                        $selectedReward = $reward;
                        break;
                    }
                }
            }
            
            // If reward is voucher, create voucher in vouchers table
            if ($selectedReward && $selectedReward['type'] === 'voucher') {
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
            $createdReward = $getRewardStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($createdReward) {
                $rewards[] = $createdReward;
            }
        }
        
        // Delete tickets after processing (xóa vé sau khi quay)
        if (!empty($ticketIds)) {
            $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
            $deleteSql = "DELETE FROM lottery_tickets WHERE id IN ({$placeholders})";
            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->execute($ticketIds);
        }
        
        $pdo->commit();
        
        sendSuccess([
            'tickets_used' => count($ticketIds),
            'rewards' => $rewards,
            'message' => "Đã quay thành công {$quantity} vé!"
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Use lottery ticket error: " . $e->getMessage());
    sendError('Không thể sử dụng vé quay: ' . $e->getMessage(), 500);
}
?>
