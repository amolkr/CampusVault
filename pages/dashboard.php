<?php
requireLogin();
$user = getCurrentUser();

$stats = [
    'uploads' => 0,
    'bookmarks' => 0,
    'downloads' => 0,
    'rating' => 0,
];

$result = preparedQuery("SELECT COUNT(*) AS total, COALESCE(SUM(download_count), 0) AS downloads FROM resources WHERE uploader_id = ?", 'i', [(int)$user['id']]);
if ($result) {
    $row = $result->fetch_assoc();
    $stats['uploads'] = (int)$row['total'];
    $stats['downloads'] = (int)$row['downloads'];
}

$result = preparedQuery("SELECT COUNT(*) AS total FROM bookmarks WHERE user_id = ?", 'i', [(int)$user['id']]);
if ($result) $stats['bookmarks'] = (int)$result->fetch_assoc()['total'];

$result = preparedQuery("
    SELECT COALESCE(AVG(rt.rating), 0) AS rating
    FROM ratings rt
    INNER JOIN resources r ON r.id = rt.resource_id
    WHERE r.uploader_id = ?
", 'i', [(int)$user['id']]);
if ($result) $stats['rating'] = round((float)$result->fetch_assoc()['rating'], 1);

$recent = preparedQuery("
    SELECT r.*, c.name AS category_name,
        (SELECT COALESCE(AVG(rt.rating), 0) FROM ratings rt WHERE rt.resource_id = r.id) AS avg_rating
    FROM resources r
    LEFT JOIN categories c ON c.id = r.category_id
    WHERE r.uploader_id = ?
    ORDER BY r.created_at DESC
    LIMIT 8
", 'i', [(int)$user['id']]);
?>

<main class="main-container">
    <div class="dashboard-head">
        <div>
            <h1 class="section-title">Dashboard</h1>
            <p class="section-subtitle">Welcome, <?= h($user['full_name']) ?>. Track your uploads and saved resources.</p>
        </div>
        <a href="<?= h(SITE_URL) ?>/index.php?page=upload" class="btn btn-primary">
            <i class="fa-solid fa-upload"></i> Upload Resource
        </a>
    </div>

    <section class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fa-solid fa-folder-open"></i></div>
            <div>
                <div class="stat-value"><?= number_format($stats['uploads']) ?></div>
                <div class="stat-label">Uploads</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fa-solid fa-download"></i></div>
            <div>
                <div class="stat-value"><?= number_format($stats['downloads']) ?></div>
                <div class="stat-label">Downloads</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="fa-solid fa-bookmark"></i></div>
            <div>
                <div class="stat-value"><?= number_format($stats['bookmarks']) ?></div>
                <div class="stat-label">Bookmarks</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-star"></i></div>
            <div>
                <div class="stat-value"><?= h($stats['rating']) ?></div>
                <div class="stat-label">Average Rating</div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="section-title compact-title">Recent Uploads</h2>
            <a href="<?= h(SITE_URL) ?>/index.php?page=my-resources" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <?php if ($recent && $recent->num_rows): ?>
                <table>
                    <thead>
                    <tr>
                        <th>Resource</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Downloads</th>
                        <th>Rating</th>
                        <th>Uploaded</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($item = $recent->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="table-resource">
                                    <span class="file-icon-wrap <?= h(getFileTone($item['file_type'])) ?>"><i class="fa-solid <?= h(getFileIcon($item['file_type'])) ?>"></i></span>
                                    <a href="<?= h(resourceFileUrl($item)) ?>"><?= h($item['title']) ?></a>
                                </div>
                            </td>
                            <td><?= h($item['category_name'] ?? 'General') ?></td>
                            <td><span class="badge badge-<?= $item['status'] === 'approved' ? 'success' : ($item['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= h(ucfirst($item['status'])) ?></span></td>
                            <td><?= (int)$item['download_count'] ?></td>
                            <td><?= h(round((float)$item['avg_rating'], 1)) ?></td>
                            <td><?= h(timeAgo($item['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <h3>No uploads yet</h3>
                    <p>Share your first file and it will appear here.</p>
                    <a href="<?= h(SITE_URL) ?>/index.php?page=upload" class="btn btn-primary">Upload Resource</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
