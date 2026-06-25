<?php
require_once __DIR__ . '/config.php';

requireLogin();

$resourceId = (int)($_GET['id'] ?? 0);
if ($resourceId <= 0) {
    http_response_code(404);
    exit('Resource not found.');
}

$user = getCurrentUser();
$result = preparedQuery(
    "SELECT * FROM resources WHERE id = ? AND (status = 'approved' OR uploader_id = ?) LIMIT 1",
    'ii',
    [$resourceId, (int)$user['id']]
);
$resource = $result ? $result->fetch_assoc() : null;

if (!$resource) {
    http_response_code(404);
    exit('Resource not found.');
}

$storedName = basename((string)$resource['file_path']);
$filePath = UPLOAD_DIR . $storedName;

if (!is_file($filePath)) {
    http_response_code(404);
    exit('File not found.');
}

preparedExecute("UPDATE resources SET download_count = download_count + 1 WHERE id = ?", 'i', [$resourceId]);

$downloadName = preg_replace('/[^a-zA-Z0-9._ -]/', '-', (string)$resource['title']);
$extension = pathinfo($storedName, PATHINFO_EXTENSION);
$expectedExtension = $extension ? '.' . strtolower($extension) : '';
if ($expectedExtension && substr(strtolower($downloadName), -strlen($expectedExtension)) !== $expectedExtension) {
    $downloadName .= '.' . $extension;
}
$downloadName = str_replace(['"', "\\", "\r", "\n"], '-', $downloadName ?: $storedName);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');
readfile($filePath);
exit;
?>
