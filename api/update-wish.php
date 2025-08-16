<?php
// Đặt timezone cho PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// Get PDO connection
$pdo = db_get_pdo_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only PUT/POST method allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $wishId = $input['id'] ?? '';
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? null;
    
    // Lấy scheduled_date và scheduled_time từ input
    $scheduledDate = $input['scheduled_date'] ?? null;
    $scheduledTime = $input['scheduled_time'] ?? null;
    
    // Các field khác (để tương thích với frontend nhưng không dùng trong database)
    $category = $input['category'] ?? null;
    $priority = $input['priority'] ?? 'medium';
    $price = $input['price'] ?? null;
    $currency = $input['currency'] ?? 'VND';
    $productUrl = $input['product_url'] ?? null;
    $purchaseStatus = $input['purchase_status'] ?? 'researching';
    
    // Handle both old datetime format and new target_date
    $targetDate = $input['target_date'] ?? null;
    
    // Fallback: nếu không có scheduled_date nhưng có target_date, sử dụng target_date
    if (!$scheduledDate && $targetDate) {
        $scheduledDate = $targetDate;
    }
    
    // Legacy datetime support
    $datetime = $input['datetime'] ?? null;
    if ($datetime && !$scheduledDate) {
        $dt = new DateTime($datetime);
        $scheduledDate = $dt->format('Y-m-d');
        $scheduledTime = $dt->format('H:i:s');
    }

    if (empty($wishId) || empty($title)) {
        throw new Exception('Wish ID and title are required');
    }

    // Check if wish exists
    $checkQuery = "SELECT id FROM wishes WHERE id = ?";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$wishId]);
    
    if (!$checkStmt->fetch()) {
        throw new Exception('Wish not found');
    }

    // Update wish
    $query = "UPDATE wishes 
              SET title = ?, description = ?, 
                  scheduled_date = ?, scheduled_time = ?, updated_at = NOW()
              WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        $title,
        $description,
        $scheduledDate,
        $scheduledTime,
        $wishId
    ]);

    if ($result) {
        // Lấy thông tin wish sau khi update
        $getWishQuery = "SELECT * FROM wishes WHERE id = ?";
        $getWishStmt = $pdo->prepare($getWishQuery);
        $getWishStmt->execute([$wishId]);
        $updatedWish = $getWishStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Wish updated successfully',
            'data' => $updatedWish
        ]);
    } else {
        throw new Exception('Failed to update wish');
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
