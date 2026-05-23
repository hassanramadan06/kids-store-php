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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="<?= url('assets/images/favicon.svg') ?>">
<link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="admin-login">
  <div class="admin-login__card">
    <div class="admin-login__brand">
      <svg viewBox="0 0 64 64" width="60" height="60">
        <path d="M52 14 a14 14 0 1 0 0 18 a10 10 0 0 1 0 -18 z" fill="#c8b6e2"/>
        <circle cx="30" cy="34" r="14" fill="#fff" stroke="#f4a6b4" stroke-width="2"/>
        <circle cx="26" cy="30" r="3.5" fill="#fff" opacity=".9"/>
        <path d="M14 18 l1.5 4 l4 1.5 l-4 1.5 l-1.5 4 l-1.5 -4 l-4 -1.5 l4 -1.5 z" fill="#efc754"/>
        <path d="M48 50 l1 2.5 l2.5 1 l-2.5 1 l-1 2.5 l-1 -2.5 l-2.5 -1 l2.5 -1 z" fill="#ed8e7c"/>
      </svg>
      <h1 class="brand-name" style="font-size:1.4rem; line-height:1.2;">
        <span style="color:#f4a6b4">L</span><span style="color:#ed8e7c">u</span><span style="color:#efc754">l</span><span style="color:#8fd5c0">u</span><span style="color:#c8b6e2">a</span><span style="color:#f3bc9e">t</span>&nbsp;<span style="color:#ed8e7c">A</span><span style="color:#efc754">l</span><span style="color:#8fd5c0">m</span><span style="color:#c8b6e2">i</span><span style="color:#f3bc9e">s</span><span style="color:#f4a6b4">b</span><span style="color:#ed8e7c">a</span><span style="color:#efc754">h</span>
      </h1>
      <p>Admin panel — sign in to manage your store</p>
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
