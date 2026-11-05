<?php
require_once __DIR__ . '/../config/database.php';
require_admin();
$pdo=db(); $error=null; $edit=null;
$hotels=$pdo->query('SELECT id,name FROM hotels ORDER BY name')->fetchAll();
if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM rooms WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf(); $action=$_POST['action']??'';
    if($action==='save'){
        $id=(int)($_POST['id']??0);$hotelId=(int)($_POST['hotel_id']??0);$name=trim($_POST['name']??'');$description=trim($_POST['description']??'');$capacity=(int)($_POST['capacity']??0);$price=(float)($_POST['price']??0);
        if($name===''||$capacity<1||$capacity>20||$price<=0){$error='Enter a room name, capacity from 1 to 20, and a positive nightly price.';}
        else{
            if($id)$s=$pdo->prepare('UPDATE rooms SET hotel_id=?,name=?,description=?,capacity=?,price_per_night=? WHERE id=?');
            else $s=$pdo->prepare('INSERT INTO rooms (hotel_id,name,description,capacity,price_per_night) VALUES (?,?,?,?,?)');
            $v=[$hotelId,$name,$description,$capacity,$price];if($id)$v[]=$id;$s->execute($v);flash('success',$id?'Room updated.':'Room added.');redirect('admin/rooms.php');
        }
    } elseif($action==='toggle'){$pdo->prepare('UPDATE rooms SET is_available=1-is_available WHERE id=?')->execute([(int)$_POST['id']]);flash('success','Room availability updated.');redirect('admin/rooms.php');}
    if(isset($_POST['id'])){$s=$pdo->prepare('SELECT * FROM rooms WHERE id=?');$s->execute([(int)$_POST['id']]);$edit=$s->fetch();}
}
$rooms=$pdo->query('SELECT r.*,h.name AS hotel_name FROM rooms r JOIN hotels h ON h.id=r.hotel_id ORDER BY r.id DESC')->fetchAll();
$pageTitle='Manage rooms';require __DIR__.'/_header.php';
?>
<section class="page-intro"><span class="eyebrow">Accommodation</span><h1>Rooms</h1><p>Set room details, nightly rates, and availability.</p></section>
<div class="admin-columns"><section class="form-card"><h2><?= $edit?'Edit room':'Add a room' ?></h2><?php if($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><?php if(!$hotels): ?><p>Add a hotel before creating rooms.</p><?php else: ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>"><label>Hotel<select name="hotel_id" required><?php foreach($hotels as $hotel): ?><option value="<?= (int)$hotel['id'] ?>" <?= (int)($edit['hotel_id']??0)===(int)$hotel['id']?'selected':'' ?>><?= e($hotel['name']) ?></option><?php endforeach; ?></select></label><label>Room name<input name="name" required maxlength="120" value="<?= e($edit['name']??'') ?>"></label><label>Description<textarea name="description" rows="3"><?= e($edit['description']??'') ?></textarea></label><div class="form-row"><label>Guest capacity<input type="number" name="capacity" min="1" max="20" required value="<?= (int)($edit['capacity']??2) ?>"></label><label>Price per night ($)<input type="number" name="price" min="0.01" step="0.01" required value="<?= e($edit['price_per_night']??'') ?>"></label></div><button class="button button-dark"><?= $edit?'Save room':'Add room' ?></button><?php if($edit): ?> <a class="text-link" href="<?= e(app_url('admin/rooms.php')) ?>">Cancel</a><?php endif; ?></form><?php endif; ?></section>
<section><div class="section-heading compact"><h2>All rooms</h2><span><?= count($rooms) ?> total</span></div><div class="table-wrap"><table><thead><tr><th>Room</th><th>Rate</th><th>Availability</th><th>Actions</th></tr></thead><tbody><?php foreach($rooms as $room): ?><tr><td><strong><?= e($room['name']) ?></strong><small><?= e($room['hotel_name']) ?> · <?= (int)$room['capacity'] ?> guests</small></td><td>$<?= number_format((float)$room['price_per_night'],2) ?></td><td><span class="badge <?= $room['is_available']?'confirmed':'cancelled' ?>"><?= $room['is_available']?'Available':'Hidden' ?></span></td><td><a class="text-link" href="<?= e(app_url('admin/rooms.php?edit='.(int)$room['id'])) ?>">Edit</a><form class="inline-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$room['id'] ?>"><button class="text-button"><?= $room['is_available']?'Hide':'Enable' ?></button></form></td></tr><?php endforeach; ?></tbody></table></div></section></div>
<?php require __DIR__.'/_footer.php'; ?>
