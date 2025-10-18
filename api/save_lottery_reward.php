<?php
require_once 'connect.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

// Require authentication
requireAuth();
$userId = getCurrentUserId();

// Parse input data
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode error: " . json_last_error_msg());
    sendError('Dữ liệu JSON không hợp lệ');
}

// Validate and sanitize input
$rewardName = sanitizeInput($input['reward_name'] ?? '');
$rewardType = sanitizeInput($input['reward_type'] ?? '');
$rewardValue = sanitizeInput($input['reward_value'] ?? '');
$rewardCode = sanitizeInput($input['reward_code'] ?? '');
$ticketId = isset($input['ticket_id']) ? filter_var($input['ticket_id'], FILTER_VALIDATE_INT) : null;
$expiresDays = filter_var($input['expires_days'] ?? 30, FILTER_VALIDATE_INT);

// Validate required fields
if (empty($rewardName) || empty($rewardType)) {
    sendError('Dữ liệu phần thưởng không đầy đủ.');
}

// Generate a unique reward code if not provided and it's a redeemable type
if (in_array($rewardType, ['discount', 'free_shipping', 'accessory', 'gift']) && empty($rewardCode)) {
    $rewardCode = strtoupper(bin2hex(random_bytes(5))); // Generate a 10-char hex code
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Calculate expiration date
    $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiresDays days"));

    error_log("Saving reward for user_id: {$userId}, ticket_id: {$ticketId}, reward: {$rewardName}");
    
    // Insert reward into database using direct PDO (same as use_lottery_ticket.php)
    $sql = "INSERT INTO lottery_rewards 
            (user_id, ticket_id, reward_name, reward_type, reward_value, reward_code, reward_image, status, expires_at, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $userId,
        $ticketId,
        $rewardName,
        $rewardType,
        $rewardValue ?: null,
        $rewardCode ?: null,
        null, // reward_image
        'pending', // status
        $expiresAt,
        null // notes
    ]);
    
    $rewardId = $pdo->lastInsertId();
    
    error_log("Reward saved successfully with ID: {$rewardId}");

    // Return success response
    sendSuccess([
        'reward_id' => $rewardId,
        'reward_code' => $rewardCode,
        'expires_at' => $expiresAt
    ], 'Phần thưởng đã được lưu thành công!');

} catch (Exception $e) {
    error_log("Save lottery reward error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Return error response
    sendError('Không thể lưu phần thưởng: ' . $e->getMessage(), 500);
}
?>
