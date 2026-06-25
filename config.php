<?php

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'academic_platform');

// Site Configuration
define('SITE_NAME', 'AcadShare');
define('SITE_URL', 'http://localhost/academic_platform');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('ALLOWED_TYPES', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'png', 'jpg', 'jpeg']);
define('MAX_FILE_SIZE', 20 * 1024 * 1024);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDB() {
    static $conn = null;

    if ($conn === null) {
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            die("<div style='font-family:sans-serif;padding:40px;background:#fee;border:1px solid #f88;border-radius:8px;max-width:600px;margin:50px auto'>
                <h2>Database Connection Failed</h2>
                <p>Could not connect to MySQL. Please make sure:</p>
                <ul>
                    <li>XAMPP is running with Apache and MySQL</li>
                    <li>The database <strong>" . h(DB_NAME) . "</strong> exists</li>
                    <li>You imported <code>database.sql</code> in phpMyAdmin</li>
                </ul>
                <p><small>Error: " . h($conn->connect_error) . "</small></p>
            </div>");
        }

        $conn->set_charset('utf8mb4');
    }

    return $conn;
}

function h($input) {
    return htmlspecialchars((string)$input, ENT_QUOTES, 'UTF-8');
}

function sanitize($input) {
    return h(strip_tags(trim((string)$input)));
}

function preparedQuery($sql, $types = '', $params = []) {
    $stmt = getDB()->prepare($sql);
    if (!$stmt) {
        return false;
    }

    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        return false;
    }

    return $stmt->get_result();
}

function preparedExecute($sql, $types = '', $params = []) {
    $stmt = getDB()->prepare($sql);
    if (!$stmt) {
        return false;
    }

    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    return $stmt->execute();
}

function getCurrentUser() {
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $result = preparedQuery("SELECT * FROM users WHERE id = ? LIMIT 1", 'i', [(int)$_SESSION['user_id']]);
    return $result ? $result->fetch_assoc() : null;
}

function requireLogin() {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/index.php?page=login&redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    $user = getCurrentUser();

    if (!$user || $user['role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/index.php?page=dashboard');
        exit;
    }
}

function formatSize($bytes) {
    $bytes = (int)$bytes;
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function timeAgo($datetime) {
    if (!$datetime) {
        return 'Just now';
    }

    try {
        $now = new DateTime();
        $ago = new DateTime($datetime);
    } catch (Exception $e) {
        return '';
    }

    $diff = $now->diff($ago);
    if ($diff->days === 0) {
        if ($diff->h === 0) return max(1, $diff->i) . ' min ago';
        return $diff->h . ' hr ago';
    }
    if ($diff->days < 7) return $diff->days . ' days ago';
    return $ago->format('d M Y');
}

function getFileIcon($type) {
    $icons = [
        'pdf' => 'fa-file-pdf',
        'doc' => 'fa-file-word',
        'docx' => 'fa-file-word',
        'ppt' => 'fa-file-powerpoint',
        'pptx' => 'fa-file-powerpoint',
        'xls' => 'fa-file-excel',
        'xlsx' => 'fa-file-excel',
        'zip' => 'fa-file-archive',
        'txt' => 'fa-file-alt',
        'jpg' => 'fa-file-image',
        'jpeg' => 'fa-file-image',
        'png' => 'fa-file-image',
    ];

    return $icons[strtolower((string)$type)] ?? 'fa-file';
}

function getFileTone($type) {
    $type = strtolower((string)$type);
    if ($type === 'pdf') return 'icon-pdf';
    if (in_array($type, ['doc', 'docx'], true)) return 'icon-doc';
    if (in_array($type, ['ppt', 'pptx'], true)) return 'icon-ppt';
    if (in_array($type, ['jpg', 'jpeg', 'png'], true)) return 'icon-img';
    return 'icon-default';
}

function renderStars($rating) {
    $rating = (float)$rating;
    $html = '<div class="stars" aria-label="' . h(round($rating, 1)) . ' out of 5 stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<i class="fa-solid fa-star' . ($i > round($rating) ? ' empty' : '') . '"></i>';
    }
    return $html . '</div>';
}

function resourceFileUrl($resource) {
    if (is_array($resource)) {
        $id = (int)($resource['id'] ?? 0);
    } else {
        $id = (int)$resource;
    }

    return SITE_URL . '/download.php?id=' . $id;
}
?>
