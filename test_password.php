<?php
/**
 * File test để tạo password hash và verify
 * Chạy file này trên localhost hoặc hosting để lấy hash đúng
 */

echo "<h2>Password Hash Generator & Tester</h2>";

// Password cần hash
$password = 'admin123';

// Tạo hash mới
$new_hash = password_hash($password, PASSWORD_BCRYPT);

echo "<h3>1. Tạo Hash Mới:</h3>";
echo "<strong>Password:</strong> admin123<br>";
echo "<strong>New Hash:</strong><br>";
echo "<textarea style='width:100%;height:60px;'>$new_hash</textarea><br><br>";

// Test các hash có sẵn
$test_hashes = [
    'Hash trong database hiện tại' => '$2y$10$rGHvG3J5YLxZ0h6qKz.uJeP7VzN4Y0QfY9xY8f.g1rP6qB2K4nDUO',
    'Hash cũ' => '$2y$10$E4Mjzm5z3xW0nYJX5K5XKOYqXvXdWzQ5Q5XYZ5Z5Z5Z5Z5Z5Z5Z5Z5'
];

echo "<h3>2. Test Verify với các hash:</h3>";
foreach ($test_hashes as $label => $hash) {
    $result = password_verify($password, $hash);
    $status = $result ? '✅ ĐÚNG' : '❌ SAI';
    echo "<strong>$label:</strong> $status<br>";
    echo "Hash: <code>$hash</code><br><br>";
}

// Kết nối database và check
echo "<h3>3. Check password trong database:</h3>";
require_once 'api/db_mysqli.php';

$sql = "SELECT id, username, password, is_admin FROM users WHERE username = 'admin'";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo "<strong>User tìm thấy:</strong><br>";
    echo "ID: {$row['id']}<br>";
    echo "Username: {$row['username']}<br>";
    echo "Is Admin: " . ($row['is_admin'] ? 'Yes' : 'No') . "<br>";
    echo "Password Hash: <br><textarea style='width:100%;height:60px;'>{$row['password']}</textarea><br>";
    
    $verify_result = password_verify($password, $row['password']);
    echo "<br><strong>Verify với 'admin123': " . ($verify_result ? '✅ ĐÚNG' : '❌ SAI') . "</strong><br>";
} else {
    echo "❌ Không tìm thấy user admin trong database!<br>";
}

echo "<hr>";
echo "<h3>4. SQL để update password:</h3>";
echo "<textarea style='width:100%;height:80px;'>UPDATE users SET password = '$new_hash' WHERE username = 'admin';</textarea>";

$conn->close();
?>

