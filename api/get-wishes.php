<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include_once 'connect.php';

try {
    // Lấy tham số từ URL
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    // Xây dựng query với tất cả các trường mới
    $sql = "SELECT id, item_id, title, description, category, priority, price, currency, 
                   product_url, purchase_status, target_date, is_completed, 
                   created_at, updated_at 
            FROM wishes";
    
    $params = [];
    
    if ($status !== 'all') {
        $sql .= " WHERE is_completed = ?";
        $params[] = ($status === 'completed') ? 1 : 0;
    }
    
    $sql .= " ORDER BY target_date ASC, created_at DESC";
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $wishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Đếm tổng số wishes
    $countSql = "SELECT COUNT(*) as total FROM wishes";
    if ($status !== 'all') {
        $countSql .= " WHERE is_completed = " . (($status === 'completed') ? 1 : 0);
    }
    
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Format dữ liệu
    foreach ($wishes as &$wish) {
        $wish['is_completed'] = (bool)$wish['is_completed'];
        $wish['price'] = $wish['price'] ? intval($wish['price']) : null;
        $wish['target_date'] = $wish['target_date'] ? date('d/m/Y', strtotime($wish['target_date'])) : null;
        $wish['created_at'] = date('d/m/Y H:i', strtotime($wish['created_at']));
        $wish['updated_at'] = $wish['updated_at'] ? date('d/m/Y H:i', strtotime($wish['updated_at'])) : null;
        
        // Format priority icon
        $priorityIcons = [
            'low' => '🟢',
            'medium' => '🟡', 
            'high' => '🔴'
        ];
        $wish['priority_icon'] = $priorityIcons[$wish['priority']] ?? '🟡';
        
        // Format purchase status icon
        $statusIcons = [
            'researching' => '🔍',
            'saving' => '💰',
            'ready_to_buy' => '✅'
        ];
        $wish['purchase_status_icon'] = $statusIcons[$wish['purchase_status']] ?? '🔍';
        
        // Format price with currency
        if ($wish['price']) {
            $currencySymbols = [
                'VND' => 'đ',
                'USD' => '$',
                'EUR' => '€'
            ];
            $symbol = $currencySymbols[$wish['currency']] ?? 'đ';
            $wish['formatted_price'] = number_format($wish['price']) . ' ' . $symbol;
        } else {
            $wish['formatted_price'] = null;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $wishes,
        'total' => $totalCount,
        'has_more' => ($offset + $limit) < $totalCount
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy danh sách mong muốn: ' . $e->getMessage()
    ]);
}
?>
