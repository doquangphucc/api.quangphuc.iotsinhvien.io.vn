<?php
// Delete project
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');

if (!hasPermission($conn, 'projects', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa dự án']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

// Get project media files before deleting
$stmt = $conn->prepare("SELECT image_url, video_url FROM projects WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Delete image file if exists
    if ($project && !empty($project['image_url'])) {
        $image_path = $project['image_url'];
        
        // Try uploads directory
        $image_file = __DIR__ . '/../../uploads/project_images/' . basename($image_path);
        if (file_exists($image_file)) {
            unlink($image_file);
        }
    }
    
    // Delete video file if exists
    if ($project && !empty($project['video_url'])) {
        $video_path = $project['video_url'];
        
        // Try uploads directory
        $video_file = __DIR__ . '/../../uploads/project_videos/' . basename($video_path);
        if (file_exists($video_file)) {
            unlink($video_file);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Xóa dự án thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();


