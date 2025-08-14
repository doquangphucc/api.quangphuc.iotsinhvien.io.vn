<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include_once 'connect.php';

try {
    // Lấy tham số từ URL
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    // Xây dựng query
    $sql = "SELECT id, title, description, due_date, is_completed, created_at 
            FROM tasks";
    
    $params = [];
    
    if ($status !== 'all') {
        $sql .= " WHERE is_completed = ?";
        $params[] = ($status === 'completed') ? 1 : 0;
    }
    
    $sql .= " ORDER BY due_date ASC, created_at DESC";
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Đếm tổng số tasks
    $countSql = "SELECT COUNT(*) as total FROM tasks";
    if ($status !== 'all') {
        $countSql .= " WHERE is_completed = " . (($status === 'completed') ? 1 : 0);
    }
    
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Format dữ liệu
    foreach ($tasks as &$task) {
        $task['is_completed'] = (bool)$task['is_completed'];
        $task['due_date'] = $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : null;
        $task['created_at'] = date('d/m/Y H:i', strtotime($task['created_at']));
    }

    echo json_encode([
        'success' => true,
        'data' => $tasks,
        'total' => $totalCount,
        'has_more' => ($offset + $limit) < $totalCount
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy danh sách công việc: ' . $e->getMessage()
    ]);
}
?>
