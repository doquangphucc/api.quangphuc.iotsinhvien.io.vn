<?php
// Test script để debug lỗi save_lottery_reward.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST SAVE LOTTERY REWARD DEBUG ===\n\n";

// Test 1: Kiểm tra database connection
echo "1. Testing database connection...\n";
try {
    require_once 'config.php';
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database connection OK\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Kiểm tra bảng lottery_rewards
echo "\n2. Testing lottery_rewards table...\n";
try {
    $stmt = $pdo->query("DESCRIBE lottery_rewards");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Table lottery_rewards exists with " . count($columns) . " columns\n";
    
    // Kiểm tra các cột quan trọng
    $requiredColumns = ['id', 'user_id', 'reward_name', 'reward_type', 'reward_value', 'reward_code', 'reward_image', 'status', 'ticket_id', 'expires_at', 'notes'];
    $foundColumns = array_column($columns, 'Field');
    
    foreach ($requiredColumns as $col) {
        if (in_array($col, $foundColumns)) {
            echo "  ✅ Column '$col' exists\n";
        } else {
            echo "  ❌ Column '$col' MISSING\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Table lottery_rewards error: " . $e->getMessage() . "\n";
}

// Test 3: Kiểm tra session
echo "\n3. Testing session...\n";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "✅ Session user_id: " . $_SESSION['user_id'] . "\n";
} else {
    echo "❌ No session user_id found\n";
    echo "Available session data: " . print_r($_SESSION, true) . "\n";
}

// Test 4: Test insert vào bảng lottery_rewards
echo "\n4. Testing insert into lottery_rewards...\n";
try {
    $testData = [
        'user_id' => 1, // Test với user_id = 1
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
    
    $fields = array_keys($testData);
    $fieldList = implode(',', $fields);
    $paramList = ':' . implode(', :', $fields);
    
    $sql = "INSERT INTO lottery_rewards ({$fieldList}) VALUES ({$paramList})";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($testData);
    
    if ($result) {
        $insertId = $pdo->lastInsertId();
        echo "✅ Test insert successful, ID: $insertId\n";
        
        // Xóa record test
        $pdo->prepare("DELETE FROM lottery_rewards WHERE id = ?")->execute([$insertId]);
        echo "✅ Test record cleaned up\n";
    } else {
        echo "❌ Test insert failed\n";
    }
} catch (Exception $e) {
    echo "❌ Test insert error: " . $e->getMessage() . "\n";
    echo "SQL Error Info: " . print_r($stmt->errorInfo(), true) . "\n";
}

// Test 5: Kiểm tra file save_lottery_reward.php
echo "\n5. Testing save_lottery_reward.php syntax...\n";
$phpFile = 'save_lottery_reward.php';
if (file_exists($phpFile)) {
    $output = shell_exec("php -l $phpFile 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "✅ PHP syntax OK\n";
    } else {
        echo "❌ PHP syntax error:\n$output\n";
    }
} else {
    echo "❌ File save_lottery_reward.php not found\n";
}

// Test 6: Kiểm tra các file dependencies
echo "\n6. Testing dependencies...\n";
$deps = ['connect.php', 'auth_helpers.php', 'config.php'];
foreach ($deps as $dep) {
    if (file_exists($dep)) {
        echo "✅ $dep exists\n";
    } else {
        echo "❌ $dep MISSING\n";
    }
}

echo "\n=== DEBUG COMPLETE ===\n";
?>