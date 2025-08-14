<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'connect.php';

try {
    // Lấy tham số từ query string
    $username = $_GET['username'] ?? '';
    $status = $_GET['status'] ?? 'all'; // 'all', 'completed', 'pending'
    $limit = (int)($_GET['limit'] ?? 100);
    $offset = (int)($_GET['offset'] ?? 0);
    $sort = $_GET['sort'] ?? 'created_at'; // 'created_at', 'title', 'price', 'priority'
    $order = strtoupper($_GET['order'] ?? 'DESC'); // 'ASC' hoặc 'DESC'
    
    if (empty($username)) {
        throw new Exception('Username is required');
    }

    // Validate sort column
    $validSortColumns = ['created_at', 'title', 'price', 'priority', 'category'];
    if (!in_array($sort, $validSortColumns)) {
        $sort = 'created_at';
    }

    // Validate order
    if (!in_array($order, ['ASC', 'DESC'])) {
        $order = 'DESC';
    }

    // Xây dựng điều kiện WHERE cho status
    $statusCondition = '';
    $params = [$username];
    
    if ($status === 'completed') {
        $statusCondition = ' AND w.status = 1';
    } elseif ($status === 'pending') {
        $statusCondition = ' AND w.status = 0';
    }

    // Đếm tổng số wishes
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM wishes w
        LEFT JOIN tai_khoan tk ON w.user_id = tk.id
        WHERE tk.user = ? $statusCondition
    ");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy danh sách wishes với phân trang
    $stmt = $pdo->prepare("
        SELECT 
            w.id, w.title, w.description, w.category, w.priority, w.price, 
            w.product_url as store_location, w.product_url as purchase_link, 
            w.status as is_completed, w.created_at, w.updated_at
        FROM wishes w
        LEFT JOIN tai_khoan tk ON w.user_id = tk.id
        WHERE tk.user = ? $statusCondition
        ORDER BY $sort $order
        LIMIT ? OFFSET ?
    ");
    
    $executeParams = array_merge($params, [$limit, $offset]);
    $stmt->execute($executeParams);
    $wishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu
    foreach ($wishes as &$wish) {
        $wish['id'] = (int)$wish['id'];
        $wish['is_completed'] = (bool)$wish['is_completed'];
        $wish['price'] = $wish['price'] ? (float)$wish['price'] : null;
        
        // Format giá tiền
        if ($wish['price']) {
            $wish['price_formatted'] = number_format($wish['price'], 0, ',', '.') . ' VND';
        } else {
            $wish['price_formatted'] = 'Chưa có giá';
        }
        
        $wish['created_at_formatted'] = date('d/m/Y H:i', strtotime($wish['created_at']));
        
        if ($wish['updated_at']) {
            $wish['updated_at_formatted'] = date('d/m/Y H:i', strtotime($wish['updated_at']));
        }

        // Thêm status text
        $wish['status_text'] = $wish['is_completed'] ? 'Đã mua' : 'Chưa mua';
    }

    // Tính tổng giá tiền
    $totalPrice = 0;
    $completedPrice = 0;
    $pendingPrice = 0;
    
    foreach ($wishes as $wish) {
        if ($wish['price']) {
            $totalPrice += $wish['price'];
            if ($wish['is_completed']) {
                $completedPrice += $wish['price'];
            } else {
                $pendingPrice += $wish['price'];
            }
        }
    }

    // Thống kê nhanh
    $stats = [
        'total' => $totalCount,
        'completed' => 0,
        'pending' => 0,
        'total_price' => $totalPrice,
        'completed_price' => $completedPrice,
        'pending_price' => $pendingPrice,
        'total_price_formatted' => number_format($totalPrice, 0, ',', '.') . ' VND',
        'completed_price_formatted' => number_format($completedPrice, 0, ',', '.') . ' VND',
        'pending_price_formatted' => number_format($pendingPrice, 0, ',', '.') . ' VND'
    ];

    foreach ($wishes as $wish) {
        if ($wish['is_completed']) {
            $stats['completed']++;
        } else {
            $stats['pending']++;
        }
    }

    // Lấy thêm thống kê từ toàn bộ database (không chỉ trang hiện tại)
    $fullStatsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_count,
            SUM(CASE WHEN w.status = 1 THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN w.status = 0 THEN 1 ELSE 0 END) as pending_count,
            COALESCE(SUM(w.price), 0) as total_amount,
            COALESCE(SUM(CASE WHEN w.status = 1 THEN w.price ELSE 0 END), 0) as completed_amount,
            COALESCE(SUM(CASE WHEN w.status = 0 THEN w.price ELSE 0 END), 0) as pending_amount
        FROM wishes w
        LEFT JOIN tai_khoan tk ON w.user_id = tk.id
        WHERE tk.user = ? $statusCondition
    ");
    $fullStatsStmt->execute($params);
    $fullStats = $fullStatsStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $wishes,
        'stats' => $stats,
        'full_stats' => [
            'total' => (int)$fullStats['total_count'],
            'completed' => (int)$fullStats['completed_count'],
            'pending' => (int)$fullStats['pending_count'],
            'total_price' => (float)$fullStats['total_amount'],
            'completed_price' => (float)$fullStats['completed_amount'],
            'pending_price' => (float)$fullStats['pending_amount'],
            'total_price_formatted' => number_format($fullStats['total_amount'], 0, ',', '.') . ' VND',
            'completed_price_formatted' => number_format($fullStats['completed_amount'], 0, ',', '.') . ' VND',
            'pending_price_formatted' => number_format($fullStats['pending_amount'], 0, ',', '.') . ' VND'
        ],
        'pagination' => [
            'total' => $totalCount,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $totalCount
        ],
        'message' => 'Wishes loaded successfully'
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
