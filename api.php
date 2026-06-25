<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$user = getCurrentUser();

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'login_required']);
    exit;
}

if ($action === 'bookmark') {
    $resourceId = (int)($_GET['id'] ?? 0);

    if ($resourceId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_resource']);
        exit;
    }

    $exists = preparedQuery(
        "SELECT id FROM bookmarks WHERE user_id = ? AND resource_id = ? LIMIT 1",
        'ii',
        [(int)$user['id'], $resourceId]
    );

    if ($exists && $exists->num_rows > 0) {
        preparedExecute(
            "DELETE FROM bookmarks WHERE user_id = ? AND resource_id = ?",
            'ii',
            [(int)$user['id'], $resourceId]
        );
        echo json_encode(['status' => 'removed']);
        exit;
    }

    preparedExecute(
        "INSERT IGNORE INTO bookmarks (user_id, resource_id) VALUES (?, ?)",
        'ii',
        [(int)$user['id'], $resourceId]
    );

    echo json_encode(['status' => 'added']);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'unknown_action']);
?>
