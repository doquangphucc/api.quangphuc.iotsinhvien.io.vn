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
    $query = "SELECT id, prize_name, is_active, created_at, updated_at 
              FROM wheel_prizes {$where}
              ORDER BY id ASC";

    $result = mysqli_query($conn, $query);
    if (!$result) {
        throw new Exception('Query error: ' . mysqli_error($conn));
    }
    $prizes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['is_active'] = (bool) $row['is_active'];
        $prizes[] = $row;
    }

    // Stats
    $statsQuery = "SELECT 
            COUNT(*) AS total_count,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_count
        FROM wheel_prizes";
    $statsResult = mysqli_query($conn, $statsQuery);
    if (!$statsResult) {
        throw new Exception('Stats query error: ' . mysqli_error($conn));
    }
    $stats = mysqli_fetch_assoc($statsResult);

    echo json_encode([
        'success' => true,
        'data' => [
            'prizes' => $prizes,
            'stats' => [
                'total_count' => (int) ($stats['total_count'] ?? 0),
                'active_count' => (int) ($stats['active_count'] ?? 0)
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Error fetching wheel prizes: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải danh sách phần thưởng. Chi tiết: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_close($conn);
?>

