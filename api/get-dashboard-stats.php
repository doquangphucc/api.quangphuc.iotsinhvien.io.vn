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
            SUM(CASE WHEN t.completed = 1 THEN 1 ELSE 0 END) as completed_tasks,
            SUM(CASE WHEN t.completed = 0 THEN 1 ELSE 0 END) as pending_tasks
        FROM tasks t
        WHERE t.username = ?
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
            SUM(CASE WHEN w.completed = 1 THEN 1 ELSE 0 END) as completed_wishes,
            SUM(CASE WHEN w.completed = 0 THEN 1 ELSE 0 END) as pending_wishes
        FROM wishes w
        WHERE w.username = ?
    ");
    $wishStmt->execute([$username]);
    $wishResult = $wishStmt->fetch(PDO::FETCH_ASSOC);

    if ($wishResult) {
        $stats['wishes']['total'] = (int)$wishResult['total_wishes'];
        $stats['wishes']['completed'] = (int)$wishResult['completed_wishes'];
        $stats['wishes']['pending'] = (int)$wishResult['pending_wishes'];
        $stats['wishes']['total_price'] = 0; // Bảng mới không có price
        $stats['wishes']['completed_price'] = 0; // Bảng mới không có price
        
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
        (SELECT 'task' as type, t.id, t.title, t.created_at, t.completed as is_completed, NULL as price 
         FROM tasks t
         WHERE t.username = ? 
         ORDER BY t.created_at DESC LIMIT 5)
        UNION ALL
        (SELECT 'wish' as type, w.id, w.title, w.created_at, w.completed as is_completed, NULL as price 
         FROM wishes w
         WHERE w.username = ? 
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
            SUM(CASE WHEN t.completed = 1 THEN 1 ELSE 0 END) as completed_count
        FROM tasks t
        WHERE t.username = ? AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(t.created_at), type
        
        UNION ALL
        
        SELECT 
            DATE(w.created_at) as date,
            'wish' as type,
            COUNT(*) as count,
            SUM(CASE WHEN w.completed = 1 THEN 1 ELSE 0 END) as completed_count
        FROM wishes w
        WHERE w.username = ? AND w.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
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
