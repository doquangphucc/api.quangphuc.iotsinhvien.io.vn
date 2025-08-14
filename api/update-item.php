<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, OPTIONS');
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
    
    if (empty($type) || $id <= 0) {
        throw new Exception('Type and ID are required');
    }

    if ($type === 'task') {
        // Cập nhật task
        $updates = [];
        $params = [];
        
        if (isset($input['title'])) {
            $updates[] = 'title = ?';
            $params[] = $input['title'];
        }
        
        if (isset($input['description'])) {
            $updates[] = 'description = ?';
            $params[] = $input['description'];
        }
        
        if (isset($input['priority'])) {
            $updates[] = 'priority = ?';
            $params[] = $input['priority'];
        }
        
        if (isset($input['category'])) {
            $updates[] = 'category = ?';
            $params[] = $input['category'];
        }
        
        if (isset($input['due_date'])) {
            if (empty($input['due_date'])) {
                $updates[] = 'due_date = NULL';
            } else {
                $updates[] = 'due_date = ?';
                $params[] = $input['due_date'];
            }
        }
        
        if (isset($input['is_completed'])) {
            $updates[] = 'is_completed = ?';
            $params[] = $input['is_completed'] ? 1 : 0;
        }

        if (empty($updates)) {
            throw new Exception('No fields to update');
        }

        $updates[] = 'updated_at = NOW()';
        $params[] = $id;
        
        $sql = "UPDATE tasks SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Task not found or no changes made');
        }

        // Lấy thông tin task đã cập nhật
        $selectStmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $selectStmt->execute([$id]);
        $updatedTask = $selectStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $updatedTask
        ], JSON_UNESCAPED_UNICODE);

    } elseif ($type === 'wish') {
        // Cập nhật wish
        $updates = [];
        $params = [];
        
        if (isset($input['title'])) {
            $updates[] = 'title = ?';
            $params[] = $input['title'];
        }
        
        if (isset($input['description'])) {
            $updates[] = 'description = ?';
            $params[] = $input['description'];
        }
        
        if (isset($input['price'])) {
            if (empty($input['price'])) {
                $updates[] = 'price = NULL';
            } else {
                $updates[] = 'price = ?';
                $params[] = (float)$input['price'];
            }
        }
        
        if (isset($input['category'])) {
            $updates[] = 'category = ?';
            $params[] = $input['category'];
        }
        
        if (isset($input['store_location'])) {
            $updates[] = 'store_location = ?';
            $params[] = $input['store_location'];
        }
        
        if (isset($input['purchase_link'])) {
            $updates[] = 'purchase_link = ?';
            $params[] = $input['purchase_link'];
        }
        
        if (isset($input['priority'])) {
            $updates[] = 'priority = ?';
            $params[] = $input['priority'];
        }
        
        if (isset($input['is_completed'])) {
            $updates[] = 'is_completed = ?';
            $params[] = $input['is_completed'] ? 1 : 0;
        }

        if (empty($updates)) {
            throw new Exception('No fields to update');
        }

        $updates[] = 'updated_at = NOW()';
        $params[] = $id;
        
        $sql = "UPDATE wishes SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Wish not found or no changes made');
        }

        // Lấy thông tin wish đã cập nhật
        $selectStmt = $pdo->prepare("SELECT * FROM wishes WHERE id = ?");
        $selectStmt->execute([$id]);
        $updatedWish = $selectStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Wish updated successfully',
            'data' => $updatedWish
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
