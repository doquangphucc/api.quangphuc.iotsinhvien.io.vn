<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'connect.php';

try {
    // Lấy username từ query parameter
    $username = $_GET['username'] ?? '';
    
    if (empty($username)) {
        throw new Exception('Username is required');
    }

    // Khởi tạo thống kê
    $stats = [
        'tasks' => [
            'total' => 0,
            'completed' => 0,
            'pending' => 0,
            'completion_rate' => 0
        ],
        'wishes' => [
            'total' => 0,
            'completed' => 0,
            'pending' => 0,
            'completion_rate' => 0,
            'total_price' => 0,
            'completed_price' => 0
        ],
        'overall' => [
            'total_items' => 0,
            'completed_items' => 0,
            'overall_completion_rate' => 0
        ]
    ];

    // Lấy thống kê tasks
    $taskStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_tasks,
            SUM(CASE WHEN t.status = 1 THEN 1 ELSE 0 END) as completed_tasks,
            SUM(CASE WHEN t.status = 0 THEN 1 ELSE 0 END) as pending_tasks
        FROM tasks t
        LEFT JOIN tai_khoan tk ON t.user_id = tk.id
        WHERE tk.user = ?
    ");
    $taskStmt->execute([$username]);
    $taskResult = $taskStmt->fetch(PDO::FETCH_ASSOC);

    if ($taskResult) {
        $stats['tasks']['total'] = (int)$taskResult['total_tasks'];
        $stats['tasks']['completed'] = (int)$taskResult['completed_tasks'];
        $stats['tasks']['pending'] = (int)$taskResult['pending_tasks'];
        
        if ($stats['tasks']['total'] > 0) {
            $stats['tasks']['completion_rate'] = round(($stats['tasks']['completed'] / $stats['tasks']['total']) * 100, 1);
        }
    }

    // Lấy thống kê wishes
    $wishStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_wishes,
            SUM(CASE WHEN w.status = 1 THEN 1 ELSE 0 END) as completed_wishes,
            SUM(CASE WHEN w.status = 0 THEN 1 ELSE 0 END) as pending_wishes,
            COALESCE(SUM(w.price), 0) as total_price,
            COALESCE(SUM(CASE WHEN w.status = 1 THEN w.price ELSE 0 END), 0) as completed_price
        FROM wishes w
        LEFT JOIN tai_khoan tk ON w.user_id = tk.id
        WHERE tk.user = ?
    ");
    $wishStmt->execute([$username]);
    $wishResult = $wishStmt->fetch(PDO::FETCH_ASSOC);

    if ($wishResult) {
        $stats['wishes']['total'] = (int)$wishResult['total_wishes'];
        $stats['wishes']['completed'] = (int)$wishResult['completed_wishes'];
        $stats['wishes']['pending'] = (int)$wishResult['pending_wishes'];
        $stats['wishes']['total_price'] = (float)$wishResult['total_price'];
        $stats['wishes']['completed_price'] = (float)$wishResult['completed_price'];
        
        if ($stats['wishes']['total'] > 0) {
            $stats['wishes']['completion_rate'] = round(($stats['wishes']['completed'] / $stats['wishes']['total']) * 100, 1);
        }
    }

    // Tính thống kê tổng quan
    $stats['overall']['total_items'] = $stats['tasks']['total'] + $stats['wishes']['total'];
    $stats['overall']['completed_items'] = $stats['tasks']['completed'] + $stats['wishes']['completed'];
    
    if ($stats['overall']['total_items'] > 0) {
        $stats['overall']['overall_completion_rate'] = round(($stats['overall']['completed_items'] / $stats['overall']['total_items']) * 100, 1);
    }

    // Lấy hoạt động gần đây (10 items gần nhất)
    $recentStmt = $pdo->prepare("
        (SELECT 'task' as type, t.id, t.title, t.created_at, t.status as is_completed, NULL as price 
         FROM tasks t
         LEFT JOIN tai_khoan tk ON t.user_id = tk.id
         WHERE tk.user = ? 
         ORDER BY t.created_at DESC LIMIT 5)
        UNION ALL
        (SELECT 'wish' as type, w.id, w.title, w.created_at, w.status as is_completed, w.price 
         FROM wishes w
         LEFT JOIN tai_khoan tk ON w.user_id = tk.id
         WHERE tk.user = ? 
         ORDER BY w.created_at DESC LIMIT 5)
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $recentStmt->execute([$username, $username]);
    $recentActivities = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Thêm hoạt động gần đây vào kết quả
    $stats['recent_activities'] = $recentActivities;

    // Lấy thống kê theo thời gian (7 ngày gần nhất)
    $weeklyStmt = $pdo->prepare("
        SELECT 
            DATE(t.created_at) as date,
            'task' as type,
            COUNT(*) as count,
            SUM(CASE WHEN t.status = 1 THEN 1 ELSE 0 END) as completed_count
        FROM tasks t
        LEFT JOIN tai_khoan tk ON t.user_id = tk.id
        WHERE tk.user = ? AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(t.created_at), type
        
        UNION ALL
        
        SELECT 
            DATE(w.created_at) as date,
            'wish' as type,
            COUNT(*) as count,
            SUM(CASE WHEN w.status = 1 THEN 1 ELSE 0 END) as completed_count
        FROM wishes w
        LEFT JOIN tai_khoan tk ON w.user_id = tk.id
        WHERE tk.user = ? AND w.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(w.created_at), type
        
        ORDER BY date DESC
    ");
    $weeklyStmt->execute([$username, $username]);
    $weeklyStats = $weeklyStmt->fetchAll(PDO::FETCH_ASSOC);

    // Thêm thống kê tuần vào kết quả
    $stats['weekly_stats'] = $weeklyStats;

    echo json_encode([
        'success' => true,
        'data' => $stats,
        'message' => 'Dashboard stats loaded successfully'
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
