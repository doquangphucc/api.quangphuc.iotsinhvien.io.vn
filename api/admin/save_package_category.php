<?php
// Suppress warnings to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    $id = isset($data['id']) ? intval($data['id']) : 0;
    $name = $data['name'] ?? '';
    $badge_text = $data['badge_text'] ?? '';
    $badge_color = $data['badge_color'] ?? 'blue';
    $display_order = isset($data['display_order']) ? intval($data['display_order']) : 0;
    $is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;

    // Validation
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Tên danh mục không được để trống']);
        exit;
    }

    if ($id > 0) {
        // Update existing package category
        $stmt = $conn->prepare("UPDATE package_categories SET name = ?, badge_text = ?, badge_color = ?, display_order = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("sssiii", $name, $badge_text, $badge_color, $display_order, $is_active, $id);
    } else {
        // Insert new package category
        $stmt = $conn->prepare("INSERT INTO package_categories (name, badge_text, badge_color, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $name, $badge_text, $badge_color, $display_order, $is_active);
    }

    if ($stmt->execute()) {
        $category_id = $id > 0 ? $id : $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Lưu danh mục gói thành công',
            'category_id' => $category_id
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

$conn->close();

