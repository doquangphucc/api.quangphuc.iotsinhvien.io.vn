<?php
// Debug version of save_lottery_reward.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG SAVE LOTTERY REWARD ===\n\n";

// Step 1: Check session
echo "1. Checking session...\n";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "✅ Session user_id: " . $_SESSION['user_id'] . "\n";
} else {
    echo "❌ No session user_id found\n";
    echo "Session data: " . print_r($_SESSION, true) . "\n";
}

// Step 2: Check request method
echo "\n2. Checking request method...\n";
echo "Request method: " . $_SERVER['REQUEST_METHOD'] . "\n";

// Step 3: Check input data
echo "\n3. Checking input data...\n";
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ JSON decode error: " . json_last_error_msg() . "\n";
} else {
    echo "✅ JSON decoded successfully\n";
    echo "Input data: " . print_r($input, true) . "\n";
}

// Step 4: Check database connection
echo "\n4. Checking database connection...\n";
try {
    require_once 'config.php';
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database connected\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Step 5: Check lottery_rewards table
echo "\n5. Checking lottery_rewards table...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'lottery_rewards'");
    $result = $stmt->fetch();
    if ($result) {
        echo "✅ lottery_rewards table exists\n";
        
        // Test insert with mock data
        echo "\n6. Testing insert with mock data...\n";
        $testData = [
            'user_id' => 1,
            'ticket_id' => null,
            'reward_name' => 'Debug Test Reward',
            'reward_type' => 'gift',
            'reward_value' => 'Debug Value',
            'reward_code' => 'DEBUG123',
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
            echo "✅ Test insert SUCCESS, ID: $insertId\n";
            
            // Clean up
            $pdo->prepare("DELETE FROM lottery_rewards WHERE id = ?")->execute([$insertId]);
            echo "✅ Test record cleaned up\n";
        } else {
            echo "❌ Test insert FAILED\n";
        }
        
    } else {
        echo "❌ lottery_rewards table does NOT exist\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?>
