<?php
require_once __DIR__ . '/../config/database.php';
require_admin();
$payments=db()->query('SELECT p.*,b.user_id,b.check_in,b.check_out,u.name AS guest,r.name AS room,h.name AS hotel FROM payments p JOIN bookings b ON b.id=p.booking_id JOIN users u ON u.id=b.user_id JOIN rooms r ON r.id=b.room_id JOIN hotels h ON h.id=r.hotel_id ORDER BY p.created_at DESC')->fetchAll();
$total=(float)db()->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'")->fetchColumn();
$pageTitle='Payments';require __DIR__.'/_header.php';
?>
<section class="page-intro"><span class="eyebrow">Transactions</span><h1>Payments</h1><p>Recorded payments and their related reservations.</p></section><div class="stats-grid"><div class="stat-card"><span>Total received</span><strong>$<?= number_format($total,2) ?></strong></div><div class="stat-card"><span>Transactions</span><strong><?= count($payments) ?></strong></div></div>
<?php if(!$payments): ?><div class="empty-state"><h3>No payments yet</h3><p>Payments are listed here after guests complete checkout.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Reference</th><th>Guest</th><th>Stay</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead><tbody><?php foreach($payments as $p): ?><tr><td><?= e($p['transaction_reference']?:'—') ?></td><td><?= e($p['guest']) ?></td><td><?= e($p['hotel']) ?><small><?= e($p['room']) ?> · <?= e($p['check_in']) ?> to <?= e($p['check_out']) ?></small></td><td>$<?= number_format((float)$p['amount'],2) ?></td><td><?= e(ucfirst($p['method'])) ?></td><td><span class="badge <?= e($p['status']) ?>"><?= e(ucfirst($p['status'])) ?></span></td><td><?= e($p['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<?php require __DIR__.'/_footer.php'; ?>
