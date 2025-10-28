<?php
// Add or update product category with image upload

// Disable PHP errors/warnings display to prevent breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Increase upload limits for file uploads (up to 5MB)
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);

// Start session with proper config
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

// Handle CORS properly for same-origin with credentials
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'https://api.quangphuc.iotsinhvien.io.vn' || empty($origin)) {
    header('Access-Control-Allow-Origin: https://api.quangphuc.iotsinhvien.io.vn');
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

// Check permission - edit for existing category, create for new
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$required_action = $id > 0 ? 'edit' : 'create';

if (!hasPermission($conn, 'categories', $required_action)) {
    echo json_encode([
        'success' => false, 
        'message' => "Bạn không có quyền {$required_action} danh mục"
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

// Check for duplicate display_order (except current category)
$check_stmt = $conn->prepare("SELECT id, name FROM product_categories WHERE display_order = ? AND id != ?");
$check_stmt->bind_param("ii", $display_order, $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
if ($row = $check_result->fetch_assoc()) {
    echo json_encode([
        'success' => false, 
        'message' => "Thứ tự hiển thị {$display_order} đã được sử dụng bởi danh mục khác: \"{$row['name']}\". Vui lòng chọn thứ tự khác."
    ]);
    exit;
}
$check_stmt->close();

// Handle image upload
if (isset($_FILES['logo'])) {
    $file = $_FILES['logo'];
    
    // Check for upload errors FIRST
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File vượt quá upload_max_filesize (hiện tại: ' . ini_get('upload_max_filesize') . ')',
            UPLOAD_ERR_FORM_SIZE => 'File vượt quá MAX_FILE_SIZE trong form',
            UPLOAD_ERR_PARTIAL => 'File chỉ upload được một phần',
            UPLOAD_ERR_NO_FILE => 'Không có file được upload',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file ra đĩa',
            UPLOAD_ERR_EXTENSION => 'Extension PHP đã chặn upload'
        ];
        
        $error_msg = $error_messages[$file['error']] ?? 'Lỗi upload không xác định (code: ' . $file['error'] . ')';
        
        echo json_encode([
            'success' => false, 
            'message' => $error_msg,
            'debug' => [
                'error_code' => $file['error'],
                'file_size' => $file['size'],
                'file_size_mb' => round($file['size'] / (1024 * 1024), 2),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            ]
        ]);
        exit;
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $max_size = 3 * 1024 * 1024; // 3MB - increased

    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF)']);
        exit;
    }

    // Validate file size
    if ($file['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'Kích thước ảnh không được vượt quá 3MB']);
        exit;
    }

    // Create upload directory if not exists
    $upload_dir = __DIR__ . '/../../assets/img/categories/';
    if (!is_dir($upload_dir)) {
        // Use @ to suppress warnings and check result
        if (!@mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Không thể tạo thư mục upload. Vui lòng tạo thư mục assets/img/categories/ thủ công và chmod 755 hoặc 777'
            ]);
            exit;
        }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        echo json_encode([
            'success' => false,
            'message' => 'Thư mục upload không có quyền ghi. Vui lòng chmod 755 hoặc 777 cho thư mục assets/img/categories/'
        ]);
        exit;
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