<?php
/**
 * Security Headers Helper
 * Adds security headers to HTTP responses
 * Enhanced with stricter CSP and additional protections
 */

// Store nonce for current request
$GLOBALS['csp_nonce'] = null;

/**
 * Generate a cryptographic nonce for CSP
 * @return string
 */
function generateCSPNonce(): string {
    if ($GLOBALS['csp_nonce'] === null) {
        $GLOBALS['csp_nonce'] = base64_encode(random_bytes(16));
    }
    return $GLOBALS['csp_nonce'];
}

/**
 * Get the current CSP nonce
 * @return string|null
 */
function getCSPNonce(): ?string {
    return $GLOBALS['csp_nonce'];
}

/**
 * Set security headers for API responses
 * Call this at the beginning of API endpoints
 */
function setSecurityHeaders() {
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // XSS Protection (legacy browsers)
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions Policy (formerly Feature-Policy)
    $permissions = [
        'geolocation=(self)',
        'microphone=()',
        'camera=()',
        'payment=(self)',
        'usb=()',
        'magnetometer=()',
        'gyroscope=()',
        'accelerometer=()'
    ];
    header('Permissions-Policy: ' . implode(', ', $permissions));
    
    // Cache control for API responses
    header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Cross-Origin policies
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
}

/**
 * Set Content Security Policy header (Strict mode with nonces)
 * Use this for HTML pages - requires adding nonce to inline scripts
 * @param bool $reportOnly If true, only report violations without blocking
 */
function setStrictCSPHeader(bool $reportOnly = false) {
    $nonce = generateCSPNonce();
    
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com https://cdn.jsdelivr.net",
        "img-src 'self' data: https: blob:",
        "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
        "connect-src 'self' https://api.quangphuc.iotsinhvien.io.vn https://hceco.io.vn https://provinces.open-api.vn",
        "frame-ancestors 'self'",
        "base-uri 'self'",
        "form-action 'self' https://api.quangphuc.iotsinhvien.io.vn https://hceco.io.vn",
        "object-src 'none'",
        "upgrade-insecure-requests"
    ];
    
    $headerName = $reportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
    header($headerName . ': ' . implode('; ', $csp));
}

/**
 * Set Content Security Policy header (Backward compatible mode)
 * Use this when you need 'unsafe-inline' for legacy scripts
 */
function setCSPHeader() {
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
        "img-src 'self' data: https: blob:",
        "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
        "connect-src 'self' https://api.quangphuc.iotsinhvien.io.vn https://hceco.io.vn https://provinces.open-api.vn",
        "frame-ancestors 'self'",
        "base-uri 'self'",
        "form-action 'self' https://api.quangphuc.iotsinhvien.io.vn https://hceco.io.vn",
        "object-src 'none'",
        "upgrade-insecure-requests"
    ];
    
    header('Content-Security-Policy: ' . implode('; ', $csp));
}

/**
 * Set CSP for API endpoints (more relaxed)
 */
function setAPICSPHeader() {
    $csp = [
        "default-src 'none'",
        "frame-ancestors 'none'",
        "base-uri 'none'",
        "form-action 'none'"
    ];
    
    header('Content-Security-Policy: ' . implode('; ', $csp));
}

/**
 * Set HSTS header (only use with HTTPS)
 * @param int $maxAge Max age in seconds (default 1 year)
 * @param bool $preload Set to true if site is in HSTS preload list
 */
function setHSTSHeader($maxAge = 31536000, $preload = false) {
    // Only set HSTS if connection is secure
    $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    if ($isSecure) {
        $header = 'Strict-Transport-Security: max-age=' . $maxAge . '; includeSubDomains';
        if ($preload) {
            $header .= '; preload';
        }
        header($header);
    }
}

/**
 * Set all recommended security headers for API
 */
function setAllSecurityHeaders() {
    setSecurityHeaders();
    setHSTSHeader();
    setAPICSPHeader();
}

/**
 * Set all recommended security headers for HTML pages
 * @param bool $strictCSP Use strict CSP with nonces (requires updating inline scripts)
 */
function setAllPageSecurityHeaders(bool $strictCSP = false) {
    setSecurityHeaders();
    setHSTSHeader();
    
    if ($strictCSP) {
        setStrictCSPHeader();
    } else {
        setCSPHeader();
    }
}

/**
 * Get HTML meta tag for CSP (for static HTML files)
 * @return string
 */
function getCSPMetaTag(): string {
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
        "img-src 'self' data: https: blob:",
        "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
        "connect-src 'self' https://api.quangphuc.iotsinhvien.io.vn https://hceco.io.vn https://provinces.open-api.vn",
        "frame-ancestors 'self'",
        "base-uri 'self'",
        "form-action 'self' https://api.quangphuc.iotsinhvien.io.vn https://hceco.io.vn",
        "object-src 'none'",
        "upgrade-insecure-requests"
    ];
    
    return '<meta http-equiv="Content-Security-Policy" content="' . htmlspecialchars(implode('; ', $csp)) . '">';
}
