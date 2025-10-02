<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

require_once 'session.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];

    // Lấy danh sách khảo sát của user với đầy đủ thông tin
    $stmt = $conn->prepare("
        SELECT 
            s.id,
            s.full_name,
            s.phone,
            s.region,
            s.phase,
            s.solar_panel_type,
            s.monthly_bill,
            s.usage_time,
            s.created_at,
            r.monthly_kwh,
            r.sun_hours,
            r.panels_needed,
            r.panel_cost,
            r.inverter_id,
            r.inverter_name,
            r.inverter_price,
            r.cabinet_id,
            r.cabinet_name,
            r.cabinet_price,
            r.battery_needed,
            r.battery_type,
            r.battery_quantity,
            r.battery_cost,
            r.accessories_cost,
            r.labor_cost,
            r.dc_cable_cost,
            r.total_cost
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
            'fullName' => $row['full_name'],
            'phone' => $row['phone'],
            'region' => $row['region'],
            'phase' => $row['phase'],
            'solarPanelType' => $row['solar_panel_type'],
            'monthlyBill' => $row['monthly_bill'],
            'usageTime' => $row['usage_time'],
            'createdAt' => $row['created_at'],
            'results' => $row['monthly_kwh'] ? [
                'monthlyKWh' => $row['monthly_kwh'],
                'sunHours' => $row['sun_hours'],
                'panelsNeeded' => $row['panels_needed'],
                'panelCost' => $row['panel_cost'],
                'inverterId' => $row['inverter_id'],
                'inverterName' => $row['inverter_name'],
                'inverterPrice' => $row['inverter_price'],
                'cabinetId' => $row['cabinet_id'],
                'cabinetName' => $row['cabinet_name'],
                'cabinetPrice' => $row['cabinet_price'],
                'batteryNeeded' => $row['battery_needed'],
                'batteryType' => $row['battery_type'],
                'batteryQuantity' => $row['battery_quantity'],
                'batteryCost' => $row['battery_cost'],
                'accessoriesCost' => $row['accessories_cost'],
                'laborCost' => $row['labor_cost'],
                'dcCableCost' => $row['dc_cable_cost'],
                'totalCost' => $row['total_cost']
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
