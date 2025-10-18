<?php
require_once 'connect.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "📋 Cấu trúc bảng lottery_rewards:\n";
    $stmt = $pdo->query("DESCRIBE lottery_rewards");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ") " . 
             ($column['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . 
             ($column['Default'] ? " DEFAULT " . $column['Default'] : '') . "\n";
    }
    
    echo "\n📊 Số records hiện tại: ";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM lottery_rewards");
    $count = $stmt->fetch()['count'];
    echo $count . "\n";
    
    if ($count > 0) {
        echo "\n📝 Dữ liệu mẫu:\n";
        $stmt = $pdo->query("SELECT * FROM lottery_rewards LIMIT 3");
        $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rewards as $reward) {
            echo "- ID: " . $reward['id'] . ", Name: " . $reward['reward_name'] . 
                 ", Type: " . $reward['reward_type'] . ", Status: " . $reward['status'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
