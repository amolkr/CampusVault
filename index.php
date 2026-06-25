<?php
require_once __DIR__ . '/config.php';

$user = getCurrentUser();
$page = $_GET['page'] ?? 'home';

$routes = [
    'home' => __DIR__ . '/pages/home.php',
    'browse' => __DIR__ . '/pages/browse.php',
    'search' => __DIR__ . '/pages/browse.php',
    'login' => __DIR__ . '/pages/login.php',
    'register' => __DIR__ . '/pages/register.php',
    'dashboard' => __DIR__ . '/pages/dashboard.php',
    'upload' => __DIR__ . '/pages/upload.php',
    'my-resources' => __DIR__ . '/pages/browse.php',
    'bookmarks' => __DIR__ . '/pages/browse.php',
    'profile' => __DIR__ . '/pages/dashboard.php',
];

if (!isset($routes[$page])) {
    http_response_code(404);
    $page = 'home';
}

$avatarInitial = $user ? strtoupper(substr(trim($user['full_name']), 0, 1)) : '';
$profilePic = $user['profile_pic'] ?? '';
$profilePicPath = __DIR__ . '/uploads/' . $profilePic;
$hasProfilePic = $profilePic && is_file($profilePicPath);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h(SITE_NAME) ?> - Academic Resource Sharing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h(SITE_URL) ?>/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a class="nav-brand" href="<?= h(SITE_URL) ?>/index.php">
            <span class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></span>
            <span class="brand-text">Acad<span class="accent">Share</span></span>
        </a>

        <div class="nav-search">
            <form action="<?= h(SITE_URL) ?>/index.php" method="GET">
                <input type="hidden" name="page" value="search">
                <div class="search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="q" placeholder="Search notes, papers, books..." value="<?= sanitize($_GET['q'] ?? '') ?>">
                    <button type="submit">Search</button>
                </div>
            </form>
        </div>

        <div class="nav-links">
            <a href="<?= h(SITE_URL) ?>/index.php?page=browse" class="nav-link <?= in_array($page, ['browse', 'search'], true) ? 'active' : '' ?>">Browse</a>
            <?php if ($user): ?>
                <a href="<?= h(SITE_URL) ?>/index.php?page=upload" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-upload"></i> Upload
                </a>
                <div class="nav-user">
                    <button class="user-avatar-btn" type="button" onclick="toggleUserMenu()" aria-expanded="false" aria-controls="userMenu">
                        <?php if ($hasProfilePic): ?>
                            <img src="<?= h(SITE_URL) ?>/uploads/<?= h($profilePic) ?>" alt="Avatar" class="user-avatar">
                        <?php else: ?>
                            <span class="user-avatar user-avatar-fallback"><?= h($avatarInitial) ?></span>
                        <?php endif; ?>
                        <span><?= h(explode(' ', trim($user['full_name']))[0]) ?></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="user-menu" id="userMenu">
                        <a href="<?= h(SITE_URL) ?>/index.php?page=dashboard"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                        <a href="<?= h(SITE_URL) ?>/index.php?page=my-resources"><i class="fa-solid fa-folder-open"></i> My Resources</a>
                        <a href="<?= h(SITE_URL) ?>/index.php?page=bookmarks"><i class="fa-solid fa-bookmark"></i> Bookmarks</a>
                        <a href="<?= h(SITE_URL) ?>/index.php?page=profile"><i class="fa-solid fa-user-gear"></i> Profile</a>
                        <hr>
                        <a href="<?= h(SITE_URL) ?>/logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= h(SITE_URL) ?>/index.php?page=login" class="btn btn-outline btn-sm">Login</a>
                <a href="<?= h(SITE_URL) ?>/index.php?page=register" class="btn btn-primary btn-sm">Sign Up</a>
            <?php endif; ?>
        </div>

        <button class="hamburger" type="button" onclick="toggleMobileNav()" aria-label="Open navigation">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</nav>

<div class="mobile-nav" id="mobileNav">
    <a href="<?= h(SITE_URL) ?>/index.php?page=browse">Browse</a>
    <?php if ($user): ?>
        <a href="<?= h(SITE_URL) ?>/index.php?page=dashboard">Dashboard</a>
        <a href="<?= h(SITE_URL) ?>/index.php?page=upload">Upload Resource</a>
        <a href="<?= h(SITE_URL) ?>/index.php?page=my-resources">My Resources</a>
        <a href="<?= h(SITE_URL) ?>/logout.php">Logout</a>
    <?php else: ?>
        <a href="<?= h(SITE_URL) ?>/index.php?page=login">Login</a>
        <a href="<?= h(SITE_URL) ?>/index.php?page=register">Sign Up</a>
    <?php endif; ?>
</div>

<?php require $routes[$page]; ?>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <span class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></span>
            <span class="brand-text">Acad<span class="accent">Share</span></span>
            <p>Centralised platform for sharing academic resources among students and faculty.</p>
        </div>
        <div class="footer-links">
            <h4>Quick Links</h4>
            <a href="<?= h(SITE_URL) ?>/index.php?page=browse">Browse Resources</a>
            <a href="<?= h(SITE_URL) ?>/index.php?page=upload">Upload</a>
            <a href="<?= h(SITE_URL) ?>/index.php?page=register">Join Now</a>
        </div>
        <div class="footer-categories">
            <h4>Categories</h4>
            <?php
            $cats = getDB()->query("SELECT * FROM categories LIMIT 5");
            if ($cats):
                while ($cat = $cats->fetch_assoc()):
            ?>
            <a href="<?= h(SITE_URL) ?>/index.php?page=browse&category=<?= (int)$cat['id'] ?>">
                <i class="fa-solid <?= h($cat['icon']) ?>"></i> <?= h($cat['name']) ?>
            </a>
            <?php
                endwhile;
            endif;
            ?>
        </div>
        <div class="footer-info">
            <h4>Tech Stack</h4>
            <p><i class="fa-brands fa-html5"></i> HTML, CSS, JavaScript</p>
            <p><i class="fa-brands fa-php"></i> PHP Backend</p>
            <p><i class="fa-solid fa-database"></i> MySQL Database</p>
            <p><i class="fa-solid fa-server"></i> XAMPP Server</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> AcadShare. Built for students, by students.</p>
    </div>
</footer>

<script>
window.SITE_URL = "<?= h(SITE_URL) ?>";
</script>
<script src="<?= h(SITE_URL) ?>/js/main.js"></script>
</body>
</html>
