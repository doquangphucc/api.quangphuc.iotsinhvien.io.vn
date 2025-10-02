<?php
// Debug file - hiển thị lỗi chi tiết
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    require_once 'db_mysqli.php'; // MySQLi connection
    require_once 'session.php';
    
    // 1. Check session
    $debug = [
        'step' => 'Session Check',
        'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not Active',
        'session_data' => $_SESSION ?? [],
        'user_logged_in' => isset($_SESSION['user_id'])
    ];
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Not logged in',
            'debug' => $debug
        ]);
        exit();
    }
    
    // 2. Check POST data
    $rawInput = file_get_contents('php://input');
    $debug['raw_input'] = $rawInput;
    $data = json_decode($rawInput, true);
    $debug['parsed_data'] = $data;
    
    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'No data received',
            'debug' => $debug
        ]);
        exit();
    }
    
    // 3. Check required fields
    $required = ['region', 'phase', 'solarPanel', 'monthlyBill', 'usageTime'];
    $missing = [];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            $missing[] = $field;
        }
    }
    
    if ($missing) {
        $debug['missing_fields'] = $missing;
        echo json_encode([
            'success' => false,
            'message' => 'Missing fields: ' . implode(', ', $missing),
            'debug' => $debug
        ]);
        exit();
    }
    
    // 4. Check database tables
    $tables_check = [];
    $result = $conn->query("SHOW TABLES LIKE 'solar_surveys'");
    $tables_check['solar_surveys'] = ($result && $result->num_rows > 0);
    
    $result = $conn->query("SHOW TABLES LIKE 'survey_results'");
    $tables_check['survey_results'] = ($result && $result->num_rows > 0);
    
    $debug['tables'] = $tables_check;
    
    if (!$tables_check['solar_surveys'] || !$tables_check['survey_results']) {
        echo json_encode([
            'success' => false,
            'message' => 'Tables not found',
            'debug' => $debug
        ]);
        exit();
    }
    
    // 5. Try to prepare statement
    $stmt = $conn->prepare("
        INSERT INTO solar_surveys 
        (user_id, full_name, phone, region, phase, solar_panel_type, monthly_bill, usage_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        $debug['prepare_error'] = $conn->error;
        echo json_encode([
            'success' => false,
            'message' => 'Prepare statement failed',
            'debug' => $debug
        ]);
        exit();
    }
    
    // 6. Get user info from database
    $user_id = $_SESSION['user_id'];
    
    $user_stmt = $conn->prepare("SELECT full_name, phone FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_data = $user_result->fetch_assoc();
    $user_stmt->close();
    
    if (!$user_data) {
        $debug['user_query_error'] = 'User not found';
        echo json_encode([
            'success' => false,
            'message' => 'User not found in database',
            'debug' => $debug
        ]);
        exit();
    }
    
    $full_name = $user_data['full_name'];
    $phone = $user_data['phone'];
    $region = $data['region'];
    $phase = (int)$data['phase'];
    $solar_panel = (int)$data['solarPanel'];
    $monthly_bill = (int)$data['monthlyBill'];
    $usage_time = $data['usageTime'];
    
    $debug['bind_values'] = [
        'user_id' => $user_id,
        'full_name' => $full_name,
        'phone' => $phone,
        'region' => $region,
        'phase' => $phase,
        'solar_panel' => $solar_panel,
        'monthly_bill' => $monthly_bill,
        'usage_time' => $usage_time
    ];
    
    $bind_result = $stmt->bind_param(
        "isssiiis",
        $user_id,
        $full_name,
        $phone,
        $region,
        $phase,
        $solar_panel,
        $monthly_bill,
        $usage_time
    );
    
    if (!$bind_result) {
        $debug['bind_error'] = $stmt->error;
        echo json_encode([
            'success' => false,
            'message' => 'Bind param failed',
            'debug' => $debug
        ]);
        exit();
    }
    
    // 7. Try to execute (DRY RUN - not really inserting)
    // Comment out execute to avoid actual insert during debug
    /*
    if (!$stmt->execute()) {
        $debug['execute_error'] = $stmt->error;
        echo json_encode([
            'success' => false,
            'message' => 'Execute failed',
            'debug' => $debug
        ]);
        exit();
    }
    */
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Debug successful - all checks passed (no actual insert)',
        'debug' => $debug
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
