<?php
// Get all products with survey configuration
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if (!hasPermission($conn, 'survey', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem khảo sát']);
    exit;
}

// Get filter: all, configured, unconfigured
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$sql = "SELECT 
            p.id,
            p.category_id,
            p.title,
            p.market_price,
            p.category_price,
            p.technical_description,
            p.image_url,
            p.is_active as product_is_active,
            pc.name as category_name,
            spc.id as survey_config_id,
            spc.survey_category,
            spc.phase_type,
            spc.price_type,
            spc.is_active as survey_is_active,
            spc.display_order,
            spc.panel_power_watt as spc_panel_power_watt,
            spc.inverter_power_watt as spc_inverter_power_watt,
            spc.battery_capacity_kwh as spc_battery_capacity_kwh,
            spc.cabinet_power_kw as spc_cabinet_power_kw,
            spc.accessory_unit,
            spc.accessory_base_qty,
            spc.accessory_dependent_qty,
            spc.accessory_dependent_target,
            p.panel_power_watt as product_panel_power_watt,
            p.inverter_power_watt as product_inverter_power_watt,
            p.battery_capacity_kwh as product_battery_capacity_kwh,
            p.cabinet_power_kw as product_cabinet_power_kw
        FROM products p
        LEFT JOIN product_categories pc ON p.category_id = pc.id
        LEFT JOIN survey_product_configs spc ON p.id = spc.product_id";

// Add filter
if ($filter === 'configured') {
    $sql .= " WHERE spc.id IS NOT NULL";
} elseif ($filter === 'unconfigured') {
    $sql .= " WHERE spc.id IS NULL";
}

$sql .= " ORDER BY p.category_id ASC, p.id ASC";

$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => (int)$row['id'],
        'category_id' => (int)$row['category_id'],
        'category_name' => $row['category_name'],
        'title' => $row['title'],
        'market_price' => floatval($row['market_price']),
        'category_price' => floatval($row['category_price'] ?? 0),
        'image_url' => $row['image_url'],
        'product_is_active' => (bool)$row['product_is_active'],
        'has_survey_config' => !is_null($row['survey_config_id']),
        'survey_config' => $row['survey_config_id'] ? [
            'id' => (int)$row['survey_config_id'],
            'survey_category' => $row['survey_category'],
            'phase_type' => $row['phase_type'],
            'price_type' => $row['price_type'],
            'is_active' => (bool)$row['survey_is_active'],
            'display_order' => (int)$row['display_order'],
            'panel_power_watt' => isset($row['spc_panel_power_watt']) ? (int)$row['spc_panel_power_watt'] : null,
            'inverter_power_watt' => isset($row['spc_inverter_power_watt']) ? (int)$row['spc_inverter_power_watt'] : null,
            'battery_capacity_kwh' => isset($row['spc_battery_capacity_kwh']) ? floatval($row['spc_battery_capacity_kwh']) : null,
            'cabinet_power_kw' => isset($row['spc_cabinet_power_kw']) ? floatval($row['spc_cabinet_power_kw']) : null,
            'accessory_unit' => $row['accessory_unit'] ?? null,
            'accessory_base_qty' => isset($row['accessory_base_qty']) ? floatval($row['accessory_base_qty']) : null,
            'accessory_dependent_qty' => isset($row['accessory_dependent_qty']) ? floatval($row['accessory_dependent_qty']) : null,
            'accessory_dependent_target' => $row['accessory_dependent_target'] ?? null
        ] : null
    ];
}

echo json_encode([
    'success' => true,
    'products' => $products,
    'filter' => $filter
]);

$conn->close();
?>
