<?php
// Tắt hiển thị lỗi để không làm hỏng JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("=== SAVE SURVEY REQUEST START ===");

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
        $selected_battery = $results['batteryOptions'][$battery_index];
        
        // Lấy thông tin region name
        $region_names = [
            'mien-bac' => 'Miền Bắc',
            'mien-trung' => 'Miền Trung',
            'mien-nam' => 'Miền Nam'
        ];
        $region_name = $region_names[$data['region']] ?? 'Không xác định';
        
        // Lấy thông tin panel
        $panel_info = $results['panelInfo'];
        
        // Tính total_cost_without_battery
        $total_cost_without_battery = $results['totalCost'] - $selected_battery['totalCost'];
        
        // Chuyển billBreakdown thành JSON
        $bill_breakdown_json = json_encode($results['billBreakdown']);

        // DEBUG: Log dữ liệu trước khi insert
        error_log("=== SAVE SURVEY DEBUG ===");
        error_log("Survey ID: " . $survey_id);
        error_log("Battery Index: " . $battery_index);
        error_log("Panel Info: " . print_r($panel_info, true));
        error_log("Accessories: " . print_r($results['accessories'], true));
        error_log("DC Cable: " . print_r($results['dcCable'], true));

        $stmt2 = $conn->prepare("
            INSERT INTO survey_results 
            (survey_id, monthly_kwh, sun_hours, region_name,
             panel_id, panel_name, panel_power, panel_price, panels_needed, panel_cost, 
             energy_per_panel_per_day, total_capacity,
             inverter_id, inverter_name, inverter_capacity, inverter_price,
             cabinet_id, cabinet_name, cabinet_capacity, cabinet_price,
             battery_needed, battery_type, battery_id, battery_name, battery_capacity, 
             battery_quantity, battery_unit_price, battery_cost,
             bach_z_qty, bach_z_price, bach_z_cost,
             clip_qty, clip_price, clip_cost,
             jack_mc4_qty, jack_mc4_price, jack_mc4_cost,
             dc_cable_length, dc_cable_price, dc_cable_cost,
             accessories_cost, labor_cost, 
             total_cost_without_battery, total_cost, bill_breakdown)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt2) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $stmt2->bind_param(
            "iddsiisdddddisdddisddddsisdddiddddddddiddddddds",
            $survey_id,                                 // i - survey_id (INT)
            $results['monthlyKWh'],                     // d - monthly_kwh (DECIMAL)
            $results['sunHours'],                       // d - sun_hours (DECIMAL)
            $region_name,                               // s - region_name (VARCHAR)
            $panel_info['id'],                          // i - panel_id (INT - FIXED!)
            $panel_info['name'],                        // s - panel_name (VARCHAR)
            $panel_info['power'],                       // d - panel_power (DECIMAL)
            $panel_info['price'],                       // d - panel_price (DECIMAL)
            $results['panelsNeeded'],                   // i - panels_needed (INT - FIXED!)
            $results['panelCost'],                      // d - panel_cost (DECIMAL)
            $results['energyPerPanelPerDay'],           // d - energy_per_panel_per_day (DECIMAL)
            $results['totalCapacity'],                  // d - total_capacity (DECIMAL)
            $results['inverter']['id'],                 // i - inverter_id (INT)
            $results['inverter']['name'],               // s - inverter_name (VARCHAR)
            $results['inverter']['capacity'],           // d - inverter_capacity (DECIMAL)
            $results['inverter']['price'],              // d - inverter_price (DECIMAL)
            $results['cabinet']['id'],                  // i - cabinet_id (INT)
            $results['cabinet']['name'],                // s - cabinet_name (VARCHAR)
            $results['cabinet']['capacity'],            // d - cabinet_capacity (DECIMAL)
            $results['cabinet']['price'],               // d - cabinet_price (DECIMAL)
            $results['batteryNeeded'],                  // d - battery_needed (DECIMAL)
            $battery_type,                              // s - battery_type (VARCHAR)
            $selected_battery['id'],                    // i - battery_id (INT)
            $selected_battery['name'],                  // s - battery_name (VARCHAR)
            $selected_battery['capacity'],              // d - battery_capacity (DECIMAL)
            $selected_battery['quantity'],              // i - battery_quantity (INT - FIXED from d to i)
            $selected_battery['price'],                 // d - battery_unit_price (DECIMAL)
            $selected_battery['totalCost'],             // d - battery_cost (DECIMAL)
            $results['accessories']['bachZ']['qty'],    // i - bach_z_qty (INT)
            $results['accessories']['bachZ']['price'],  // d - bach_z_price (DECIMAL - FIXED from i)
            $results['accessories']['bachZ']['cost'],   // d - bach_z_cost (DECIMAL)
            $results['accessories']['clip']['qty'],     // d - clip_qty (INT - but using d is ok)
            $results['accessories']['clip']['price'],   // d - clip_price (DECIMAL - FIXED from i)
            $results['accessories']['clip']['cost'],    // d - clip_cost (DECIMAL - FIXED from i)
            $results['accessories']['jackMC4']['qty'],  // d - jack_mc4_qty (INT)
            $results['accessories']['jackMC4']['price'],// d - jack_mc4_price (DECIMAL)
            $results['accessories']['jackMC4']['cost'], // d - jack_mc4_cost (DECIMAL - FIXED from i)
            $results['dcCable']['length'],              // i - dc_cable_length (INT)
            $results['dcCable']['price'],               // d - dc_cable_price (DECIMAL)
            $results['dcCable']['cost'],                // d - dc_cable_cost (DECIMAL)
            $results['accessoriesCost'],                // d - accessories_cost (DECIMAL)
            $results['laborCost'],                      // d - labor_cost (DECIMAL)
            $total_cost_without_battery,                // d - total_cost_without_battery (DECIMAL)
            $results['totalCost'],                      // d - total_cost (DECIMAL)
            $bill_breakdown_json                        // s - bill_breakdown (JSON/TEXT)
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
