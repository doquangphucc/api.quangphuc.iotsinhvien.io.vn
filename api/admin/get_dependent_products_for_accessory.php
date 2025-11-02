<?php
/**
 * API: Get list of products by dependency target type for accessory configuration
 * Dùng để load danh sách sản phẩm khi chọn đối tượng phụ thuộc trong form cấu hình phụ kiện
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!is_admin()) {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $dependent_target = isset($_GET['dependent_target']) ? $_GET['dependent_target'] : '';
    
    if (!$dependent_target) {
        echo json_encode(['success' => false, 'message' => 'Thiếu tham số dependent_target'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Map dependency target to survey category
    $category_map = [
        'panel' => 'solar_panel',
        'inverter' => 'inverter',
        'battery' => 'battery',
        'cabinet' => 'electrical_cabinet',
        'project' => null // 'project' không có sản phẩm cụ thể
    ];

    $survey_category = isset($category_map[$dependent_target]) ? $category_map[$dependent_target] : null;

    if (!$survey_category) {
        // Nếu là 'project' hoặc không hợp lệ, trả về mảng rỗng
        echo json_encode([
            'success' => true,
            'products' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Lấy danh sách sản phẩm thuộc loại đó và đã được kích hoạt trong khảo sát
    $sql = "SELECT 
                p.id,
                p.title,
                p.inverter_power_watt,
                p.panel_power_watt,
                p.battery_capacity_kwh,
                p.cabinet_power_kw,
                spc.id AS config_id,
                spc.inverter_power_watt AS config_inverter_power_watt,
                spc.panel_power_watt AS config_panel_power_watt,
                spc.battery_capacity_kwh AS config_battery_capacity_kwh,
                spc.cabinet_power_kw AS config_cabinet_power_kw,
                spc.phase_type
            FROM products p
            INNER JOIN survey_product_configs spc ON p.id = spc.product_id
            WHERE spc.survey_category = ?
              AND spc.is_active = 1
            ORDER BY p.title ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $survey_category);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Lấy công suất/thông số (ưu tiên config, sau đó là product)
        $power_watt = $row['config_inverter_power_watt'] ?? $row['inverter_power_watt'] ?? null;
        $panel_watt = $row['config_panel_power_watt'] ?? $row['panel_power_watt'] ?? null;
        $battery_kwh = $row['config_battery_capacity_kwh'] ?? $row['battery_capacity_kwh'] ?? null;
        $cabinet_kw = $row['config_cabinet_power_kw'] ?? $row['cabinet_power_kw'] ?? null;

        $products[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'config_id' => (int)$row['config_id'],
            'power_watt' => $power_watt ? (int)$power_watt : null,
            'panel_watt' => $panel_watt ? (int)$panel_watt : null,
            'battery_kwh' => $battery_kwh ? (float)$battery_kwh : null,
            'cabinet_kw' => $cabinet_kw ? (float)$cabinet_kw : null,
            'phase_type' => $row['phase_type'] ?? null
        ];
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'products' => $products,
        'count' => count($products)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy danh sách sản phẩm: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

