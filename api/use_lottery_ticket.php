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
    $availableTickets = (int)$countStmt->fetchColumn();
    
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
            // Get pre_assigned_reward_id (may be NULL from database)
            $preAssignedRewardId = $ticket['pre_assigned_reward_id'] ?? null;
            
            // Check if this ticket has a pre-assigned reward
            // PDO returns NULL as actual null value, so check if it's a valid integer > 0
            $hasPreAssignedReward = false;
            if ($preAssignedRewardId !== null) {
                $preAssignedRewardIdInt = intval($preAssignedRewardId);
                if ($preAssignedRewardIdInt > 0 && !empty($ticket['reward_name'])) {
                    $hasPreAssignedReward = true;
                }
            }
            
            if ($hasPreAssignedReward) {
                // Use pre-assigned reward from admin
                $selectedReward = [
                    'name' => $ticket['reward_name'],
                    'type' => $ticket['reward_type'],
                    'value' => $ticket['reward_value'],
                    'description' => $ticket['reward_description'],
                    'template_id' => intval($preAssignedRewardId)
                ];
            } else {
                // pre_assigned_reward_id = NULL or 0 or invalid → "May mắn lần sau" (KHÔNG random)
                $selectedReward = [
                    'name' => 'Chúc may mắn lần sau!',
                    'type' => 'gift',
                    'value' => null,
                    'description' => 'Hãy thử lại lần sau nhé!',
                    'template_id' => null
                ];
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
            
            // Ensure all values are properly formatted
            $rewardTemplateId = $selectedReward['template_id'] ?? null;
            $rewardName = $selectedReward['name'] ?? 'Chúc may mắn lần sau!';
            $rewardType = $selectedReward['type'] ?? 'gift';
            $rewardValue = isset($selectedReward['value']) && $selectedReward['value'] !== null ? floatval($selectedReward['value']) : null;
            $rewardDescription = $selectedReward['description'] ?? $rewardName;
            
            $rewardStmt = $pdo->prepare($rewardSql);
            $rewardStmt->execute([
                $userId,
                $rewardTemplateId,
                $rewardName,
                $rewardType,
                $rewardValue,
                $rewardDescription,
                $voucherCode, // Can be null
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
    error_log("Stack trace: " . $e->getTraceAsString());
    sendError('Không thể sử dụng vé quay: ' . $e->getMessage(), 500);
}
?>
