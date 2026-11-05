<?php
require_once __DIR__ . '/config/database.php';
$pageTitle = 'Find your next stay';
$hotels = db()->query("SELECT h.*, (SELECT MIN(r.price_per_night) FROM rooms r WHERE r.hotel_id=h.id AND r.is_available=1) AS starting_price FROM hotels h WHERE h.is_active=1 ORDER BY h.id DESC LIMIT 6")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">A better kind of getaway</span>
        <h1>Stay somewhere<br><em>worth remembering.</em></h1>
        <p>Discover welcoming hotels, considered rooms, and effortless booking—all in one place.</p>
        <a class="button button-light" href="<?= e(app_url('booking/rooms.php')) ?>">Explore available rooms <span aria-hidden="true">→</span></a>
    </div>
    <div class="hero-art" aria-hidden="true"><img class="hero-illustration" src="<?= e(app_url('assets/images/hero-stay.svg')) ?>" alt=""></div>
</section>
<section class="section">
    <div class="section-heading"><div><span class="eyebrow">A place for every plan</span><h2>Our featured hotels</h2></div><a class="text-link" href="<?= e(app_url('booking/rooms.php')) ?>">View all stays →</a></div>
    <?php if (!$hotels): ?>
        <div class="empty-state"><h3>Your next stay is taking shape</h3><p>No hotels have been added yet. Check back soon.</p></div>
    <?php else: ?>
        <div class="hotel-grid">
        <?php foreach ($hotels as $hotel): ?>
            <article class="hotel-card">
                <div class="hotel-art" aria-hidden="true"><span><?= e(strtoupper(substr($hotel['name'], 0, 1))) ?></span></div>
                <div class="hotel-card-body"><p class="eyebrow"><?= e($hotel['city']) ?><?= $hotel['country'] ? ', ' . e($hotel['country']) : '' ?></p><h3><?= e($hotel['name']) ?></h3><p><?= e($hotel['description'] ?: 'A comfortable place to make yourself at home.') ?></p>
                <div class="card-bottom"><span><?= $hotel['starting_price'] !== null ? 'From $' . number_format((float) $hotel['starting_price'], 0) . ' / night' : 'Rooms coming soon' ?></span><a href="<?= e(app_url('booking/rooms.php?hotel_id=' . (int) $hotel['id'])) ?>" aria-label="View rooms at <?= e($hotel['name']) ?>">View rooms →</a></div></div>
            </article>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<section class="callout"><div><span class="eyebrow">Ready when you are</span><h2>Your next great stay starts here.</h2></div><a class="button button-dark" href="<?= e(app_url('booking/rooms.php')) ?>">Browse rooms</a></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
