<?php
// Tắt hiển thị lỗi để không làm hỏng JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Bắt đầu output buffering để kiểm soát output
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once 'db_mysqli.php'; // MySQLi connection
    require_once 'session.php';
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối: ' . $e->getMessage()]);
    exit();
}

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để lưu khảo sát']);
    exit();
}

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit();
}

// Validate dữ liệu
$required_fields = ['region', 'phase', 'solarPanel', 'monthlyBill', 'usageTime'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Thiếu trường: $field"]);
        exit();
    }
}

// Validate results nếu có
if (isset($data['results'])) {
    $required_result_fields = ['monthlyKWh', 'sunHours', 'panelsNeeded', 'panelCost', 
                                'inverter', 'cabinet', 'batteryNeeded', 'totalCost'];
    foreach ($required_result_fields as $field) {
        if (!isset($data['results'][$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Thiếu kết quả: $field"]);
            exit();
        }
    }
}

try {
    $conn->begin_transaction();

    $user_id = $_SESSION['user_id'];
    
    // Lấy thông tin user từ database vì session không có full_name và phone
    $user_stmt = $conn->prepare("SELECT full_name, phone FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_data = $user_result->fetch_assoc();
    $user_stmt->close();
    
    if (!$user_data) {
        throw new Exception('Không tìm thấy thông tin người dùng');
    }
    
    $full_name = $user_data['full_name'];
    $phone = $user_data['phone'];

    // Insert vào bảng solar_surveys
    $stmt = $conn->prepare("
        INSERT INTO solar_surveys 
        (user_id, full_name, phone, region, phase, solar_panel_type, monthly_bill, usage_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isssiiis",
        $user_id,
        $full_name,
        $phone,
        $data['region'],
        $data['phase'],
        $data['solarPanel'],
        $data['monthlyBill'],
        $data['usageTime']
    );

    if (!$stmt->execute()) {
        throw new Exception('Lỗi khi lưu thông tin khảo sát: ' . $stmt->error);
    }

    $survey_id = $conn->insert_id;

    // Nếu có kết quả tính toán, lưu vào survey_results
    if (isset($data['results'])) {
        $results = $data['results'];
        
        // Xác định battery type được chọn
        $battery_type = isset($data['selectedBattery']) ? $data['selectedBattery'] : 
                       ($results['batteryOptions'][0]['totalCost'] <= $results['batteryOptions'][1]['totalCost'] ? '8cell' : '16cell');
        
        $battery_index = ($battery_type === '8cell') ? 0 : 1;
        $battery_quantity = $results['batteryOptions'][$battery_index]['quantity'];
        $battery_cost = $results['batteryOptions'][$battery_index]['totalCost'];

        $stmt2 = $conn->prepare("
            INSERT INTO survey_results 
            (survey_id, monthly_kwh, sun_hours, panels_needed, panel_cost,
             inverter_id, inverter_name, inverter_price,
             cabinet_id, cabinet_name, cabinet_price,
             battery_needed, battery_type, battery_quantity, battery_cost,
             accessories_cost, labor_cost, dc_cable_cost, total_cost)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt2->bind_param(
            "iddddisdisddsiddddd",
            $survey_id,                      // i - integer
            $results['monthlyKWh'],          // d - double
            $results['sunHours'],            // d - double
            $results['panelsNeeded'],        // d - double
            $results['panelCost'],           // d - double
            $results['inverter']['id'],      // i - integer
            $results['inverter']['name'],    // s - string
            $results['inverter']['price'],   // d - double
            $results['cabinet']['id'],       // i - integer
            $results['cabinet']['name'],     // s - string (ĐÃ SỬA từ i)
            $results['cabinet']['price'],    // d - double (ĐÃ SỬA từ s)
            $results['batteryNeeded'],       // d - double (ĐÃ SỬA từ s)
            $battery_type,                   // s - string (ĐÃ SỬA từ d)
            $battery_quantity,               // i - integer
            $battery_cost,                   // d - double
            $results['accessoriesCost'],     // d - double
            $results['laborCost'],           // d - double
            $results['dcCable']['cost'],     // d - double
            $results['totalCost']            // d - double
        );

        if (!$stmt2->execute()) {
            throw new Exception('Lỗi khi lưu kết quả khảo sát: ' . $stmt2->error);
        }

        $stmt2->close();
    }

    $conn->commit();
    
    // Clear any output buffer before sending JSON
    ob_end_clean();

    echo json_encode([
        'success' => true,
        'message' => 'Đã lưu thông tin khảo sát thành công',
        'survey_id' => $survey_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    
    // Clear any output buffer before sending JSON
    ob_end_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>
