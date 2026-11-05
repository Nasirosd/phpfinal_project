<?php
require_once __DIR__ . '/../config/database.php';
$user = require_user();
$bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT) ?: (int)($_POST['booking_id'] ?? 0);
$stmt = db()->prepare("SELECT b.id, h.name FROM bookings b JOIN rooms r ON r.id=b.room_id JOIN hotels h ON h.id=r.hotel_id WHERE b.id=? AND b.user_id=? AND b.status='confirmed' AND b.check_out<=CURRENT_DATE");
$stmt->execute([$bookingId, $user['id']]);
$booking = $stmt->fetch();
if (!$booking) { flash('error', 'Feedback is available after a confirmed stay has ended.'); redirect('booking/my_bookings.php'); }
$error = null;
$existing = db()->prepare('SELECT id FROM feedback WHERE booking_id=?');
$existing->execute([$bookingId]);
if ($existing->fetch()) { flash('error', 'Feedback has already been submitted for this booking.'); redirect('booking/my_bookings.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = trim($_POST['comment'] ?? '');
    if (!$rating || $rating < 1 || $rating > 5 || mb_strlen($comment) > 2000) $error = 'Choose a rating from 1 to 5 and keep your comment under 2,000 characters.';
    else {
        db()->prepare('INSERT INTO feedback (user_id, booking_id, rating, comment) VALUES (?, ?, ?, ?)')->execute([$user['id'], $bookingId, $rating, $comment]);
        flash('success', 'Thanks for sharing your feedback.');
        redirect('booking/my_bookings.php');
    }
}
$pageTitle = 'Share feedback';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-intro"><span class="eyebrow"><?= e($booking['name']) ?></span><h1>How was your stay?</h1><p>Your feedback helps us make every visit better.</p></section>
<div class="form-card profile-card"><?php if($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><form method="post"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int)$bookingId ?>"><label>Your rating<select name="rating" required><option value="">Select a rating</option><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> / 5<?= $i===5?' — Excellent':'' ?></option><?php endfor; ?></select></label><label>Your comments<textarea name="comment" rows="5" maxlength="2000" placeholder="Tell us about your experience..."></textarea></label><button class="button button-dark">Submit feedback</button></form></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
