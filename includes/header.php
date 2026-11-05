<?php
$pageTitle = $pageTitle ?? APP_NAME;
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= page_title($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>">
    <script src="<?= e(app_url('assets/js/app.js')) ?>" defer></script>
</head>
<body>
<header class="site-header">
    <a class="brand" href="<?= e(app_url('index.php')) ?>"><span class="brand-mark">L</span> LuxStay</a>
    <button class="menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">Menu</button>
    <nav class="main-nav" aria-label="Main navigation">
        <a href="<?= e(app_url('booking/rooms.php')) ?>">Find a stay</a>
        <?php if ($user): ?>
            <a href="<?= e(app_url('booking/my_bookings.php')) ?>">My bookings</a>
            <a href="<?= e(app_url('user/profile.php')) ?>">Profile</a>
            <a class="nav-cta" href="<?= e(app_url('user/login.php?logout=1')) ?>">Sign out</a>
        <?php else: ?>
            <a href="<?= e(app_url('user/login.php')) ?>">Sign in</a>
            <a class="nav-cta" href="<?= e(app_url('user/register.php')) ?>">Create account</a>
        <?php endif; ?>
    </nav>
</header>
<main class="page-shell">
    <?php if ($message = flash('success')): ?><div class="notice success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?><div class="notice error"><?= e($message) ?></div><?php endif; ?>
