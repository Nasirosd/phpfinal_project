<?php
require_once __DIR__ . '/../config/database.php';
$user = require_user();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid name and email address.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email=? AND id<>?');
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) $error = 'That email address is already in use.';
        else {
            $stmt = db()->prepare('UPDATE users SET name=?, email=? WHERE id=?');
            $stmt->execute([$name, $email, $user['id']]);
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            flash('success', 'Your profile has been updated.');
            redirect('user/profile.php');
        }
    }
} else { $name = $user['name']; $email = $user['email']; }
$pageTitle = 'Your profile';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-intro"><span class="eyebrow">Account settings</span><h1>Your profile</h1><p>Keep your contact details up to date.</p></section>
<div class="form-card profile-card"><?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><form method="post"><?= csrf_field() ?><label>Full name<input name="name" required maxlength="100" value="<?= e($name) ?>"></label><label>Email address<input type="email" name="email" required value="<?= e($email) ?>"></label><button class="button button-dark">Save changes</button></form></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
