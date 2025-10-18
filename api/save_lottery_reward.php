<?php
require_once 'connect.php';
require_once 'auth_helpers.php';

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

    // Prepare data for insertion - matching database schema
    $rewardData = [
        'user_id' => $userId,
        'ticket_id' => $ticketId,
        'reward_name' => $rewardName,
        'reward_type' => $rewardType,
        'reward_value' => $rewardValue ?: null,
        'reward_code' => $rewardCode ?: null,
        'reward_image' => null,
        'status' => 'pending',
        'expires_at' => $expiresAt,
        'notes' => null
    ];

    error_log("Saving reward for user_id: {$userId}, ticket_id: {$ticketId}, reward: {$rewardName}");
    
    // Insert reward into database
    $rewardId = $db->insert('lottery_rewards', $rewardData);
    
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
