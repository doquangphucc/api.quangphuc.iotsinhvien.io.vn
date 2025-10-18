<?php
// Test save_lottery_reward.php trực tiếp
header('Content-Type: application/json');

// Mock session
session_start();
$_SESSION['user_id'] = 1;

// Mock POST data
$_SERVER['REQUEST_METHOD'] = 'POST';

// Mock input
$testData = [
    'reward_name' => 'Test Reward',
    'reward_type' => 'gift',
    'reward_value' => 'Test Value',
    'reward_code' => null,
    'ticket_id' => null,
    'expires_days' => 30
];

// Simulate POST input
file_put_contents('php://temp', json_encode($testData));

echo "Testing save_lottery_reward.php...\n";

try {
    // Include the actual API
    ob_start();
    include 'save_lottery_reward.php';
    $output = ob_get_clean();
    
    echo "API Output: " . $output . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>

