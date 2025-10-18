<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'connect.php';

try {
    error_log('check_surveys.php: Starting');
    
    // Get database connection
    $pdo = $db->getConnection();
    error_log('check_surveys.php: Database connected');
    
    // Check all surveys
    $sql = "SELECT id, user_id, full_name, created_at FROM solar_surveys ORDER BY id DESC LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log('check_surveys.php: Found ' . count($surveys) . ' surveys');
    
    echo json_encode([
        'success' => true,
        'message' => 'Found ' . count($surveys) . ' surveys',
        'data' => $surveys
    ]);
    
} catch (Exception $e) {
    error_log('check_surveys.php: Exception: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
