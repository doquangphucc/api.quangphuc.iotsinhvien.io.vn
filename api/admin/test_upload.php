<?php
// Simple file upload test to diagnose the issue

// Enable error display for testing
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set higher limits
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);

header('Content-Type: application/json; charset=utf-8');

$response = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_limits' => [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'max_input_time' => ini_get('max_input_time')
    ],
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 0,
    'content_length_mb' => isset($_SERVER['CONTENT_LENGTH']) ? round($_SERVER['CONTENT_LENGTH'] / (1024 * 1024), 2) : 0
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response['files_received'] = $_FILES;
    $response['post_data'] = $_POST;
    
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $response['file_info'] = [
            'name' => $file['name'],
            'type' => $file['type'],
            'size' => $file['size'],
            'size_mb' => round($file['size'] / (1024 * 1024), 2),
            'error' => $file['error'],
            'error_message' => getUploadErrorMessage($file['error'])
        ];
    }
    
    if (isset($_POST['data'])) {
        $response['custom_data'] = $_POST['data'];
    }
}

function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_OK:
            return 'Success';
        case UPLOAD_ERR_INI_SIZE:
            return 'File vượt quá upload_max_filesize';
        case UPLOAD_ERR_FORM_SIZE:
            return 'File vượt quá MAX_FILE_SIZE';
        case UPLOAD_ERR_PARTIAL:
            return 'File chỉ upload được một phần';
        case UPLOAD_ERR_NO_FILE:
            return 'Không có file';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Thiếu thư mục tạm';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Không thể ghi file';
        case UPLOAD_ERR_EXTENSION:
            return 'Extension chặn upload';
        default:
            return 'Unknown error code: ' . $error_code;
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);

