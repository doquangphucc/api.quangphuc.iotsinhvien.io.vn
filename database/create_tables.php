<?php
// Script đơn giản để tạo 2 bảng survey
require_once '../api/connect.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Create Survey Tables</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";
echo "</head><body>";
echo "<h1>🛠️ Create Survey Tables</h1>";

// SQL để tạo bảng solar_surveys
$sql1 = "CREATE TABLE IF NOT EXISTS `solar_surveys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `region` varchar(50) NOT NULL,
  `phase` int(11) NOT NULL,
  `solar_panel_type` int(11) NOT NULL,
  `monthly_bill` int(11) NOT NULL,
  `usage_time` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `solar_surveys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// SQL để tạo bảng survey_results
$sql2 = "CREATE TABLE IF NOT EXISTS `survey_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_id` int(11) NOT NULL,
  `monthly_kwh` decimal(10,2) DEFAULT NULL,
  `sun_hours` decimal(5,2) DEFAULT NULL,
  `panels_needed` int(11) DEFAULT NULL,
  `panel_cost` decimal(15,2) DEFAULT NULL,
  `inverter_id` int(11) DEFAULT NULL,
  `inverter_name` varchar(255) DEFAULT NULL,
  `inverter_price` decimal(15,2) DEFAULT NULL,
  `cabinet_id` int(11) DEFAULT NULL,
  `cabinet_name` varchar(255) DEFAULT NULL,
  `cabinet_price` decimal(15,2) DEFAULT NULL,
  `battery_needed` tinyint(1) DEFAULT NULL,
  `battery_type` varchar(50) DEFAULT NULL,
  `battery_quantity` int(11) DEFAULT NULL,
  `battery_cost` decimal(15,2) DEFAULT NULL,
  `accessories_cost` decimal(15,2) DEFAULT NULL,
  `labor_cost` decimal(15,2) DEFAULT NULL,
  `dc_cable_cost` decimal(15,2) DEFAULT NULL,
  `total_cost` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `survey_id` (`survey_id`),
  CONSTRAINT `survey_results_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `solar_surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

echo "<h2>📋 Step 1: Create solar_surveys table</h2>";
echo "<pre>$sql1</pre>";
if ($conn->query($sql1) === TRUE) {
    echo "<p class='success'>✅ Table 'solar_surveys' created successfully (or already exists)</p>";
} else {
    echo "<p class='error'>❌ Error creating solar_surveys: " . htmlspecialchars($conn->error) . "</p>";
}

echo "<hr>";

echo "<h2>📋 Step 2: Create survey_results table</h2>";
echo "<pre>$sql2</pre>";
if ($conn->query($sql2) === TRUE) {
    echo "<p class='success'>✅ Table 'survey_results' created successfully (or already exists)</p>";
} else {
    echo "<p class='error'>❌ Error creating survey_results: " . htmlspecialchars($conn->error) . "</p>";
}

echo "<hr>";

// Kiểm tra bảng
echo "<h2>🔍 Verify Tables</h2>";
$tables = ['solar_surveys', 'survey_results'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p class='success'>✅ Table '$table' exists</p>";
        
        // Đếm số bản ghi
        $count = $conn->query("SELECT COUNT(*) as cnt FROM $table")->fetch_assoc()['cnt'];
        echo "<p style='margin-left:20px;'>📊 Records: $count</p>";
        
        // Show structure
        echo "<details style='margin-left:20px;'><summary>Show structure</summary><pre>";
        $cols = $conn->query("DESCRIBE $table");
        while ($col = $cols->fetch_assoc()) {
            echo $col['Field'] . " - " . $col['Type'] . " - " . $col['Key'] . "\n";
        }
        echo "</pre></details>";
    } else {
        echo "<p class='error'>❌ Table '$table' NOT found!</p>";
    }
}

$conn->close();

echo "<hr>";
echo "<h2>✅ Done!</h2>";
echo "<p><a href='../html/khao-sat-dien-mat-troi.html'>← Back to Survey Page</a></p>";
echo "<p><a href='../api/test_save_survey.php'>→ Test Save Survey API</a></p>";
echo "</body></html>";
?>
