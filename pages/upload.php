<?php
requireLogin();
$user = getCurrentUser();
$db = getDB();
$categories = $db->query("SELECT * FROM categories ORDER BY name");
$errors = [];
$success = '';

$values = [
    'title' => trim($_POST['title'] ?? ''),
    'description' => trim($_POST['description'] ?? ''),
    'category_id' => (int)($_POST['category_id'] ?? 0),
    'subject' => trim($_POST['subject'] ?? ''),
    'semester' => trim($_POST['semester'] ?? ''),
    'tags' => trim($_POST['tags'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($values['title'] === '') $errors[] = 'Title is required.';
    if (empty($_FILES['resource_file']['name'])) $errors[] = 'Choose a file to upload.';

    if (!$errors) {
        $file = $_FILES['resource_file'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, ALLOWED_TYPES, true)) {
            $errors[] = 'This file type is not allowed.';
        }

        if ((int)$file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File size must be 20 MB or less.';
        }

        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'The upload failed. Please try again.';
        }

        if (!$errors) {
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0775, true);
            }

            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '-', pathinfo($file['name'], PATHINFO_FILENAME));
            $storedName = uniqid('resource_', true) . '-' . $safeName . '.' . $extension;
            $target = UPLOAD_DIR . $storedName;

            if (move_uploaded_file($file['tmp_name'], $target)) {
                $ok = preparedExecute(
                    "INSERT INTO resources (title, description, file_path, file_type, file_size, category_id, uploader_id, subject, semester, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    'ssssiiisss',
                    [
                        $values['title'],
                        $values['description'],
                        $storedName,
                        $extension,
                        (int)$file['size'],
                        $values['category_id'] ?: null,
                        (int)$user['id'],
                        $values['subject'],
                        $values['semester'],
                        $values['tags'],
                    ]
                );

                if ($ok) {
                    $success = 'Resource uploaded successfully.';
                    $values = ['title' => '', 'description' => '', 'category_id' => 0, 'subject' => '', 'semester' => '', 'tags' => ''];
                } else {
                    $errors[] = 'Could not save the resource details.';
                }
            } else {
                $errors[] = 'Could not move the uploaded file.';
            }
        }
    }
}
?>

<main class="main-container narrow-container">
    <div class="section-header">
        <div>
            <h1 class="section-title">Upload Resource</h1>
            <p class="section-subtitle">Add notes, papers, manuals, presentations, and other useful study files.</p>
        </div>
    </div>

    <section class="card">
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success alert-auto-dismiss"><i class="fa-solid fa-circle-check"></i> <?= h($success) ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= h(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" action="<?= h(SITE_URL) ?>/index.php?page=upload">
                <div class="form-group">
                    <label class="form-label" for="title">Title</label>
                    <input class="form-control" id="title" name="title" value="<?= h($values['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description"><?= h($values['description']) ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">Choose category</option>
                            <?php if ($categories): ?>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?= (int)$cat['id'] ?>" <?= $values['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                                        <?= h($cat['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="subject">Subject</label>
                        <input class="form-control" id="subject" name="subject" value="<?= h($values['subject']) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="semester">Semester</label>
                        <input class="form-control" id="semester" name="semester" value="<?= h($values['semester']) ?>" placeholder="e.g. Sem 3">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tagInput">Tags</label>
                        <input class="form-control" id="tagInput" placeholder="Type a tag and press Enter">
                        <input type="hidden" id="tagsHidden" name="tags" value="<?= h($values['tags']) ?>">
                        <div class="tags mt-1" id="tagsContainer"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">File</label>
                    <label class="upload-zone" id="uploadZone">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p>Click or drag a file here</p>
                        <span class="form-hint">PDF, DOC, PPT, XLS, TXT, ZIP, PNG, JPG up to 20 MB</span>
                        <input type="file" id="fileInput" name="resource_file" required>
                    </label>
                    <div id="filePreview" class="mt-2"></div>
                </div>
                <button class="btn btn-primary" type="submit">
                    <i class="fa-solid fa-upload"></i> Save Resource
                </button>
            </form>
        </div>
    </section>
</main>
