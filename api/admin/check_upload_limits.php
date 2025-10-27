<?php
// Check actual upload limits and file error codes

header('Content-Type: application/json; charset=utf-8');

// Check PHP configuration
$php_upload_max_filesize = ini_get('upload_max_filesize');
$php_post_max_size = ini_get('post_max_size');
$php_max_execution_time = ini_get('max_execution_time');
$php_max_input_time = ini_get('max_input_time');

// Current request info
$content_length = $_SERVER['CONTENT_LENGTH'] ?? 0;
$method = $_SERVER['REQUEST_METHOD'] ?? '';

// Check if file was uploaded
$file_info = [];
if (isset($_FILES['file'])) {
    $file_info = [
        'name' => $_FILES['file']['name'],
        'type' => $_FILES['file']['type'],
        'size' => $_FILES['file']['size'],
        'error' => $_FILES['file']['error'],
        'error_message' => getUploadErrorMessage($_FILES['file']['error'])
    ];
}

function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_OK:
            return 'File uploaded successfully (no error)';
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the file upload';
        default:
            return 'Unknown upload error';
    }
}

// Try to set limits (for testing)
ini_set('upload_max_filesize', '3M');
ini_set('post_max_size', '10M');

$response = [
    'success' => true,
    'php_limits' => [
        'upload_max_filesize' => $php_upload_max_filesize,
        'post_max_size' => $php_post_max_size,
        'max_execution_time' => $php_max_execution_time,
        'max_input_time' => $php_max_input_time
    ],
    'request_info' => [
        'method' => $method,
        'content_length' => $content_length,
        'content_length_mb' => round($content_length / (1024 * 1024), 2) . ' MB'
    ],
    'file_info' => $file_info,
    'server_environment' => [
        'php_version' => phpversion(),
        'sapi_name' => php_sapi_name()
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);

