<?php
/** Cart page. */
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    if (isset($_POST['update']) && is_array($_POST['update'])) {
        foreach ($_POST['update'] as $cid => $qty) {
            cart_update((int)$cid, (int)$qty);
        }
        flash_set('success', t('Cart updated.', 'تم تحديث السلة.'));
    }
    if (!empty($_POST['remove'])) {
        cart_remove((int)$_POST['remove']);
        flash_set('success', t('Item removed.', 'تم حذف العنصر.'));
    }
    if (!empty($_POST['clear'])) {
        cart_clear();
        flash_set('success', t('Cart cleared.', 'تم إفراغ السلة.'));
    }
    redirect('cart.php');
}

$totals    = cart_totals();
$page_title = t('Shopping cart', 'سلة التسوق');
include __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="container"><h1><?= e($page_title) ?></h1></div></section>

<section class="cart-page">
  <div class="container">
    <?php if (empty($totals['items'])): ?>
      <div class="empty">
        <h3><?= t('Your cart is empty.', 'سلتك فارغة.') ?></h3>
        <a class="btn btn--primary" href="<?= url('shop.php') ?>"><?= t('Continue shopping', 'متابعة التسوق') ?></a>
      </div>
    <?php else: ?>
      <form method="post" class="cart-grid">
        <?= csrf_field() ?>

        <div class="cart-table">
          <div class="cart-table__head">
            <span><?= t('Product', 'المنتج') ?></span>
            <span><?= t('Price', 'السعر') ?></span>
            <span><?= t('Qty', 'الكمية') ?></span>
            <span><?= t('Total', 'الإجمالي') ?></span>
            <span></span>
          </div>
          <?php foreach ($totals['items'] as $it):
              $price = ($it['discount_price'] !== null && $it['discount_price'] < $it['price']) ? (float)$it['discount_price'] : (float)$it['price'];
              $line  = $price * (int)$it['quantity'];
          ?>
          <div class="cart-row">
            <div class="cart-row__product">
              <img src="<?= e(product_primary_image((int)$it['product_id'])) ?>" alt="">
              <div>
                <a href="<?= url('product.php?slug=' . urlencode($it['slug'])) ?>"><strong><?= e(tr($it, 'name')) ?></strong></a>
                <small>
                  <?php if (!empty($it['size'])): ?><?= t('Size', 'المقاس') ?>: <?= e($it['size']) ?><?php endif; ?>
                  <?php if (!empty($it['color'])): ?><?= t('Color', 'اللون') ?>: <?= e($it['color']) ?><?php endif; ?>
                </small>
              </div>
            </div>
            <div class="cart-row__price"><?= money($price) ?></div>
            <div class="cart-row__qty">
              <input type="number" name="update[<?= (int)$it['id'] ?>]" value="<?= (int)$it['quantity'] ?>" min="0">
            </div>
            <div class="cart-row__total"><?= money($line) ?></div>
            <div class="cart-row__remove">
              <button type="submit" name="remove" value="<?= (int)$it['id'] ?>" class="link-danger" aria-label="<?= e(t('Remove', 'حذف')) ?>">×</button>
            </div>
          </div>
          <?php endforeach; ?>

          <div class="cart-table__footer">
            <button type="submit" name="clear" value="1" class="btn btn--ghost"><?= t('Clear cart', 'إفراغ السلة') ?></button>
            <button type="submit" class="btn btn--primary"><?= t('Update cart', 'تحديث السلة') ?></button>
          </div>
        </div>

        <aside class="cart-summary">
          <h3><?= t('Order summary', 'ملخص الطلب') ?></h3>
          <div class="cart-summary__row"><span><?= t('Subtotal', 'الإجمالي الفرعي') ?></span><strong><?= money($totals['subtotal']) ?></strong></div>
          <div class="cart-summary__row"><span><?= t('Shipping', 'الشحن') ?></span><strong><?= $totals['shipping'] > 0 ? money($totals['shipping']) : t('Free', 'مجاني') ?></strong></div>
          <div class="cart-summary__row cart-summary__total"><span><?= t('Total', 'الإجمالي') ?></span><strong><?= money($totals['total']) ?></strong></div>
          <a class="btn btn--primary btn--block btn--lg" href="<?= url('checkout.php') ?>"><?= t('Proceed to checkout', 'إتمام الشراء') ?></a>
          <a class="btn btn--ghost btn--block" href="<?= url('shop.php') ?>"><?= t('Continue shopping', 'متابعة التسوق') ?></a>
        </aside>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
