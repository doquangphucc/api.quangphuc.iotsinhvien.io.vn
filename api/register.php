<?php
require_once __DIR__.'/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status'=>'error','message'=>'Method not allowed'], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    // Fallback nếu gửi form-encoded
    $data = $_POST;
}

$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');
$phone    = trim($data['phone'] ?? '');

if ($username === '' || $password === '' || $phone === '') {
    json_response(['status'=>'error','message'=>'Thiếu dữ liệu'],400);
}

if (!preg_match('/^[0-9]{8,15}$/',$phone)) {
    json_response(['status'=>'error','message'=>'Số điện thoại không hợp lệ'],400);
}

$conn = db_get_connection();

// Kiểm tra số điện thoại
$stmt = $conn->prepare('SELECT id FROM tai_khoan WHERE phone_number = ? LIMIT 1');
$stmt->bind_param('s', $phone);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    json_response(['status'=>'error','code'=>'PHONE_EXISTS','message'=>'Số điện thoại đã tồn tại'],409);
}
$stmt->close();

// Kiểm tra username
$stmt = $conn->prepare('SELECT id FROM tai_khoan WHERE user = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    json_response(['status'=>'error','code'=>'USER_EXISTS','message'=>'Username đã tồn tại'],409);
}
$stmt->close();

// Hash mật khẩu (có thể dùng password_hash)
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare('INSERT INTO tai_khoan (user, password, phone_number) VALUES (?,?,?)');
$stmt->bind_param('sss', $username, $hash, $phone);
if ($stmt->execute()) {
    json_response(['status'=>'success','message'=>'Đăng ký thành công','id'=>$stmt->insert_id],201);
}

json_response(['status'=>'error','message'=>'Lỗi máy chủ'],500);
?>
