<?php
// Admin API: Update Survey Data
// Cập nhật thông tin khảo sát (chỉ admin)

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

// Check if user is admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền cập nhật khảo sát'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['survey_id'])) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $surveyId = intval($input['survey_id']);
    
    // Check if survey exists
    $stmt = $conn->prepare("SELECT id FROM solar_surveys WHERE id = ?");
    $stmt->bind_param("i", $surveyId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy khảo sát'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Update customer info in solar_surveys table
    if (isset($input['full_name'])) {
        $stmt = $conn->prepare("UPDATE solar_surveys SET full_name = ? WHERE id = ?");
        $fullName = $input['full_name'];
        $stmt->bind_param("si", $fullName, $surveyId);
        $stmt->execute();
    }
    if (isset($input['phone'])) {
        $stmt = $conn->prepare("UPDATE solar_surveys SET phone = ? WHERE id = ?");
        $phone = $input['phone'];
        $stmt->bind_param("si", $phone, $surveyId);
        $stmt->execute();
    }
    
    // Update results in survey_results table
    $results = $input['results'] ?? [];
    
    // Map field names from input to database columns
    $fieldMap = [
        'panelId' => 'panel_id',
        'panelName' => 'panel_name',
        'panelPrice' => 'panel_price',
        'panelsNeeded' => 'panels_needed',
        'panelCost' => 'panel_cost',
        'inverterId' => 'inverter_id',
        'inverterName' => 'inverter_name',
        'inverterPrice' => 'inverter_price',
        'batteryId' => 'battery_id',
        'batteryName' => 'battery_name',
        'batteryUnitPrice' => 'battery_unit_price',
        'batteryQuantity' => 'battery_quantity',
        'batteryCost' => 'battery_cost',
        'cabinetId' => 'cabinet_id',
        'cabinetName' => 'cabinet_name',
        'cabinetPrice' => 'cabinet_price',
        'laborCost' => 'labor_cost',
        'totalCost' => 'total_cost',
        'accessories' => 'accessories' // JSON field
    ];
    
    // Build UPDATE query for survey_results
    $updateFields = [];
    $updateParams = [];
    
    foreach ($fieldMap as $inputKey => $dbKey) {
        if (isset($results[$inputKey])) {
            if ($inputKey === 'accessories') {
                // Store accessories as JSON
                $updateFields[] = "`$dbKey` = ?";
                $updateParams[] = json_encode($results[$inputKey], JSON_UNESCAPED_UNICODE);
            } else if (in_array($inputKey, ['panelId', 'inverterId', 'batteryId', 'cabinetId', 'panelsNeeded', 'batteryQuantity'])) {
                // Integer fields
                $updateFields[] = "`$dbKey` = ?";
                $updateParams[] = intval($results[$inputKey]);
            } else if (in_array($inputKey, ['panelPrice', 'panelCost', 'inverterPrice', 'batteryUnitPrice', 'batteryCost', 'cabinetPrice', 'laborCost', 'totalCost'])) {
                // Numeric fields
                $updateFields[] = "`$dbKey` = ?";
                $updateParams[] = floatval($results[$inputKey]);
            } else {
                // String fields
                $updateFields[] = "`$dbKey` = ?";
                $updateParams[] = mysqli_real_escape_string($conn, $results[$inputKey]);
            }
        }
    }
    
    if (!empty($updateFields)) {
        // Check if survey_results record exists
        $stmt = $conn->prepare("SELECT survey_id FROM survey_results WHERE survey_id = ?");
        $stmt->bind_param("i", $surveyId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing record
            $sql = "UPDATE survey_results SET " . implode(', ', $updateFields) . " WHERE survey_id = ?";
            $stmt = $conn->prepare($sql);
            
            // Build bind_param types string (must match the actual data types)
            $types = '';
            foreach ($updateParams as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } else if (is_float($param) || is_numeric($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $types .= 'i'; // Add type for survey_id
            $updateParams[] = $surveyId;
            $stmt->bind_param($types, ...$updateParams);
            $stmt->execute();
        } else {
            // Insert new record (shouldn't happen, but handle it)
            $fieldNames = array_map(function($f) { 
                return str_replace('`', '', str_replace(' = ?', '', $f)); 
            }, $updateFields);
            $placeholders = str_repeat('?,', count($updateFields) - 1) . '?';
            $sql = "INSERT INTO survey_results (survey_id, " . implode(', ', $fieldNames) . 
                   ") VALUES (?, " . $placeholders . ")";
            $stmt = $conn->prepare($sql);
            
            // Build types string
            $types = 'i'; // survey_id
            foreach ($updateParams as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } else if (is_float($param) || is_numeric($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $params = array_merge([$surveyId], $updateParams);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật khảo sát thành công'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log('Error in update_survey.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

