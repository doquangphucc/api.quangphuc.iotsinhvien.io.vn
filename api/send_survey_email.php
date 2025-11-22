<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set error handler to capture errors and output JSON
$errorDetails = [];
$fatalError = false;

// Register shutdown function to catch fatal errors
register_shutdown_function(function() use (&$fatalError, &$errorDetails) {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $fatalError = true;
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
});

set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$errorDetails) {
    $errorDetails[] = [
        'type' => 'Error',
        'code' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ];
    return false; // Continue with normal error handling
});

try {
    require_once 'connect.php';
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Error loading connect.php: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Helper function to safely get array value
 */
if (!function_exists('safeGetValue')) {
    function safeGetValue($array, $key, $default = 'N/A') {
        return isset($array) && is_array($array) && isset($array[$key]) ? $array[$key] : $default;
    }
}

/**
 * Helper function to safely get nested array value
 */
if (!function_exists('safeGetNestedValue')) {
    function safeGetNestedValue($array, $key1, $key2, $default = 'N/A') {
        return isset($array) && is_array($array) && isset($array[$key1]) && is_array($array[$key1]) && isset($array[$key1][$key2]) 
            ? $array[$key1][$key2] 
            : $default;
    }
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (function_exists('sendError')) {
        sendError('Phương thức không được hỗ trợ', 405);
    } else {
        http_response_code(405);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Get JSON input
try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        if (function_exists('sendError')) {
            sendError('Dữ liệu JSON không hợp lệ: ' . json_last_error_msg());
        } else {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Dữ liệu JSON không hợp lệ: ' . json_last_error_msg()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Lỗi đọc input: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate required fields
$requiredFields = ['fullname', 'phone', 'email', 'surveyData', 'results'];
$missingFields = validateRequired($input, $requiredFields);

if (!empty($missingFields)) {
    sendError('Thiếu các trường bắt buộc: ' . implode(', ', $missingFields));
}

try {
    $fullname = sanitizeInput($input['fullname'] ?? '');
    $phone = sanitizeInput($input['phone'] ?? '');
    $email = sanitizeInput($input['email'] ?? '');
    $surveyData = $input['surveyData'] ?? [];
    $results = $input['results'] ?? [];
    
    // Log received data for debugging
    error_log("send_survey_email.php - Received data: fullname=" . $fullname . ", phone=" . $phone . ", email=" . $email);
    error_log("send_survey_email.php - surveyData keys: " . implode(', ', array_keys($surveyData)));
    error_log("send_survey_email.php - results keys: " . implode(', ', array_keys($results)));
    
    // Validate email format
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("send_survey_email.php - Invalid email: " . $email);
        sendError('Địa chỉ email không hợp lệ');
    }
    
    // Build HTML email content
    try {
        error_log("send_survey_email.php - Starting to build email HTML...");
        $emailContent = buildSurveyEmailHTML($fullname, $phone, $email, $surveyData, $results);
        error_log("send_survey_email.php - Email HTML built successfully, length: " . strlen($emailContent));
    } catch (Exception $e) {
        error_log("Error building email HTML: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Lỗi khi tạo nội dung email: ' . $e->getMessage(), 500);
    } catch (Error $e) {
        error_log("Fatal error building email HTML: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Lỗi hệ thống khi tạo nội dung email: ' . $e->getMessage(), 500);
    }
    
    // Use FormSubmit API to send email (more reliable than PHP mail())
    $formSubmitUrl = 'https://formsubmit.co/ajax/doquangphuc21@gmail.com';
    $subject = "Báo Giá Điện Mặt Trời - Khách hàng: $fullname ($phone)";
    
    // Prepare form data for FormSubmit
    $formData = [
        '_subject' => $subject,
        '_template' => 'table',
        '_captcha' => 'false',
        'message' => $emailContent,
        'name' => $fullname,
        'email' => $email,
        'phone' => $phone
    ];
    
    // Send email via FormSubmit API
    error_log("send_survey_email.php - Attempting to send email via FormSubmit to: doquangphuc21@gmail.com");
    
    $ch = curl_init($formSubmitUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        error_log("send_survey_email.php - cURL Error: " . $curlError);
        sendError('Lỗi kết nối khi gửi email: ' . $curlError, 500);
    }
    
    if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        if ($responseData && isset($responseData['success']) && $responseData['success']) {
            error_log("send_survey_email.php - Email sent successfully via FormSubmit");
            sendSuccess(['sent' => true], 'Đã gửi báo giá đến email thành công!');
        } else {
            error_log("send_survey_email.php - FormSubmit returned error: " . $response);
            sendError('Không thể gửi email, vui lòng thử lại sau', 500);
        }
    } else {
        error_log("send_survey_email.php - FormSubmit HTTP Error: " . $httpCode . " Response: " . $response);
        sendError('Lỗi khi gửi email (HTTP ' . $httpCode . '), vui lòng thử lại sau', 500);
    }
    
} catch (Exception $e) {
    error_log("Exception in send_survey_email.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    // Return detailed error for debugging
    $errorMessage = 'Lỗi khi gửi email: ' . $e->getMessage();
    $errorMessage .= "\nFile: " . $e->getFile() . " Line: " . $e->getLine();
    if (!empty($errorDetails)) {
        $errorMessage .= "\nAdditional errors: " . json_encode($errorDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    sendError($errorMessage, 500);
} catch (Error $e) {
    error_log("Error in send_survey_email.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    // Return detailed error for debugging
    $errorMessage = 'Lỗi hệ thống: ' . $e->getMessage();
    $errorMessage .= "\nFile: " . $e->getFile() . " Line: " . $e->getLine();
    if (!empty($errorDetails)) {
        $errorMessage .= "\nAdditional errors: " . json_encode($errorDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    sendError($errorMessage, 500);
} catch (Throwable $e) {
    error_log("Throwable in send_survey_email.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $errorMessage = 'Lỗi không xác định: ' . $e->getMessage();
    $errorMessage .= "\nFile: " . $e->getFile() . " Line: " . $e->getLine();
    if (!empty($errorDetails)) {
        $errorMessage .= "\nAdditional errors: " . json_encode($errorDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    sendError($errorMessage, 500);
}

/**
 * Build HTML email content from survey data
 */
function buildSurveyEmailHTML($fullname, $phone, $email, $surveyData, $results) {
    
    // Get region name
    $regionMap = [
        'mien-bac' => 'Miền Bắc',
        'mien-trung' => 'Miền Trung',
        'mien-nam' => 'Miền Nam'
    ];
    $region = safeGetValue($surveyData, 'region', 'N/A');
    $regionName = isset($regionMap[$region]) ? $regionMap[$region] : $region;
    
    // Get phase name
    $phase = safeGetValue($surveyData, 'phase', 1);
    $phaseName = ($phase == 1 || $phase == '1') ? '1 Pha' : '3 Pha';
    
    // Get usage time name
    $usageTimeMap = [
        'day' => 'Ban ngày nhiều',
        'balanced' => 'Cả ngày đều',
        'night' => 'Ban đêm nhiều'
    ];
    $usageTime = safeGetValue($surveyData, 'usageTime', 'balanced');
    $usageTimeName = isset($usageTimeMap[$usageTime]) ? $usageTimeMap[$usageTime] : $usageTime;
    
    // Pre-calculate all values to avoid issues in string concatenation
    try {
        $monthlyBill = (float)safeGetValue($results, 'monthlyBill', 0);
        $monthlyKwh = (float)safeGetValue($results, 'monthlyKwh', 0);
        $dailyKwh = (float)safeGetValue($results, 'dailyKwh', 0);
        $peakSunHours = (float)safeGetValue($results, 'peakSunHours', 0);
        $roofArea = (float)safeGetValue($results, 'roofArea', 0);
        
        $solarPanelName = (string)safeGetValue($results, 'solarPanelName', 'N/A');
        $solarPanelWatt = (int)safeGetValue($results, 'solarPanelWatt', 0);
        $panelCount = (int)safeGetValue($results, 'panelCount', 0);
        $solarPanelPrice = (float)safeGetValue($results, 'solarPanelPrice', 0);
        $panelTotalPrice = (float)safeGetValue($results, 'panelTotalPrice', 0);
        
        $inverterName = (string)safeGetNestedValue($results, 'selectedInverter', 'name', 'N/A');
        $inverterPower = (int)safeGetNestedValue($results, 'selectedInverter', 'power', 0);
        $inverterPrice = (float)safeGetNestedValue($results, 'selectedInverter', 'price', 0);
        
        $cabinetName = (string)safeGetNestedValue($results, 'selectedCabinet', 'name', 'N/A');
        $cabinetPrice = (float)safeGetNestedValue($results, 'selectedCabinet', 'price', 0);
        
        $batteryName = (string)safeGetNestedValue($results, 'selectedBattery', 'name', 'N/A');
        $batteryCapacity = (float)safeGetNestedValue($results, 'selectedBattery', 'capacity', 0);
        $batteryUnits = (int)safeGetNestedValue($results, 'selectedBattery', 'units', 0);
        $batteryPrice = (float)safeGetNestedValue($results, 'selectedBattery', 'price', 0);
        $batteryTotalPrice = (float)safeGetNestedValue($results, 'selectedBattery', 'totalPrice', 0);
        
        $accessoriesTotal = (float)safeGetValue($results, 'accessoriesTotal', 0);
        $totalPrice = (float)safeGetValue($results, 'totalPrice', 0);
        
        $systemSizeKw = (float)safeGetValue($results, 'systemSizeKw', 0);
        $annualSavings = (float)safeGetValue($results, 'annualSavings', 0);
        $paybackPeriod = (string)safeGetValue($results, 'paybackPeriod', 0);
        
        $accessories = safeGetValue($results, 'accessories', []);
        if (!is_array($accessories)) {
            $accessories = [];
        }
    } catch (Exception $e) {
        error_log("Error calculating values in buildSurveyEmailHTML: " . $e->getMessage());
        throw $e;
    }
    
    // Build HTML string with error handling
    try {
        $html = '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Giá Điện Mặt Trời</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #00C851 0%, #00E676 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .section { margin-bottom: 30px; background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #00C851; }
        .section-title { color: #00C851; font-size: 18px; font-weight: bold; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th { background: #00C851; color: white; padding: 10px; text-align: left; }
        table td { padding: 8px; border-bottom: 1px solid #ddd; }
        table tr:hover { background: #f5f5f5; }
        .highlight { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .total { background: #00C851; color: white; padding: 15px; border-radius: 5px; font-size: 20px; font-weight: bold; text-align: center; margin: 20px 0; }
        .footer { background: #333; color: white; padding: 15px; text-align: center; border-radius: 0 0 10px 10px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 BÁO GIÁ HỆ THỐNG ĐIỆN MẶT TRỜI</h1>
            <p>HC Eco System - Hệ sinh thái cho tương lai</p>
        </div>
        
        <div class="content">
            <!-- Thông tin khách hàng -->
            <div class="section">
                <div class="section-title">👤 THÔNG TIN KHÁCH HÀNG</div>
                <table>
                    <tr><td><strong>Họ và tên:</strong></td><td>' . htmlspecialchars($fullname) . '</td></tr>
                    <tr><td><strong>Số điện thoại:</strong></td><td>' . htmlspecialchars($phone) . '</td></tr>
                    <tr><td><strong>Email:</strong></td><td>' . htmlspecialchars($email) . '</td></tr>
                    <tr><td><strong>Khu vực:</strong></td><td>' . htmlspecialchars($regionName) . '</td></tr>
                    <tr><td><strong>Loại điện:</strong></td><td>' . htmlspecialchars($phaseName) . '</td></tr>
                    <tr><td><strong>Thời gian sử dụng:</strong></td><td>' . htmlspecialchars($usageTimeName) . '</td></tr>
                </table>
            </div>
            
            <!-- Phân tích nhu cầu -->
            <div class="section">
                <div class="section-title">📊 PHÂN TÍCH NHU CẦU</div>
                <table>
                    <tr><td><strong>Hóa đơn điện/tháng:</strong></td><td>' . number_format($monthlyBill, 0, ',', '.') . ' VNĐ</td></tr>
                    <tr><td><strong>Mức tiêu thụ điện/tháng:</strong></td><td><strong style="color: #00C851;">' . number_format($monthlyKwh, 1, ',', '.') . ' kWh</strong></td></tr>
                    <tr><td><strong>Tiêu thụ điện/ngày:</strong></td><td>' . number_format($dailyKwh, 1, ',', '.') . ' kWh</td></tr>
                    <tr><td><strong>Giờ nắng trung bình:</strong></td><td>' . number_format($peakSunHours, 1, ',', '.') . ' giờ/ngày</td></tr>
                    <tr><td><strong>Diện tích mái cần thiết:</strong></td><td>' . number_format($roofArea, 1, ',', '.') . ' m²</td></tr>
                </table>
            </div>
            
            <!-- Bảng báo giá chi tiết -->
            <div class="section">
                <div class="section-title">📋 BẢNG BÁO GIÁ CHI TIẾT</div>
                
                <!-- Tấm Pin -->
                <h3 style="color: #00C851; margin-top: 20px;">⚡ Tấm Pin Mặt Trời</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tên thiết bị</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>' . htmlspecialchars($solarPanelName) . ' (' . $solarPanelWatt . 'W)</td>
                            <td>' . $panelCount . ' tấm</td>
                            <td>' . number_format($solarPanelPrice, 0, ',', '.') . ' đ</td>
                            <td><strong>' . number_format($panelTotalPrice, 0, ',', '.') . ' đ</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Inverter -->
                <h3 style="color: #00C851; margin-top: 20px;">🔌 Biến Tần (Inverter)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tên thiết bị</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>' . htmlspecialchars($inverterName) . ' (' . $inverterPower . 'W)</td>
                            <td>1 bộ</td>
                            <td>' . number_format($inverterPrice, 0, ',', '.') . ' đ</td>
                            <td><strong>' . number_format($inverterPrice, 0, ',', '.') . ' đ</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Tủ Điện -->
                <h3 style="color: #00C851; margin-top: 20px;">🧰 Tủ Điện Hybrid</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tên thiết bị</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>' . htmlspecialchars($cabinetName) . '</td>
                            <td>1 cái</td>
                            <td>' . number_format($cabinetPrice, 0, ',', '.') . ' đ</td>
                            <td><strong>' . number_format($cabinetPrice, 0, ',', '.') . ' đ</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Pin Lưu Trữ -->
                <h3 style="color: #00C851; margin-top: 20px;">🔋 Pin Lưu Trữ (Battery)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tên thiết bị</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>' . htmlspecialchars($batteryName) . ' (' . number_format($batteryCapacity, 2, ',', '.') . ' kWh/bộ)</td>
                            <td>' . $batteryUnits . ' bộ</td>
                            <td>' . number_format($batteryPrice, 0, ',', '.') . ' đ</td>
                            <td><strong>' . number_format($batteryTotalPrice, 0, ',', '.') . ' đ</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Phụ Kiện -->
                <h3 style="color: #00C851; margin-top: 20px;">🔧 Phụ Kiện & Vật Tư Lắp Đặt</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tên phụ kiện</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    // Add accessories
    if (is_array($accessories) && !empty($accessories)) {
        foreach ($accessories as $acc) {
            if (!is_array($acc)) continue;
            $accName = (string)safeGetValue($acc, 'name', 'N/A');
            $accQuantity = (int)safeGetValue($acc, 'quantity', 0);
            $accUnit = (string)safeGetValue($acc, 'unit', 'cái');
            $accPrice = (float)safeGetValue($acc, 'price', 0);
            $accTotalPrice = (float)safeGetValue($acc, 'totalPrice', 0);
            $html .= '<tr>
                <td>' . htmlspecialchars($accName) . '</td>
                <td>' . $accQuantity . ' ' . $accUnit . '</td>
                <td>' . number_format($accPrice, 0, ',', '.') . ' đ</td>
                <td>' . number_format($accTotalPrice, 0, ',', '.') . ' đ</td>
            </tr>';
        }
    }
    
    $html .= '</tbody>
                </table>
            </div>
            
            <!-- Tổng kết -->
            <div class="section">
                <div class="section-title">💰 TỔNG KẾT CHI PHÍ</div>
                <table>
                    <tr>
                        <td><strong>Tổng thiết bị:</strong></td>
                        <td style="text-align: right;">' . number_format($totalPrice - $accessoriesTotal, 0, ',', '.') . ' đ</td>
                    </tr>
                    <tr>
                        <td><strong>Phụ kiện & lắp đặt:</strong></td>
                        <td style="text-align: right;">' . number_format($accessoriesTotal, 0, ',', '.') . ' đ</td>
                    </tr>
                </table>
                <div class="total">
                    TỔNG CHI PHÍ ĐẦU TƯ: ' . number_format($totalPrice, 0, ',', '.') . ' VNĐ
                </div>
            </div>
            
            <!-- Thông tin hệ thống -->
            <div class="section">
                <div class="section-title">🎯 TỔNG QUAN HỆ THỐNG</div>
                <table>
                    <tr><td><strong>Hệ thống:</strong></td><td>' . number_format($systemSizeKw, 2, ',', '.') . ' kWp</td></tr>
                    <tr><td><strong>Tấm pin:</strong></td><td>' . $panelCount . ' tấm</td></tr>
                    <tr><td><strong>Inverter:</strong></td><td>' . htmlspecialchars($inverterName) . '</td></tr>
                    <tr><td><strong>Pin lưu trữ:</strong></td><td>' . number_format($batteryUnits * $batteryCapacity, 2, ',', '.') . ' kWh (' . $batteryUnits . ' bộ)</td></tr>
                    <tr><td><strong>Tiết kiệm/năm:</strong></td><td>' . number_format($annualSavings / 1000000, 1, ',', '.') . ' triệu VNĐ</td></tr>
                    <tr><td><strong>Hoàn vốn:</strong></td><td>' . $paybackPeriod . ' năm</td></tr>
                </table>
            </div>
            
            <div class="highlight">
                <strong>⚠️ Lưu ý:</strong> Báo giá chưa bao gồm chi phí vận chuyển đến công trình. Chi phí vận chuyển sẽ được tính dựa trên khoảng cách từ kho đến công trình của bạn.
            </div>
        </div>
        
        <div class="footer">
            <p>© 2025 HC Eco System - Hệ sinh thái cho tương lai</p>
            <p>Hotline: 0969 397 434 | Email: hcecosystem@gmail.com</p>
            <p>Website: hceco.io.vn</p>
        </div>
    </div>
</body>
</html>';
    
        return $html;
    } catch (Exception $e) {
        error_log("Error building HTML string: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        throw new Exception("Lỗi khi tạo HTML email: " . $e->getMessage());
    } catch (Error $e) {
        error_log("Fatal error building HTML string: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        throw new Error("Lỗi hệ thống khi tạo HTML email: " . $e->getMessage());
    }
}

