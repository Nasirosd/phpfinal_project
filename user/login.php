<?php
require_once __DIR__ . '/../config/database.php';
if (isset($_GET['logout'])) { unset($_SESSION['user']); session_regenerate_id(true); flash('success', 'You have signed out.'); redirect('index.php'); }
if (current_user()) redirect('user/dashboard.php');
$error = null;
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $stmt = db()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? AND is_active = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email']];
        redirect('user/dashboard.php');
    }
    $error = 'Email or password is incorrect.';
}
$pageTitle = 'Sign in';
require __DIR__ . '/../includes/header.php';
?>
<section class="auth-layout"><div class="auth-aside"><span class="eyebrow">Welcome back</span><h1>Good to have<br><em>you here.</em></h1><p>Sign in to find your bookings and plan your next stay.</p></div>
<div class="form-card"><h2>Sign in</h2><p class="muted">Use your LuxStay account details.</p><?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?><label>Email address<input type="email" name="email" autocomplete="email" required value="<?= e($email) ?>"></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="button button-dark button-full">Sign in</button></form>
<p class="form-foot">New to LuxStay? <a href="<?= e(app_url('user/register.php')) ?>">Create an account</a></p></div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
