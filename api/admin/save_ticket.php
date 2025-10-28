<?php
// Add or update lottery ticket
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? intval($data['id']) : 0;
$required_action = $id > 0 ? 'edit' : 'create';

if (!hasPermission($conn, 'tickets', $required_action)) {
    echo json_encode(['success' => false, 'message' => "Bạn không có quyền {$required_action} vé quay"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id = isset($data['id']) ? intval($data['id']) : 0;
$user_id = intval($data['user_id'] ?? 0);
$ticket_type = $data['ticket_type'] ?? 'bonus';
$status = $data['status'] ?? 'active';
$pre_assigned_reward_id = !empty($data['pre_assigned_reward_id']) ? intval($data['pre_assigned_reward_id']) : null;

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'User ID không hợp lệ']);
    exit;
}

if ($id > 0) {
    // Update
    $stmt = $conn->prepare("UPDATE lottery_tickets SET user_id = ?, ticket_type = ?, status = ?, pre_assigned_reward_id = ? WHERE id = ?");
    $stmt->bind_param("issii", $user_id, $ticket_type, $status, $pre_assigned_reward_id, $id);
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO lottery_tickets (user_id, ticket_type, status, pre_assigned_reward_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $user_id, $ticket_type, $status, $pre_assigned_reward_id);
}

if ($stmt->execute()) {
    $ticket_id = $id > 0 ? $id : $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Lưu vé thành công',
        'ticket_id' => $ticket_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>

