<?php
// Get all survey submissions with results
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

if (!hasPermission($conn, 'survey', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem khảo sát']);
    exit;
}

try {
    // Get all surveys with user info and results
    $sql = "SELECT 
                s.id,
                s.user_id,
                s.full_name,
                s.phone,
                s.region,
                s.phase,
                s.solar_panel_type,
                s.monthly_bill,
                s.usage_time,
                s.created_at,
                u.username,
                u.email,
                r.total_cost,
                r.panels_needed,
                r.total_capacity,
                r.inverter_name,
                r.battery_type,
                r.region_name
            FROM solar_surveys s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN survey_results r ON s.id = r.survey_id
            ORDER BY s.created_at DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Lỗi truy vấn database: ' . $conn->error);
    }
    
    $surveys = [];
    while ($row = $result->fetch_assoc()) {
        // Region names
        $regionNames = [
            'mien-bac' => 'Miền Bắc',
            'mien-trung' => 'Miền Trung',
            'mien-nam' => 'Miền Nam'
        ];
        
        // Usage time names
        $usageNames = [
            'day' => 'Ban ngày',
            'balanced' => 'Cân bằng',
            'night' => 'Ban đêm'
        ];
        
        $surveys[] = [
            'id' => (int)$row['id'],
            'user_id' => (int)$row['user_id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'full_name' => $row['full_name'],
            'phone' => $row['phone'],
            'region' => $row['region'],
            'region_name' => $row['region_name'] ?? $regionNames[$row['region']] ?? $row['region'],
            'phase' => (int)$row['phase'],
            'solar_panel_type' => (int)$row['solar_panel_type'],
            'monthly_bill' => (float)$row['monthly_bill'],
            'usage_time' => $row['usage_time'],
            'usage_time_name' => $usageNames[$row['usage_time']] ?? $row['usage_time'],
            'created_at' => $row['created_at'],
            'has_results' => $row['total_cost'] !== null,
            'total_cost' => $row['total_cost'] ? (float)$row['total_cost'] : null,
            'panels_needed' => $row['panels_needed'] ? (int)$row['panels_needed'] : null,
            'total_capacity' => $row['total_capacity'] ? (float)$row['total_capacity'] : null,
            'inverter_name' => $row['inverter_name'],
            'battery_type' => $row['battery_type']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'surveys' => $surveys,
            'total' => count($surveys)
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Get Survey History error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy lịch sử khảo sát: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

