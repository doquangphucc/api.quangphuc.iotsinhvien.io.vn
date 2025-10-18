<?php
// Quick test for lottery_rewards table
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== LOTTERY REWARDS TABLE TEST ===\n\n";

try {
    require_once 'config.php';
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database connected\n\n";
    
    // Check if lottery_rewards table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'lottery_rewards'");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ lottery_rewards table EXISTS\n";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE lottery_rewards");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Columns: " . count($columns) . "\n";
        
        foreach ($columns as $col) {
            echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
        
        // Test insert
        echo "\n--- Testing INSERT ---\n";
        $testData = [
            'user_id' => 1,
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
            echo "✅ Test insert SUCCESS, ID: $insertId\n";
            
            // Clean up
            $pdo->prepare("DELETE FROM lottery_rewards WHERE id = ?")->execute([$insertId]);
            echo "✅ Test record cleaned up\n";
        } else {
            echo "❌ Test insert FAILED\n";
        }
        
    } else {
        echo "❌ lottery_rewards table DOES NOT EXIST!\n";
        echo "Please import database/complete_database.sql\n";
    }
    
    // Check users table
    echo "\n--- Checking users table ---\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "Users count: " . $result['count'] . "\n";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT id, username FROM users LIMIT 3");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample users:\n";
        foreach ($users as $user) {
            echo "- ID: {$user['id']}, Username: {$user['username']}\n";
        }
    }
    
    // Check lottery_tickets table
    echo "\n--- Checking lottery_tickets table ---\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM lottery_tickets");
    $result = $stmt->fetch();
    echo "Lottery tickets count: " . $result['count'] . "\n";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT id, user_id, status FROM lottery_tickets LIMIT 3");
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample tickets:\n";
        foreach ($tickets as $ticket) {
            echo "- ID: {$ticket['id']}, User: {$ticket['user_id']}, Status: {$ticket['status']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
