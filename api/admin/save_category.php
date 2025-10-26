<?php
// Add or update product category with image upload

// Start session first
session_start();
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

// CORS headers
header('Access-Control-Allow-Origin: https://api.quangphuc.iotsinhvien.io.vn');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

// Debug log
error_log("Save category - Session ID: " . session_id());
error_log("Save category - User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("Save category - is_admin check: " . (is_admin() ? 'TRUE' : 'FALSE'));

if (!is_admin()) {
    echo json_encode([
        'success' => false, 
        'message' => 'Không có quyền truy cập',
        'debug' => [
            'session_id' => session_id(),
            'has_user_id' => isset($_SESSION['user_id']),
            'user_id' => $_SESSION['user_id'] ?? null
        ]
    ]);
    exit;
}

// Get POST data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = $_POST['name'] ?? '';
$display_order = intval($_POST['display_order'] ?? 1);
$is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
$logo_url = $_POST['logo_url'] ?? ''; // Existing logo URL for edit

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Tên danh mục không được để trống']);
    exit;
}

// Handle image upload
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['logo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $max_size = 2 * 1024 * 1024; // 2MB

    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF)']);
        exit;
    }

    // Validate file size
    if ($file['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'Kích thước ảnh không được vượt quá 2MB']);
        exit;
    }

    // Create upload directory if not exists
    $upload_dir = __DIR__ . '/../../assets/img/categories/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'category_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Delete old logo if exists and is local file
        if ($id > 0 && !empty($logo_url) && strpos($logo_url, '/assets/img/categories/') !== false) {
            $old_file = __DIR__ . '/../../' . $logo_url;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        
        $logo_url = '/assets/img/categories/' . $filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh']);
        exit;
    }
} elseif (empty($logo_url) && $id == 0) {
    // New category must have logo
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ảnh logo']);
    exit;
}

// Save to database
if ($id > 0) {
    // Update existing category
    $stmt = $conn->prepare("UPDATE product_categories SET name = ?, logo_url = ?, display_order = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssiii", $name, $logo_url, $display_order, $is_active, $id);
} else {
    // Insert new category
    $stmt = $conn->prepare("INSERT INTO product_categories (name, logo_url, display_order, is_active) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $name, $logo_url, $display_order, $is_active);
}

if ($stmt->execute()) {
    $category_id = $id > 0 ? $id : $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Lưu danh mục thành công',
        'category_id' => $category_id,
        'logo_url' => $logo_url
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();