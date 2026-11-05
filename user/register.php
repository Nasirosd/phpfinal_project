<?php
require_once __DIR__ . '/../config/database.php';
if (current_user()) redirect('user/dashboard.php');
$errors = [];
$name = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Enter a name between 2 and 100 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'An account already exists for that email.';
        else {
            $stmt = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int) db()->lastInsertId(), 'name' => $name, 'email' => $email];
            flash('success', 'Welcome to LuxStay! Your account is ready.');
            redirect('user/dashboard.php');
        }
    }
}
$pageTitle = 'Create your account';
require __DIR__ . '/../includes/header.php';
?>
<section class="auth-layout"><div class="auth-aside"><span class="eyebrow">Your next trip awaits</span><h1>Make yourself<br><em>at home.</em></h1><p>Create an account to book stays and keep all your travel plans together.</p></div>
<div class="form-card"><h2>Create account</h2><p class="muted">It only takes a moment.</p>
<?php foreach ($errors as $error): ?><div class="notice error"><?= e($error) ?></div><?php endforeach; ?>
<form method="post"><?= csrf_field() ?><label>Full name<input name="name" autocomplete="name" required maxlength="100" value="<?= e($name) ?>"></label><label>Email address<input type="email" name="email" autocomplete="email" required value="<?= e($email) ?>"></label><label>Password<input type="password" name="password" autocomplete="new-password" minlength="8" required><small>Use at least 8 characters.</small></label><button class="button button-dark button-full">Create account</button></form>
<p class="form-foot">Already have an account? <a href="<?= e(app_url('user/login.php')) ?>">Sign in</a></p></div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
