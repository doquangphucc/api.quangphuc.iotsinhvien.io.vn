<?php
require_once 'connect.php';

requireAuth();

$userId = getCurrentUserId();

try {
    $db = Database::getInstance();
    
    // Get surveys with results using JOIN
    $sql = "
        SELECT 
            s.id,
            s.full_name,
            s.phone,
            s.region,
            s.phase,
            s.solar_panel_type,
            s.monthly_bill,
            s.usage_time,
            s.created_at,
            r.id as result_id,
            r.monthly_kwh,
            r.sun_hours,
            r.region_name,
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
            r.total_cost,
            r.bill_breakdown
        FROM solar_surveys s
        LEFT JOIN survey_results r ON s.id = r.survey_id
        WHERE s.user_id = ?
        ORDER BY s.created_at DESC
    ";
    
    $stmt = $db->query($sql, [$userId]);
    $rows = $stmt->fetchAll();
    
    $surveys = [];
    foreach ($rows as $row) {
        $survey = [
            'id' => (int)$row['id'],
            'fullName' => $row['full_name'],
            'phone' => $row['phone'],
            'region' => $row['region'],
            'regionName' => $row['region_name'],
            'phase' => (int)$row['phase'],
            'solarPanelType' => (int)$row['solar_panel_type'],
            'monthlyBill' => (float)$row['monthly_bill'],
            'usageTime' => $row['usage_time'],
            'createdAt' => $row['created_at']
        ];
        
        // If results exist, add them
        if ($row['result_id']) {
            $survey['results'] = [
                'monthlyKWh' => (float)$row['monthly_kwh'],
                'sunHours' => (float)$row['sun_hours'],
                
                // Panel info
                'panelId' => (int)$row['panel_id'],
                'panelName' => $row['panel_name'],
                'panelPower' => (float)$row['panel_power'],
                'panelPrice' => (float)$row['panel_price'],
                'panelsNeeded' => (int)$row['panels_needed'],
                'panelCost' => (float)$row['panel_cost'],
                'energyPerPanelPerDay' => (float)$row['energy_per_panel_per_day'],
                'totalCapacity' => (float)$row['total_capacity'],
                
                // Inverter info
                'inverterId' => (int)$row['inverter_id'],
                'inverterName' => $row['inverter_name'],
                'inverterCapacity' => (float)$row['inverter_capacity'],
                'inverterPrice' => (float)$row['inverter_price'],
                
                // Cabinet info
                'cabinetId' => (int)$row['cabinet_id'],
                'cabinetName' => $row['cabinet_name'],
                'cabinetCapacity' => (float)$row['cabinet_capacity'],
                'cabinetPrice' => (float)$row['cabinet_price'],
                
                // Battery info
                'batteryNeeded' => (float)$row['battery_needed'],
                'batteryType' => $row['battery_type'],
                'batteryId' => (int)$row['battery_id'],
                'batteryName' => $row['battery_name'],
                'batteryCapacity' => (float)$row['battery_capacity'],
                'batteryQuantity' => (int)$row['battery_quantity'],
                'batteryUnitPrice' => (float)$row['battery_unit_price'],
                'batteryCost' => (float)$row['battery_cost'],
                
                // Accessories breakdown
                'bachZQty' => (int)$row['bach_z_qty'],
                'bachZPrice' => (float)$row['bach_z_price'],
                'bachZCost' => (float)$row['bach_z_cost'],
                
                'clipQty' => (int)$row['clip_qty'],
                'clipPrice' => (float)$row['clip_price'],
                'clipCost' => (float)$row['clip_cost'],
                
                'jackMC4Qty' => (int)$row['jack_mc4_qty'],
                'jackMC4Price' => (float)$row['jack_mc4_price'],
                'jackMC4Cost' => (float)$row['jack_mc4_cost'],
                
                'dcCableLength' => (int)$row['dc_cable_length'],
                'dcCablePrice' => (float)$row['dc_cable_price'],
                'dcCableCost' => (float)$row['dc_cable_cost'],
                
                // Total costs
                'accessoriesCost' => (float)$row['accessories_cost'],
                'laborCost' => (float)$row['labor_cost'],
                'totalCostWithoutBattery' => (float)$row['total_cost_without_battery'],
                'totalCost' => (float)$row['total_cost'],
                
                // Bill breakdown (JSON)
                'billBreakdown' => $row['bill_breakdown'] ? json_decode($row['bill_breakdown'], true) : null
            ];
        }
        
        $surveys[] = $survey;
    }
    
    sendSuccess([
        'surveys' => $surveys,
        'total' => count($surveys)
    ]);

} catch (Exception $e) {
    error_log("Get Survey History error: " . $e->getMessage());
    sendError('Lỗi hệ thống, không thể lấy lịch sử khảo sát: ' . $e->getMessage(), 500);
}
?>
