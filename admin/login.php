<?php
require_once __DIR__ . '/../config/database.php';
if (isset($_GET['logout'])) { unset($_SESSION['admin']); session_regenerate_id(true); flash('success', 'Administrator signed out.'); redirect('admin/login.php'); }
if (!empty($_SESSION['admin'])) redirect('admin/dashboard.php');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('SELECT id, username, password_hash FROM admins WHERE username=?');
    $stmt->execute([trim($_POST['username'] ?? '')]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($_POST['password'] ?? '', $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = ['id'=>(int)$admin['id'], 'username'=>$admin['username']];
        redirect('admin/dashboard.php');
    }
    $error = 'Username or password is incorrect.';
}
$pageTitle = 'Administrator sign in';
require __DIR__ . '/../includes/header.php';
?>
<section class="auth-layout"><div class="auth-aside"><span class="eyebrow">Staff portal</span><h1>Manage the<br><em>LuxStay experience.</em></h1><p>Sign in to manage properties, rooms, reservations, payments, and guest feedback.</p></div><div class="form-card"><h2>Administrator sign in</h2><?php if($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><form method="post"><?= csrf_field() ?><label>Username<input name="username" autocomplete="username" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="button button-dark button-full">Sign in</button></form><p class="form-foot">Initial login: <strong>admin</strong> / <strong>admin123</strong>. Change this password in the database before deployment.</p></div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
