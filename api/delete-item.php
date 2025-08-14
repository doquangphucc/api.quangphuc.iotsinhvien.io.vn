<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'connect.php';

try {
    // Lấy dữ liệu từ request
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON data');
    }

    $type = $input['type'] ?? ''; // 'task' hoặc 'wish'
    $id = (int)($input['id'] ?? 0);
    $username = $input['username'] ?? '';
    
    if (empty($type) || $id <= 0 || empty($username)) {
        throw new Exception('Type, ID and username are required');
    }

    if ($type === 'task') {
        // Kiểm tra xem task có tồn tại và thuộc về user không
        $checkStmt = $pdo->prepare("SELECT id, title FROM tasks WHERE id = ? AND username = ?");
        $checkStmt->execute([$id, $username]);
        $task = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$task) {
            throw new Exception('Task not found or you do not have permission to delete it');
        }

        // Xóa task
        $deleteStmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND username = ?");
        $deleteStmt->execute([$id, $username]);
        
        if ($deleteStmt->rowCount() === 0) {
            throw new Exception('Failed to delete task');
        }

        echo json_encode([
            'success' => true,
            'message' => 'Task deleted successfully',
            'deleted_item' => [
                'type' => 'task',
                'id' => $id,
                'title' => $task['title']
            ]
        ], JSON_UNESCAPED_UNICODE);

    } elseif ($type === 'wish') {
        // Kiểm tra xem wish có tồn tại và thuộc về user không
        $checkStmt = $pdo->prepare("SELECT id, title FROM wishes WHERE id = ? AND username = ?");
        $checkStmt->execute([$id, $username]);
        $wish = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$wish) {
            throw new Exception('Wish not found or you do not have permission to delete it');
        }

        // Xóa wish
        $deleteStmt = $pdo->prepare("DELETE FROM wishes WHERE id = ? AND username = ?");
        $deleteStmt->execute([$id, $username]);
        
        if ($deleteStmt->rowCount() === 0) {
            throw new Exception('Failed to delete wish');
        }

        echo json_encode([
            'success' => true,
            'message' => 'Wish deleted successfully',
            'deleted_item' => [
                'type' => 'wish',
                'id' => $id,
                'title' => $wish['title']
            ]
        ], JSON_UNESCAPED_UNICODE);

    } else {
        throw new Exception('Invalid type. Must be "task" or "wish"');
    }

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
