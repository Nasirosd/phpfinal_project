<?php
require_once __DIR__ . '/../config/database.php';
$user = require_user();
$stmt = db()->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=? AND status IN ('pending','confirmed')");
$stmt->execute([$user['id']]);
$upcomingCount = (int) $stmt->fetchColumn();
$stmt = db()->prepare("SELECT b.*, r.name AS room_name, h.name AS hotel_name FROM bookings b JOIN rooms r ON r.id=b.room_id JOIN hotels h ON h.id=r.hotel_id WHERE b.user_id=? ORDER BY b.created_at DESC LIMIT 3");
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();
$pageTitle = 'Your dashboard';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-intro"><span class="eyebrow">Your LuxStay</span><h1>Welcome back, <?= e(explode(' ', $user['name'])[0]) ?>.</h1><p>Your next comfortable stay is just around the corner.</p></section>
<div class="stats-grid"><div class="stat-card"><span>Upcoming bookings</span><strong><?= $upcomingCount ?></strong></div><div class="stat-card"><span>Account email</span><strong class="stat-email"><?= e($user['email']) ?></strong></div><a class="stat-card stat-link" href="<?= e(app_url('booking/rooms.php')) ?>"><span>Find a new stay</span><strong>Explore →</strong></a></div>
<div class="section-heading compact"><h2>Recent bookings</h2><a class="text-link" href="<?= e(app_url('booking/my_bookings.php')) ?>">All bookings →</a></div>
<?php if (!$bookings): ?><div class="empty-state"><h3>No bookings yet</h3><p>Find a room and your travel plans will show up here.</p><a class="button button-dark" href="<?= e(app_url('booking/rooms.php')) ?>">Explore rooms</a></div>
<?php else: ?><div class="table-wrap"><table><thead><tr><th>Stay</th><th>Dates</th><th>Total</th><th>Status</th></tr></thead><tbody><?php foreach ($bookings as $booking): ?><tr><td><strong><?= e($booking['hotel_name']) ?></strong><small><?= e($booking['room_name']) ?></small></td><td><?= e($booking['check_in']) ?> – <?= e($booking['check_out']) ?></td><td>$<?= number_format((float)$booking['total_amount'], 2) ?></td><td><span class="badge <?= e($booking['status']) ?>"><?= e(ucfirst($booking['status'])) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
