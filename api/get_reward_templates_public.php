<?php
require_once 'connect.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get all active reward templates
    $sql = "SELECT * FROM reward_templates WHERE is_active = 1 ORDER BY reward_type ASC, id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'templates' => $templates
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Get reward templates error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải danh sách phần thưởng'
    ], JSON_UNESCAPED_UNICODE);
}
?>
