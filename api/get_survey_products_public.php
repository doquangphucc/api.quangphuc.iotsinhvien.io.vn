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
                spc.id AS config_id,
                spc.survey_category,
                spc.phase_type,
                spc.price_type,
                spc.panel_power_watt AS spc_panel_power_watt,
                spc.inverter_power_watt AS spc_inverter_power_watt,
                spc.battery_capacity_kwh AS spc_battery_capacity_kwh,
                spc.cabinet_power_kw AS spc_cabinet_power_kw,
                spc.accessory_unit AS spc_accessory_unit,
                spc.accessory_base_qty AS spc_accessory_base_qty,
                spc.accessory_dependent_qty AS spc_accessory_dependent_qty,
                spc.accessory_dependent_target AS spc_accessory_dependent_target,
                spc.is_active,
                spc.display_order,
                GROUP_CONCAT(sad.dependent_product_id ORDER BY sad.dependent_product_id) AS dependent_product_ids
            FROM products p
            INNER JOIN survey_product_configs spc ON p.id = spc.product_id
            LEFT JOIN survey_accessory_dependencies sad ON spc.id = sad.accessory_config_id
            WHERE spc.is_active = 1
              -- Chỉ cần kiểm tra spc.is_active cho khảo sát, không cần p.is_active
              -- vì có thể có phụ kiện chỉ dùng trong khảo sát mà không cần hiển thị ở trang sản phẩm
            GROUP BY spc.id, p.id, p.title, p.market_price, p.category_price, p.technical_description, 
                     p.image_url, p.panel_power_watt, p.inverter_power_watt, p.battery_capacity_kwh, 
                     p.cabinet_power_kw, spc.survey_category, spc.phase_type, spc.price_type,
                     spc.panel_power_watt, spc.inverter_power_watt, spc.battery_capacity_kwh, 
                     spc.cabinet_power_kw, spc.accessory_unit, spc.accessory_base_qty, 
                     spc.accessory_dependent_qty, spc.accessory_dependent_target, spc.is_active, spc.display_order
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

        // Xử lý dependent_product_ids từ GROUP_CONCAT (chuỗi CSV) thành array
        $dependentProductIds = null;
        if (!empty($row['dependent_product_ids'])) {
            $dependentProductIds = array_map('intval', explode(',', $row['dependent_product_ids']));
        }

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
            'accessory_unit' => $row['spc_accessory_unit'] ?? null,
            'accessory_base_qty' => isset($row['spc_accessory_base_qty']) ? floatval($row['spc_accessory_base_qty']) : null,
            'accessory_dependent_qty' => isset($row['spc_accessory_dependent_qty']) ? floatval($row['spc_accessory_dependent_qty']) : null,
            'accessory_dependent_target' => $row['spc_accessory_dependent_target'] ?? null,
            'dependent_product_ids' => $dependentProductIds, // Array các product_id phụ thuộc (null nếu không có)
            'config_id' => (int)$row['config_id'], // ID của survey_product_configs
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
