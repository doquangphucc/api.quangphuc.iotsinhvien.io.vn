<?php
// Debug: Log start
error_log("=== SAVE LOTTERY REWARD DEBUG START ===");

require_once 'connect.php';
error_log("DEBUG: connect.php loaded");

require_once 'auth_helpers.php';
error_log("DEBUG: auth_helpers.php loaded");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("DEBUG: Wrong method: " . $_SERVER['REQUEST_METHOD']);
    sendError('Phương thức không được hỗ trợ', 405);
}

error_log("DEBUG: Method OK, checking auth...");

requireAuth();
error_log("DEBUG: Auth OK");

$userId = getCurrentUserId();
error_log("DEBUG: User ID: " . $userId);

$input = json_decode(file_get_contents('php://input'), true);
error_log("DEBUG: Input data: " . json_encode($input));

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("DEBUG: JSON decode error: " . json_last_error_msg());
    sendError('Dữ liệu JSON không hợp lệ');
}

$rewardName = sanitizeInput($input['reward_name'] ?? '');
$rewardType = sanitizeInput($input['reward_type'] ?? '');
$rewardValue = sanitizeInput($input['reward_value'] ?? NULL);
$rewardCode = sanitizeInput($input['reward_code'] ?? NULL);
$ticketId = filter_var($input['ticket_id'] ?? NULL, FILTER_VALIDATE_INT);
$expiresDays = filter_var($input['expires_days'] ?? 30, FILTER_VALIDATE_INT);

if (empty($rewardName) || empty($rewardType)) {
    sendError('Dữ liệu phần thưởng không đầy đủ.');
}

// Generate a unique reward code if not provided and it's a redeemable type
if (in_array($rewardType, ['discount', 'free_shipping', 'accessory', 'gift']) && empty($rewardCode)) {
    $rewardCode = strtoupper(bin2hex(random_bytes(5))); // Generate a 10-char hex code
}

try {
    error_log("DEBUG: Starting database operations...");
    
    $db = Database::getInstance();
    error_log("DEBUG: Database instance created");
    
    $pdo = $db->getConnection();
    error_log("DEBUG: Database connection obtained");

    $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiresDays days"));
    error_log("DEBUG: Expires at: " . $expiresAt);

    $rewardData = [
        'user_id' => $userId,
        'ticket_id' => $ticketId,
        'reward_name' => $rewardName,
        'reward_type' => $rewardType,
        'reward_value' => $rewardValue,
        'reward_code' => $rewardCode,
        'reward_image' => null, // Add missing field
        'status' => 'pending',
        'expires_at' => $expiresAt,
        'notes' => null // Add missing field
    ];

    error_log("Attempting to save reward with data: " . json_encode($rewardData));
    
    $rewardId = $db->insert('lottery_rewards', $rewardData);
    
    error_log("Reward saved successfully with ID: " . $rewardId);

    sendSuccess(['reward_id' => $rewardId, 'reward_code' => $rewardCode], 'Phần thưởng đã được lưu thành công!');

} catch (Exception $e) {
    error_log("Save lottery reward error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    error_log("Input data: " . json_encode($input));
    
    // Return detailed error for debugging
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Không thể lưu phần thưởng: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'input' => $input
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>

