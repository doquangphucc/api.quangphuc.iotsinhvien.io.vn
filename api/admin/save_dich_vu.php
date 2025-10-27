<?php
// Save dich_vu (create or update)
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check admin access
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$id = isset($input['id']) ? intval($input['id']) : null;
$name = $input['name'] ?? '';
$description = $input['description'] ?? '';
$logo_url = $input['logo_url'] ?? '';
$highlight_color = $input['highlight_color'] ?? '#3FA34D';
$link_name = $input['link_name'] ?? '';
$link_type = $input['link_type'] ?? 'page';
$link_value = $input['link_value'] ?? '';
$is_active = isset($input['is_active']) ? ($input['is_active'] ? 1 : 0) : 1;
$display_order = isset($input['display_order']) ? intval($input['display_order']) : 0;

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Tên dịch vụ không được để trống']);
    exit;
}

if ($id) {
    // Update existing
    $sql = "UPDATE dich_vu SET name=?, description=?, logo_url=?, highlight_color=?, link_name=?, link_type=?, link_value=?, is_active=?, display_order=?, updated_at=CURRENT_TIMESTAMP WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssiii", $name, $description, $logo_url, $highlight_color, $link_name, $link_type, $link_value, $is_active, $display_order, $id);
} else {
    // Insert new
    $sql = "INSERT INTO dich_vu (name, description, logo_url, highlight_color, link_name, link_type, link_value, is_active, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssii", $name, $description, $logo_url, $highlight_color, $link_name, $link_type, $link_value, $is_active, $display_order);
}

if ($stmt->execute()) {
    $new_id = $id ? $id : $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => $id ? 'Cập nhật dịch vụ thành công' : 'Thêm dịch vụ thành công',
        'id' => $new_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

