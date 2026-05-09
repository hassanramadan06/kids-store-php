<?php
/** Product details page with image gallery, zoom, sizes/colors, related items. */
require_once __DIR__ . '/includes/init.php';

$slug = trim((string)($_GET['slug'] ?? ''));
if ($slug === '' && !empty($_GET['id'])) {
    $product = get_product((int)$_GET['id']);
} else {
    $product = $slug ? get_product_by_slug($slug) : null;
}

if (!$product) {
    http_response_code(404);
    $page_title = t('Not found', 'غير موجود');
    include __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:4rem 0;text-align:center;"><h1>404</h1><p>'.t('Product not found.', 'المنتج غير موجود.').'</p></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

DB::run('UPDATE products SET views = views + 1 WHERE id = ?', [(int)$product['id']]);

$images   = get_product_images((int)$product['id']);
$category = DB::one('SELECT * FROM categories WHERE id = ?', [(int)$product['category_id']]);
$section  = DB::one('SELECT * FROM sections WHERE id = ?',   [(int)$product['section_id']]);
$related  = search_products(['category_id' => (int)$product['category_id']], 1, 4)['rows'];
// Drop self from related
$related  = array_values(array_filter($related, fn($r) => (int)$r['id'] !== (int)$product['id']));

$name        = tr($product, 'name');
$description = tr($product, 'description');
$price       = (float)$product['price'];
$discount    = $product['discount_price'] !== null ? (float)$product['discount_price'] : null;
$on_sale     = $discount !== null && $discount < $price;
$final_price = $on_sale ? $discount : $price;
$off         = $on_sale ? (int) round((($price - $discount) / max($price, 0.01)) * 100) : 0;
$sizes       = array_filter(array_map('trim', explode(',', (string)($product['sizes'] ?? ''))));
$colors      = array_filter(array_map('trim', explode(',', (string)($product['colors'] ?? ''))));

$page_title       = $name;
$page_description = str_truncate($description, 160);
include __DIR__ . '/includes/header.php';
?>
<section class="page-head page-head--slim">
  <div class="container">
    <nav class="crumbs">
      <a href="<?= url('') ?>"><?= t('Home', 'الرئيسية') ?></a><span>/</span>
      <?php if ($section): ?>
        <a href="<?= url('section.php?slug=' . urlencode($section['slug'])) ?>"><?= e(tr($section, 'name')) ?></a><span>/</span>
      <?php endif; ?>
      <?php if ($category): ?>
        <a href="<?= url('category.php?slug=' . urlencode($category['slug'])) ?>"><?= e(tr($category, 'name')) ?></a><span>/</span>
      <?php endif; ?>
      <span><?= e($name) ?></span>
    </nav>
  </div>
</section>

<section class="product-detail">
  <div class="container product-detail__grid">

    <div class="product-detail__gallery js-gallery">
      <div class="gallery__main">
        <?php if ($on_sale): ?><span class="badge badge--sale">-<?= $off ?>%</span><?php endif; ?>
        <img id="js-gallery-main"
             src="<?= e(upload_url($images[0]['image_path'] ?? 'assets/images/placeholder-1.svg')) ?>"
             alt="<?= e($name) ?>" data-zoom>
      </div>
      <div class="gallery__thumbs">
        <?php foreach ($images as $i => $img): ?>
          <button type="button" class="gallery__thumb <?= $i===0?'is-active':'' ?>"
                  data-src="<?= e(upload_url($img['image_path'])) ?>"
                  aria-label="<?= e(t('Image', 'صورة')) ?> <?= $i+1 ?>">
            <img src="<?= e(upload_url($img['image_path'])) ?>" alt="">
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="product-detail__info">
      <h1 class="product-detail__title"><?= e($name) ?></h1>
      <?php if (!empty($product['sku'])): ?><div class="product-detail__sku"><?= t('SKU', 'الرمز') ?>: <?= e($product['sku']) ?></div><?php endif; ?>
      <div class="product-detail__price">
        <?php if ($on_sale): ?>
          <span class="price price--new"><?= money($discount) ?></span>
          <span class="price price--old"><?= money($price) ?></span>
          <span class="badge badge--sale">-<?= $off ?>%</span>
        <?php else: ?>
          <span class="price"><?= money($price) ?></span>
        <?php endif; ?>
      </div>

      <div class="product-detail__stock <?= ((int)$product['stock'] > 0) ? 'in' : 'out' ?>">
        <?php if ((int)$product['stock'] > 0): ?>
          <?= sprintf(t('In stock (%d available)', 'متوفر (%d قطعة)'), (int)$product['stock']) ?>
        <?php else: ?>
          <?= t('Out of stock', 'غير متوفر') ?>
        <?php endif; ?>
      </div>

      <div class="product-detail__desc"><?= nl2br(e($description)) ?></div>

      <form class="product-detail__form js-product-form" data-id="<?= (int)$product['id'] ?>" onsubmit="event.preventDefault();">
        <?php if ($sizes): ?>
          <div class="opt-group">
            <span class="opt-group__label"><?= t('Size', 'المقاس') ?></span>
            <div class="opt-pills" role="radiogroup">
              <?php foreach ($sizes as $i => $sz): ?>
                <label class="opt-pill"><input type="radio" name="size" value="<?= e($sz) ?>" <?= $i===0?'checked':'' ?>><span><?= e($sz) ?></span></label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($colors): ?>
          <div class="opt-group">
            <span class="opt-group__label"><?= t('Color', 'اللون') ?></span>
            <div class="opt-pills opt-pills--color" role="radiogroup">
              <?php foreach ($colors as $i => $clr): ?>
                <label class="opt-pill"><input type="radio" name="color" value="<?= e($clr) ?>" <?= $i===0?'checked':'' ?>><span><?= e($clr) ?></span></label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="opt-group">
          <span class="opt-group__label"><?= t('Quantity', 'الكمية') ?></span>
          <div class="qty">
            <button type="button" class="qty__btn js-qty-minus" aria-label="-">−</button>
            <input type="number" class="qty__input" name="qty" value="1" min="1" max="<?= max(1,(int)$product['stock']) ?>">
            <button type="button" class="qty__btn js-qty-plus" aria-label="+">+</button>
          </div>
        </div>

        <div class="product-detail__actions">
          <button type="button" class="btn btn--primary btn--lg js-add-cart-detail" <?= ((int)$product['stock'] > 0) ? '' : 'disabled' ?>>
            <span class="ic ic-cart" aria-hidden="true"></span>
            <?= t('Add to cart', 'أضف للسلة') ?>
          </button>
          <button type="button" class="btn btn--ghost js-wish js-wish-detail <?= wishlist_has((int)$product['id']) ? 'is-active' : '' ?>"
                  data-id="<?= (int)$product['id'] ?>">
            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 21s-7-4.5-9.3-9.1C1.4 9.4 2.7 6 6 6c2 0 3.4 1.1 4 2.5C10.6 7.1 12 6 14 6c3.3 0 4.6 3.4 3.3 5.9C19 16.5 12 21 12 21z"/></svg>
            <?= t('Wishlist', 'المفضلة') ?>
          </button>
        </div>
      </form>

      <ul class="product-perks">
        <li><span class="ic ic-truck"></span><?= t('Fast delivery 1-3 days', 'توصيل سريع 1-3 أيام') ?></li>
        <li><span class="ic ic-return"></span><?= t('Easy 14-day returns', 'استرجاع مرن خلال 14 يوم') ?></li>
        <li><span class="ic ic-shield"></span><?= t('Safe & quality-tested', 'منتجات آمنة ومختبرة') ?></li>
      </ul>
    </div>
  </div>
</section>

<?php if ($related): ?>
<section class="section section--alt">
  <div class="container">
    <header class="section__head">
      <h2 class="section__title"><?= t('You may also like', 'قد يعجبك أيضًا') ?></h2>
    </header>
    <div class="products-grid">
      <?php foreach ($related as $p) include __DIR__ . '/includes/product_card.php'; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
