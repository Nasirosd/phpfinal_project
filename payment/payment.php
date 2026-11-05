<?php
require_once __DIR__ . '/../config/database.php';
$user = require_user();
$bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT) ?: (int)($_POST['booking_id'] ?? 0);
$stmt = db()->prepare('SELECT b.*, r.name AS room_name, h.name AS hotel_name FROM bookings b JOIN rooms r ON r.id=b.room_id JOIN hotels h ON h.id=r.hotel_id WHERE b.id=? AND b.user_id=?');
$stmt->execute([$bookingId, $user['id']]);
$booking = $stmt->fetch();
if (!$booking || $booking['status'] === 'cancelled') { flash('error', 'This booking cannot be paid.'); redirect('booking/my_bookings.php'); }
$paymentQuery = db()->prepare('SELECT status FROM payments WHERE booking_id=?');
$paymentQuery->execute([$bookingId]);
$paymentStatus = $paymentQuery->fetchColumn();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!$paymentStatus || $paymentStatus !== 'paid') {
        $pdo = db();
        $pdo->beginTransaction();
        $pdo->prepare("INSERT INTO payments (booking_id, amount, method, status, transaction_reference, paid_at) VALUES (?, ?, 'demo', 'paid', ?, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE amount=VALUES(amount), method='demo', status='paid', transaction_reference=VALUES(transaction_reference), paid_at=CURRENT_TIMESTAMP")
            ->execute([$bookingId, $booking['total_amount'], 'LS-' . strtoupper(bin2hex(random_bytes(5)))]);
        $pdo->prepare("UPDATE bookings SET status='confirmed' WHERE id=? AND status='pending'")->execute([$bookingId]);
        $pdo->commit();
        flash('success', 'Demo payment successful. Your booking is confirmed.');
    }
    redirect('booking/my_bookings.php');
}
$pageTitle = 'Complete your payment';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-intro"><span class="eyebrow">Secure checkout</span><h1>Complete your booking.</h1><p>Review your stay before confirming.</p></section>
<div class="checkout-layout"><div class="form-card"><h2><?= e($booking['hotel_name']) ?></h2><p><?= e($booking['room_name']) ?></p><div class="summary-row"><span>Check-in</span><strong><?= e($booking['check_in']) ?></strong></div><div class="summary-row"><span>Check-out</span><strong><?= e($booking['check_out']) ?></strong></div><div class="summary-row"><span>Guests</span><strong><?= (int)$booking['guests'] ?></strong></div><div class="summary-row total-row"><span>Total</span><strong>$<?= number_format((float)$booking['total_amount'],2) ?></strong></div>
<?php if($paymentStatus==='paid'): ?><div class="notice success">This booking has already been paid.</div><a class="button button-dark" href="<?= e(app_url('booking/my_bookings.php')) ?>">View my bookings</a><?php else: ?><div class="notice">Demo checkout: no real payment details are collected or charged.</div><form method="post"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int)$bookingId ?>"><button class="button button-dark button-full">Confirm demo payment · $<?= number_format((float)$booking['total_amount'],2) ?></button></form><?php endif; ?></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
