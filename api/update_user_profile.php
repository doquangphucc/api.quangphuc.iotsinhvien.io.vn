<?php
require_once 'connect.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Dữ liệu JSON không hợp lệ');
}



$userId = getCurrentUserId();

$fullName = sanitizeInput($input['full_name'] ?? '');
$phone = sanitizeInput($input['phone'] ?? '');
$newPassword = $input['new_password'] ?? '';

// Validation
if (empty($fullName) || empty($phone)) {
    sendError('Họ tên và số điện thoại là bắt buộc.');
}
if (!empty($newPassword) && strlen($newPassword) < 6) {
    sendError('Mật khẩu mới phải có ít nhất 6 ký tự.');
}

try {
    $db = Database::getInstance();
    
    $updateData = [
        'full_name' => $fullName,
        'phone' => $phone
    ];

    if (!empty($newPassword)) {
        $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    $success = $db->update('users', $updateData, ['id' => $userId]);

    if ($success) {
        // Fetch updated user data to send back to the client
        $updatedUser = $db->selectOne('users', ['id' => $userId], 'id, full_name, username, phone');
        sendSuccess(['user' => $updatedUser], 'Cập nhật thông tin thành công!');
    } else {
        sendError('Cập nhật thất bại hoặc không có gì thay đổi.');
    }

} catch (Exception $e) {
    error_log("Update Profile error: " . $e->getMessage());
    sendError('Lỗi hệ thống, không thể cập nhật thông tin.', 500);
}

// Add the update method to the Database class in connect.php if it doesn't exist
// You would need to add this method to your connect.php file:
/*
public function update($table, $data, $conditions) {
    $dataFields = [];
    foreach ($data as $key => $value) {
        $dataFields[] = "{$key} = :data_{$key}";
    }
    $dataList = implode(', ', $dataFields);

    $whereClause = [];
    foreach ($conditions as $key => $value) {
        $whereClause[] = "{$key} = :cond_{$key}";
    }
    $whereList = implode(' AND ', $whereClause);

    $sql = "UPDATE {$table} SET {$dataList} WHERE {$whereList}";

    $params = [];
    foreach ($data as $key => $value) {
        $params[":data_{$key}"] = $value;
    }
    foreach ($conditions as $key => $value) {
        $params[":cond_{$key}"] = $value;
    }

    try {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Update failed: " . $e->getMessage());
        throw $e;
    }
}
*/
?>