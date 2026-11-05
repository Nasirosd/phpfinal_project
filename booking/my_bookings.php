<?php
require_once __DIR__ . '/../config/database.php';
$user = require_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $cancel = db()->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=? AND status='pending' AND NOT EXISTS (SELECT 1 FROM payments WHERE payments.booking_id=bookings.id AND payments.status='paid')");
    $cancel->execute([$bookingId, $user['id']]);
    if ($cancel->rowCount() === 1) flash('success', 'Your pending booking has been cancelled.');
    else flash('error', 'Only unpaid pending bookings can be cancelled here.');
    redirect('booking/my_bookings.php');
}
$stmt = db()->prepare('SELECT b.*, r.name AS room_name, h.name AS hotel_name, h.city, p.status AS payment_status FROM bookings b JOIN rooms r ON r.id=b.room_id JOIN hotels h ON h.id=r.hotel_id LEFT JOIN payments p ON p.booking_id=b.id WHERE b.user_id=? ORDER BY b.created_at DESC');
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();
$pageTitle = 'My bookings';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-intro"><span class="eyebrow">Your travel plans</span><h1>My bookings</h1><p>All your stays, together in one place.</p></section>
<?php if (!$bookings): ?><div class="empty-state"><h3>Nothing booked yet</h3><p>When you book a stay, you’ll find it here.</p><a class="button button-dark" href="<?= e(app_url('booking/rooms.php')) ?>">Browse rooms</a></div><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Stay</th><th>Dates</th><th>Guests</th><th>Total</th><th>Status</th><th>Payment</th><th></th></tr></thead><tbody>
<?php foreach($bookings as $booking): ?><tr><td><strong><?= e($booking['hotel_name']) ?></strong><small><?= e($booking['room_name']) ?> · <?= e($booking['city']) ?></small></td><td><?= e($booking['check_in']) ?><small>to <?= e($booking['check_out']) ?></small></td><td><?= (int)$booking['guests'] ?></td><td>$<?= number_format((float)$booking['total_amount'],2) ?></td><td><span class="badge <?= e($booking['status']) ?>"><?= e(ucfirst($booking['status'])) ?></span></td><td><?= e(ucfirst($booking['payment_status']??'unpaid')) ?></td><td><?php if($booking['status']==='pending' && $booking['payment_status']!=='paid'): ?><a class="text-link" href="<?= e(app_url('payment/payment.php?booking_id='.(int)$booking['id'])) ?>">Pay now</a><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>"><button class="text-button">Cancel</button></form><?php endif; ?><?php if($booking['status']==='confirmed'): ?><a class="text-link" href="<?= e(app_url('feedback/create.php?booking_id='.(int)$booking['id'])) ?>">Leave feedback</a><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
