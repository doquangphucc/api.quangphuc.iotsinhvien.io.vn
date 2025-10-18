<?php
require_once 'api/config.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $stmt = $pdo->query('SHOW TABLES LIKE "lottery_rewards"');
    $result = $stmt->fetch();
    
    if ($result) {
        echo "Bảng lottery_rewards tồn tại\n";
        echo "Cấu trúc bảng:\n";
        $stmt = $pdo->query('DESCRIBE lottery_rewards');
        while ($row = $stmt->fetch()) {
            echo $row['Field'] . ' - ' . $row['Type'] . "\n";
        }
    } else {
        echo "Bảng lottery_rewards KHÔNG tồn tại\n";
        echo "Cần tạo bảng trước!\n";
    }
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
?>
