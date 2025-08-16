<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';
require_once 'connect.php';

try {
    // Lấy tham số từ query string
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-10 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+20 days'));
    $username = $_GET['username'] ?? '';

    if (empty($username)) {
        throw new Exception('Username is required');
    }

    // Tạo array các ngày trong khoảng thời gian
    $period = new DatePeriod(
        new DateTime($startDate),
        new DateInterval('P1D'),
        (new DateTime($endDate))->modify('+1 day')
    );

    $timeline = [];

    foreach ($period as $date) {
        $dateString = $date->format('Y-m-d');
        $dayData = [
            'date' => $dateString,
            'formatted_date' => formatDate($date),
            'tasks' => [],
            'wishes' => []
        ];

        // Lấy tasks cho ngày này
        $taskQuery = "SELECT id, title, description, scheduled_date, scheduled_time, 
                             completed as is_completed, created_at 
                      FROM tasks 
                      WHERE username = ? AND DATE(scheduled_date) = ? 
                      ORDER BY scheduled_time ASC, created_at DESC";
        $taskStmt = $pdo->prepare($taskQuery);
        $taskStmt->execute([$username, $dateString]);
        $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format tasks
        foreach ($tasks as &$task) {
            $task['is_completed'] = (bool)$task['is_completed'];
            // Remove priority formatting since priority column doesn't exist
        }
        $dayData['tasks'] = $tasks;

        // Lấy wishes cho ngày này
        $wishQuery = "SELECT id, title, description, completed as is_completed, 
                             created_at, scheduled_date as target_date
                      FROM wishes 
                      WHERE username = ? AND DATE(scheduled_date) = ? 
                      ORDER BY created_at DESC";
        $wishStmt = $pdo->prepare($wishQuery);
        $wishStmt->execute([$username, $dateString]);
        $wishes = $wishStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format wishes
        foreach ($wishes as &$wish) {
            $wish['is_completed'] = (bool)$wish['is_completed'];
            // Xóa price processing vì không có các cột này trong database
            $wish['price'] = null; // Set null để tương thích với frontend
            $wish['formatted_price'] = null;
        }
        $dayData['wishes'] = $wishes;

        $timeline[] = $dayData;
    }

    echo json_encode([
        'success' => true,
        'data' => $timeline
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function formatDate($date) {
    $today = new DateTime();
    $tomorrow = (clone $today)->modify('+1 day');
    $yesterday = (clone $today)->modify('-1 day');
    
    if ($date->format('Y-m-d') === $today->format('Y-m-d')) {
        return 'Hôm nay';
    } elseif ($date->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
        return 'Ngày mai';
    } elseif ($date->format('Y-m-d') === $yesterday->format('Y-m-d')) {
        return 'Hôm qua';
    } else {
        $diff = $today->diff($date);
        if ($date > $today) {
            if ($diff->days == 1) {
                return 'Ngày mai';
            } else {
                return $diff->days . ' ngày nữa';
            }
        } else {
            if ($diff->days == 1) {
                return 'Hôm qua';
            } else {
                return $diff->days . ' ngày trước';
            }
        }
    }
}
?>
