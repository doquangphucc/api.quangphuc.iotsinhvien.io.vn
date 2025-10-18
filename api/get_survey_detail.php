<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'connect.php';

try {
    error_log('get_survey_detail.php: Starting request');
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        error_log('get_survey_detail.php: User not logged in');
        sendError('Bạn cần đăng nhập để thực hiện hành động này.', 401);
        exit;
    }
    
    $userId = getCurrentUserId();
    $surveyId = $_GET['id'] ?? null;
    
    error_log('get_survey_detail.php: User ID: ' . $userId . ', Survey ID: ' . $surveyId);
    
    if (!$surveyId) {
        sendError('Thiếu ID khảo sát');
        exit;
    }
    
    // Validate survey ID is numeric
    if (!is_numeric($surveyId)) {
        sendError('ID khảo sát không hợp lệ');
        exit;
    }
    
    $pdo = $db->getConnection();
    error_log('get_survey_detail.php: Database connection established');
    
    // Get survey with results
    $sql = "SELECT 
                s.id,
                s.user_id,
                s.full_name,
                s.phone,
                s.region,
                s.region_name,
                s.phase,
                s.solar_panel_type,
                s.monthly_bill,
                s.usage_time,
                s.created_at,
                r.monthly_kwh,
                r.sun_hours,
                r.panel_id,
                r.panel_name,
                r.panel_power,
                r.panel_price,
                r.panels_needed,
                r.panel_cost,
                r.energy_per_panel_per_day,
                r.total_capacity,
                r.inverter_id,
                r.inverter_name,
                r.inverter_capacity,
                r.inverter_price,
                r.cabinet_id,
                r.cabinet_name,
                r.cabinet_capacity,
                r.cabinet_price,
                r.battery_needed,
                r.battery_type,
                r.battery_id,
                r.battery_name,
                r.battery_capacity,
                r.battery_quantity,
                r.battery_unit_price,
                r.battery_cost,
                r.bach_z_qty,
                r.bach_z_price,
                r.bach_z_cost,
                r.clip_qty,
                r.clip_price,
                r.clip_cost,
                r.jack_mc4_qty,
                r.jack_mc4_price,
                r.jack_mc4_cost,
                r.dc_cable_length,
                r.dc_cable_price,
                r.dc_cable_cost,
                r.accessories_cost,
                r.labor_cost,
                r.total_cost_without_battery,
                r.total_cost
            FROM solar_surveys s
            LEFT JOIN survey_results r ON s.id = r.survey_id
            WHERE s.id = ? AND s.user_id = ?
            ORDER BY s.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    error_log('get_survey_detail.php: SQL prepared: ' . $sql);
    
    $stmt->execute([$surveyId, $userId]);
    error_log('get_survey_detail.php: SQL executed with params: ' . $surveyId . ', ' . $userId);
    
    $survey = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log('get_survey_detail.php: Survey data: ' . json_encode($survey));
    
    if (!$survey) {
        error_log('get_survey_detail.php: No survey found');
        sendError('Không tìm thấy khảo sát');
        exit;
    }
    
    // Format the response
    $formattedSurvey = [
        'id' => (int)$survey['id'],
        'fullName' => $survey['full_name'],
        'phone' => $survey['phone'],
        'region' => $survey['region'],
        'regionName' => $survey['region_name'],
        'phase' => (int)$survey['phase'],
        'solarPanelType' => (int)$survey['solar_panel_type'],
        'monthlyBill' => (int)$survey['monthly_bill'],
        'usageTime' => $survey['usage_time'],
        'createdAt' => $survey['created_at'],
        'results' => [
            'monthlyKWh' => isset($survey['monthly_kwh']) && $survey['monthly_kwh'] !== null ? (float)$survey['monthly_kwh'] : null,
            'sunHours' => isset($survey['sun_hours']) && $survey['sun_hours'] !== null ? (float)$survey['sun_hours'] : null,
            'panelId' => isset($survey['panel_id']) && $survey['panel_id'] !== null ? (int)$survey['panel_id'] : null,
            'panelName' => $survey['panel_name'] ?? null,
            'panelPower' => isset($survey['panel_power']) && $survey['panel_power'] !== null ? (float)$survey['panel_power'] : null,
            'panelPrice' => isset($survey['panel_price']) && $survey['panel_price'] !== null ? (int)$survey['panel_price'] : null,
            'panelsNeeded' => isset($survey['panels_needed']) && $survey['panels_needed'] !== null ? (int)$survey['panels_needed'] : null,
            'panelCost' => isset($survey['panel_cost']) && $survey['panel_cost'] !== null ? (int)$survey['panel_cost'] : null,
            'energyPerPanelPerDay' => isset($survey['energy_per_panel_per_day']) && $survey['energy_per_panel_per_day'] !== null ? (float)$survey['energy_per_panel_per_day'] : null,
            'totalCapacity' => isset($survey['total_capacity']) && $survey['total_capacity'] !== null ? (float)$survey['total_capacity'] : null,
            'inverterId' => isset($survey['inverter_id']) && $survey['inverter_id'] !== null ? (int)$survey['inverter_id'] : null,
            'inverterName' => $survey['inverter_name'] ?? null,
            'inverterCapacity' => isset($survey['inverter_capacity']) && $survey['inverter_capacity'] !== null ? (float)$survey['inverter_capacity'] : null,
            'inverterPrice' => isset($survey['inverter_price']) && $survey['inverter_price'] !== null ? (int)$survey['inverter_price'] : null,
            'cabinetId' => isset($survey['cabinet_id']) && $survey['cabinet_id'] !== null ? (int)$survey['cabinet_id'] : null,
            'cabinetName' => $survey['cabinet_name'] ?? null,
            'cabinetCapacity' => isset($survey['cabinet_capacity']) && $survey['cabinet_capacity'] !== null ? (float)$survey['cabinet_capacity'] : null,
            'cabinetPrice' => isset($survey['cabinet_price']) && $survey['cabinet_price'] !== null ? (int)$survey['cabinet_price'] : null,
            'batteryNeeded' => isset($survey['battery_needed']) && $survey['battery_needed'] !== null ? (int)$survey['battery_needed'] : null,
            'batteryType' => $survey['battery_type'] ?? null,
            'batteryId' => isset($survey['battery_id']) && $survey['battery_id'] !== null ? (int)$survey['battery_id'] : null,
            'batteryName' => $survey['battery_name'] ?? null,
            'batteryCapacity' => isset($survey['battery_capacity']) && $survey['battery_capacity'] !== null ? (float)$survey['battery_capacity'] : null,
            'batteryQuantity' => isset($survey['battery_quantity']) && $survey['battery_quantity'] !== null ? (int)$survey['battery_quantity'] : null,
            'batteryUnitPrice' => isset($survey['battery_unit_price']) && $survey['battery_unit_price'] !== null ? (int)$survey['battery_unit_price'] : null,
            'batteryCost' => isset($survey['battery_cost']) && $survey['battery_cost'] !== null ? (int)$survey['battery_cost'] : null,
            'bachZQty' => isset($survey['bach_z_qty']) && $survey['bach_z_qty'] !== null ? (int)$survey['bach_z_qty'] : null,
            'bachZPrice' => isset($survey['bach_z_price']) && $survey['bach_z_price'] !== null ? (int)$survey['bach_z_price'] : null,
            'bachZCost' => isset($survey['bach_z_cost']) && $survey['bach_z_cost'] !== null ? (int)$survey['bach_z_cost'] : null,
            'clipQty' => isset($survey['clip_qty']) && $survey['clip_qty'] !== null ? (int)$survey['clip_qty'] : null,
            'clipPrice' => isset($survey['clip_price']) && $survey['clip_price'] !== null ? (int)$survey['clip_price'] : null,
            'clipCost' => isset($survey['clip_cost']) && $survey['clip_cost'] !== null ? (int)$survey['clip_cost'] : null,
            'jackMC4Qty' => isset($survey['jack_mc4_qty']) && $survey['jack_mc4_qty'] !== null ? (int)$survey['jack_mc4_qty'] : null,
            'jackMC4Price' => isset($survey['jack_mc4_price']) && $survey['jack_mc4_price'] !== null ? (int)$survey['jack_mc4_price'] : null,
            'jackMC4Cost' => isset($survey['jack_mc4_cost']) && $survey['jack_mc4_cost'] !== null ? (int)$survey['jack_mc4_cost'] : null,
            'dcCableLength' => isset($survey['dc_cable_length']) && $survey['dc_cable_length'] !== null ? (int)$survey['dc_cable_length'] : null,
            'dcCablePrice' => isset($survey['dc_cable_price']) && $survey['dc_cable_price'] !== null ? (int)$survey['dc_cable_price'] : null,
            'dcCableCost' => isset($survey['dc_cable_cost']) && $survey['dc_cable_cost'] !== null ? (int)$survey['dc_cable_cost'] : null,
            'accessoriesCost' => isset($survey['accessories_cost']) && $survey['accessories_cost'] !== null ? (int)$survey['accessories_cost'] : null,
            'laborCost' => isset($survey['labor_cost']) && $survey['labor_cost'] !== null ? (int)$survey['labor_cost'] : null,
            'totalCostWithoutBattery' => isset($survey['total_cost_without_battery']) && $survey['total_cost_without_battery'] !== null ? (int)$survey['total_cost_without_battery'] : null,
            'totalCost' => isset($survey['total_cost']) && $survey['total_cost'] !== null ? (int)$survey['total_cost'] : null,
            'billBreakdown' => []
        ]
    ];
    
    sendSuccess(['survey' => $formattedSurvey], 'Lấy chi tiết khảo sát thành công');
    
} catch (Exception $e) {
    error_log('Error in get_survey_detail.php: ' . $e->getMessage());
    sendError('Lỗi server: ' . $e->getMessage());
}
?>
