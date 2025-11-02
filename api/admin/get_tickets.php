<?php
// Get all lottery tickets
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if (!hasPermission($conn, 'tickets', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem vé quay']);
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$count_only = isset($_GET['count_only']) && $_GET['count_only'] === '1';
$all = isset($_GET['all']) && $_GET['all'] === '1'; // Lấy tất cả không pagination

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 100; // 100 vé mỗi trang
$offset = ($page - 1) * $per_page;

// Build WHERE clause
$whereConditions = [];
if ($user_id > 0) {
    $whereConditions[] = "lt.user_id = " . $user_id;
}
if ($status === 'active') {
    $whereConditions[] = "lt.status = 'active'";
}

$whereClause = !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "";

// Get total count
$countSql = "SELECT COUNT(*) as total FROM lottery_tickets lt" . $whereClause;
$countResult = $conn->query($countSql);
$totalRow = $countResult->fetch_assoc();
$total = intval($totalRow['total'] ?? 0);
$total_pages = ceil($total / $per_page);

// If count_only, just return the count
if ($count_only) {
    echo json_encode([
        'success' => true,
        'count' => $total
    ]);
    $conn->close();
    exit;
}

// Get tickets with or without pagination
$sql = "SELECT lt.*, u.full_name, u.username, u.phone,
        rt.reward_name as pre_assigned_reward_name
        FROM lottery_tickets lt
        LEFT JOIN users u ON lt.user_id = u.id
        LEFT JOIN reward_templates rt ON lt.pre_assigned_reward_id = rt.id"
        . $whereClause . 
        " ORDER BY lt.id DESC";
        
if (!$all) {
    // Apply pagination only if not requesting all tickets
    $sql .= " LIMIT " . $per_page . " OFFSET " . $offset;
}

$result = $conn->query($sql);

$tickets = [];
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}

echo json_encode([
    'success' => true,
    'tickets' => $tickets,
    'pagination' => [
        'page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => $total_pages
    ]
]);

$conn->close();
?>

