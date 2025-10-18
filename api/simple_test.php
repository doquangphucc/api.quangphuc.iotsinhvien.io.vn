<?php
require_once 'connect.php';

// Simple test to insert a reward
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get first user ID
    $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "❌ No users found in database\n";
        exit;
    }
    
    $userId = $user['id'];
    echo "✅ Using user ID: " . $userId . "\n";
    
    // Test data
    $rewardData = [
        'user_id' => $userId,
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
    
    echo "📊 Inserting data: " . json_encode($rewardData) . "\n";
    
    $rewardId = $db->insert('lottery_rewards', $rewardData);
    echo "✅ SUCCESS! Reward ID: " . $rewardId . "\n";
    
    // Clean up
    $pdo->prepare("DELETE FROM lottery_rewards WHERE id = ?")->execute([$rewardId]);
    echo "🧹 Cleaned up test data\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
