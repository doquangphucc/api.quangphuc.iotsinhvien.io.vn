<?php
// Test database insert
require_once 'connect.php';
require_once 'auth_helpers.php';

// Mock session
session_start();
$_SESSION['user_id'] = 1;

try {
    requireAuth();
    $userId = getCurrentUserId();
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get first user
    $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $user = $stmt->fetch();
    
    if (!$user) {
        sendError('No users found in database', 500);
    }
    
    $testData = [
        'user_id' => $user['id'],
        'ticket_id' => null,
        'reward_name' => 'Test Reward',
        'reward_type' => 'gift',
        'reward_value' => 'Test Value',
        'reward_code' => 'TEST123',
        'reward_image' => null,
        'status' => 'pending',
        'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
        'notes' => null
    ];
    
    $rewardId = $db->insert('lottery_rewards', $testData);
    
    // Clean up
    $pdo->prepare("DELETE FROM lottery_rewards WHERE id = ?")->execute([$rewardId]);
    
    sendSuccess(['reward_id' => $rewardId], 'Database insert test successful');
    
} catch (Exception $e) {
    sendError('Database insert test failed: ' . $e->getMessage(), 500);
}
?>
