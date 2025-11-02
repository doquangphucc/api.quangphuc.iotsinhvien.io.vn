<?php
/**
 * API Proxy: Proxy cho provinces.open-api.vn để tránh lỗi SSL
 * Gọi API từ server thay vì từ browser để tránh lỗi ERR_CERT_DATE_INVALID
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get API endpoint from query string
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'api/p/';
$depth = isset($_GET['depth']) ? $_GET['depth'] : '';

// Build URL
$baseUrl = 'https://provinces.open-api.vn/';
$url = $baseUrl . $endpoint;
if ($depth) {
    $url .= (strpos($url, '?') !== false ? '&' : '?') . 'depth=' . $depth;
}

try {
    // Use cURL with SSL verification disabled (only for this specific API)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for this API only
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi gọi API: ' . $error
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($httpCode !== 200) {
        http_response_code($httpCode);
        echo json_encode([
            'success' => false,
            'message' => 'API trả về mã lỗi: ' . $httpCode
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Return the response directly
    echo $response;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi proxy API: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

