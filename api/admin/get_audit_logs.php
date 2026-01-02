<?php
/**
 * Admin API: Get Audit Logs
 * Returns audit logs with filtering and pagination
 */

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../helpers/security_middleware.php';
require_once __DIR__ . '/../helpers/audit_logger.php';
require_once __DIR__ . '/../check_admin_access.php';

// Apply admin security
applyAdminSecurity();

// Check admin access
checkAdminAccess();

// Get filter parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? min(100, max(10, (int)$_GET['limit'])) : 50;
$offset = ($page - 1) * $limit;

$filters = [];

if (!empty($_GET['user_id'])) {
    $filters['user_id'] = (int)$_GET['user_id'];
}

if (!empty($_GET['action'])) {
    $filters['action'] = $_GET['action'];
}

if (!empty($_GET['entity_type'])) {
    $filters['entity_type'] = $_GET['entity_type'];
}

if (!empty($_GET['severity'])) {
    $filters['severity'] = $_GET['severity'];
}

if (!empty($_GET['from_date'])) {
    $filters['from_date'] = $_GET['from_date'];
}

if (!empty($_GET['to_date'])) {
    $filters['to_date'] = $_GET['to_date'];
}

if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

// Get logs
$result = AuditLogger::getLogs($filters, $limit, $offset);

// Calculate pagination
$totalPages = ceil($result['total'] / $limit);

sendSuccess([
    'logs' => $result['logs'],
    'pagination' => [
        'current_page' => $page,
        'per_page' => $limit,
        'total_items' => $result['total'],
        'total_pages' => $totalPages,
        'has_next' => $page < $totalPages,
        'has_prev' => $page > 1
    ],
    'filters' => $filters
], 'Audit logs retrieved successfully');
