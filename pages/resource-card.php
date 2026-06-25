<?php
$fileType = $resource['file_type'] ?? '';
$fileUrl = resourceFileUrl($resource);
$isBookmarked = !empty($resource['is_bookmarked']);
?>
<article class="card resource-card">
    <div class="resource-card-header">
        <div class="file-icon-wrap <?= h(getFileTone($fileType)) ?>">
            <i class="fa-solid <?= h(getFileIcon($fileType)) ?>"></i>
        </div>
        <div>
            <h3 class="resource-title">
                <a href="<?= h($fileUrl) ?>" target="_blank" rel="noopener"><?= h($resource['title']) ?></a>
            </h3>
            <div class="resource-meta">
                <span><i class="fa-solid fa-folder"></i> <?= h($resource['category_name'] ?? 'General') ?></span>
                <span><i class="fa-solid fa-clock"></i> <?= h(timeAgo($resource['created_at'] ?? null)) ?></span>
            </div>
        </div>
    </div>
    <div class="resource-card-body">
        <p class="resource-description"><?= h($resource['description'] ?? 'No description added.') ?></p>
        <?php if (!empty($resource['subject']) || !empty($resource['semester'])): ?>
            <div class="tags mt-2">
                <?php if (!empty($resource['subject'])): ?><span class="tag"><?= h($resource['subject']) ?></span><?php endif; ?>
                <?php if (!empty($resource['semester'])): ?><span class="tag"><?= h($resource['semester']) ?></span><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="resource-card-footer">
        <div>
            <?= renderStars($resource['avg_rating'] ?? 0) ?>
            <small class="text-muted">by <?= h($resource['uploader_name'] ?? 'Unknown') ?></small>
        </div>
        <div class="resource-actions">
            <?php if (getCurrentUser()): ?>
                <button class="action-btn bookmark-btn <?= $isBookmarked ? 'bookmarked' : '' ?>" type="button" data-id="<?= (int)$resource['id'] ?>" title="Bookmark">
                    <i class="<?= $isBookmarked ? 'fa-solid' : 'fa-regular' ?> fa-bookmark"></i>
                </button>
            <?php endif; ?>
            <a class="action-btn" href="<?= h($fileUrl) ?>" target="_blank" rel="noopener" title="Open file">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </a>
        </div>
    </div>
</article>
