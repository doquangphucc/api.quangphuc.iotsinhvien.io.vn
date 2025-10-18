<?php
// Script để kiểm tra cấu trúc bảng lottery_rewards
require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== KIỂM TRA CẤU TRÚC BẢNG lottery_rewards ===\n\n";
    
    // Kiểm tra bảng có tồn tại không
    $stmt = $pdo->query("SHOW TABLES LIKE 'lottery_rewards'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ Bảng lottery_rewards CHƯA TỒN TẠI!\n";
        echo "Vui lòng import file database/rewards_table.sql vào phpMyAdmin\n";
        exit;
    }
    
    echo "✅ Bảng lottery_rewards tồn tại\n\n";
    
    // Lấy cấu trúc bảng
    $stmt = $pdo->query("DESCRIBE lottery_rewards");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Cấu trúc bảng:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-20s %-30s %-10s %-10s\n", "Field", "Type", "Null", "Default");
    echo str_repeat("-", 80) . "\n";
    
    $expectedColumns = [
        'id', 'user_id', 'reward_name', 'reward_type', 'reward_value',
        'reward_code', 'reward_image', 'status', 'ticket_id', 'won_at',
        'used_at', 'expires_at', 'notes', 'created_at', 'updated_at'
    ];
    
    $foundColumns = [];
    foreach ($columns as $col) {
        printf("%-20s %-30s %-10s %-10s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Default'] ?? 'NULL'
        );
        $foundColumns[] = $col['Field'];
    }
    
    echo "\n";
    echo "Kiểm tra các cột bắt buộc:\n";
    echo str_repeat("-", 80) . "\n";
    
    $missingColumns = array_diff($expectedColumns, $foundColumns);
    $extraColumns = array_diff($foundColumns, $expectedColumns);
    
    if (empty($missingColumns) && empty($extraColumns)) {
        echo "✅ Tất cả các cột đều khớp với schema!\n";
    } else {
        if (!empty($missingColumns)) {
            echo "❌ Thiếu các cột: " . implode(", ", $missingColumns) . "\n";
        }
        if (!empty($extraColumns)) {
            echo "⚠️  Cột thừa (không trong schema): " . implode(", ", $extraColumns) . "\n";
        }
    }
    
    // Đếm số lượng records
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM lottery_rewards");
    $count = $stmt->fetch();
    
    echo "\n";
    echo "Số lượng phần thưởng trong database: " . $count['total'] . "\n";
    
    if ($count['total'] > 0) {
        echo "\nDữ liệu mẫu (5 record gần nhất):\n";
        echo str_repeat("-", 120) . "\n";
        $stmt = $pdo->query("SELECT id, user_id, reward_name, reward_type, status, created_at 
                            FROM lottery_rewards 
                            ORDER BY created_at DESC 
                            LIMIT 5");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($samples as $sample) {
            echo sprintf("ID: %d | User: %d | %s (%s) | Status: %s | Created: %s\n",
                $sample['id'],
                $sample['user_id'],
                $sample['reward_name'],
                $sample['reward_type'],
                $sample['status'],
                $sample['created_at']
            );
        }
    }
    
    echo "\n✅ Kiểm tra hoàn tất!\n";
    
} catch (PDOException $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>

