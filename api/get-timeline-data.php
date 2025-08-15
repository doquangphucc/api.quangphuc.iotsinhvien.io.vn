<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'connect.php';

try {
    // Lấy tham số từ query string
    $username = $_GET['username'] ?? '';
    $limit = (int)($_GET['limit'] ?? 50);
    $days = (int)($_GET['days'] ?? 10); // Lấy data từ X ngày trước đến nay
    
    if (empty($username)) {
        throw new Exception('Username is required');
    }

    // Tính ngày bắt đầu
    $startDate = date('Y-m-d', strtotime("-$days days"));

    // Lấy dữ liệu từ cả 2 bảng tasks và wishes, sắp xếp theo thời gian
    $stmt = $pdo->prepare("
        (
            SELECT 
                'task' as type,
                    t.id AS id,
                    t.title,
                DATE(t.created_at) AS date_only,
                t.created_at as timestamp,
                'việc nào' as category_text,
                t.status as is_completed
            FROM tasks t
            LEFT JOIN tai_khoan tk ON t.user_id = tk.id
            WHERE tk.user = ? AND DATE(t.created_at) >= ?
        )
        UNION ALL
        (
            SELECT 
                'wish' as type,
                    w.id AS id,
                    w.title,
                DATE(w.created_at) AS date_only,
                w.created_at as timestamp,
                'đồ nào' as category_text,
                w.status as is_completed
            FROM wishes w
            LEFT JOIN tai_khoan tk ON w.user_id = tk.id
            WHERE tk.user = ? AND DATE(w.created_at) >= ?
        )
        ORDER BY timestamp DESC
        LIMIT ?
    ");
    
    $stmt->execute([$username, $startDate, $username, $startDate, $limit]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu cho timeline
    $timeline = [];
    foreach ($items as $item) {
        $timestamp = new DateTime($item['timestamp']);
        $now = new DateTime();
        $diff = $now->diff($timestamp);
        
        // Tính số ngày từ hôm nay
        $daysAgo = $diff->days;
        if ($daysAgo == 0) {
            $timeText = "Hôm nay";
        } elseif ($daysAgo == 1) {
            $timeText = "Hôm qua";  
        } else {
            $timeText = $daysAgo . " ngày trước";
        }

        // Tạo text hiển thị
        $displayText = $item['type'] == 'task' ? 
            "Chưa có việc nào" : 
            "Chưa có đồ nào";

        if (!empty(trim($item['title']))) {
            $displayText = $item['title'];
        }

            $timeline[] = [
                'type' => $item['type'],
                'id' => (int)$item['id'],
                'title' => $displayText,
                'original_title' => $item['title'],
                'date_only' => $item['date_only'], // normalized date
                'timestamp' => $item['timestamp'],
                'time_formatted' => $timestamp->format('d/m/Y H:i'),
                'days_ago' => $daysAgo,
                'time_text' => $timeText,
                'is_completed' => (bool)$item['is_completed'],
                'status_text' => $item['is_completed'] ? 'Đã hoàn thành' : 'Chưa hoàn thành',
                'side' => ($item['type'] == 'task') ? 'left' : 'right' // Để xác định bên trái hay phải của timeline
            ];
    }

    // Thống kê nhanh và khoảng thời gian
    $minDate = null;
    $maxDate = null;
    
    if (count($timeline) > 0) {
        $dates = array_map(fn($item) => $item['timestamp'], $timeline);
        $minDate = min($dates);
        $maxDate = max($dates);
    }

    $stats = [
        'total_items' => count($timeline),
        'tasks' => count(array_filter($timeline, fn($item) => $item['type'] == 'task')),
        'wishes' => count(array_filter($timeline, fn($item) => $item['type'] == 'wish')),
        'completed_items' => count(array_filter($timeline, fn($item) => $item['is_completed'])),
        'pending_items' => count(array_filter($timeline, fn($item) => !$item['is_completed'])),
        'days_range' => $days,
        'date_from' => $startDate,
        'date_to' => date('Y-m-d'),
        'actual_min_date' => $minDate ? substr($minDate, 0, 10) : null,
        'actual_max_date' => $maxDate ? substr($maxDate, 0, 10) : null
    ];

    echo json_encode([
        'success' => true,
        'data' => $timeline,
        'stats' => $stats,
        'message' => 'Timeline data loaded successfully'
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
