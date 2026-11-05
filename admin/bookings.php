<?php
require_once __DIR__ . '/../config/database.php';
require_admin();
$pdo=db();$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();$id=(int)($_POST['id']??0);$status=$_POST['status']??'';
    if(!in_array($status,['pending','confirmed','cancelled'],true))$error='Invalid booking status.';
    else{
        $payment=$pdo->prepare("SELECT status FROM payments WHERE booking_id=?");
        $payment->execute([$id]);
        if($payment->fetchColumn()==='paid' && $status!=='confirmed') $error='Paid bookings must remain confirmed here. Handle any refund through your payment provider before changing the booking.';
        else{$s=$pdo->prepare('UPDATE bookings SET status=? WHERE id=?');$s->execute([$status,$id]);flash('success','Booking status updated.');redirect('admin/bookings.php');}
    }
}
$bookings=$pdo->query('SELECT b.*,u.name AS guest,u.email,r.name AS room,h.name AS hotel,p.status AS payment_status FROM bookings b JOIN users u ON u.id=b.user_id JOIN rooms r ON r.id=b.room_id JOIN hotels h ON h.id=r.hotel_id LEFT JOIN payments p ON p.booking_id=b.id ORDER BY b.created_at DESC')->fetchAll();
$pageTitle='Manage bookings';require __DIR__.'/_header.php';
?>
<section class="page-intro"><span class="eyebrow">Reservations</span><h1>Bookings</h1><p>Review guest reservations and update their status.</p></section>
<?php if($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><div class="table-wrap"><table><thead><tr><th>Guest</th><th>Stay</th><th>Dates</th><th>Guests</th><th>Total</th><th>Payment</th><th>Status</th><th>Update</th></tr></thead><tbody><?php foreach($bookings as $b): ?><tr><td><strong><?= e($b['guest']) ?></strong><small><?= e($b['email']) ?></small></td><td><?= e($b['hotel']) ?><small><?= e($b['room']) ?></small></td><td><?= e($b['check_in']) ?><small>to <?= e($b['check_out']) ?></small></td><td><?= (int)$b['guests'] ?></td><td>$<?= number_format((float)$b['total_amount'],2) ?></td><td><?= e(ucfirst($b['payment_status']??'unpaid')) ?></td><td><span class="badge <?= e($b['status']) ?>"><?= e(ucfirst($b['status'])) ?></span></td><td><form method="post" class="status-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><select name="status"><?php foreach(['pending','confirmed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $b['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select><button class="text-button">Save</button></form></td></tr><?php endforeach; ?></tbody></table></div>
<?php require __DIR__.'/_footer.php'; ?>
