<?php
// Script để verify type mapping trong bind_param

echo "<h2>📋 Verify Type Mapping for save_survey.php</h2>";
echo "<pre>";

$columns = [
    ['survey_id', 'INT', 'i'],
    ['monthly_kwh', 'DOUBLE', 'd'],
    ['sun_hours', 'DOUBLE', 'd'],
    ['panels_needed', 'DOUBLE', 'd'],
    ['panel_cost', 'DOUBLE', 'd'],
    ['inverter_id', 'INT', 'i'],
    ['inverter_name', 'VARCHAR', 's'],
    ['inverter_price', 'DOUBLE', 'd'],
    ['cabinet_id', 'INT', 'i'],
    ['cabinet_name', 'VARCHAR', 's'],
    ['cabinet_price', 'DOUBLE', 'd'],
    ['battery_needed', 'DOUBLE', 'd'],
    ['battery_type', 'VARCHAR', 's'],
    ['battery_quantity', 'INT', 'i'],
    ['battery_cost', 'DOUBLE', 'd'],
    ['accessories_cost', 'DOUBLE', 'd'],
    ['labor_cost', 'DOUBLE', 'd'],
    ['dc_cable_cost', 'DOUBLE', 'd'],
    ['total_cost', 'DOUBLE', 'd']
];

$type_string = '';
foreach ($columns as $col) {
    $type_string .= $col[2];
}

echo "Expected type string: " . $type_string . "\n";
echo "Length: " . strlen($type_string) . " characters\n\n";

echo "Column mapping:\n";
echo str_pad("Position", 10) . str_pad("Column", 25) . str_pad("SQL Type", 15) . "Bind Type\n";
echo str_repeat("-", 70) . "\n";

foreach ($columns as $index => $col) {
    $pos = $index + 1;
    echo str_pad($pos, 10) . str_pad($col[0], 25) . str_pad($col[1], 15) . $col[2] . "\n";
}

echo "\n✅ Correct type string: \"" . $type_string . "\"\n";
echo "   Total parameters: " . count($columns) . "\n";

// Check against actual file
$file_content = file_get_contents(__DIR__ . '/save_survey.php');
if (preg_match('/\$stmt2->bind_param\(\s*"([^"]+)"/', $file_content, $matches)) {
    $actual_type_string = $matches[1];
    echo "\n📄 Current in save_survey.php: \"" . $actual_type_string . "\"\n";
    echo "   Length: " . strlen($actual_type_string) . " characters\n";
    
    if ($actual_type_string === $type_string) {
        echo "\n✅ ✅ ✅ TYPE STRING IS CORRECT! ✅ ✅ ✅\n";
    } else {
        echo "\n❌ ❌ ❌ TYPE STRING MISMATCH! ❌ ❌ ❌\n";
        echo "\nExpected: " . $type_string . "\n";
        echo "Actual:   " . $actual_type_string . "\n";
        
        // Show differences
        $len = max(strlen($type_string), strlen($actual_type_string));
        echo "\nDifferences:\n";
        for ($i = 0; $i < $len; $i++) {
            $exp = isset($type_string[$i]) ? $type_string[$i] : ' ';
            $act = isset($actual_type_string[$i]) ? $actual_type_string[$i] : ' ';
            if ($exp !== $act) {
                echo "Position " . ($i + 1) . ": Expected '$exp', Got '$act'\n";
            }
        }
    }
}

echo "</pre>";
?>
