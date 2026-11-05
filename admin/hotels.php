<?php
require_once __DIR__ . '/../config/database.php';
require_admin();
$pdo = db();
$error = null;
$edit = null;
if (isset($_GET['edit'])) {
    $stmt=$pdo->prepare('SELECT * FROM hotels WHERE id=?'); $stmt->execute([(int)$_GET['edit']]); $edit=$stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action=$_POST['action'] ?? '';
    if ($action==='save') {
        $id=(int)($_POST['id'] ?? 0); $name=trim($_POST['name'] ?? ''); $city=trim($_POST['city'] ?? '');
        $country=trim($_POST['country'] ?? ''); $address=trim($_POST['address'] ?? ''); $description=trim($_POST['description'] ?? '');
        if (mb_strlen($name)<2 || mb_strlen($name)>150 || $city==='' || mb_strlen($city)>100) $error='Hotel name and city are required.';
        else {
            if ($id) $stmt=$pdo->prepare('UPDATE hotels SET name=?,city=?,country=?,address=?,description=? WHERE id=?');
            else $stmt=$pdo->prepare('INSERT INTO hotels (name,city,country,address,description) VALUES (?,?,?,?,?)');
            $values=[$name,$city,$country,$address,$description]; if($id) $values[]=$id; $stmt->execute($values);
            flash('success',$id?'Hotel updated.':'Hotel added.'); redirect('admin/hotels.php');
        }
    } elseif ($action==='toggle') {
        $pdo->prepare('UPDATE hotels SET is_active=1-is_active WHERE id=?')->execute([(int)$_POST['id']]);
        flash('success','Hotel visibility updated.'); redirect('admin/hotels.php');
    }
    if (isset($_POST['id'])) { $stmt=$pdo->prepare('SELECT * FROM hotels WHERE id=?'); $stmt->execute([(int)$_POST['id']]); $edit=$stmt->fetch(); }
}
$hotels=$pdo->query('SELECT h.*, (SELECT COUNT(*) FROM rooms r WHERE r.hotel_id=h.id) AS room_count FROM hotels h ORDER BY h.id DESC')->fetchAll();
$pageTitle='Manage hotels';
require __DIR__ . '/_header.php';
?>
<section class="page-intro"><span class="eyebrow">Properties</span><h1>Hotels</h1><p>Add and maintain the properties guests can book.</p></section>
<div class="admin-columns"><section class="form-card"><h2><?= $edit?'Edit hotel':'Add a hotel' ?></h2><?php if($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>"><label>Hotel name<input name="name" required maxlength="150" value="<?= e($edit['name']??'') ?>"></label><div class="form-row"><label>City<input name="city" required value="<?= e($edit['city']??'') ?>"></label><label>Country<input name="country" value="<?= e($edit['country']??'') ?>"></label></div><label>Address<input name="address" value="<?= e($edit['address']??'') ?>"></label><label>Description<textarea name="description" rows="3"><?= e($edit['description']??'') ?></textarea></label><button class="button button-dark"><?= $edit?'Save hotel':'Add hotel' ?></button><?php if($edit): ?> <a class="text-link" href="<?= e(app_url('admin/hotels.php')) ?>">Cancel</a><?php endif; ?></form></section>
<section><div class="section-heading compact"><h2>All hotels</h2><span><?= count($hotels) ?> total</span></div><div class="table-wrap"><table><thead><tr><th>Hotel</th><th>Rooms</th><th>Visibility</th><th>Actions</th></tr></thead><tbody><?php foreach($hotels as $hotel): ?><tr><td><strong><?= e($hotel['name']) ?></strong><small><?= e($hotel['city']) ?><?= $hotel['country']?', '.e($hotel['country']):'' ?></small></td><td><?= (int)$hotel['room_count'] ?></td><td><span class="badge <?= $hotel['is_active']?'confirmed':'cancelled' ?>"><?= $hotel['is_active']?'Active':'Hidden' ?></span></td><td><a class="text-link" href="<?= e(app_url('admin/hotels.php?edit='.(int)$hotel['id'])) ?>">Edit</a><form class="inline-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$hotel['id'] ?>"><button class="text-button"><?= $hotel['is_active']?'Hide':'Activate' ?></button></form></td></tr><?php endforeach; ?></tbody></table></div></section></div>
<?php require __DIR__ . '/_footer.php'; ?>
