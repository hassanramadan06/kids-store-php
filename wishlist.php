<?php
require_once __DIR__ . '/includes/init.php';
$items = wishlist_items();
$page_title = t('Wishlist', 'المفضلة');
include __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="container"><h1><?= e($page_title) ?></h1></div></section>
<section class="section">
  <div class="container">
    <?php if (empty($items)): ?>
      <div class="empty">
        <h3><?= t('Your wishlist is empty.', 'قائمة المفضلة فارغة.') ?></h3>
        <a class="btn btn--primary" href="<?= url('shop.php') ?>"><?= t('Find products you love', 'ابحث عن منتجاتك المفضلة') ?></a>
      </div>
    <?php else: ?>
      <div class="products-grid">
        <?php foreach ($items as $p) include __DIR__ . '/includes/product_card.php'; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
