<?php
/** Admin layout header. Pages set $admin_page (active key) and $admin_title. */
$admin_page  = $admin_page  ?? 'dashboard';
$admin_title = $admin_title ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin · <?= e($admin_title) ?> — <?= e(SITE_NAME_EN) ?></title>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="base-url"   content="<?= e(BASE_URL) ?>">
<link rel="icon" type="image/svg+xml" href="<?= url('assets/images/favicon.svg') ?>">
<link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="admin">
<aside class="admin__sidebar">
  <div class="admin__brand">
    <svg viewBox="0 0 64 64" width="40" height="40"><circle cx="32" cy="32" r="30" fill="#ffd6e7"/><circle cx="32" cy="28" r="11" fill="#fff"/><circle cx="27" cy="27" r="2" fill="#3a2a3f"/><circle cx="37" cy="27" r="2" fill="#3a2a3f"/><path d="M27 33 q5 4 10 0" stroke="#3a2a3f" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
    <span>Kids Store</span>
  </div>
  <nav class="admin__nav">
    <a class="<?= $admin_page === 'dashboard'  ? 'is-active' : '' ?>" href="<?= url('admin/index.php') ?>"><span class="ic ic-home"></span> Dashboard</a>
    <a class="<?= $admin_page === 'orders'     ? 'is-active' : '' ?>" href="<?= url('admin/orders.php') ?>"><span class="ic ic-cart"></span> Orders</a>
    <a class="<?= $admin_page === 'products'   ? 'is-active' : '' ?>" href="<?= url('admin/products.php') ?>"><span class="ic ic-box"></span> Products</a>
    <a class="<?= $admin_page === 'sections'   ? 'is-active' : '' ?>" href="<?= url('admin/sections.php') ?>"><span class="ic ic-grid"></span> Sections</a>
    <a class="<?= $admin_page === 'categories' ? 'is-active' : '' ?>" href="<?= url('admin/categories.php') ?>"><span class="ic ic-tag"></span> Categories</a>
    <a class="<?= $admin_page === 'offers'     ? 'is-active' : '' ?>" href="<?= url('admin/offers.php') ?>"><span class="ic ic-fire"></span> Offers</a>
    <a class="<?= $admin_page === 'users'      ? 'is-active' : '' ?>" href="<?= url('admin/users.php') ?>"><span class="ic ic-user"></span> Customers</a>
    <a class="<?= $admin_page === 'messages'   ? 'is-active' : '' ?>" href="<?= url('admin/messages.php') ?>"><span class="ic ic-mail"></span> Messages</a>
  </nav>
  <div class="admin__sidebar-footer">
    <a href="<?= url('') ?>" target="_blank">View site →</a>
    <a href="<?= url('admin/logout.php') ?>" class="link-danger">Logout</a>
  </div>
</aside>
<main class="admin__main">
  <header class="admin__topbar">
    <button class="admin__menu-btn" type="button" aria-label="Toggle menu">☰</button>
    <h1><?= e($admin_title) ?></h1>
    <div class="admin__user">
      <span><?= e($current_admin['full_name']) ?></span>
    </div>
  </header>
  <?php if ($flash_ok = flash_pop('success')): ?><div class="alert alert--ok"><?= e($flash_ok) ?></div><?php endif; ?>
  <?php if ($flash_er = flash_pop('error')):   ?><div class="alert alert--err"><?= e($flash_er) ?></div><?php endif; ?>
  <div class="admin__content">
