<?php
require_once __DIR__ . '/../config/database.php';
require_admin();
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();db()->prepare('DELETE FROM feedback WHERE id=?')->execute([(int)($_POST['id']??0)]);flash('success','Feedback removed.');redirect('admin/feedback.php');}
$feedback=db()->query('SELECT f.*,u.name AS guest,h.name AS hotel,r.name AS room FROM feedback f JOIN users u ON u.id=f.user_id JOIN bookings b ON b.id=f.booking_id JOIN rooms r ON r.id=b.room_id JOIN hotels h ON h.id=r.hotel_id ORDER BY f.created_at DESC')->fetchAll();
$pageTitle='Guest feedback';require __DIR__.'/_header.php';
?>
<section class="page-intro"><span class="eyebrow">Guest experience</span><h1>Feedback</h1><p>Read and moderate feedback submitted by guests.</p></section>
<?php if(!$feedback): ?><div class="empty-state"><h3>No feedback yet</h3><p>Guest reviews will appear here after completed stays.</p></div><?php else: ?><div class="feedback-grid"><?php foreach($feedback as $item): ?><article class="feedback-card"><div class="feedback-rating" aria-label="<?= (int)$item['rating'] ?> out of 5"><?= str_repeat('★',(int)$item['rating']) . str_repeat('☆',5-(int)$item['rating']) ?></div><blockquote><?= e($item['comment']?:'No written comment.') ?></blockquote><p><strong><?= e($item['guest']) ?></strong> · <?= e($item['hotel']) ?>, <?= e($item['room']) ?></p><small><?= e($item['created_at']) ?></small><form method="post" class="delete-form" onsubmit="return confirm('Remove this feedback?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button class="text-button danger-text">Remove</button></form></article><?php endforeach; ?></div><?php endif; ?>
<?php require __DIR__.'/_footer.php'; ?>
