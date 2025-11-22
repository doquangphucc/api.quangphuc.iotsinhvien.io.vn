<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once 'connect.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Dữ liệu JSON không hợp lệ');
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
    
    // Validate email format
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendError('Địa chỉ email không hợp lệ');
    }
    
    // Build HTML email content
    try {
        $emailContent = buildSurveyEmailHTML($fullname, $phone, $email, $surveyData, $results);
    } catch (Exception $e) {
        error_log("Error building email HTML: " . $e->getMessage());
        sendError('Lỗi khi tạo nội dung email: ' . $e->getMessage(), 500);
    }
    
    // Email configuration
    $to = 'doquangphuc21@gmail.com';
    $subject = "Báo Giá Điện Mặt Trời - Khách hàng: $fullname ($phone)";
    
    // Email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: HC Eco System <noreply@hceco.io.vn>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Send email
    $mailSent = @mail($to, $subject, $emailContent, $headers);
    
    if ($mailSent) {
        sendSuccess(['sent' => true], 'Đã gửi báo giá đến email thành công!');
    } else {
        // Log error details
        $lastError = error_get_last();
        error_log("Failed to send email. Last error: " . print_r($lastError, true));
        sendError('Không thể gửi email, vui lòng thử lại sau');
    }
    
} catch (Exception $e) {
    error_log("Exception in send_survey_email.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    sendError('Lỗi khi gửi email: ' . $e->getMessage(), 500);
} catch (Error $e) {
    error_log("Error in send_survey_email.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    sendError('Lỗi hệ thống: ' . $e->getMessage(), 500);
}

/**
 * Build HTML email content from survey data
 */
function buildSurveyEmailHTML($fullname, $phone, $email, $surveyData, $results) {
    $regionName = [
        'mien-bac' => 'Miền Bắc',
        'mien-trung' => 'Miền Trung',
        'mien-nam' => 'Miền Nam'
    ][$surveyData['region']] ?? $surveyData['region'];
    
    $phaseName = $surveyData['phase'] == 1 ? '1 Pha' : '3 Pha';
    
    $usageTimeName = [
        'day' => 'Ban ngày nhiều',
        'balanced' => 'Cả ngày đều',
        'night' => 'Ban đêm nhiều'
    ][$surveyData['usageTime']] ?? $surveyData['usageTime'];
    
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
                    <tr><td><strong>Hóa đơn điện/tháng:</strong></td><td>' . number_format($results['monthlyBill'] ?? 0, 0, ',', '.') . ' VNĐ</td></tr>
                    <tr><td><strong>Mức tiêu thụ điện/tháng:</strong></td><td><strong style="color: #00C851;">' . number_format($results['monthlyKwh'] ?? 0, 1, ',', '.') . ' kWh</strong></td></tr>
                    <tr><td><strong>Tiêu thụ điện/ngày:</strong></td><td>' . number_format($results['dailyKwh'] ?? 0, 1, ',', '.') . ' kWh</td></tr>
                    <tr><td><strong>Giờ nắng trung bình:</strong></td><td>' . number_format($results['peakSunHours'] ?? 0, 1, ',', '.') . ' giờ/ngày</td></tr>
                    <tr><td><strong>Diện tích mái cần thiết:</strong></td><td>' . number_format($results['roofArea'] ?? 0, 1, ',', '.') . ' m²</td></tr>
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
                            <td>' . htmlspecialchars($results['solarPanelName'] ?? 'N/A') . ' (' . ($results['solarPanelWatt'] ?? 0) . 'W)</td>
                            <td>' . ($results['panelCount'] ?? 0) . ' tấm</td>
                            <td>' . number_format($results['solarPanelPrice'] ?? 0, 0, ',', '.') . ' đ</td>
                            <td><strong>' . number_format($results['panelTotalPrice'] ?? 0, 0, ',', '.') . ' đ</strong></td>
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
                            <td>' . htmlspecialchars(isset($results['selectedInverter']) && is_array($results['selectedInverter']) ? ($results['selectedInverter']['name'] ?? 'N/A') : 'N/A') . ' (' . (isset($results['selectedInverter']) && is_array($results['selectedInverter']) ? ($results['selectedInverter']['power'] ?? 0) : 0) . 'W)</td>
                            <td>1 bộ</td>
                            <td>' . number_format(isset($results['selectedInverter']) && is_array($results['selectedInverter']) ? ($results['selectedInverter']['price'] ?? 0) : 0, 0, ',', '.') . ' đ</td>
                            <td><strong>' . number_format(isset($results['selectedInverter']) && is_array($results['selectedInverter']) ? ($results['selectedInverter']['price'] ?? 0) : 0, 0, ',', '.') . ' đ</strong></td>
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
                            <td>' . htmlspecialchars(isset($results['selectedCabinet']) && is_array($results['selectedCabinet']) ? ($results['selectedCabinet']['name'] ?? 'N/A') : 'N/A') . '</td>
                            <td>1 cái</td>
                            <td>' . number_format(isset($results['selectedCabinet']) && is_array($results['selectedCabinet']) ? ($results['selectedCabinet']['price'] ?? 0) : 0, 0, ',', '.') . ' đ</td>
                            <td><strong>' . number_format(isset($results['selectedCabinet']) && is_array($results['selectedCabinet']) ? ($results['selectedCabinet']['price'] ?? 0) : 0, 0, ',', '.') . ' đ</strong></td>
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
                            <td>' . htmlspecialchars(isset($results['selectedBattery']) && is_array($results['selectedBattery']) ? ($results['selectedBattery']['name'] ?? 'N/A') : 'N/A') . ' (' . number_format(isset($results['selectedBattery']) && is_array($results['selectedBattery']) ? ($results['selectedBattery']['capacity'] ?? 0) : 0, 2, ',', '.') . ' kWh/bộ)</td>
                            <td>' . (isset($results['selectedBattery']) && is_array($results['selectedBattery']) ? ($results['selectedBattery']['units'] ?? 0) : 0) . ' bộ</td>
                            <td>' . number_format(isset($results['selectedBattery']) && is_array($results['selectedBattery']) ? ($results['selectedBattery']['price'] ?? 0) : 0, 0, ',', '.') . ' đ</td>
                            <td><strong>' . number_format(isset($results['selectedBattery']) && is_array($results['selectedBattery']) ? ($results['selectedBattery']['totalPrice'] ?? 0) : 0, 0, ',', '.') . ' đ</strong></td>
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
    if (isset($results['accessories']) && is_array($results['accessories'])) {
        foreach ($results['accessories'] as $acc) {
            $html .= '<tr>
                <td>' . htmlspecialchars($acc['name'] ?? 'N/A') . '</td>
                <td>' . ($acc['quantity'] ?? 0) . ' ' . ($acc['unit'] ?? 'cái') . '</td>
                <td>' . number_format($acc['price'] ?? 0, 0, ',', '.') . ' đ</td>
                <td>' . number_format($acc['totalPrice'] ?? 0, 0, ',', '.') . ' đ</td>
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
                        <td style="text-align: right;">' . number_format(($results['totalPrice'] ?? 0) - ($results['accessoriesTotal'] ?? 0), 0, ',', '.') . ' đ</td>
                    </tr>
                    <tr>
                        <td><strong>Phụ kiện & lắp đặt:</strong></td>
                        <td style="text-align: right;">' . number_format($results['accessoriesTotal'] ?? 0, 0, ',', '.') . ' đ</td>
                    </tr>
                </table>
                <div class="total">
                    TỔNG CHI PHÍ ĐẦU TƯ: ' . number_format($results['totalPrice'] ?? 0, 0, ',', '.') . ' VNĐ
                </div>
            </div>
            
            <!-- Thông tin hệ thống -->
            <div class="section">
                <div class="section-title">🎯 TỔNG QUAN HỆ THỐNG</div>
                <table>
                    <tr><td><strong>Hệ thống:</strong></td><td>' . number_format($results['systemSizeKw'] ?? 0, 2, ',', '.') . ' kWp</td></tr>
                    <tr><td><strong>Tấm pin:</strong></td><td>' . ($results['panelCount'] ?? 0) . ' tấm</td></tr>
                    <tr><td><strong>Inverter:</strong></td><td>' . htmlspecialchars(isset($results['selectedInverter']) && is_array($results['selectedInverter']) ? ($results['selectedInverter']['name'] ?? 'N/A') : 'N/A') . '</td></tr>
                    <tr><td><strong>Pin lưu trữ:</strong></td><td>' . number_format((isset($results['selectedBattery']) && is_array($results['selectedBattery']) ? ($results['selectedBattery']['units'] ?? 0) : 0) * (isset($results['selectedBattery']) && is_array($results['selectedBattery']) ? ($results['selectedBattery']['capacity'] ?? 0) : 0), 2, ',', '.') . ' kWh (' . (isset($results['selectedBattery']) && is_array($results['selectedBattery']) ? ($results['selectedBattery']['units'] ?? 0) : 0) . ' bộ)</td></tr>
                    <tr><td><strong>Tiết kiệm/năm:</strong></td><td>' . number_format(($results['annualSavings'] ?? 0) / 1000000, 1, ',', '.') . ' triệu VNĐ</td></tr>
                    <tr><td><strong>Hoàn vốn:</strong></td><td>' . ($results['paybackPeriod'] ?? 0) . ' năm</td></tr>
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
}

