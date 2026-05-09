<?php
/**
 * Reusable product card. Expects $p (product row).
 * Use:  $p = $product_row; include __DIR__ . '/includes/product_card.php';
 */
$_pid    = (int) $p['id'];
$_name   = tr($p, 'name');
$_price  = (float) $p['price'];
$_disc   = $p['discount_price'] !== null ? (float) $p['discount_price'] : null;
$_onsale = $_disc !== null && $_disc < $_price;
$_off    = $_onsale ? (int) round((($_price - $_disc) / max($_price, 0.01)) * 100) : 0;
$_img    = product_primary_image($_pid);
$_inWish = wishlist_has($_pid);
?>
<article class="product-card" data-product-id="<?= $_pid ?>">
  <a class="product-card__media" href="<?= url('product.php?slug=' . urlencode($p['slug'])) ?>">
    <img src="<?= e($_img) ?>" alt="<?= e($_name) ?>" loading="lazy">
    <?php if ($_onsale): ?><span class="badge badge--sale">-<?= $_off ?>%</span><?php endif; ?>
    <?php if (!empty($p['is_featured'])): ?><span class="badge badge--feat"><?= t('Featured', 'مميز') ?></span><?php endif; ?>
    <button type="button" class="wish-btn js-wish <?= $_inWish ? 'is-active' : '' ?>"
            data-id="<?= $_pid ?>"
            aria-label="<?= e(t('Toggle wishlist', 'إضافة للمفضلة')) ?>">
      <svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 21s-7-4.5-9.3-9.1C1.4 9.4 2.7 6 6 6c2 0 3.4 1.1 4 2.5C10.6 7.1 12 6 14 6c3.3 0 4.6 3.4 3.3 5.9C19 16.5 12 21 12 21z"/></svg>
    </button>
  </a>
  <div class="product-card__body">
    <h3 class="product-card__title">
      <a href="<?= url('product.php?slug=' . urlencode($p['slug'])) ?>"><?= e($_name) ?></a>
    </h3>
    <div class="product-card__price">
      <?php if ($_onsale): ?>
        <span class="price price--new"><?= money($_disc) ?></span>
        <span class="price price--old"><?= money($_price) ?></span>
      <?php else: ?>
        <span class="price"><?= money($_price) ?></span>
      <?php endif; ?>
    </div>
    <button type="button" class="btn btn--primary btn--block js-add-cart" data-id="<?= $_pid ?>">
      <span class="ic ic-cart" aria-hidden="true"></span>
      <?= t('Add to cart', 'أضف للسلة') ?>
    </button>
  </div>
</article>
