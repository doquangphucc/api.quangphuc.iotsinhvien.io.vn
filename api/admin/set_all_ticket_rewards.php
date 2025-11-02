<?php
// Set reward for all tickets matching criteria
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

if (!hasPermission($conn, 'tickets', 'edit')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền cấu hình vé quay']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = isset($data['user_id']) && $data['user_id'] > 0 ? intval($data['user_id']) : null;
$ticket_status = $data['ticket_status'] ?? 'active';
$reward_id = isset($data['reward_id']) && $data['reward_id'] !== null ? intval($data['reward_id']) : null;

// Start transaction
$conn->begin_transaction();

try {
    // Build WHERE clause based on criteria
    $where = [];
    $params = [];
    $types = '';
    
    if ($user_id !== null) {
        $where[] = "user_id = ?";
        $params[] = $user_id;
        $types .= "i";
    }
    
    if ($ticket_status === 'active') {
        $where[] = "status = 'active'";
    }
    
    $whereClause = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";
    
    // Update tickets
    if ($reward_id !== null && $reward_id > 0) {
        // Set specific reward
        $sql = "UPDATE lottery_tickets SET pre_assigned_reward_id = ?" . $whereClause;
        
        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            $params = array_merge([$reward_id], $params);
            $types = "i" . $types;
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $affected = $conn->affected_rows;
            $stmt->close();
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $reward_id);
            $stmt->execute();
            $affected = $conn->affected_rows;
            $stmt->close();
        }
    } else {
        // Remove reward (set to NULL) - "May mắn lần sau"
        $sql = "UPDATE lottery_tickets SET pre_assigned_reward_id = NULL" . $whereClause;
        
        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $affected = $conn->affected_rows;
            $stmt->close();
        } else {
            $result = $conn->query($sql);
            $affected = $conn->affected_rows;
        }
    }
    
    $conn->commit();
    
    $rewardText = $reward_id !== null && $reward_id > 0 ? "phần thưởng" : "may mắn lần sau";
    
    echo json_encode([
        'success' => true,
        'message' => "Đã cấu hình {$rewardText} cho {$affected} vé quay",
        'affected_rows' => $affected
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}

$conn->close();
?>

