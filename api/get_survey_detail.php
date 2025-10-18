<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    error_log('get_survey_detail.php: Starting request');
    
    // Simple test first
    echo json_encode([
        'success' => true,
        'message' => 'API is working',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION
    ]);
    exit;
    
} catch (Exception $e) {
    error_log('Error in get_survey_detail.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>
