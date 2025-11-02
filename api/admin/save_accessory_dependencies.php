<?php
/**
 * API: Save accessory dependencies (mapping phụ kiện với sản phẩm phụ thuộc)
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

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $accessory_config_id = isset($data['accessory_config_id']) ? intval($data['accessory_config_id']) : 0;
    $dependent_product_ids = isset($data['dependent_product_ids']) && is_array($data['dependent_product_ids']) 
        ? array_map('intval', $data['dependent_product_ids']) 
        : [];

    if (!$accessory_config_id) {
        echo json_encode(['success' => false, 'message' => 'Thiếu accessory_config_id'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Xóa tất cả dependencies cũ của phụ kiện này
    $deleteSql = "DELETE FROM survey_accessory_dependencies WHERE accessory_config_id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $accessory_config_id);
    $stmt->execute();
    $stmt->close();

    // Insert dependencies mới (nếu có)
    if (!empty($dependent_product_ids)) {
        $insertSql = "INSERT INTO survey_accessory_dependencies (accessory_config_id, dependent_product_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insertSql);

        foreach ($dependent_product_ids as $product_id) {
            if ($product_id > 0) {
                $stmt->bind_param("ii", $accessory_config_id, $product_id);
                $stmt->execute();
            }
        }
        $stmt->close();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Đã lưu mapping phụ kiện thành công',
        'count' => count($dependent_product_ids)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lưu mapping: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

