<?php
// File test để debug lỗi save_survey
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Test Save Survey</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;} pre{background:#2d2d2d;padding:15px;border-radius:5px;overflow:auto;} .success{color:#4ec9b0;} .error{color:#f48771;} .warning{color:#dcdcaa;}</style>";
echo "</head><body>";

echo "<h1 style='color:#4ec9b0;'>🔍 Test Save Survey API</h1>";

// 1. Kiểm tra kết nối database
echo "<h2>1️⃣ Database Connection</h2>";
try {
    require_once 'db_mysqli.php';
    echo "<pre class='success'>✅ Connected to database successfully</pre>";
    echo "<pre>Database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "</pre>";
} catch (Exception $e) {
    echo "<pre class='error'>❌ Connection failed: " . $e->getMessage() . "</pre>";
    exit();
}

// 2. Kiểm tra bảng survey_results tồn tại
echo "<h2>2️⃣ Check survey_results Table</h2>";
$result = $conn->query("SHOW TABLES LIKE 'survey_results'");
if ($result && $result->num_rows > 0) {
    echo "<pre class='success'>✅ Table 'survey_results' exists</pre>";
    
    // Đếm số cột
    $cols = $conn->query("DESCRIBE survey_results");
    $colCount = $cols->num_rows;
    echo "<pre>📊 Number of columns: <strong style='color:#dcdcaa;'>{$colCount}</strong></pre>";
    
    // Hiển thị cấu trúc
    echo "<details><summary style='cursor:pointer;color:#4ec9b0;'>📋 Show table structure</summary><pre>";
    $cols = $conn->query("DESCRIBE survey_results");
    $colNames = [];
    while ($col = $cols->fetch_assoc()) {
        $colNames[] = $col['Field'];
        echo str_pad($col['Field'], 30) . " | " . str_pad($col['Type'], 20) . " | " . $col['Null'] . " | " . $col['Key'] . "\n";
    }
    echo "</pre></details>";
    
    // Kiểm tra các cột quan trọng
    echo "<h3>🔍 Check Required Columns</h3>";
    $requiredCols = [
        'region_name', 'panel_id', 'panel_name', 'panel_power', 'panel_price',
        'energy_per_panel_per_day', 'total_capacity', 'inverter_capacity',
        'cabinet_capacity', 'battery_id', 'battery_name', 'battery_capacity',
        'battery_unit_price', 'bach_z_qty', 'bach_z_price', 'bach_z_cost',
        'clip_qty', 'clip_price', 'clip_cost', 'jack_mc4_qty', 'jack_mc4_price',
        'jack_mc4_cost', 'dc_cable_length', 'dc_cable_price', 'dc_cable_cost',
        'bill_breakdown', 'total_cost_without_battery'
    ];
    
    $missingCols = array_diff($requiredCols, $colNames);
    
    if (empty($missingCols)) {
        echo "<pre class='success'>✅ All required columns exist (" . count($requiredCols) . " columns)</pre>";
    } else {
        echo "<pre class='error'>❌ Missing columns:</pre>";
        echo "<pre class='error'>" . implode("\n", $missingCols) . "</pre>";
        echo "<hr>";
        echo "<h2 style='color:#f48771;'>⚠️ ACTION REQUIRED</h2>";
        echo "<p style='color:#dcdcaa;'>Bạn cần cập nhật bảng survey_results với schema mới!</p>";
        echo "<p>Có 2 cách:</p>";
        echo "<ol>";
        echo "<li><strong>Xóa bảng cũ và import lại:</strong><br>";
        echo "<code style='background:#2d2d2d;padding:5px;'>DROP TABLE IF EXISTS survey_results;</code><br>";
        echo "Sau đó import file <code>database/survey_tables.sql</code> vào phpMyAdmin</li>";
        echo "<li><strong>Hoặc chạy ALTER TABLE:</strong> (sẽ mất thời gian hơn)</li>";
        echo "</ol>";
        exit();
    }
    
} else {
    echo "<pre class='error'>❌ Table 'survey_results' NOT found!</pre>";
    echo "<p style='color:#dcdcaa;'>Bạn cần tạo bảng bằng cách import file <code>database/survey_tables.sql</code></p>";
    exit();
}

// 3. Test INSERT statement
echo "<h2>3️⃣ Test INSERT Statement Preparation</h2>";
try {
    $stmt = $conn->prepare("
        INSERT INTO survey_results 
        (survey_id, monthly_kwh, sun_hours, region_name,
         panel_id, panel_name, panel_power, panel_price, panels_needed, panel_cost, 
         energy_per_panel_per_day, total_capacity,
         inverter_id, inverter_name, inverter_capacity, inverter_price,
         cabinet_id, cabinet_name, cabinet_capacity, cabinet_price,
         battery_needed, battery_type, battery_id, battery_name, battery_capacity, 
         battery_quantity, battery_unit_price, battery_cost,
         bach_z_qty, bach_z_price, bach_z_cost,
         clip_qty, clip_price, clip_cost,
         jack_mc4_qty, jack_mc4_price, jack_mc4_cost,
         dc_cable_length, dc_cable_price, dc_cable_cost,
         accessories_cost, labor_cost, 
         total_cost_without_battery, total_cost, bill_breakdown)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt) {
        echo "<pre class='success'>✅ INSERT statement prepared successfully</pre>";
        echo "<pre>📊 Number of parameters: <strong style='color:#dcdcaa;'>45</strong></pre>";
        $stmt->close();
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo "<pre class='error'>❌ Failed to prepare INSERT: " . $e->getMessage() . "</pre>";
    exit();
}

// 4. Tóm tắt
echo "<hr>";
echo "<h2 style='color:#4ec9b0;'>✅ Summary</h2>";
echo "<pre class='success'>";
echo "✅ Database connected\n";
echo "✅ Table 'survey_results' exists with " . $colCount . " columns\n";
echo "✅ All required columns present\n";
echo "✅ INSERT statement valid\n";
echo "</pre>";
echo "<p style='color:#dcdcaa;'>👉 Bạn có thể thử lưu khảo sát bây giờ!</p>";
echo "<p><a href='../html/khao-sat-dien-mat-troi.html' style='color:#4ec9b0;'>← Back to Survey Page</a></p>";

$conn->close();
echo "</body></html>";
?>
