<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once 'connect.php';

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $id = $input['id'] ?? null;              // Frontend đang gửi primary key dạng số
    $type = $input['type'] ?? null;          // 'task' | 'wish'
    $explicitStatus = $input['status'] ?? null; // Tuỳ chọn: 'completed' | 'pending'

    if (!$id || !$type) {
        throw new Exception('Missing id or type');
    }

    if (!in_array($type, ['task','wish'])) {
        throw new Exception('Invalid type');
    }

    $table = $type === 'task' ? 'tasks' : 'wishes';

    // Lấy bản ghi theo primary key id trước (phù hợp cấu trúc hiện tại trên trang all-tasks / all-wishes)
    $select = $pdo->prepare("SELECT id, status FROM {$table} WHERE id = ? LIMIT 1");
    $select->execute([$id]);
    $row = $select->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception('Item not found');
    }

    // Nếu client không gửi status thì tự toggle
    if ($explicitStatus === null) {
        $newStatusValue = $row['status'] ? 0 : 1;
    } else {
        if (!in_array($explicitStatus, ['completed','pending'])) {
            throw new Exception('Invalid status value');
        }
        $newStatusValue = $explicitStatus === 'completed' ? 1 : 0;
    }

    $update = $pdo->prepare("UPDATE {$table} SET status = ?, updated_at = NOW() WHERE id = ?");
    $update->execute([$newStatusValue, $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Status updated',
        'id' => (int)$id,
        'type' => $type,
        'is_completed' => (bool)$newStatusValue
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
}
?>
