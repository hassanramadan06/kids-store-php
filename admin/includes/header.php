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
    <svg viewBox="0 0 64 64" width="40" height="40">
      <path d="M52 14 a14 14 0 1 0 0 18 a10 10 0 0 1 0 -18 z" fill="#c8b6e2"/>
      <circle cx="30" cy="34" r="14" fill="#fff" stroke="#f4a6b4" stroke-width="2"/>
      <circle cx="26" cy="30" r="3.5" fill="#fff" opacity=".9"/>
      <path d="M14 18 l1.5 4 l4 1.5 l-4 1.5 l-1.5 4 l-1.5 -4 l-4 -1.5 l4 -1.5 z" fill="#efc754"/>
    </svg>
    <span style="font-weight:800;">
      <span style="color:#f4a6b4">L</span><span style="color:#ed8e7c">u</span><span style="color:#efc754">l</span><span style="color:#8fd5c0">u</span><span style="color:#c8b6e2">a</span><span style="color:#f3bc9e">t</span>
    </span>
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
