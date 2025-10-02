<?php
// Simple test - just insert without all the complex logic
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Simple connection
    require_once 'config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    require_once 'session.php';
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }
    
    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'No data received']);
        exit();
    }
    
    // Get user info
    $user_id = $_SESSION['user_id'];
    $user_stmt = $conn->prepare("SELECT full_name, phone FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_data = $user_result->fetch_assoc();
    $user_stmt->close();
    
    if (!$user_data) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    // Insert survey
    $stmt = $conn->prepare("
        INSERT INTO solar_surveys 
        (user_id, full_name, phone, region, phase, solar_panel_type, monthly_bill, usage_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "isssiiis",
        $user_id,
        $user_data['full_name'],
        $user_data['phone'],
        $data['region'],
        $data['phase'],
        $data['solarPanel'],
        $data['monthlyBill'],
        $data['usageTime']
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Insert failed: ' . $stmt->error);
    }
    
    $survey_id = $conn->insert_id;
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Survey saved successfully',
        'survey_id' => $survey_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
