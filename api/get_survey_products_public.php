<?php
// Public API to get survey products for survey page
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_mysqli.php';

try {
    $sql = "SELECT 
                p.id,
                p.title,
                p.market_price,
                p.category_price,
                p.technical_description,
                p.image_url,
                p.panel_power_watt,
                p.inverter_power_watt,
                p.battery_capacity_kwh,
                p.cabinet_power_kw,
                spc.survey_category,
                spc.phase_type,
                spc.price_type,
                spc.panel_power_watt AS spc_panel_power_watt,
                spc.inverter_power_watt AS spc_inverter_power_watt,
                spc.battery_capacity_kwh AS spc_battery_capacity_kwh,
                spc.cabinet_power_kw AS spc_cabinet_power_kw,
                spc.is_active,
                spc.display_order
            FROM products p
            INNER JOIN survey_product_configs spc ON p.id = spc.product_id
            WHERE p.is_active = 1 
              AND spc.is_active = 1
            ORDER BY spc.survey_category ASC, spc.phase_type ASC, spc.display_order ASC";
    
    $result = $conn->query($sql);
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        // Determine which price to use
        $price = ($row['price_type'] === 'market_price') ? $row['market_price'] : $row['category_price'];
        
        // Use image URL as-is from database
        // Paths are like "assets/img/products/xxx.png" which work from document root
        $imageUrl = $row['image_url'];
        
        // Prefer survey-specific numeric specs if present
        $panelWatt = isset($row['spc_panel_power_watt']) && $row['spc_panel_power_watt'] !== null ? (int)$row['spc_panel_power_watt'] : (isset($row['panel_power_watt']) ? (int)$row['panel_power_watt'] : null);
        $inverterWatt = isset($row['spc_inverter_power_watt']) && $row['spc_inverter_power_watt'] !== null ? (int)$row['spc_inverter_power_watt'] : (isset($row['inverter_power_watt']) ? (int)$row['inverter_power_watt'] : null);
        $batteryKwh = isset($row['spc_battery_capacity_kwh']) && $row['spc_battery_capacity_kwh'] !== null ? floatval($row['spc_battery_capacity_kwh']) : (isset($row['battery_capacity_kwh']) ? floatval($row['battery_capacity_kwh']) : null);
        $cabinetKw = isset($row['spc_cabinet_power_kw']) && $row['spc_cabinet_power_kw'] !== null ? floatval($row['spc_cabinet_power_kw']) : (isset($row['cabinet_power_kw']) ? floatval($row['cabinet_power_kw']) : null);

        $products[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'price' => floatval($price),
            'market_price' => floatval($row['market_price']),
            'category_price' => floatval($row['category_price']),
            'technical_description' => $row['technical_description'],
            'image_url' => $imageUrl,
            'panel_power_watt' => $panelWatt,
            'inverter_power_watt' => $inverterWatt,
            'battery_capacity_kwh' => $batteryKwh,
            'cabinet_power_kw' => $cabinetKw,
            'survey_category' => $row['survey_category'],
            'phase_type' => $row['phase_type'],
            'display_order' => (int)$row['display_order']
        ];
    }
    
    // Group products by category
    $grouped = [
        'solar_panel' => [],
        'inverter' => [
            '1_phase' => [],
            '3_phase' => [],
            'both' => []
        ],
        'battery' => [],
        'electrical_cabinet' => [
            '1_phase' => [],
            '3_phase' => [],
            'both' => []
        ],
        'accessory' => []
    ];
    
    foreach ($products as $product) {
        $cat = $product['survey_category'];
        $phase = $product['phase_type'];
        
        if ($cat === 'inverter' || $cat === 'electrical_cabinet') {
            if ($phase === '1_phase') {
                $grouped[$cat]['1_phase'][] = $product;
            } elseif ($phase === '3_phase') {
                $grouped[$cat]['3_phase'][] = $product;
            } elseif ($phase === 'both') {
                $grouped[$cat]['both'][] = $product;
            }
        } else {
            $grouped[$cat][] = $product;
        }
    }
    
    echo json_encode([
        'success' => true,
        'products' => $grouped
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy danh sách sản phẩm: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
