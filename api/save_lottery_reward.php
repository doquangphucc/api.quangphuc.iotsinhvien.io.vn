<?php
require_once 'connect.php';
require_once 'auth_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

requireAuth();
$userId = getCurrentUserId();

$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
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
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiresDays days"));

    $rewardData = [
        'user_id' => $userId,
        'ticket_id' => $ticketId,
        'reward_name' => $rewardName,
        'reward_type' => $rewardType,
        'reward_value' => $rewardValue,
        'reward_code' => $rewardCode,
        'status' => 'pending',
        'expires_at' => $expiresAt
    ];

    $rewardId = $db->insert('lottery_rewards', $rewardData);

    sendSuccess(['reward_id' => $rewardId, 'reward_code' => $rewardCode], 'Phần thưởng đã được lưu thành công!');

} catch (Exception $e) {
    error_log("Save lottery reward error: " . $e->getMessage());
    sendError('Không thể lưu phần thưởng.', 500);
}
?>

