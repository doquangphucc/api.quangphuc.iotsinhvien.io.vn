<?php
// Simple test script - minimal dependencies
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== SIMPLE DEBUG TEST ===\n";

// Test 1: Basic PHP
echo "1. PHP Version: " . phpversion() . "\n";

// Test 2: Check if config exists
echo "2. Checking config.php...\n";
if (file_exists('config.php')) {
    echo "✅ config.php exists\n";
    require_once 'config.php';
    echo "✅ config.php loaded\n";
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    echo "DB_USER: " . DB_USER . "\n";
} else {
    echo "❌ config.php not found\n";
    exit;
}

// Test 3: Database connection
echo "\n3. Testing database connection...\n";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database connected successfully\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 4: Check if lottery_rewards table exists
echo "\n4. Checking lottery_rewards table...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'lottery_rewards'");
    $result = $stmt->fetch();
    if ($result) {
        echo "✅ lottery_rewards table exists\n";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE lottery_rewards");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Columns: " . count($columns) . "\n";
        foreach ($columns as $col) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "❌ lottery_rewards table does NOT exist\n";
        echo "Please import database/rewards_table.sql\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "\n";
}

// Test 5: Check users table
echo "\n5. Checking users table...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Users table exists with " . $result['count'] . " users\n";
} catch (Exception $e) {
    echo "❌ Users table error: " . $e->getMessage() . "\n";
}

// Test 6: Check lottery_tickets table
echo "\n6. Checking lottery_tickets table...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM lottery_tickets");
    $result = $stmt->fetch();
    echo "✅ lottery_tickets table exists with " . $result['count'] . " tickets\n";
} catch (Exception $e) {
    echo "❌ lottery_tickets table error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
