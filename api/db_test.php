<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    error_log('db_test.php: Starting');
    
    // Test database connection
    $host = 'localhost';
    $dbname = 'nangluongmattroi';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    error_log('db_test.php: Database connected');
    
    // Test simple query
    $sql = "SELECT COUNT(*) as count FROM solar_surveys";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log('db_test.php: Query executed, count: ' . $result['count']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'survey_count' => $result['count']
    ]);
    
} catch (Exception $e) {
    error_log('db_test.php: Exception: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
