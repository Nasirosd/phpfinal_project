<?php
require_once __DIR__ . '/../config/database.php';
require_admin();
$stats = [
    'hotels' => (int)db()->query('SELECT COUNT(*) FROM hotels WHERE is_active=1')->fetchColumn(),
    'rooms' => (int)db()->query('SELECT COUNT(*) FROM rooms WHERE is_available=1')->fetchColumn(),
    'bookings' => (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status IN ('pending','confirmed')")->fetchColumn(),
    'revenue' => (float)db()->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'")->fetchColumn(),
];
$recent = db()->query('SELECT b.id,b.check_in,b.check_out,b.total_amount,b.status,u.name AS guest,h.name AS hotel FROM bookings b JOIN users u ON u.id=b.user_id JOIN rooms r ON r.id=b.room_id JOIN hotels h ON h.id=r.hotel_id ORDER BY b.created_at DESC LIMIT 8')->fetchAll();
$pageTitle = 'Admin dashboard';
require __DIR__ . '/_header.php';
?>
<section class="page-intro"><span class="eyebrow">Administration</span><h1>Good day, <?= e($_SESSION['admin']['username']) ?>.</h1><p>Here’s what’s happening across your properties.</p></section>
<div class="stats-grid"><div class="stat-card"><span>Active hotels</span><strong><?= $stats['hotels'] ?></strong></div><div class="stat-card"><span>Available rooms</span><strong><?= $stats['rooms'] ?></strong></div><div class="stat-card"><span>Active bookings</span><strong><?= $stats['bookings'] ?></strong></div><div class="stat-card"><span>Payments received</span><strong>$<?= number_format($stats['revenue'],2) ?></strong></div></div>
<div class="section-heading compact"><h2>Recent bookings</h2><a class="text-link" href="<?= e(app_url('admin/bookings.php')) ?>">Manage bookings →</a></div>
<?php if(!$recent): ?><div class="empty-state"><h3>No bookings yet</h3><p>Guest reservations will appear here.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Guest</th><th>Hotel</th><th>Dates</th><th>Total</th><th>Status</th></tr></thead><tbody><?php foreach($recent as $b): ?><tr><td><?= e($b['guest']) ?></td><td><?= e($b['hotel']) ?></td><td><?= e($b['check_in']) ?> – <?= e($b['check_out']) ?></td><td>$<?= number_format((float)$b['total_amount'],2) ?></td><td><span class="badge <?= e($b['status']) ?>"><?= e(ucfirst($b['status'])) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<?php require __DIR__ . '/_footer.php'; ?>
