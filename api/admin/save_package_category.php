<?php
// Add or update package category with image upload

// Increase upload limits for file uploads (2MB)
ini_set('upload_max_filesize', '2M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);

try {
// Start output buffering to prevent any unexpected output
ob_start();

// Start session with proper config
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

// Clean any output from includes
ob_clean();

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

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Get POST data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = $_POST['name'] ?? '';
$badge_text = $_POST['badge_text'] ?? '';
$badge_color = $_POST['badge_color'] ?? 'blue';
$display_order = intval($_POST['display_order'] ?? 0);
$is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
$logo_url = $_POST['logo_url'] ?? ''; // Existing logo URL for edit

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Tên danh mục không được để trống']);
    exit;
}

// Check for duplicate display_order (except current category)
$check_stmt = $conn->prepare("SELECT id, name FROM package_categories WHERE display_order = ? AND id != ?");
$check_stmt->bind_param("ii", $display_order, $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
if ($row = $check_result->fetch_assoc()) {
    echo json_encode([
        'success' => false, 
        'message' => "Thứ tự hiển thị {$display_order} đã được sử dụng bởi danh mục gói khác: \"{$row['name']}\". Vui lòng chọn thứ tự khác."
    ]);
    exit;
}
$check_stmt->close();

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
    $upload_dir = __DIR__ . '/../../assets/img/package-categories/';
    if (!is_dir($upload_dir)) {
        // Use @ to suppress warnings and check result
        if (!@mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Không thể tạo thư mục upload. Vui lòng tạo thư mục assets/img/package-categories/ thủ công và chmod 755 hoặc 777'
            ]);
            exit;
        }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        echo json_encode([
            'success' => false,
            'message' => 'Thư mục upload không có quyền ghi. Vui lòng chmod 755 hoặc 777 cho thư mục assets/img/package-categories/'
        ]);
        exit;
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'package-category_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Delete old logo if exists and is local file
        if ($id > 0 && !empty($logo_url) && strpos($logo_url, '/assets/img/package-categories/') !== false) {
            $old_file = __DIR__ . '/../../' . $logo_url;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        
        $logo_url = '/assets/img/package-categories/' . $filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh']);
        exit;
    }
}

// Ensure logo_url is not null
if (empty($logo_url)) {
    $logo_url = null;
}

// Save to database
if ($id > 0) {
    // Update existing category
    $stmt = $conn->prepare("UPDATE package_categories SET name = ?, logo_url = ?, badge_text = ?, badge_color = ?, display_order = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssssiii", $name, $logo_url, $badge_text, $badge_color, $display_order, $is_active, $id);
} else {
    // Insert new category
    $stmt = $conn->prepare("INSERT INTO package_categories (name, logo_url, badge_text, badge_color, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $name, $logo_url, $badge_text, $badge_color, $display_order, $is_active);
}

try {
    if ($stmt->execute()) {
        $category_id = $id > 0 ? $id : $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Lưu danh mục gói thành công',
            'category_id' => $category_id,
            'logo_url' => $logo_url
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

if (isset($stmt)) $stmt->close();
if (isset($conn)) $conn->close();

} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}