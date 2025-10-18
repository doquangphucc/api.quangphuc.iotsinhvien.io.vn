<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    error_log('simple_test.php: Starting');
    
    // Test basic PHP
    echo json_encode([
        'success' => true,
        'message' => 'PHP is working',
        'php_version' => PHP_VERSION,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log('simple_test.php: Exception: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'PHP error: ' . $e->getMessage()
    ]);
}
?>