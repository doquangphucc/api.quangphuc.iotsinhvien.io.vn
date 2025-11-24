<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_mysqli.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$page = $_GET['page'] ?? '';
$page = trim($page);
if ($page === '') {
    $page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if ($page === '' || $page === '/') {
        $page = 'index.html';
    }
}

try {
    $sql = "SELECT id, title, image_url, target_link, target_pages 
            FROM promotions 
            WHERE is_active = 1 
            ORDER BY updated_at DESC, id DESC";
    $result = $conn->query($sql);

    $matches = [];
    while ($row = $result->fetch_assoc()) {
        if (empty($row['image_url'])) {
            continue;
        }

        $pages = json_decode($row['target_pages'] ?? '[]', true);
        if (!is_array($pages)) {
            $pages = [];
        }

        if (!in_array($page, $pages, true)) {
            continue;
        }

        $matches[] = [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'image_url' => $row['image_url'],
            'target_link' => $row['target_link']
        ];
    }

    echo json_encode([
        'success' => true,
        'promotions' => $matches
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải khuyến mãi: ' . $e->getMessage()
    ]);
}

$conn->close();

