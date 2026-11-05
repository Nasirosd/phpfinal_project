<?php
require_once __DIR__ . '/../config/database.php';
$user = require_user();
$roomId = filter_input(INPUT_GET, 'room_id', FILTER_VALIDATE_INT) ?: (int)($_POST['room_id'] ?? 0);
$stmt = db()->prepare('SELECT r.*, h.name AS hotel_name, h.city FROM rooms r JOIN hotels h ON h.id=r.hotel_id WHERE r.id=? AND r.is_available=1 AND h.is_active=1');
$stmt->execute([$roomId]);
$room = $stmt->fetch();
if (!$room) { flash('error', 'That room is not available.'); redirect('booking/rooms.php'); }
$errors = [];
$checkIn = $_POST['check_in'] ?? '';
$checkOut = $_POST['check_out'] ?? '';
$guests = (int)($_POST['guests'] ?? 1);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!valid_date_range($checkIn, $checkOut)) $errors[] = 'Choose a future check-in date and a later check-out date.';
    if ($guests < 1 || $guests > (int)$room['capacity']) $errors[] = 'Guest count must fit this room (maximum ' . (int)$room['capacity'] . ').';
    if (!$errors) {
        $nights = (int)(new DateTimeImmutable($checkIn))->diff(new DateTimeImmutable($checkOut))->days;
        $pdo = db();
        $pdo->beginTransaction();
        $lock = $pdo->prepare('SELECT id FROM rooms WHERE id=? AND is_available=1 FOR UPDATE');
        $lock->execute([$roomId]);
        $overlap = $pdo->prepare("SELECT id FROM bookings WHERE room_id=? AND status IN ('pending','confirmed') AND check_in < ? AND check_out > ? LIMIT 1");
        $overlap->execute([$roomId, $checkOut, $checkIn]);
        if (!$lock->fetch() || $overlap->fetch()) {
            $pdo->rollBack();
            $errors[] = 'This room is no longer available for those dates. Please choose another date.';
        } else {
            $total = $nights * (float)$room['price_per_night'];
            $insert = $pdo->prepare('INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, total_amount) VALUES (?, ?, ?, ?, ?, ?)');
            $insert->execute([$user['id'], $roomId, $checkIn, $checkOut, $guests, $total]);
            $bookingId = (int)$pdo->lastInsertId();
            $pdo->commit();
            redirect('payment/payment.php?booking_id=' . $bookingId);
        }
    }
}
$pageTitle = 'Book your stay';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-intro"><span class="eyebrow"><?= e($room['hotel_name']) ?> · <?= e($room['city']) ?></span><h1>Make it your stay.</h1><p><?= e($room['name']) ?> · $<?= number_format((float)$room['price_per_night'],2) ?> per night · Up to <?= (int)$room['capacity'] ?> guests</p></section>
<div class="form-card booking-form"><?php foreach($errors as $error): ?><div class="notice error"><?= e($error) ?></div><?php endforeach; ?><form method="post"><?= csrf_field() ?><input type="hidden" name="room_id" value="<?= (int)$roomId ?>"><div class="form-row"><label>Check-in<input type="date" name="check_in" min="<?= e(date('Y-m-d', strtotime('+1 day'))) ?>" required value="<?= e($checkIn) ?>"></label><label>Check-out<input type="date" name="check_out" min="<?= e(date('Y-m-d', strtotime('+2 day'))) ?>" required value="<?= e($checkOut) ?>"></label></div><label>Guests<select name="guests"><?php for($i=1;$i<=(int)$room['capacity'];$i++): ?><option value="<?= $i ?>" <?= $guests===$i?'selected':'' ?>><?= $i ?> guest<?= $i>1?'s':'' ?></option><?php endfor; ?></select></label><p class="muted">Your total will be calculated from the selected dates. Payment is recorded as a demo transaction.</p><button class="button button-dark">Continue to payment</button></form></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
