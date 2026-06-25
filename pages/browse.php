<?php
$db = getDB();
$mode = $_GET['page'] ?? 'browse';
$query = trim($_GET['q'] ?? '');
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$semester = trim($_GET['semester'] ?? '');

if (in_array($mode, ['browse', 'search', 'my-resources', 'bookmarks'], true)) {
    requireLogin();
    $user = getCurrentUser();
}

$categories = $db->query("SELECT * FROM categories ORDER BY name");
$where = ["r.status = 'approved'"];
$types = '';
$params = [];
$joinBookmark = '';

if ($mode === 'my-resources') {
    $where[] = 'r.uploader_id = ?';
    $types .= 'i';
    $params[] = (int)$user['id'];
}

if ($mode === 'bookmarks') {
    $joinBookmark = 'INNER JOIN bookmarks b ON b.resource_id = r.id AND b.user_id = ?';
    $types .= 'i';
    $params[] = (int)$user['id'];
}

if ($query !== '') {
    $where[] = '(r.title LIKE ? OR r.description LIKE ? OR r.subject LIKE ? OR r.tags LIKE ?)';
    $like = '%' . $query . '%';
    $types .= 'ssss';
    array_push($params, $like, $like, $like, $like);
}

if ($categoryId > 0) {
    $where[] = 'r.category_id = ?';
    $types .= 'i';
    $params[] = $categoryId;
}

if ($semester !== '') {
    $where[] = 'r.semester = ?';
    $types .= 's';
    $params[] = $semester;
}

$bookmarkSelect = $user ? ", EXISTS(SELECT 1 FROM bookmarks bx WHERE bx.resource_id = r.id AND bx.user_id = " . (int)$user['id'] . ") AS is_bookmarked" : ", 0 AS is_bookmarked";
$sql = "
    SELECT r.*, c.name AS category_name, u.full_name AS uploader_name,
        (SELECT COALESCE(AVG(rt.rating), 0) FROM ratings rt WHERE rt.resource_id = r.id) AS avg_rating
        $bookmarkSelect
    FROM resources r
    $joinBookmark
    LEFT JOIN categories c ON c.id = r.category_id
    LEFT JOIN users u ON u.id = r.uploader_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY r.created_at DESC
";

$resources = preparedQuery($sql, $types, $params);
$title = $mode === 'my-resources' ? 'My Resources' : ($mode === 'bookmarks' ? 'Bookmarks' : 'Browse Resources');
?>

<main class="main-container">
    <div class="section-header">
        <div>
            <h1 class="section-title"><?= h($title) ?></h1>
            <p class="section-subtitle">Filter and find useful notes, papers, manuals, and study material.</p>
        </div>
        <?php if ($user): ?>
            <a href="<?= h(SITE_URL) ?>/index.php?page=upload" class="btn btn-primary">
                <i class="fa-solid fa-upload"></i> Upload
            </a>
        <?php endif; ?>
    </div>

    <div class="layout-sidebar">
        <aside class="sidebar-card">
            <h3>Filters</h3>
            <form action="<?= h(SITE_URL) ?>/index.php" method="GET">
                <input type="hidden" name="page" value="<?= h($mode === 'search' ? 'browse' : $mode) ?>">
                <div class="filter-group">
                    <label for="q">Search</label>
                    <input class="form-control" id="q" name="q" value="<?= h($query) ?>" placeholder="Title, subject, tags">
                </div>
                <div class="filter-group">
                    <label for="category">Category</label>
                    <select class="form-control" id="category" name="category">
                        <option value="">All categories</option>
                        <?php if ($categories): ?>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>>
                                    <?= h($cat['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="semester">Semester</label>
                    <input class="form-control" id="semester" name="semester" value="<?= h($semester) ?>" placeholder="e.g. Sem 3">
                </div>
                <button class="btn btn-primary btn-block" type="submit">
                    <i class="fa-solid fa-filter"></i> Apply Filters
                </button>
                <a class="btn btn-ghost btn-block mt-2" href="<?= h(SITE_URL) ?>/index.php?page=<?= h($mode) ?>">Reset</a>
            </form>
        </aside>

        <section>
            <div class="resources-grid">
                <?php if ($resources && $resources->num_rows): ?>
                    <?php while ($resource = $resources->fetch_assoc()): ?>
                        <?php include __DIR__ . '/resource-card.php'; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state span-grid">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <h3>No matching resources</h3>
                        <p>Try a different search or upload a new resource for others.</p>
                        <a href="<?= h(SITE_URL) ?>/index.php?page=upload" class="btn btn-primary">Upload Resource</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>
