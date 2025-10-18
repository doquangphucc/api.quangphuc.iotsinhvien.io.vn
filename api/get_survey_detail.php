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
    // Check if user is logged in
    if (!isLoggedIn()) {
        sendError('Bạn cần đăng nhập để thực hiện hành động này.', 401);
        exit;
    }
    
    $userId = getCurrentUserId();
    $surveyId = $_GET['id'] ?? null;
    
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
    $stmt->execute([$surveyId, $userId]);
    $survey = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$survey) {
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
            'monthlyKWh' => $survey['monthly_kwh'] ? (float)$survey['monthly_kwh'] : null,
            'sunHours' => $survey['sun_hours'] ? (float)$survey['sun_hours'] : null,
            'panelId' => $survey['panel_id'] ? (int)$survey['panel_id'] : null,
            'panelName' => $survey['panel_name'],
            'panelPower' => $survey['panel_power'] ? (float)$survey['panel_power'] : null,
            'panelPrice' => $survey['panel_price'] ? (int)$survey['panel_price'] : null,
            'panelsNeeded' => $survey['panels_needed'] ? (int)$survey['panels_needed'] : null,
            'panelCost' => $survey['panel_cost'] ? (int)$survey['panel_cost'] : null,
            'energyPerPanelPerDay' => $survey['energy_per_panel_per_day'] ? (float)$survey['energy_per_panel_per_day'] : null,
            'totalCapacity' => $survey['total_capacity'] ? (float)$survey['total_capacity'] : null,
            'inverterId' => $survey['inverter_id'] ? (int)$survey['inverter_id'] : null,
            'inverterName' => $survey['inverter_name'],
            'inverterCapacity' => $survey['inverter_capacity'] ? (float)$survey['inverter_capacity'] : null,
            'inverterPrice' => $survey['inverter_price'] ? (int)$survey['inverter_price'] : null,
            'cabinetId' => $survey['cabinet_id'] ? (int)$survey['cabinet_id'] : null,
            'cabinetName' => $survey['cabinet_name'],
            'cabinetCapacity' => $survey['cabinet_capacity'] ? (float)$survey['cabinet_capacity'] : null,
            'cabinetPrice' => $survey['cabinet_price'] ? (int)$survey['cabinet_price'] : null,
            'batteryNeeded' => $survey['battery_needed'] ? (int)$survey['battery_needed'] : null,
            'batteryType' => $survey['battery_type'],
            'batteryId' => $survey['battery_id'] ? (int)$survey['battery_id'] : null,
            'batteryName' => $survey['battery_name'],
            'batteryCapacity' => $survey['battery_capacity'] ? (float)$survey['battery_capacity'] : null,
            'batteryQuantity' => $survey['battery_quantity'] ? (int)$survey['battery_quantity'] : null,
            'batteryUnitPrice' => $survey['battery_unit_price'] ? (int)$survey['battery_unit_price'] : null,
            'batteryCost' => $survey['battery_cost'] ? (int)$survey['battery_cost'] : null,
            'bachZQty' => $survey['bach_z_qty'] ? (int)$survey['bach_z_qty'] : null,
            'bachZPrice' => $survey['bach_z_price'] ? (int)$survey['bach_z_price'] : null,
            'bachZCost' => $survey['bach_z_cost'] ? (int)$survey['bach_z_cost'] : null,
            'clipQty' => $survey['clip_qty'] ? (int)$survey['clip_qty'] : null,
            'clipPrice' => $survey['clip_price'] ? (int)$survey['clip_price'] : null,
            'clipCost' => $survey['clip_cost'] ? (int)$survey['clip_cost'] : null,
            'jackMC4Qty' => $survey['jack_mc4_qty'] ? (int)$survey['jack_mc4_qty'] : null,
            'jackMC4Price' => $survey['jack_mc4_price'] ? (int)$survey['jack_mc4_price'] : null,
            'jackMC4Cost' => $survey['jack_mc4_cost'] ? (int)$survey['jack_mc4_cost'] : null,
            'dcCableLength' => $survey['dc_cable_length'] ? (int)$survey['dc_cable_length'] : null,
            'dcCablePrice' => $survey['dc_cable_price'] ? (int)$survey['dc_cable_price'] : null,
            'dcCableCost' => $survey['dc_cable_cost'] ? (int)$survey['dc_cable_cost'] : null,
            'accessoriesCost' => $survey['accessories_cost'] ? (int)$survey['accessories_cost'] : null,
            'laborCost' => $survey['labor_cost'] ? (int)$survey['labor_cost'] : null,
            'totalCostWithoutBattery' => $survey['total_cost_without_battery'] ? (int)$survey['total_cost_without_battery'] : null,
            'totalCost' => $survey['total_cost'] ? (int)$survey['total_cost'] : null,
            'billBreakdown' => []
        ]
    ];
    
    sendSuccess('Lấy chi tiết khảo sát thành công', ['survey' => $formattedSurvey]);
    
} catch (Exception $e) {
    error_log('Error in get_survey_detail.php: ' . $e->getMessage());
    sendError('Lỗi server: ' . $e->getMessage());
}
?>
