<?php
// Save or update survey product configuration
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

// Set headers first
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if (!is_admin()) {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        echo json_encode([
            'success' => false, 
            'message' => 'Dữ liệu không hợp lệ',
            'debug' => ['raw_input' => substr($json, 0, 100)]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
    $survey_category = isset($data['survey_category']) ? $data['survey_category'] : '';
    $phase_type = isset($data['phase_type']) ? $data['phase_type'] : 'none';
    $price_type = isset($data['price_type']) ? $data['price_type'] : 'market_price';
    $is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;
    $display_order = isset($data['display_order']) ? intval($data['display_order']) : 0;
    $notes = isset($data['notes']) ? $data['notes'] : '';

    if (!$product_id || !$survey_category) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validate survey_category
    $valid_categories = ['solar_panel', 'inverter', 'battery', 'electrical_cabinet', 'accessory'];
    if (!in_array($survey_category, $valid_categories)) {
        echo json_encode(['success' => false, 'message' => 'Loại sản phẩm không hợp lệ'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Check if config already exists
    $checkSql = "SELECT id FROM survey_product_configs WHERE product_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    $stmt->close();

    if ($existing) {
        // Update existing config
        $sql = "UPDATE survey_product_configs 
                SET survey_category = ?, 
                    phase_type = ?, 
                    price_type = ?, 
                    is_active = ?, 
                    display_order = ?, 
                    notes = ?,
                    updated_at = NOW()
                WHERE product_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiisi",
            $survey_category, 
            $phase_type, 
            $price_type, 
            $is_active, 
            $display_order, 
            $notes,
            $product_id
        );
    } else {
        // Insert new config
        $sql = "INSERT INTO survey_product_configs 
                (product_id, survey_category, phase_type, price_type, is_active, display_order, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssiis", 
            $product_id, 
            $survey_category, 
            $phase_type, 
            $price_type, 
            $is_active, 
            $display_order, 
            $notes
        );
    }

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => $existing ? 'Đã cập nhật cấu hình' : 'Đã thêm cấu hình mới'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Lỗi: ' . $stmt->error
        ], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi PHP: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
}
?>
