<?php
// Script đếm số tham số trong bind_param

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Count Parameters</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;} pre{background:#2d2d2d;padding:15px;border-radius:5px;} .highlight{color:#4ec9b0;font-weight:bold;}</style>";
echo "</head><body>";

echo "<h1 style='color:#4ec9b0;'>🔢 Count Bind Parameters</h1>";

// Type string hiện tại
$typeString = "iddsissdddddisdddisddddsisdddidddidddiidddddds";

echo "<h2>Type String Analysis</h2>";
echo "<pre style='color:#dcdcaa;'>$typeString</pre>";
echo "<p>Length: <span class='highlight'>" . strlen($typeString) . "</span> characters</p>";

// Đếm từng loại
$i_count = substr_count($typeString, 'i');
$d_count = substr_count($typeString, 'd');
$s_count = substr_count($typeString, 's');

echo "<h3>Type Breakdown:</h3>";
echo "<pre>";
echo "i (integer):  <span class='highlight'>$i_count</span>\n";
echo "d (double):   <span class='highlight'>$d_count</span>\n";
echo "s (string):   <span class='highlight'>$s_count</span>\n";
echo "─────────────────────\n";
echo "TOTAL:        <span class='highlight'>" . ($i_count + $d_count + $s_count) . "</span>";
echo "</pre>";

// Liệt kê 45 trường cần insert
$fields = [
    'survey_id', 'monthly_kwh', 'sun_hours', 'region_name',
    'panel_id', 'panel_name', 'panel_power', 'panel_price', 'panels_needed', 'panel_cost',
    'energy_per_panel_per_day', 'total_capacity',
    'inverter_id', 'inverter_name', 'inverter_capacity', 'inverter_price',
    'cabinet_id', 'cabinet_name', 'cabinet_capacity', 'cabinet_price',
    'battery_needed', 'battery_type', 'battery_id', 'battery_name', 'battery_capacity',
    'battery_quantity', 'battery_unit_price', 'battery_cost',
    'bach_z_qty', 'bach_z_price', 'bach_z_cost',
    'clip_qty', 'clip_price', 'clip_cost',
    'jack_mc4_qty', 'jack_mc4_price', 'jack_mc4_cost',
    'dc_cable_length', 'dc_cable_price', 'dc_cable_cost',
    'accessories_cost', 'labor_cost',
    'total_cost_without_battery', 'total_cost', 'bill_breakdown'
];

echo "<h2>Expected Fields</h2>";
echo "<p>Number of fields: <span class='highlight'>" . count($fields) . "</span></p>";
echo "<details><summary>Show all fields</summary><pre>";
foreach ($fields as $i => $field) {
    echo ($i + 1) . ". $field\n";
}
echo "</pre></details>";

// So sánh
echo "<hr>";
echo "<h2>Comparison</h2>";
if (strlen($typeString) === count($fields)) {
    echo "<pre style='color:#4ec9b0;'>✅ MATCH: Type string length (" . strlen($typeString) . ") = Number of fields (" . count($fields) . ")</pre>";
} else {
    echo "<pre style='color:#f48771;'>❌ MISMATCH!</pre>";
    echo "<pre style='color:#f48771;'>Type string: " . strlen($typeString) . " characters</pre>";
    echo "<pre style='color:#f48771;'>Fields:      " . count($fields) . " parameters</pre>";
    echo "<pre style='color:#dcdcaa;'>Difference:  " . abs(strlen($typeString) - count($fields)) . "</pre>";
}

// Đề xuất type string đúng
echo "<hr>";
echo "<h2>Suggested Type String (based on database schema)</h2>";
$suggested = "iddsiisdddddisdddisddddsisdddiddddddddiddddddds";
echo "<pre style='color:#4ec9b0;'>$suggested</pre>";
echo "<p>Length: <span class='highlight'>" . strlen($suggested) . "</span></p>";

echo "<h3>Mapping:</h3>";
echo "<pre style='font-size:0.85rem;'>";
$types = [
    'i', 'd', 'd', 's',  // survey_id, monthly_kwh, sun_hours, region_name
    'i', 's', 'd', 'd', 'i', 'd',  // panel_id, panel_name, panel_power, panel_price, panels_needed, panel_cost
    'd', 'd',  // energy_per_panel_per_day, total_capacity
    'i', 's', 'd', 'd',  // inverter_id, inverter_name, inverter_capacity, inverter_price
    'i', 's', 'd', 'd',  // cabinet_id, cabinet_name, cabinet_capacity, cabinet_price
    'd', 's', 'i', 's', 'd',  // battery_needed, battery_type, battery_id, battery_name, battery_capacity
    'i', 'd', 'd',  // battery_quantity, battery_unit_price, battery_cost
    'i', 'd', 'd',  // bach_z_qty, bach_z_price, bach_z_cost
    'd', 'd', 'd',  // clip_qty, clip_price, clip_cost (qty should be int but can use double)
    'd', 'd', 'd',  // jack_mc4_qty, jack_mc4_price, jack_mc4_cost
    'i', 'd', 'd',  // dc_cable_length, dc_cable_price, dc_cable_cost
    'd', 'd',  // accessories_cost, labor_cost
    'd', 'd', 's'  // total_cost_without_battery, total_cost, bill_breakdown
];

foreach ($fields as $i => $field) {
    $type = $types[$i];
    echo str_pad(($i + 1) . ".", 4) . str_pad($type, 3) . " => $field\n";
}
echo "</pre>";

echo "</body></html>";
?>
