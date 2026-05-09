<?php
/**
 * Site-wide page header. Pages should set:
 *   $page_title       (optional)  -- string
 *   $page_description (optional)  -- meta description
 *   $body_class       (optional)  -- extra body class
 * before including this file.
 */
require_once __DIR__ . '/init.php';

$page_title       = $page_title       ?? $site_name;
$page_description = $page_description ?? t('Children clothing & baby products store', 'متجر ملابس الأطفال ومستلزمات حديثي الولادة');

$nav_sections = get_sections();
foreach ($nav_sections as &$_s) {
    $_s['categories'] = get_categories((int)$_s['id']);
}
unset($_s);
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>" dir="<?= $rtl ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= e($page_description) ?>">
<meta name="theme-color" content="#ffd6e7">
<title><?= e($page_title) ?> — <?= e($site_name) ?></title>

<link rel="icon" type="image/svg+xml" href="<?= url('assets/images/favicon.svg') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">

<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="base-url"   content="<?= e(BASE_URL) ?>">
</head>
<body class="<?= $rtl ? 'rtl' : 'ltr' ?> <?= e($body_class ?? '') ?>" data-lang="<?= e($lang) ?>">

<!-- Top bar ------------------------------------------------------ -->
<div class="topbar">
  <div class="container topbar__inner">
    <div class="topbar__msg">
      <i class="ic ic-truck" aria-hidden="true"></i>
      <span><?= t('Free shipping on orders over 1000 EGP', 'شحن مجاني للطلبات فوق 1000 ج.م') ?></span>
    </div>
    <div class="topbar__links">
      <a href="<?= url('?lang=' . ($rtl ? 'en' : 'ar')) ?>" class="lang-toggle">
        <?= $rtl ? 'EN' : 'العربية' ?>
      </a>
      <?php if (current_user()): ?>
        <a href="<?= url('account.php') ?>"><i class="ic ic-user"></i> <?= e(current_user()['full_name']) ?></a>
        <a href="<?= url('logout.php') ?>"><?= t('Logout', 'تسجيل الخروج') ?></a>
      <?php else: ?>
        <a href="<?= url('login.php') ?>"><?= t('Login', 'دخول') ?></a>
        <a href="<?= url('register.php') ?>"><?= t('Register', 'تسجيل') ?></a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Main header -------------------------------------------------- -->
<header class="site-header">
  <div class="container site-header__inner">
    <a href="<?= url('') ?>" class="logo" aria-label="<?= e($site_name) ?>">
      <span class="logo__mark" aria-hidden="true">
        <svg viewBox="0 0 64 64" width="44" height="44">
          <circle cx="32" cy="32" r="30" fill="#ffd6e7"/>
          <circle cx="32" cy="28" r="11" fill="#fff"/>
          <circle cx="27" cy="27" r="2" fill="#3a2a3f"/>
          <circle cx="37" cy="27" r="2" fill="#3a2a3f"/>
          <path d="M27 33 q5 4 10 0" stroke="#3a2a3f" stroke-width="2" fill="none" stroke-linecap="round"/>
          <path d="M19 49 q13 -10 26 0 v6 H19 z" fill="#a7e8d8"/>
        </svg>
      </span>
      <span class="logo__text">
        <strong><?= e($site_name) ?></strong>
        <small><?= t('Kids fashion & baby essentials', 'ملابس الأطفال ومستلزمات الرضع') ?></small>
      </span>
    </a>

    <form class="search" action="<?= url('search.php') ?>" method="get" role="search">
      <input type="search" name="q" placeholder="<?= e(t('Search for products…', 'ابحث عن منتجات…')) ?>"
             value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off" aria-label="<?= e(t('Search', 'بحث')) ?>">
      <button type="submit" aria-label="<?= e(t('Search', 'بحث')) ?>">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
          <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/>
          <line x1="16" y1="16" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>
      <ul class="search__suggest" hidden></ul>
    </form>

    <div class="header-actions">
      <a class="header-btn" href="<?= url('wishlist.php') ?>" aria-label="<?= e(t('Wishlist', 'المفضلة')) ?>">
        <svg viewBox="0 0 24 24" width="22" height="22"><path d="M12 21s-7-4.5-9.3-9.1C1.4 9.4 2.7 6 6 6c2 0 3.4 1.1 4 2.5C10.6 7.1 12 6 14 6c3.3 0 4.6 3.4 3.3 5.9C19 16.5 12 21 12 21z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
        <span class="badge js-wishlist-count"><?= (int) wishlist_count() ?></span>
      </a>
      <a class="header-btn" href="<?= url('cart.php') ?>" aria-label="<?= e(t('Cart', 'السلة')) ?>">
        <svg viewBox="0 0 24 24" width="22" height="22"><path d="M3 4h2l2 12h11l2-8H7" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="9" cy="20" r="1.5" fill="currentColor"/><circle cx="17" cy="20" r="1.5" fill="currentColor"/></svg>
        <span class="badge js-cart-count"><?= (int) cart_count() ?></span>
      </a>
      <button class="hamburger" aria-label="<?= e(t('Menu', 'القائمة')) ?>" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>

  <!-- Main navigation ------------------------------------------- -->
  <nav class="main-nav" aria-label="<?= e(t('Main navigation', 'القائمة الرئيسية')) ?>">
    <div class="container">
      <ul class="nav-list">
        <li><a href="<?= url('') ?>"><?= t('Home', 'الرئيسية') ?></a></li>
        <?php foreach ($nav_sections as $sec): ?>
          <li class="nav-item has-mega">
            <a href="<?= url('section.php?slug=' . urlencode($sec['slug'])) ?>">
              <?= e(tr($sec, 'name')) ?>
              <?php if (!empty($sec['categories'])): ?>
                <svg class="nav-caret" viewBox="0 0 12 12" width="10" height="10" aria-hidden="true"><path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" stroke-width="2"/></svg>
              <?php endif; ?>
            </a>
            <?php if (!empty($sec['categories'])): ?>
              <div class="mega" role="menu">
                <ul>
                  <?php foreach ($sec['categories'] as $c): ?>
                    <li>
                      <a href="<?= url('category.php?slug=' . urlencode($c['slug'])) ?>">
                        <?= e(tr($c, 'name')) ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
                <a class="mega__cta" href="<?= url('section.php?slug=' . urlencode($sec['slug'])) ?>">
                  <?= t('Browse all', 'عرض الكل') ?> →
                </a>
              </div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
        <li><a href="<?= url('about.php') ?>"><?= t('About', 'من نحن') ?></a></li>
        <li><a href="<?= url('contact.php') ?>"><?= t('Contact', 'تواصل') ?></a></li>
      </ul>
    </div>
  </nav>
</header>

<!-- Toast container --------------------------------------------- -->
<div id="toast-stack" class="toast-stack" aria-live="polite"></div>

<!-- Flash messages ---------------------------------------------- -->
<?php
$_flash_ok  = flash_pop('success');
$_flash_err = flash_pop('error');
if ($_flash_ok || $_flash_err):
?>
<div class="container" style="margin-top:1rem;">
  <?php if ($_flash_ok): ?>
    <div class="alert alert--ok"><?= e($_flash_ok) ?></div>
  <?php endif; ?>
  <?php if ($_flash_err): ?>
    <div class="alert alert--err"><?= e($_flash_err) ?></div>
  <?php endif; ?>
</div>
<?php endif; ?>

<main class="site-main">
