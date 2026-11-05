<?php
require_once __DIR__ . '/../config/database.php';
$hotels = db()->query('SELECT id, name, city FROM hotels WHERE is_active=1 ORDER BY name')->fetchAll();
$hotelId = filter_input(INPUT_GET, 'hotel_id', FILTER_VALIDATE_INT) ?: 0;
$stmt = db()->prepare('SELECT r.*, h.name AS hotel_name, h.city, h.country FROM rooms r JOIN hotels h ON h.id=r.hotel_id WHERE r.is_available=1 AND h.is_active=1 AND (?=0 OR h.id=?) ORDER BY h.name, r.price_per_night');
$stmt->execute([$hotelId, $hotelId]);
$rooms = $stmt->fetchAll();
$pageTitle = 'Explore rooms';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-intro"><span class="eyebrow">Find your place</span><h1>Rooms for the way you travel.</h1><p>Browse available rooms across our hotels.</p></section>
<form class="filter-bar" method="get"><label for="hotel_id">Hotel</label><select id="hotel_id" name="hotel_id"><option value="0">All hotels</option><?php foreach($hotels as $hotel): ?><option value="<?= (int)$hotel['id'] ?>" <?= $hotelId===(int)$hotel['id']?'selected':'' ?>><?= e($hotel['name'] . ' — ' . $hotel['city']) ?></option><?php endforeach; ?></select><button class="button button-dark">Filter rooms</button></form>
<?php if (!$rooms): ?><div class="empty-state"><h3>No rooms found</h3><p>Try another hotel or check back later.</p></div><?php else: ?><div class="room-grid"><?php foreach($rooms as $room): ?><article class="room-card"><div class="room-visual" aria-hidden="true"><span>✦</span></div><div class="room-content"><p class="eyebrow"><?= e($room['hotel_name']) ?> · <?= e($room['city']) ?></p><h2><?= e($room['name']) ?></h2><p><?= e($room['description'] ?: 'A thoughtfully appointed room for a restful stay.') ?></p><div class="room-meta"><span>Up to <?= (int)$room['capacity'] ?> guests</span><strong>$<?= number_format((float)$room['price_per_night'],2) ?><small> / night</small></strong></div><a class="button button-dark button-full" href="<?= e(app_url('booking/create.php?room_id=' . (int)$room['id'])) ?>">Book this room</a></div></article><?php endforeach; ?></div><?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
