<?php
if ($user) {
    header('Location: ' . SITE_URL . '/index.php?page=dashboard');
    exit;
}

$error = '';
$email = trim($_POST['email'] ?? '');
$redirect = $_GET['redirect'] ?? (SITE_URL . '/index.php?page=dashboard');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $result = preparedQuery("SELECT * FROM users WHERE email = ? LIMIT 1", 's', [$email]);
    $account = $result ? $result->fetch_assoc() : null;

    if ($account && password_verify($password, $account['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$account['id'];

        if (strpos($redirect, '/') === 0) {
            header('Location: ' . $redirect);
        } else {
            header('Location: ' . SITE_URL . '/index.php?page=dashboard');
        }
        exit;
    }

    $error = 'Invalid email or password.';
}
?>

<main class="auth-container">
    <section class="auth-card">
        <div class="auth-logo">
            <span class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></span>
            <h1>Welcome back</h1>
            <p>Log in to manage and share academic resources.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= h(SITE_URL) ?>/index.php?page=login&redirect=<?= urlencode($redirect) ?>">
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= h($email) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <button class="btn btn-primary btn-block" type="submit">Login</button>
        </form>

        <div class="auth-divider"><span>New here?</span></div>
        <a class="btn btn-outline btn-block" href="<?= h(SITE_URL) ?>/index.php?page=register">Create Account</a>
    </section>
</main>
