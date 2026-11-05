<?php
$pageTitle = $pageTitle ?? 'Admin';
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= page_title($pageTitle) ?></title><link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>"><script src="<?= e(app_url('assets/js/app.js')) ?>" defer></script></head><body class="admin-body">
<header class="admin-header"><a class="brand" href="<?= e(app_url('admin/dashboard.php')) ?>"><span class="brand-mark">L</span> LuxStay <small>ADMIN</small></a><button class="menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">Menu</button><nav class="main-nav"><a href="<?= e(app_url('admin/dashboard.php')) ?>">Overview</a><a href="<?= e(app_url('admin/hotels.php')) ?>">Hotels</a><a href="<?= e(app_url('admin/rooms.php')) ?>">Rooms</a><a href="<?= e(app_url('admin/bookings.php')) ?>">Bookings</a><a href="<?= e(app_url('admin/payments.php')) ?>">Payments</a><a href="<?= e(app_url('admin/feedback.php')) ?>">Feedback</a><a class="nav-cta" href="<?= e(app_url('admin/login.php?logout=1')) ?>">Sign out</a></nav></header><main class="page-shell admin-shell">
<?php if ($message = flash('success')): ?><div class="notice success"><?= e($message) ?></div><?php endif; ?><?php if ($message = flash('error')): ?><div class="notice error"><?= e($message) ?></div><?php endif; ?>
