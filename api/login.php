<?php
require_once __DIR__.'/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status'=>'error','message'=>'Method not allowed'], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if ($username === '' || $password === '') {
    json_response(['status'=>'error','message'=>'Thiếu dữ liệu'],400);
}

$conn = db_get_connection();

$stmt = $conn->prepare('SELECT id, password FROM tai_khoan WHERE user = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    json_response(['status'=>'error','code'=>'USER_NOT_FOUND','message'=>'Sai tài khoản hoặc mật khẩu'],401);
}

if (!password_verify($password, $row['password'])) {
    json_response(['status'=>'error','code'=>'WRONG_PASSWORD','message'=>'Sai tài khoản hoặc mật khẩu'],401);
}

// Có thể tạo session / token tại đây nếu cần sau này.
json_response(['status'=>'success','message'=>'Đăng nhập thành công','user_id'=>$row['id']]);
?>
