<?php
// Đặt timezone cho PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// Get PDO connection
$pdo = db_get_pdo_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = $input['username'] ?? '';
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? null;
    $category = $input['category'] ?? null;
    $priority = $input['priority'] ?? 'medium';
    $price = $input['price'] ?? null;
    $currency = $input['currency'] ?? 'VND';
    $productUrl = $input['product_url'] ?? null;
    $purchaseStatus = $input['purchase_status'] ?? 'researching';
    
    // Handle both old datetime format and new separate date/time
    $scheduledDate = $input['scheduled_date'] ?? null;
    $scheduledTime = $input['scheduled_time'] ?? null;
    $targetDate = $input['target_date'] ?? null;
    
    // Legacy datetime support
    $datetime = $input['datetime'] ?? null;
    if ($datetime && !$scheduledDate && !$targetDate) {
        $dt = new DateTime($datetime);
        $scheduledDate = $dt->format('Y-m-d');
        $scheduledTime = $dt->format('H:i:s');
        $targetDate = $dt->format('Y-m-d'); // Keep backward compatibility
    }
    
    // If scheduled_date is provided, also set target_date for backward compatibility
    if ($scheduledDate && !$targetDate) {
        $targetDate = $scheduledDate;
    }

    if (empty($username) || empty($title)) {
        throw new Exception('Username and title are required');
    }

    // Generate unique item_id (không cần thiết cho database mới nhưng giữ lại cho tương thích)
    $itemId = 'wish_' . uniqid() . '_' . time();
    
    // Database mới dùng username trực tiếp, không cần user_id và các cột phức tạp
    $query = "INSERT INTO wishes 
              (username, title, description, scheduled_date, scheduled_time, completed) 
              VALUES (?, ?, ?, ?, ?, 0)";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        $username, // Dùng username trực tiếp
        $title,
        $description,
        $scheduledDate,  // Đã thiếu cái này!
        $scheduledTime   // Sửa tên biến
    ]);

    if ($result) {
        $wishId = $pdo->lastInsertId();
        
        // Lấy thông tin wish vừa tạo
        $getWishQuery = "SELECT * FROM wishes WHERE id = ?";
        $getWishStmt = $pdo->prepare($getWishQuery);
        $getWishStmt->execute([$wishId]);
        $newWish = $getWishStmt->fetch(PDO::FETCH_ASSOC);

        // Convert timestamps to Vietnam time for display
        if ($newWish['created_at']) {
            $newWish['created_at'] = date('Y-m-d H:i:s', strtotime($newWish['created_at'] . ' +7 hours'));
        }
        if ($newWish['updated_at']) {
            $newWish['updated_at'] = date('Y-m-d H:i:s', strtotime($newWish['updated_at'] . ' +7 hours'));
        }

        echo json_encode([
            'success' => true,
            'message' => 'Wish added successfully',
            'data' => $newWish
        ]);
    } else {
        throw new Exception('Failed to add wish');
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_type' => 'PDO_ERROR',
        'debug' => [
            'input' => $input ?? null,
            'sql_error' => $e->errorInfo ?? null
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_type' => 'GENERAL_ERROR',
        'debug' => [
            'input' => $input ?? null,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
