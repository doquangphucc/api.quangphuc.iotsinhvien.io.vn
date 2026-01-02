<?php
/**
 * Admin API: Get Blocked IPs (WAF)
 * Returns list of IPs blocked by the WAF
 */

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../helpers/security_middleware.php';
require_once __DIR__ . '/../helpers/waf_helper.php';
require_once __DIR__ . '/permission_helper.php';

// Apply admin security (without WAF check for this endpoint)
applySecurityMiddleware([
    'waf' => false, // Don't check WAF here
    'csrf' => false,
    'audit' => true
]);

// Only full admin can manage blocked IPs
requireFullAdmin();

// GET: List blocked IPs
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $blockedIPs = WAF::getBlockedIPsList();
    
    $formattedList = [];
    foreach ($blockedIPs as $ip => $data) {
        $formattedList[] = [
            'ip' => $ip,
            'blocked_at' => date('Y-m-d H:i:s', $data['blocked_at']),
            'expires_at' => date('Y-m-d H:i:s', $data['expires']),
            'remaining_seconds' => $data['remaining'],
            'reason' => $data['reason']
        ];
    }
    
    sendSuccess([
        'blocked_ips' => $formattedList,
        'total' => count($formattedList)
    ]);
}

// POST: Unblock an IP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF for POST
    CSRFProtection::verify();
    
    $input = json_decode(getRawRequestBody(), true);
    $ip = $input['ip'] ?? '';
    
    if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
        sendError('IP không hợp lệ', 400);
    }
    
    if (WAF::unblockIP($ip)) {
        // Log the action
        require_once __DIR__ . '/../helpers/audit_logger.php';
        AuditLogger::log(
            'UNBLOCK_IP',
            'waf',
            $ip,
            null,
            ['ip' => $ip],
            "Admin unblocked IP: $ip",
            'WARNING'
        );
        
        sendSuccess(['ip' => $ip], "IP $ip đã được gỡ chặn");
    } else {
        sendError('IP không tồn tại trong danh sách chặn', 404);
    }
}

sendError('Method not allowed', 405);
