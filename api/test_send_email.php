<?php
// Simple test script to check send_survey_email.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing send_survey_email.php</h2>";

// Test data
$testData = [
    'fullname' => 'Test User',
    'phone' => '0987654321',
    'email' => 'test@example.com',
    'surveyData' => [
        'region' => 'mien-bac',
        'phase' => 1,
        'solarPanel' => '1',
        'monthlyBill' => 2000000,
        'usageTime' => 'balanced'
    ],
    'results' => [
        'monthlyBill' => 2000000,
        'monthlyKwh' => 500,
        'dailyKwh' => 16.67,
        'peakSunHours' => 3.7,
        'roofArea' => 30,
        'systemSizeKw' => 5.0,
        'panelCount' => 10,
        'solarPanelWatt' => 500,
        'solarPanelName' => 'Test Panel',
        'solarPanelPrice' => 5000000,
        'panelTotalPrice' => 50000000,
        'selectedInverter' => [
            'name' => 'Test Inverter',
            'power' => 5000,
            'price' => 15000000
        ],
        'selectedCabinet' => [
            'name' => 'Test Cabinet',
            'price' => 2000000
        ],
        'selectedBattery' => [
            'name' => 'Test Battery',
            'capacity' => 5.12,
            'units' => 2,
            'price' => 15000000,
            'totalPrice' => 30000000
        ],
        'accessories' => [
            [
                'name' => 'Test Accessory',
                'quantity' => 1,
                'unit' => 'cái',
                'price' => 1000000,
                'totalPrice' => 1000000
            ]
        ],
        'accessoriesTotal' => 1000000,
        'totalPrice' => 98000000,
        'annualSavings' => 24000000,
        'paybackPeriod' => 4
    ]
];

// Make request
$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/send_survey_email.php';
echo "<p>Testing URL: <code>$url</code></p>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($testData))
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $headerSize);

curl_close($ch);

echo "<h3>Response:</h3>";
echo "<p>HTTP Code: <strong>$httpCode</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
echo htmlspecialchars($body);
echo "</pre>";

if ($httpCode === 200) {
    $data = json_decode($body, true);
    if ($data && $data['success']) {
        echo "<p style='color: green;'>✅ Test passed! Email sent successfully.</p>";
    } else {
        echo "<p style='color: red;'>❌ Test failed: " . ($data['message'] ?? 'Unknown error') . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ HTTP Error: $httpCode</p>";
}

