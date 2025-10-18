<?php
// API để lấy danh sách phần thưởng của user
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'session.php';
require_once 'db_mysqli.php';

// Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Chỉ chấp nhận phương thức GET'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để xem phần thưởng'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy tham số filter từ query string
$status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$order_by = isset($_GET['order_by']) ? trim($_GET['order_by']) : 'won_at';
$order_dir = isset($_GET['order_dir']) ? strtoupper(trim($_GET['order_dir'])) : 'DESC';

// Validate order direction
if (!in_array($order_dir, ['ASC', 'DESC'])) {
    $order_dir = 'DESC';
}

// Validate order by
$allowed_order_fields = ['won_at', 'reward_name', 'reward_type', 'expires_at', 'status'];
if (!in_array($order_by, $allowed_order_fields)) {
    $order_by = 'won_at';
}

try {
    $conn = DatabaseConnection::getInstance()->getConnection();
    
    // Xây dựng query
    $where_clause = "WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";
    
    if ($status !== 'all') {
        $where_clause .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    // Đếm tổng số phần thưởng
    $count_sql = "SELECT COUNT(*) as total FROM lottery_rewards $where_clause";
    $stmt_count = $conn->prepare($count_sql);
    $stmt_count->bind_param($types, ...$params);
    $stmt_count->execute();
    $count_result = $stmt_count->get_result();
    $total = $count_result->fetch_assoc()['total'];
    $stmt_count->close();
    
    // Lấy danh sách phần thưởng
    $sql = "SELECT 
                lr.*,
                lt.ticket_code,
                u.full_name as user_name,
                CASE 
                    WHEN lr.status = 'expired' THEN 'expired'
                    WHEN lr.expires_at < NOW() THEN 'expired'
                    ELSE lr.status
                END as current_status,
                DATEDIFF(lr.expires_at, NOW()) as days_until_expiry
            FROM lottery_rewards lr
            LEFT JOIN lottery_tickets lt ON lr.ticket_id = lt.id
            LEFT JOIN users u ON lr.user_id = u.id
            $where_clause
            ORDER BY $order_by $order_dir
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rewards = [];
    while ($row = $result->fetch_assoc()) {
        // Tự động cập nhật status nếu đã hết hạn
        if ($row['current_status'] === 'expired' && $row['status'] !== 'expired') {
            $update_sql = "UPDATE lottery_rewards SET status = 'expired' WHERE id = ?";
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param("i", $row['id']);
            $stmt_update->execute();
            $stmt_update->close();
            $row['status'] = 'expired';
        }
        
        $rewards[] = $row;
    }
    
    $stmt->close();
    
    // Thống kê
    $stats_sql = "SELECT 
                    COUNT(*) as total_rewards,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'used' THEN 1 ELSE 0 END) as used_count,
                    SUM(CASE WHEN status = 'expired' OR expires_at < NOW() THEN 1 ELSE 0 END) as expired_count
                  FROM lottery_rewards
                  WHERE user_id = ?";
    
    $stmt_stats = $conn->prepare($stats_sql);
    $stmt_stats->bind_param("i", $user_id);
    $stmt_stats->execute();
    $stats_result = $stmt_stats->get_result();
    $stats = $stats_result->fetch_assoc();
    $stmt_stats->close();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Lấy danh sách phần thưởng thành công',
        'data' => [
            'rewards' => $rewards,
            'pagination' => [
                'total' => intval($total),
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total
            ],
            'stats' => [
                'total_rewards' => intval($stats['total_rewards']),
                'pending_count' => intval($stats['pending_count']),
                'used_count' => intval($stats['used_count']),
                'expired_count' => intval($stats['expired_count'])
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

