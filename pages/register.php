<?php
if ($user) {
    header('Location: ' . SITE_URL . '/index.php?page=dashboard');
    exit;
}

$errors = [];
$values = [
    'full_name' => trim($_POST['full_name'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'role' => $_POST['role'] ?? 'student',
    'department' => trim($_POST['department'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $allowedRoles = ['student', 'faculty'];

    if ($values['full_name'] === '') $errors[] = 'Full name is required.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if (!in_array($values['role'], $allowedRoles, true)) $values['role'] = 'student';

    $existing = preparedQuery("SELECT id FROM users WHERE email = ? LIMIT 1", 's', [$values['email']]);
    if ($existing && $existing->num_rows > 0) $errors[] = 'This email is already registered.';

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ok = preparedExecute(
            "INSERT INTO users (full_name, email, password, role, department) VALUES (?, ?, ?, ?, ?)",
            'sssss',
            [$values['full_name'], $values['email'], $hash, $values['role'], $values['department']]
        );

        if ($ok) {
            $_SESSION['user_id'] = getDB()->insert_id;
            header('Location: ' . SITE_URL . '/index.php?page=dashboard');
            exit;
        }

        $errors[] = 'Could not create your account. Please try again.';
    }
}
?>

<main class="auth-container">
    <section class="auth-card">
        <div class="auth-logo">
            <span class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></span>
            <h1>Create account</h1>
            <p>Join your academic resource hub.</p>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= h(implode(' ', $errors)) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= h(SITE_URL) ?>/index.php?page=register">
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name</label>
                <input class="form-control" id="full_name" name="full_name" value="<?= h($values['full_name']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= h($values['email']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="role">Role</label>
                    <select class="form-control" id="role" name="role">
                        <option value="student" <?= $values['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                        <option value="faculty" <?= $values['role'] === 'faculty' ? 'selected' : '' ?>>Faculty</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="department">Department</label>
                    <input class="form-control" id="department" name="department" value="<?= h($values['department']) ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <button class="btn btn-primary btn-block" type="submit">Sign Up</button>
        </form>

        <div class="auth-divider"><span>Already joined?</span></div>
        <a class="btn btn-outline btn-block" href="<?= h(SITE_URL) ?>/index.php?page=login">Login</a>
    </section>
</main>
