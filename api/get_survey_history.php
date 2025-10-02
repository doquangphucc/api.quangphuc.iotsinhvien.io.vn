<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';
require_once 'session.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];

    // Lấy danh sách khảo sát của user
    $stmt = $conn->prepare("
        SELECT 
            s.id,
            s.region,
            s.phase,
            s.solar_panel_type,
            s.monthly_bill,
            s.usage_time,
            s.created_at,
            r.monthly_kwh,
            r.panels_needed,
            r.total_cost,
            r.inverter_name,
            r.battery_type,
            r.battery_quantity
        FROM solar_surveys s
        LEFT JOIN survey_results r ON s.id = r.survey_id
        WHERE s.user_id = ?
        ORDER BY s.created_at DESC
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $surveys = [];
    while ($row = $result->fetch_assoc()) {
        $surveys[] = [
            'id' => $row['id'],
            'region' => $row['region'],
            'phase' => $row['phase'],
            'solarPanelType' => $row['solar_panel_type'],
            'monthlyBill' => $row['monthly_bill'],
            'usageTime' => $row['usage_time'],
            'createdAt' => $row['created_at'],
            'results' => $row['monthly_kwh'] ? [
                'monthlyKWh' => $row['monthly_kwh'],
                'panelsNeeded' => $row['panels_needed'],
                'totalCost' => $row['total_cost'],
                'inverterName' => $row['inverter_name'],
                'batteryType' => $row['battery_type'],
                'batteryQuantity' => $row['battery_quantity']
            ] : null
        ];
    }

    echo json_encode([
        'success' => true,
        'surveys' => $surveys
    ]);

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>
