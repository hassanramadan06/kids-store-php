<?php
require __DIR__ . '/includes/bootstrap.php';

$product = null;
if (!empty($_GET['id'])) {
    $product = DB::one('SELECT * FROM products WHERE id = ?', [(int)$_GET['id']]);
    if (!$product) redirect('admin/products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $section_id    = (int)($_POST['section_id'] ?? 0);
    $category_id   = (int)($_POST['category_id'] ?? 0);
    $name_ar       = trim((string)($_POST['name_ar'] ?? ''));
    $name_en       = trim((string)($_POST['name_en'] ?? ''));
    $slug          = trim((string)($_POST['slug']    ?? '')) ?: slugify($name_en ?: $name_ar);
    $sku           = trim((string)($_POST['sku']     ?? ''));
    $description_ar= trim((string)($_POST['description_ar'] ?? ''));
    $description_en= trim((string)($_POST['description_en'] ?? ''));
    $price         = (float)($_POST['price'] ?? 0);
    $discount      = $_POST['discount_price'] !== '' ? (float)$_POST['discount_price'] : null;
    $stock         = (int)($_POST['stock'] ?? 0);
    $sizes         = trim((string)($_POST['sizes'] ?? ''));
    $colors        = trim((string)($_POST['colors'] ?? ''));
    $is_featured   = empty($_POST['is_featured']) ? 0 : 1;
    $is_active     = empty($_POST['is_active'])   ? 0 : 1;

    if (!$section_id || !$category_id || !$name_ar || !$name_en || $price < 0) {
        flash_set('error', 'Please fill all required fields correctly.');
    } else {
        if ($product) {
            DB::run(
                'UPDATE products SET section_id=?, category_id=?, name_ar=?, name_en=?, slug=?, sku=?,
                    description_ar=?, description_en=?, price=?, discount_price=?, stock=?, sizes=?, colors=?,
                    is_featured=?, is_active=?
                 WHERE id=?',
                [
                    $section_id, $category_id, $name_ar, $name_en, $slug, $sku,
                    $description_ar, $description_en, $price, $discount, $stock,
                    $sizes ?: null, $colors ?: null, $is_featured, $is_active,
                    (int)$product['id'],
                ]
            );
            $product_id = (int)$product['id'];
        } else {
            DB::run(
                'INSERT INTO products (section_id, category_id, name_ar, name_en, slug, sku,
                                       description_ar, description_en, price, discount_price, stock, sizes, colors,
                                       is_featured, is_active)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $section_id, $category_id, $name_ar, $name_en, $slug, $sku,
                    $description_ar, $description_en, $price, $discount, $stock,
                    $sizes ?: null, $colors ?: null, $is_featured, $is_active,
                ]
            );
            $product_id = (int) DB::lastId();
        }

        // Up to 4 images
        if (!empty($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
            $existing = (int) DB::scalar('SELECT COUNT(*) FROM product_images WHERE product_id = ?', [$product_id]);
            $primary  = (int) DB::scalar('SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_primary = 1', [$product_id]);
            $slots = max(0, 4 - $existing);
            for ($i = 0; $i < count($_FILES['images']['name']) && $slots > 0; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                $f = [
                    'name'     => $_FILES['images']['name'][$i],
                    'type'     => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i],
                ];
                $rel = save_uploaded_image($f, 'products');
                if ($rel) {
                    $is_primary = ($primary === 0 && $existing === 0 && $slots === (4 - $existing)) ? 1 : 0;
                    DB::run(
                        'INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?,?,?,?)',
                        [$product_id, $rel, $is_primary, 4 - $slots + 1]
                    );
                    $slots--;
                    if ($is_primary) $primary = 1;
                }
            }
        }

        flash_set('success', $product ? 'Product updated.' : 'Product created.');
        redirect('admin/product_form.php?id=' . $product_id);
    }
}

$sections   = DB::all('SELECT id, name_en FROM sections ORDER BY sort_order');
$categories = DB::all('SELECT id, name_en, section_id FROM categories ORDER BY name_en');
$images     = $product ? DB::all('SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC', [(int)$product['id']]) : [];

$admin_page  = 'products';
$admin_title = $product ? ('Edit · ' . $product['name_en']) : 'New product';
include __DIR__ . '/includes/header.php';
?>
<form method="post" enctype="multipart/form-data" class="form form--wide">
  <?= csrf_field() ?>
  <div class="grid-2">
    <section class="panel">
      <header class="panel__head"><h2>Basic info</h2></header>
      <div class="panel__body">
        <label>Section *
          <select name="section_id" id="js-section" required>
            <option value="">— Choose —</option>
            <?php foreach ($sections as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= ((int)($product['section_id']??0)===(int)$s['id']) ? 'selected' : '' ?>><?= e($s['name_en']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Category *
          <select name="category_id" id="js-category" required>
            <option value="">— Choose —</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int)$c['id'] ?>" data-section="<?= (int)$c['section_id'] ?>"
                <?= ((int)($product['category_id']??0)===(int)$c['id']) ? 'selected' : '' ?>>
                <?= e($c['name_en']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Name (Arabic) *
          <input type="text" name="name_ar" required dir="rtl" value="<?= e($product['name_ar'] ?? '') ?>">
        </label>
        <label>Name (English) *
          <input type="text" name="name_en" required value="<?= e($product['name_en'] ?? '') ?>">
        </label>
        <label>Slug
          <input type="text" name="slug" placeholder="auto from name" value="<?= e($product['slug'] ?? '') ?>">
        </label>
        <label>SKU
          <input type="text" name="sku" value="<?= e($product['sku'] ?? '') ?>">
        </label>
        <label>Description (Arabic)
          <textarea name="description_ar" rows="4" dir="rtl"><?= e($product['description_ar'] ?? '') ?></textarea>
        </label>
        <label>Description (English)
          <textarea name="description_en" rows="4"><?= e($product['description_en'] ?? '') ?></textarea>
        </label>
      </div>
    </section>

    <section class="panel">
      <header class="panel__head"><h2>Pricing & inventory</h2></header>
      <div class="panel__body">
        <div class="row">
          <label>Price * <input type="number" step="0.01" min="0" name="price" required value="<?= e((string)($product['price'] ?? 0)) ?>"></label>
          <label>Discount price <input type="number" step="0.01" min="0" name="discount_price" value="<?= e((string)($product['discount_price'] ?? '')) ?>"></label>
          <label>Stock <input type="number" min="0" name="stock" value="<?= e((string)($product['stock'] ?? 0)) ?>"></label>
        </div>
        <label>Sizes (comma separated)
          <input type="text" name="sizes" placeholder="3M,6M,12M,2Y" value="<?= e($product['sizes'] ?? '') ?>">
        </label>
        <label>Colors (comma separated)
          <input type="text" name="colors" placeholder="Pink,Blue,White" value="<?= e($product['colors'] ?? '') ?>">
        </label>
        <label class="inline">
          <input type="checkbox" name="is_active" value="1" <?= empty($product) || !empty($product['is_active']) ? 'checked' : '' ?>>
          Active (visible in store)
        </label>
        <label class="inline">
          <input type="checkbox" name="is_featured" value="1" <?= !empty($product['is_featured']) ? 'checked' : '' ?>>
          Featured (homepage)
        </label>
      </div>
    </section>
  </div>

  <section class="panel">
    <header class="panel__head"><h2>Images (up to 4)</h2></header>
    <div class="panel__body">
      <?php if ($images): ?>
        <div class="image-grid">
          <?php foreach ($images as $img): ?>
            <div class="image-grid__item">
              <img src="<?= e(upload_url($img['image_path'])) ?>" alt="">
              <?php if ($img['is_primary']): ?><span class="pill pill--feat">Primary</span><?php endif; ?>
              <form method="post" onsubmit="return confirm('Remove image?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_image">
                <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                <button class="btn btn--xs btn--danger" type="submit">×</button>
              </form>
            </div>
          <?php endforeach; ?>
          <?php for ($i = count($images); $i < 4; $i++): ?>
            <div class="image-grid__item image-grid__item--empty">slot <?= $i+1 ?></div>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
      <label>Upload images (multiple, JPG/PNG/WEBP, max 8MB each, up to <?= 4 - count($images) ?> remaining)
        <input type="file" name="images[]" accept="image/*" multiple>
      </label>
    </div>
  </section>

  <div class="form__actions form__actions--sticky">
    <button class="btn btn--primary btn--lg" type="submit"><?= $product ? 'Save changes' : 'Create product' ?></button>
    <a class="btn btn--ghost" href="<?= url('admin/products.php') ?>">Cancel</a>
    <?php if ($product): ?>
      <a class="btn btn--ghost" target="_blank" href="<?= url('product.php?slug=' . urlencode($product['slug'])) ?>">View on storefront →</a>
    <?php endif; ?>
  </div>
</form>

<script>
// Filter category select to match selected section
(function () {
  var s = document.getElementById('js-section');
  var c = document.getElementById('js-category');
  if (!s || !c) return;
  function refresh() {
    var sec = s.value;
    Array.from(c.options).forEach(function (o) {
      if (!o.value) { o.hidden = false; return; }
      o.hidden = sec && o.dataset.section !== sec;
    });
    if (c.selectedOptions[0] && c.selectedOptions[0].hidden) c.value = '';
  }
  s.addEventListener('change', refresh);
  refresh();
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
