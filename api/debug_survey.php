<?php
// Debug file - Hiển thị lỗi chi tiết
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug Survey Data</title>";
echo "<style>body{font-family:monospace;background:#1e1e1e;color:#d4d4d4;padding:20px;} pre{background:#2d2d2d;padding:15px;border-radius:5px;overflow:auto;} .error{color:#f48771;} .success{color:#4ec9b0;}</style>";
echo "</head><body>";

echo "<h1 style='color:#4ec9b0;'>🔍 Debug Survey Save</h1>";

// Giả lập dữ liệu từ frontend
$sampleData = [
    'region' => 'mien-nam',
    'phase' => 1,
    'solarPanel' => '590',
    'monthlyBill' => 1500000,
    'usageTime' => 'balanced',
    'selectedBattery' => '8cell',
    'results' => [
        'monthlyKWh' => 500,
        'billBreakdown' => [
            ['tier' => 1, 'kwhUsed' => 50, 'price' => 1984, 'cost' => 99200],
            ['tier' => 2, 'kwhUsed' => 50, 'price' => 2050, 'cost' => 102500]
        ],
        'sunHours' => 4,
        'regionName' => 'Miền Nam',
        'panelInfo' => [
            'id' => 1,
            'name' => 'Tấm Pin Jinko Solar 590W',
            'power' => 0.59,
            'price' => 1800000,
            'image' => '../assets/img/products/tam-pin-jinko-solar-590w-tiger-neo.jpg'
        ],
        'energyPerPanelPerDay' => 2.36,
        'panelsNeeded' => 7,
        'totalCapacity' => 4.13,
        'panelCost' => 12600000,
        'inverter' => [
            'id' => 3,
            'name' => 'ECO Hybrid 5kW',
            'capacity' => 5,
            'price' => 13850000,
            'image' => '../assets/img/products/eco-hybrid-5kw-sna5000wpv.png'
        ],
        'cabinet' => [
            'id' => 19,
            'name' => 'Tủ điện Hybrid 1 pha 6kW',
            'capacity' => 6,
            'price' => 1850000,
            'image' => '../assets/img/products/electrical-cabinet.jpg'
        ],
        'batteryNeeded' => 8.26,
        'batteryOptions' => [
            [
                'id' => 18,
                'name' => 'Cell BYD 173ah LiFePO4',
                'capacity' => 8.8,
                'price' => 14500000,
                'image' => '../assets/img/products/cell-byd-173ah-lifepo4.png',
                'quantity' => 1,
                'totalCost' => 14500000
            ],
            [
                'id' => 17,
                'name' => 'Cell A-Cornex LiFePO4 16 Cell',
                'capacity' => 16,
                'price' => 25500000,
                'image' => '../assets/img/products/cell-a-cornex-lifepo4-16cell.png',
                'quantity' => 1,
                'totalCost' => 25500000
            ]
        ],
        'accessories' => [
            'bachZ' => ['qty' => 42, 'price' => 20000, 'cost' => 840000, 'name' => 'Bach Z', 'image' => '../assets/img/products/bachz.png'],
            'clip' => ['qty' => 42, 'price' => 15000, 'cost' => 630000, 'name' => 'Kẹp biên', 'image' => '../assets/img/products/kepbien-tamgiua.png'],
            'jackMC4' => ['qty' => 9, 'price' => 30000, 'cost' => 270000, 'name' => 'Jack MC4', 'image' => '../assets/img/products/jackcam.png']
        ],
        'dcCable' => ['length' => 100, 'price' => 20000, 'cost' => 2000000, 'name' => 'Dây DC', 'image' => '../assets/img/products/daydien.png'],
        'accessoriesCost' => 3740000,
        'laborCost' => 6000000,
        'totalCost' => 52540000
    ]
];

echo "<h2>1️⃣ Sample Data Structure</h2>";
echo "<pre style='color:#dcdcaa;'>" . json_encode($sampleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

// Test database connection
echo "<h2>2️⃣ Database Connection</h2>";
try {
    require_once 'db_mysqli.php';
    echo "<pre class='success'>✅ Connected</pre>";
} catch (Exception $e) {
    echo "<pre class='error'>❌ Failed: " . $e->getMessage() . "</pre>";
    exit();
}

// Test session
echo "<h2>3️⃣ Session Check</h2>";
try {
    require_once 'session.php';
    if (isset($_SESSION['user_id'])) {
        echo "<pre class='success'>✅ Logged in as user_id: " . $_SESSION['user_id'] . "</pre>";
    } else {
        echo "<pre class='error'>❌ Not logged in</pre>";
        echo "<p style='color:#dcdcaa;'>Note: Trong debug này, chúng ta sẽ giả lập user_id = 1</p>";
        $_SESSION['user_id'] = 1;
        $_SESSION['full_name'] = 'Test User';
        $_SESSION['phone'] = '0123456789';
    }
} catch (Exception $e) {
    echo "<pre class='error'>❌ Session error: " . $e->getMessage() . "</pre>";
}

// Test INSERT preparation
echo "<h2>4️⃣ Test INSERT Preparation</h2>";
$results = $sampleData['results'];
$battery_type = $sampleData['selectedBattery'];
$battery_index = ($battery_type === '8cell') ? 0 : 1;
$selected_battery = $results['batteryOptions'][$battery_index];

$region_names = [
    'mien-bac' => 'Miền Bắc',
    'mien-trung' => 'Miền Trung',
    'mien-nam' => 'Miền Nam'
];
$region_name = $region_names[$sampleData['region']] ?? 'Không xác định';
$panel_info = $results['panelInfo'];
$total_cost_without_battery = $results['totalCost'] - $selected_battery['totalCost'];
$bill_breakdown_json = json_encode($results['billBreakdown']);

// Count parameters
$params = [
    1,  // survey_id
    $results['monthlyKWh'],
    $results['sunHours'],
    $region_name,
    $panel_info['id'],
    $panel_info['name'],
    $panel_info['power'],
    $panel_info['price'],
    $results['panelsNeeded'],
    $results['panelCost'],
    $results['energyPerPanelPerDay'],
    $results['totalCapacity'],
    $results['inverter']['id'],
    $results['inverter']['name'],
    $results['inverter']['capacity'],
    $results['inverter']['price'],
    $results['cabinet']['id'],
    $results['cabinet']['name'],
    $results['cabinet']['capacity'],
    $results['cabinet']['price'],
    $results['batteryNeeded'],
    $battery_type,
    $selected_battery['id'],
    $selected_battery['name'],
    $selected_battery['capacity'],
    $selected_battery['quantity'],
    $selected_battery['price'],
    $selected_battery['totalCost'],
    $results['accessories']['bachZ']['qty'],
    $results['accessories']['bachZ']['price'],
    $results['accessories']['bachZ']['cost'],
    $results['accessories']['clip']['qty'],
    $results['accessories']['clip']['price'],
    $results['accessories']['clip']['cost'],
    $results['accessories']['jackMC4']['qty'],
    $results['accessories']['jackMC4']['price'],
    $results['accessories']['jackMC4']['cost'],
    $results['dcCable']['length'],
    $results['dcCable']['price'],
    $results['dcCable']['cost'],
    $results['accessoriesCost'],
    $results['laborCost'],
    $total_cost_without_battery,
    $results['totalCost'],
    $bill_breakdown_json
];

echo "<pre class='success'>✅ Prepared " . count($params) . " parameters</pre>";

$typeString = "iddsiisdddddisdddisddddsisdddiddddddddiddddddds";
echo "<pre>Type string length: <strong style='color:#4ec9b0;'>" . strlen($typeString) . "</strong></pre>";

if (count($params) === strlen($typeString)) {
    echo "<pre class='success'>✅ MATCH: Parameters (" . count($params) . ") = Type string (" . strlen($typeString) . ")</pre>";
} else {
    echo "<pre class='error'>❌ MISMATCH: Parameters (" . count($params) . ") ≠ Type string (" . strlen($typeString) . ")</pre>";
}

// Show all parameters with types
echo "<details><summary style='color:#4ec9b0;cursor:pointer;'>Show all parameters</summary><pre>";
foreach ($params as $i => $param) {
    $type = substr($typeString, $i, 1);
    $value = is_string($param) ? (strlen($param) > 50 ? substr($param, 0, 50) . '...' : $param) : $param;
    echo str_pad(($i + 1) . ".", 4) . str_pad($type, 3) . " => " . gettype($param) . " => " . var_export($value, true) . "\n";
}
echo "</pre></details>";

echo "<hr>";
echo "<h2 style='color:#4ec9b0;'>✅ All checks passed!</h2>";
echo "<p>Nếu tất cả OK ở đây nhưng vẫn lỗi khi save thực tế, vấn đề có thể là:</p>";
echo "<ul style='color:#dcdcaa;'>";
echo "<li>Frontend không gửi đủ dữ liệu (thiếu trường trong results object)</li>";
echo "<li>Server chưa pull code mới nhất</li>";
echo "<li>PHP version hoặc mysqli extension có vấn đề</li>";
echo "</ul>";

$conn->close();
echo "</body></html>";
?>
