<?php
// Check if user is admin
require_once __DIR__ . '/../db_mysqli.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Chưa đăng nhập'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if user is admin
$stmt = $conn->prepare("SELECT is_admin, full_name, username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !$user['is_admin']) {
    echo json_encode([
        'success' => false,
        'message' => 'Không có quyền truy cập'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'is_admin' => true,
    'user' => [
        'id' => $user_id,
        'full_name' => $user['full_name'],
        'username' => $user['username']
    ]
]);

$stmt->close();
$conn->close();