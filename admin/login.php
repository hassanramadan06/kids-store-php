<?php
require_once __DIR__ . '/../includes/init.php';
if (current_admin()) redirect('admin/index.php');

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $u = trim((string)($_POST['username'] ?? ''));
    $p = (string)($_POST['password'] ?? '');
    if (!$u || !$p) {
        $err = 'Please fill both fields.';
    } else {
        $admin = admin_login($u, $p);
        if ($admin) redirect('admin/index.php');
        $err = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — <?= e(SITE_NAME_EN) ?></title>
<link rel="icon" type="image/svg+xml" href="<?= url('assets/images/favicon.svg') ?>">
<link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="admin-login">
  <div class="admin-login__card">
    <div class="admin-login__brand">
      <svg viewBox="0 0 64 64" width="56" height="56">
        <circle cx="32" cy="32" r="30" fill="#ffd6e7"/>
        <circle cx="32" cy="28" r="11" fill="#fff"/>
        <circle cx="27" cy="27" r="2" fill="#3a2a3f"/><circle cx="37" cy="27" r="2" fill="#3a2a3f"/>
        <path d="M27 33 q5 4 10 0" stroke="#3a2a3f" stroke-width="2" fill="none" stroke-linecap="round"/>
      </svg>
      <h1>Kids Store Admin</h1>
      <p>Sign in to manage your store</p>
    </div>
    <?php if ($err): ?><div class="alert alert--err"><?= e($err) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <label>Username or Email
        <input type="text" name="username" required autofocus>
      </label>
      <label>Password
        <input type="password" name="password" required>
      </label>
      <button class="btn btn--primary btn--block btn--lg" type="submit">Sign in</button>
    </form>
    <p class="admin-login__hint">
      Default: <strong>admin</strong> / <strong>Admin@12345</strong>
    </p>
  </div>
</body>
</html>
