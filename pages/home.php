<?php
$db = getDB();

$stats = [
    'resources' => 0,
    'students' => 0,
    'downloads' => 0,
];

$result = $db->query("SELECT COUNT(*) AS total FROM resources WHERE status = 'approved'");
if ($result) $stats['resources'] = (int)$result->fetch_assoc()['total'];

$result = $db->query("SELECT COUNT(*) AS total FROM users");
if ($result) $stats['students'] = (int)$result->fetch_assoc()['total'];

$result = $db->query("SELECT COALESCE(SUM(download_count), 0) AS total FROM resources WHERE status = 'approved'");
if ($result) $stats['downloads'] = (int)$result->fetch_assoc()['total'];

$categories = $db->query("
    SELECT c.*, COUNT(r.id) AS resource_count
    FROM categories c
    LEFT JOIN resources r ON r.category_id = c.id AND r.status = 'approved'
    GROUP BY c.id
    ORDER BY c.name
");

$recent = null;
if ($user) {
    $recent = $db->query("
        SELECT r.*, c.name AS category_name, u.full_name AS uploader_name,
            (SELECT COALESCE(AVG(rt.rating), 0) FROM ratings rt WHERE rt.resource_id = r.id) AS avg_rating
        FROM resources r
        LEFT JOIN categories c ON c.id = r.category_id
        LEFT JOIN users u ON u.id = r.uploader_id
        WHERE r.status = 'approved'
        ORDER BY r.created_at DESC
        LIMIT 6
    ");
}

$browseUrl = $user
    ? SITE_URL . '/index.php?page=browse'
    : SITE_URL . '/index.php?page=login&redirect=' . urlencode('/academic_platform/index.php?page=browse');
?>

<section class="hero">
    <div class="hero-content">
        <h1>Share academic resources in one trusted place</h1>
        <p>Upload notes, previous papers, lab manuals, textbooks, and presentations so your class can find what it needs faster.</p>
        <div class="hero-actions">
            <a href="<?= h($browseUrl) ?>" class="btn btn-white btn-lg">
                <i class="fa-solid fa-layer-group"></i> Browse Resources
            </a>
            <a href="<?= h(SITE_URL) ?>/index.php?page=upload" class="btn btn-glass btn-lg">
                <i class="fa-solid fa-upload"></i> Upload Now
            </a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <div class="number"><?= number_format($stats['resources']) ?></div>
                <div class="label">Resources</div>
            </div>
            <div class="hero-stat">
                <div class="number"><?= number_format($stats['students']) ?></div>
                <div class="label">Members</div>
            </div>
            <div class="hero-stat">
                <div class="number"><?= number_format($stats['downloads']) ?></div>
                <div class="label">Downloads</div>
            </div>
        </div>
    </div>
</section>

<main class="main-container">
    <section class="mb-3">
        <div class="section-header">
            <h2 class="section-title">Browse by <span>Category</span></h2>
            <a href="<?= h(SITE_URL) ?>/index.php?page=browse" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="categories-grid">
            <?php if ($categories && $categories->num_rows): ?>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <a class="category-card" href="<?= h(SITE_URL) ?>/index.php?page=browse&category=<?= (int)$cat['id'] ?>">
                        <i class="fa-solid <?= h($cat['icon']) ?>"></i>
                        <h3><?= h($cat['name']) ?></h3>
                        <span><?= (int)$cat['resource_count'] ?> resources</span>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-folder-open"></i>
                    <h3>No categories yet</h3>
                    <p>Import the database file to load the default categories.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <div class="section-header">
            <h2 class="section-title">Recently <span>Added</span></h2>
            <a href="<?= h($browseUrl) ?>" class="btn btn-ghost btn-sm">Explore</a>
        </div>
        <div class="resources-grid">
            <?php if (!$user): ?>
                <div class="empty-state span-grid">
                    <i class="fa-solid fa-lock"></i>
                    <h3>Login required</h3>
                    <p>Resources are available only after you log in.</p>
                    <a href="<?= h($browseUrl) ?>" class="btn btn-primary">Login to Browse</a>
                </div>
            <?php elseif ($recent && $recent->num_rows): ?>
                <?php while ($resource = $recent->fetch_assoc()): ?>
                    <?php include __DIR__ . '/resource-card.php'; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state span-grid">
                    <i class="fa-solid fa-file-circle-plus"></i>
                    <h3>No resources uploaded yet</h3>
                    <p>Be the first to share useful academic material.</p>
                    <a href="<?= h(SITE_URL) ?>/index.php?page=upload" class="btn btn-primary">Upload Resource</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
