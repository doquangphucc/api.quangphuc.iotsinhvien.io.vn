<?php
require_once 'connect.php';
require_once 'auth_helpers.php';

// Test API save_lottery_reward.php với dữ liệu mẫu
echo "🧪 Testing save_lottery_reward.php API...\n\n";

try {
    // Simulate POST data
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Mock session data (thay bằng user_id thật)
    $_SESSION['user_id'] = 1; // Thay bằng user_id thật từ database
    
    // Mock input data
    $testData = [
        'reward_name' => 'Test Reward',
        'reward_type' => 'gift',
        'reward_value' => 'Test Value',
        'reward_code' => null,
        'ticket_id' => null,
        'expires_days' => 30
    ];
    
    echo "📝 Test data: " . json_encode($testData) . "\n\n";
    
    // Test database connection
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "✅ Database connection successful\n";
    
    // Test table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'lottery_rewards'");
    if ($stmt->fetch()) {
        echo "✅ Table lottery_rewards exists\n";
    } else {
        echo "❌ Table lottery_rewards does not exist\n";
        exit;
    }
    
    // Test user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo "✅ User ID " . $_SESSION['user_id'] . " exists\n";
    } else {
        echo "❌ User ID " . $_SESSION['user_id'] . " does not exist\n";
        echo "Available users:\n";
        $stmt = $pdo->query("SELECT id, username FROM users LIMIT 5");
        while ($row = $stmt->fetch()) {
            echo "- ID: " . $row['id'] . ", Username: " . $row['username'] . "\n";
        }
        exit;
    }
    
    // Test insert
    $expiresAt = date('Y-m-d H:i:s', strtotime("+30 days"));
    $rewardData = [
        'user_id' => $_SESSION['user_id'],
        'ticket_id' => null,
        'reward_name' => $testData['reward_name'],
        'reward_type' => $testData['reward_type'],
        'reward_value' => $testData['reward_value'],
        'reward_code' => 'TEST123',
        'status' => 'pending',
        'expires_at' => $expiresAt
    ];
    
    echo "📊 Attempting insert with data: " . json_encode($rewardData) . "\n";
    
    $rewardId = $db->insert('lottery_rewards', $rewardData);
    echo "✅ Insert successful! Reward ID: " . $rewardId . "\n";
    
    // Clean up test data
    $pdo->prepare("DELETE FROM lottery_rewards WHERE id = ?")->execute([$rewardId]);
    echo "🧹 Test data cleaned up\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
