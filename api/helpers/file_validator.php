<?php
/**
 * File Upload Validator
 * Validates file uploads to prevent malicious file attacks
 */

class FileUploadValidator {
    // Allowed MIME types for images
    const ALLOWED_IMAGE_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp'
    ];
    
    // Allowed MIME types for videos
    const ALLOWED_VIDEO_MIMES = [
        'video/mp4',
        'video/webm',
        'video/quicktime'
    ];
    
    // Allowed extensions for images
    const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Allowed extensions for videos
    const ALLOWED_VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov'];
    
    // Max file size (10MB default for images)
    const MAX_IMAGE_SIZE = 10 * 1024 * 1024;
    
    // Max file size (50MB default for videos)
    const MAX_VIDEO_SIZE = 50 * 1024 * 1024;
    
    // Dangerous file signatures (magic bytes)
    private static $dangerousSignatures = [
        '<?php',
        '<?=',
        '<script',
        '#!/',
        'eval(',
        'base64_decode',
        'exec(',
        'system(',
        'shell_exec',
        'passthru('
    ];
    
    /**
     * Validate an uploaded image file
     * @param array $file - $_FILES element
     * @param array $options - Optional settings
     * @return array ['valid' => bool, 'error' => string|null, 'mime' => string|null]
     */
    public static function validateImage($file, $options = []) {
        $maxSize = $options['max_size'] ?? self::MAX_IMAGE_SIZE;
        $allowedMimes = $options['allowed_mimes'] ?? self::ALLOWED_IMAGE_MIMES;
        $allowedExtensions = $options['allowed_extensions'] ?? self::ALLOWED_IMAGE_EXTENSIONS;
        
        // Check for upload errors
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = self::getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE);
            return ['valid' => false, 'error' => $errorMessage, 'mime' => null];
        }
        
        // Check if file exists
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'File không tồn tại hoặc không được upload hợp lệ', 'mime' => null];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 1);
            return ['valid' => false, 'error' => "File quá lớn. Giới hạn: {$maxSizeMB}MB", 'mime' => null];
        }
        
        // Check extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'error' => 'Định dạng file không được hỗ trợ', 'mime' => null];
        }
        
        // Verify actual MIME type using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $actualMime = $finfo->file($file['tmp_name']);
        
        if (!in_array($actualMime, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Loại file không hợp lệ', 'mime' => $actualMime];
        }
        
        // Verify it's actually an image using getimagesize
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'error' => 'File không phải là ảnh hợp lệ', 'mime' => $actualMime];
        }
        
        // Check for embedded PHP/script code
        $content = file_get_contents($file['tmp_name']);
        foreach (self::$dangerousSignatures as $signature) {
            if (stripos($content, $signature) !== false) {
                return ['valid' => false, 'error' => 'File chứa nội dung không hợp lệ', 'mime' => $actualMime];
            }
        }
        
        return [
            'valid' => true, 
            'error' => null, 
            'mime' => $actualMime,
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'extension' => $extension
        ];
    }
    
    /**
     * Validate an uploaded video file
     * @param array $file - $_FILES element
     * @param array $options - Optional settings
     * @return array ['valid' => bool, 'error' => string|null, 'mime' => string|null]
     */
    public static function validateVideo($file, $options = []) {
        $maxSize = $options['max_size'] ?? self::MAX_VIDEO_SIZE;
        $allowedMimes = $options['allowed_mimes'] ?? self::ALLOWED_VIDEO_MIMES;
        $allowedExtensions = $options['allowed_extensions'] ?? self::ALLOWED_VIDEO_EXTENSIONS;
        
        // Check for upload errors
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = self::getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE);
            return ['valid' => false, 'error' => $errorMessage, 'mime' => null];
        }
        
        // Check if file exists
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'File không tồn tại hoặc không được upload hợp lệ', 'mime' => null];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 1);
            return ['valid' => false, 'error' => "File quá lớn. Giới hạn: {$maxSizeMB}MB", 'mime' => null];
        }
        
        // Check extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'error' => 'Định dạng video không được hỗ trợ', 'mime' => null];
        }
        
        // Verify actual MIME type using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $actualMime = $finfo->file($file['tmp_name']);
        
        if (!in_array($actualMime, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Loại file không phải video hợp lệ', 'mime' => $actualMime];
        }
        
        // Check first bytes for dangerous content
        $handle = fopen($file['tmp_name'], 'rb');
        $header = fread($handle, 4096);
        fclose($handle);
        
        foreach (self::$dangerousSignatures as $signature) {
            if (stripos($header, $signature) !== false) {
                return ['valid' => false, 'error' => 'File chứa nội dung không hợp lệ', 'mime' => $actualMime];
            }
        }
        
        return [
            'valid' => true,
            'error' => null,
            'mime' => $actualMime,
            'extension' => $extension
        ];
    }
    
    /**
     * Validate media file (image or video)
     * @param array $file - $_FILES element
     * @param string $type - 'image' or 'video'
     * @param array $options - Optional settings
     * @return array
     */
    public static function validateMedia($file, $type = 'image', $options = []) {
        if ($type === 'video') {
            return self::validateVideo($file, $options);
        }
        return self::validateImage($file, $options);
    }
    
    /**
     * Validate any uploaded file (non-image)
     * @param array $file - $_FILES element
     * @param array $allowedMimes - Allowed MIME types
     * @param array $allowedExtensions - Allowed extensions
     * @param int $maxSize - Max file size in bytes
     * @return array
     */
    public static function validateFile($file, $allowedMimes, $allowedExtensions, $maxSize = self::MAX_IMAGE_SIZE) {
        // Check for upload errors
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = self::getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE);
            return ['valid' => false, 'error' => $errorMessage, 'mime' => null];
        }
        
        // Check if file exists
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'File không tồn tại hoặc không được upload hợp lệ', 'mime' => null];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 1);
            return ['valid' => false, 'error' => "File quá lớn. Giới hạn: {$maxSizeMB}MB", 'mime' => null];
        }
        
        // Check extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'error' => 'Định dạng file không được hỗ trợ', 'mime' => null];
        }
        
        // Verify actual MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $actualMime = $finfo->file($file['tmp_name']);
        
        if (!in_array($actualMime, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Loại file không hợp lệ', 'mime' => $actualMime];
        }
        
        // Check for dangerous content
        $content = file_get_contents($file['tmp_name']);
        foreach (self::$dangerousSignatures as $signature) {
            if (stripos($content, $signature) !== false) {
                return ['valid' => false, 'error' => 'File chứa nội dung không hợp lệ', 'mime' => $actualMime];
            }
        }
        
        return [
            'valid' => true, 
            'error' => null, 
            'mime' => $actualMime,
            'extension' => $extension
        ];
    }
    
    /**
     * Generate safe filename
     * @param string $originalName
     * @param string $prefix
     * @return string
     */
    public static function generateSafeFilename($originalName, $prefix = '') {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeName = $prefix . bin2hex(random_bytes(16)) . '.' . $extension;
        return $safeName;
    }
    
    /**
     * Get human-readable upload error message
     * @param int $errorCode
     * @return string
     */
    private static function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File vượt quá giới hạn upload_max_filesize trong php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File vượt quá giới hạn MAX_FILE_SIZE trong form',
            UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
            UPLOAD_ERR_NO_FILE => 'Không có file nào được upload',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file lên đĩa',
            UPLOAD_ERR_EXTENSION => 'Upload bị dừng bởi extension PHP'
        ];
        
        return $errors[$errorCode] ?? 'Lỗi upload không xác định';
    }
    
    /**
     * Strip EXIF data from image for privacy
     * @param string $filepath
     * @param string $mime
     * @return bool
     */
    public static function stripExifData($filepath, $mime) {
        if (!in_array($mime, ['image/jpeg', 'image/jpg'])) {
            return true; // Only JPEG has EXIF
        }
        
        if (!function_exists('imagecreatefromjpeg')) {
            return true; // GD not available
        }
        
        $img = @imagecreatefromjpeg($filepath);
        if ($img === false) {
            return false;
        }
        
        $result = imagejpeg($img, $filepath, 90);
        imagedestroy($img);
        
        return $result;
    }
}

/**
 * Quick image validation function
 * @param array $file - $_FILES element
 * @return array
 */
function validateImageUpload($file, $maxSizeMB = 10) {
    return FileUploadValidator::validateImage($file, [
        'max_size' => $maxSizeMB * 1024 * 1024
    ]);
}
