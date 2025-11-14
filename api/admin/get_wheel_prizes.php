<?php
/**
 * API: Lấy danh sách phần thưởng vòng quay admin
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

if (!hasPermission($conn, 'wheel', 'view')) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn không có quyền xem phần thưởng vòng quay'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$status = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : 'active';
$allowedStatus = ['active', 'all'];
if (!in_array($status, $allowedStatus)) {
    $status = 'active';
}

try {
    $where = $status === 'active' ? 'WHERE is_active = 1' : '';
    $query = "SELECT id, prize_name, prize_description, prize_value, prize_icon, prize_color, probability_weight, is_active, created_at, updated_at 
              FROM wheel_prizes {$where}
              ORDER BY probability_weight DESC, prize_name ASC";

    $result = mysqli_query($conn, $query);
    $prizes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['probability_weight'] = (int) $row['probability_weight'];
        $row['is_active'] = (bool) $row['is_active'];
        $prizes[] = $row;
    }

    // Stats
    $statsQuery = "SELECT 
            COUNT(*) AS total_count,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_count,
            COALESCE(SUM(probability_weight), 0) AS total_weight,
            COALESCE(SUM(CASE WHEN is_active = 1 THEN probability_weight ELSE 0 END), 0) AS active_weight
        FROM wheel_prizes";
    $statsResult = mysqli_query($conn, $statsQuery);
    $stats = mysqli_fetch_assoc($statsResult);

    echo json_encode([
        'success' => true,
        'data' => [
            'prizes' => $prizes,
            'stats' => [
                'total_count' => (int) ($stats['total_count'] ?? 0),
                'active_count' => (int) ($stats['active_count'] ?? 0),
                'total_weight' => (int) ($stats['total_weight'] ?? 0),
                'active_weight' => (int) ($stats['active_weight'] ?? 0)
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('Error fetching wheel prizes: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải danh sách phần thưởng'
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_close($conn);
?>

