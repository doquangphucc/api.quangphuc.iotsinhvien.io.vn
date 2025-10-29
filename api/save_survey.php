<?php
require_once 'connect.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

requireAuth();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Dữ liệu JSON không hợp lệ');
}

// Validate required fields
$requiredFields = ['region', 'phase', 'solarPanel', 'monthlyBill', 'usageTime'];
$missingFields = validateRequired($input, $requiredFields);

if (!empty($missingFields)) {
    sendError('Thiếu các trường bắt buộc: ' . implode(', ', $missingFields));
}

// Validate results if provided
if (isset($input['results'])) {
    $requiredResultFields = ['monthlyKWh', 'sunHours', 'panelsNeeded', 'panelCost', 
                            'inverter', 'cabinet', 'batteryNeeded', 'totalCost'];
    foreach ($requiredResultFields as $field) {
        if (!isset($input['results'][$field])) {
            sendError("Thiếu kết quả: $field");
        }
    }
}

try {
    $db = Database::getInstance();
    $userId = getCurrentUserId();
    
    // Get user info
    $user = $db->selectOne('users', ['id' => $userId], 'full_name, phone');
    if (!$user) {
        sendError('Không tìm thấy thông tin người dùng');
    }
    
    // Begin transaction
    $pdo = $db->getConnection();
    $pdo->beginTransaction();
    
    // Insert into solar_surveys
    $surveyData = [
        'user_id' => $userId,
        'full_name' => $user['full_name'],
        'phone' => $user['phone'],
        'region' => sanitizeInput($input['region']),
        'phase' => (int)$input['phase'],
        'solar_panel_type' => (int)$input['solarPanel'],
        'monthly_bill' => (float)$input['monthlyBill'],
        'usage_time' => sanitizeInput($input['usageTime'])
    ];
    
    $surveyId = $db->insert('solar_surveys', $surveyData);
    
    // If results provided, insert into survey_results
    if (isset($input['results'])) {
        $results = $input['results'];
        
        // Determine selected battery in a robust way
        $selectedBattery = null;
        $batteryOptions = isset($results['batteryOptions']) && is_array($results['batteryOptions']) ? $results['batteryOptions'] : [];
        
        // If frontend provided explicit selected battery id or type
        if (isset($input['selectedBattery'])) {
            $sel = $input['selectedBattery'];
            // Try find by id or name
            foreach ($batteryOptions as $opt) {
                if ((isset($opt['id']) && (string)$opt['id'] === (string)$sel) || (isset($opt['name']) && $opt['name'] === $sel)) {
                    $selectedBattery = $opt;
                    break;
                }
            }
        }
        
        // Fallbacks: pick first available option
        if ($selectedBattery === null && count($batteryOptions) > 0) {
            $selectedBattery = $batteryOptions[0];
        }
        
        // Final safety: ensure fields exist to avoid NULL constraint issues
        if (!is_array($selectedBattery)) {
            $selectedBattery = [
                'id' => 0,
                'name' => 'Pin lưu trữ',
                'capacity' => 0,
                'quantity' => 1,
                'price' => 0,
                'totalCost' => 0
            ];
        } else {
            $selectedBattery = array_merge(
                [
                    'id' => 0,
                    'name' => 'Pin lưu trữ',
                    'capacity' => 0,
                    'quantity' => 1,
                    'price' => 0,
                    'totalCost' => 0
                ],
                $selectedBattery
            );
        }
        
        // Determine battery_type from selected battery
        // Try to infer from name (8cell, 16cell) or capacity
        $batteryType = 'unknown';
        if (isset($selectedBattery['name'])) {
            $name = strtolower($selectedBattery['name']);
            if (strpos($name, '8') !== false || strpos($name, '8cell') !== false || (isset($selectedBattery['capacity']) && $selectedBattery['capacity'] <= 10)) {
                $batteryType = '8cell';
            } elseif (strpos($name, '16') !== false || strpos($name, '16cell') !== false || (isset($selectedBattery['capacity']) && $selectedBattery['capacity'] > 10)) {
                $batteryType = '16cell';
            }
        }
        
        // If still unknown and there are multiple options, compare by cost (old logic)
        if ($batteryType === 'unknown' && count($batteryOptions) >= 2) {
            $batteryType = ($batteryOptions[0]['totalCost'] <= $batteryOptions[1]['totalCost']) ? '8cell' : '16cell';
        }
        
        // Final fallback
        if ($batteryType === 'unknown') {
            $batteryType = '8cell';
        }
        
        // Region names
        $regionNames = [
            'mien-bac' => 'Miền Bắc',
            'mien-trung' => 'Miền Trung', 
            'mien-nam' => 'Miền Nam'
        ];
        $regionName = $regionNames[$input['region']] ?? 'Không xác định';
        
        $panelInfo = $results['panelInfo'];
        $totalCostWithoutBattery = $results['totalCost'] - $selectedBattery['totalCost'];
        $billBreakdownJson = json_encode($results['billBreakdown']);
        
        // Debug: Log product IDs before saving
        error_log("Save Survey - panelInfo: " . json_encode($panelInfo));
        error_log("Save Survey - inverter: " . json_encode($results['inverter']));
        error_log("Save Survey - cabinet: " . json_encode($results['cabinet']));
        error_log("Save Survey - selectedBattery: " . json_encode($selectedBattery));
        
        $resultData = [
            'survey_id' => $surveyId,
            'monthly_kwh' => (float)$results['monthlyKWh'],
            'sun_hours' => (float)$results['sunHours'],
            'region_name' => $regionName,
            'panel_id' => (int)($panelInfo['id'] ?? 0),
            'panel_name' => $panelInfo['name'],
            'panel_power' => (float)$panelInfo['power'],
            'panel_price' => (float)$panelInfo['price'],
            'panels_needed' => (int)$results['panelsNeeded'],
            'panel_cost' => (float)$results['panelCost'],
            'energy_per_panel_per_day' => (float)$results['energyPerPanelPerDay'],
            'total_capacity' => (float)$results['totalCapacity'],
            'inverter_id' => (int)($results['inverter']['id'] ?? 0),
            'inverter_name' => $results['inverter']['name'],
            'inverter_capacity' => (float)$results['inverter']['capacity'],
            'inverter_price' => (float)$results['inverter']['price'],
            'cabinet_id' => (int)($results['cabinet']['id'] ?? 0),
            'cabinet_name' => $results['cabinet']['name'],
            'cabinet_capacity' => (float)$results['cabinet']['capacity'],
            'cabinet_price' => (float)$results['cabinet']['price'],
            'battery_needed' => (float)$results['batteryNeeded'],
            'battery_type' => $batteryType,
            'battery_id' => (int)$selectedBattery['id'],
            'battery_name' => $selectedBattery['name'],
            'battery_capacity' => (float)$selectedBattery['capacity'],
            'battery_quantity' => (int)$selectedBattery['quantity'],
            'battery_unit_price' => (float)$selectedBattery['price'],
            'battery_cost' => (float)$selectedBattery['totalCost'],
            'bach_z_qty' => (int)$results['accessories']['bachZ']['qty'],
            'bach_z_price' => (float)$results['accessories']['bachZ']['price'],
            'bach_z_cost' => (float)$results['accessories']['bachZ']['cost'],
            'clip_qty' => (int)$results['accessories']['clip']['qty'],
            'clip_price' => (float)$results['accessories']['clip']['price'],
            'clip_cost' => (float)$results['accessories']['clip']['cost'],
            'jack_mc4_qty' => (int)$results['accessories']['jackMC4']['qty'],
            'jack_mc4_price' => (float)$results['accessories']['jackMC4']['price'],
            'jack_mc4_cost' => (float)$results['accessories']['jackMC4']['cost'],
            'dc_cable_length' => (int)$results['dcCable']['length'],
            'dc_cable_price' => (float)$results['dcCable']['price'],
            'dc_cable_cost' => (float)$results['dcCable']['cost'],
            'accessories_cost' => (float)$results['accessoriesCost'],
            'labor_cost' => (float)$results['laborCost'],
            'total_cost_without_battery' => $totalCostWithoutBattery,
            'total_cost' => (float)$results['totalCost'],
            'bill_breakdown' => $billBreakdownJson
        ];
        
        $db->insert('survey_results', $resultData);
    }
    
    $pdo->commit();
    sendSuccess(['survey_id' => $surveyId], 'Đã lưu thông tin khảo sát thành công');
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Save Survey error: " . $e->getMessage());
    sendError('Không thể lưu khảo sát: ' . $e->getMessage(), 500);
}
?>
