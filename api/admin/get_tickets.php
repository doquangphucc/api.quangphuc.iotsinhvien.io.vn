<?php
// Get all lottery tickets
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$sql = "SELECT lt.*, u.full_name, u.username, u.phone,
        rt.reward_name as pre_assigned_reward_name
        FROM lottery_tickets lt
        LEFT JOIN users u ON lt.user_id = u.id
        LEFT JOIN reward_templates rt ON lt.pre_assigned_reward_id = rt.id";

if ($user_id > 0) {
    $sql .= " WHERE lt.user_id = " . $user_id;
}

$sql .= " ORDER BY lt.created_at DESC";

$result = $conn->query($sql);

$tickets = [];
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}

echo json_encode([
    'success' => true,
    'tickets' => $tickets
]);

$conn->close();
?>

