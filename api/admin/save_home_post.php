<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$id = isset($data['id']) ? intval($data['id']) : 0;
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$highlight_text = trim($data['highlight_text'] ?? '');
$highlight_color = trim($data['highlight_color'] ?? 'green');
$image_url = trim($data['image_url'] ?? '');
$image_position = trim($data['image_position'] ?? 'right');
$button_text = trim($data['button_text'] ?? '');
$button_url = trim($data['button_url'] ?? '');
$button_color = trim($data['button_color'] ?? 'green');
$features = $data['features'] ?? [];
$display_order = isset($data['display_order']) ? intval($data['display_order']) : 0;
$is_active = isset($data['is_active']) ? (bool)$data['is_active'] : true;
$section_id = trim($data['section_id'] ?? 'solutions');

// Determine action (create or edit)
$action = $id > 0 ? 'edit' : 'create';

// Check permission
if (!hasPermission($conn, 'home', $action)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => "Bạn không có quyền {$action} bài đăng trang chủ"]);
    exit;
}

// Validate required fields
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Tiêu đề không được để trống']);
    exit;
}

if (empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Nội dung không được để trống']);
    exit;
}

if (empty($image_url)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ảnh cho bài đăng']);
    exit;
}

// Validate image position
if (!in_array($image_position, ['left', 'right'])) {
    $image_position = 'right';
}

// Convert features array to JSON
$features_json = json_encode($features, JSON_UNESCAPED_UNICODE);

try {
    if ($id > 0) {
        // Update existing post
        $stmt = $conn->prepare("UPDATE home_posts SET 
            title = ?, 
            description = ?, 
            highlight_text = ?, 
            highlight_color = ?, 
            image_url = ?, 
            image_position = ?, 
            button_text = ?, 
            button_url = ?, 
            button_color = ?, 
            features = ?, 
            display_order = ?, 
            is_active = ?,
            section_id = ?
            WHERE id = ?");
        
        $stmt->bind_param("ssssssssssissi", 
            $title, 
            $description, 
            $highlight_text, 
            $highlight_color, 
            $image_url, 
            $image_position, 
            $button_text, 
            $button_url, 
            $button_color, 
            $features_json, 
            $display_order, 
            $is_active,
            $section_id,
            $id
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật bài đăng thành công',
                'id' => $id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi cập nhật bài đăng: ' . $stmt->error
            ]);
        }
        
        $stmt->close();
    } else {
        // Create new post
        $stmt = $conn->prepare("INSERT INTO home_posts 
            (title, description, highlight_text, highlight_color, image_url, image_position, 
             button_text, button_url, button_color, features, display_order, is_active, section_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssssssssiis", 
            $title, 
            $description, 
            $highlight_text, 
            $highlight_color, 
            $image_url, 
            $image_position, 
            $button_text, 
            $button_url, 
            $button_color, 
            $features_json, 
            $display_order, 
            $is_active,
            $section_id
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Thêm bài đăng thành công',
                'id' => $conn->insert_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi thêm bài đăng: ' . $stmt->error
            ]);
        }
        
        $stmt->close();
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}

$conn->close();

