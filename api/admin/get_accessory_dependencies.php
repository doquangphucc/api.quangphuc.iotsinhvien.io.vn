<?php
/**
 * API: Get accessory dependencies (mapping phụ kiện với sản phẩm phụ thuộc)
 * Dùng để load danh sách sản phẩm đã được tick khi edit phụ kiện
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

    $accessory_config_id = isset($_GET['accessory_config_id']) ? intval($_GET['accessory_config_id']) : 0;

    if (!$accessory_config_id) {
        echo json_encode(['success' => false, 'message' => 'Thiếu tham số accessory_config_id'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = "SELECT dependent_product_id 
            FROM survey_accessory_dependencies 
            WHERE accessory_config_id = ?
            ORDER BY dependent_product_id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $accessory_config_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $dependent_product_ids = [];
    while ($row = $result->fetch_assoc()) {
        $dependent_product_ids[] = (int)$row['dependent_product_id'];
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'dependent_product_ids' => $dependent_product_ids,
        'count' => count($dependent_product_ids)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy mapping: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

