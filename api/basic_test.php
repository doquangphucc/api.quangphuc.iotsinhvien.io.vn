<?php
// Ultra simple test - no database
echo "PHP is working!<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
echo "PHP version: " . phpversion() . "<br>";

// Test file includes
echo "<br>Testing file includes:<br>";
if (file_exists('config.php')) {
    echo "✅ config.php exists<br>";
    try {
        require_once 'config.php';
        echo "✅ config.php loaded<br>";
        echo "DB_HOST: " . DB_HOST . "<br>";
    } catch (Exception $e) {
        echo "❌ config.php error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ config.php not found<br>";
}

if (file_exists('connect.php')) {
    echo "✅ connect.php exists<br>";
} else {
    echo "❌ connect.php not found<br>";
}

if (file_exists('auth_helpers.php')) {
    echo "✅ auth_helpers.php exists<br>";
} else {
    echo "❌ auth_helpers.php not found<br>";
}

if (file_exists('save_lottery_reward.php')) {
    echo "✅ save_lottery_reward.php exists<br>";
} else {
    echo "❌ save_lottery_reward.php not found<br>";
}

echo "<br>Test complete!";
?>
