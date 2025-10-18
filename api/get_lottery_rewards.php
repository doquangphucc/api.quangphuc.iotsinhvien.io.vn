<?php
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Phương thức không được hỗ trợ', 405);
}

requireAuth();
$userId = getCurrentUserId();

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
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Xây dựng query
    $where_clause = "WHERE user_id = :user_id";
    $params = [':user_id' => $userId];
    
    if ($status !== 'all') {
        $where_clause .= " AND status = :status";
        $params[':status'] = $status;
    }
    
    // Đếm tổng số phần thưởng
    $count_sql = "SELECT COUNT(*) as total FROM lottery_rewards $where_clause";
    $stmt_count = $pdo->prepare($count_sql);
    $stmt_count->execute($params);
    $total = $stmt_count->fetchColumn();
    
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
            LIMIT :limit OFFSET :offset";
    
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tự động cập nhật status nếu đã hết hạn
    foreach ($rewards as &$reward) {
        if ($reward['current_status'] === 'expired' && $reward['status'] !== 'expired') {
            $update_sql = "UPDATE lottery_rewards SET status = 'expired' WHERE id = :id";
            $stmt_update = $pdo->prepare($update_sql);
            $stmt_update->execute([':id' => $reward['id']]);
            $reward['status'] = 'expired';
        }
    }
    
    // Thống kê
    $stats_sql = "SELECT 
                    COUNT(*) as total_rewards,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'used' THEN 1 ELSE 0 END) as used_count,
                    SUM(CASE WHEN status = 'expired' OR expires_at < NOW() THEN 1 ELSE 0 END) as expired_count
                  FROM lottery_rewards
                  WHERE user_id = :user_id";
    
    $stmt_stats = $pdo->prepare($stats_sql);
    $stmt_stats->execute([':user_id' => $userId]);
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
    sendSuccess([
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
    ], 'Lấy danh sách phần thưởng thành công');
    
} catch (Exception $e) {
    error_log("Get lottery rewards error: " . $e->getMessage());
    sendError('Không thể lấy danh sách phần thưởng.', 500);
}
?>

