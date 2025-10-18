<?php
require_once 'connect.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Kiểm tra bảng lottery_rewards có tồn tại không
    $stmt = $pdo->query("SHOW TABLES LIKE 'lottery_rewards'");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ Bảng lottery_rewards tồn tại\n";
        
        // Kiểm tra cấu trúc bảng
        $stmt = $pdo->query("DESCRIBE lottery_rewards");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📋 Cấu trúc bảng:\n";
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
        
        // Đếm số records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM lottery_rewards");
        $count = $stmt->fetch()['count'];
        echo "📊 Số records hiện tại: " . $count . "\n";
        
    } else {
        echo "❌ Bảng lottery_rewards KHÔNG tồn tại\n";
        echo "🔧 Cần tạo bảng trước!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
