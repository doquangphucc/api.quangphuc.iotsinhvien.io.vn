<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    error_log('test_survey_detail.php: Starting test');
    
    require_once 'connect.php';
    error_log('test_survey_detail.php: connect.php loaded');
    
    // Test basic functions
    if (function_exists('isLoggedIn')) {
        error_log('test_survey_detail.php: isLoggedIn function exists');
        $isLoggedIn = isLoggedIn();
        error_log('test_survey_detail.php: isLoggedIn result: ' . ($isLoggedIn ? 'true' : 'false'));
    } else {
        error_log('test_survey_detail.php: isLoggedIn function NOT found');
    }
    
    if (function_exists('getCurrentUserId')) {
        error_log('test_survey_detail.php: getCurrentUserId function exists');
        $userId = getCurrentUserId();
        error_log('test_survey_detail.php: getCurrentUserId result: ' . $userId);
    } else {
        error_log('test_survey_detail.php: getCurrentUserId function NOT found');
    }
    
    // Test database connection
    $pdo = $db->getConnection();
    error_log('test_survey_detail.php: Database connection successful');
    
    // Test simple query
    $sql = "SELECT COUNT(*) as count FROM solar_surveys";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log('test_survey_detail.php: Survey count: ' . $result['count']);
    
    // Test survey query
    $surveyId = $_GET['id'] ?? '9';
    $sql = "SELECT s.id, s.user_id, s.full_name FROM solar_surveys s WHERE s.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$surveyId]);
    $survey = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($survey) {
        error_log('test_survey_detail.php: Survey found: ' . json_encode($survey));
    } else {
        error_log('test_survey_detail.php: No survey found for ID: ' . $surveyId);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Test completed successfully',
        'data' => [
            'isLoggedIn' => $isLoggedIn ?? false,
            'userId' => $userId ?? null,
            'surveyCount' => $result['count'] ?? 0,
            'survey' => $survey ?? null
        ]
    ]);
    
} catch (Exception $e) {
    error_log('test_survey_detail.php: Exception: ' . $e->getMessage());
    error_log('test_survey_detail.php: Stack trace: ' . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Test failed: ' . $e->getMessage(),
        'error' => $e->getTraceAsString()
    ]);
}
?>
