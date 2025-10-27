<?php
// Add or update intro post

// Disable PHP errors/warnings display to prevent breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Increase upload limits for file uploads (up to 5MB)
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Get POST data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$display_order = intval($_POST['display_order'] ?? 0);
$is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
$image_url = $_POST['image_url'] ?? '';
$video_url = $_POST['video_url'] ?? '';

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Tiêu đề không được để trống']);
    exit;
}

// Save to database
if ($id > 0) {
    // Update existing post
    $stmt = $conn->prepare("UPDATE intro_posts SET title = ?, description = ?, image_url = ?, video_url = ?, display_order = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssssiii", $title, $description, $image_url, $video_url, $display_order, $is_active, $id);
} else {
    // Insert new post
    $stmt = $conn->prepare("INSERT INTO intro_posts (title, description, image_url, video_url, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $title, $description, $image_url, $video_url, $display_order, $is_active);
}

if ($stmt->execute()) {
    $post_id = $id > 0 ? $id : $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Lưu bài viết thành công',
        'post_id' => $post_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();

