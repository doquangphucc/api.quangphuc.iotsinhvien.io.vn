<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    error_log('get_survey_detail.php: Starting request');
    
    require_once 'session.php';
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        error_log('get_survey_detail.php: User not logged in');
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
        exit;
    }
    
    $userId = getCurrentUserId();
    error_log('get_survey_detail.php: User ID: ' . $userId);
    
    // Simple test with authentication
    echo json_encode([
        'success' => true,
        'message' => 'API is working with authentication',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'user_id' => $userId
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
