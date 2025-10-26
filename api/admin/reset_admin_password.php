<?php
/**
 * Helper script to reset admin password
 * Delete this file after use for security
 */

require_once __DIR__ . '/../db_mysqli.php';

// New password
$new_password = 'admin123';

// Hash password
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

// Update admin user
$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed_password);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Admin password reset successfully!',
        'username' => 'admin',
        'password' => $new_password,
        'hash' => $hashed_password
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to reset password: ' . $conn->error
    ], JSON_PRETTY_PRINT);
}

$stmt->close();
$conn->close();

echo "\n\n⚠️ IMPORTANT: Delete this file (reset_admin_password.php) immediately after use!\n";
?>

