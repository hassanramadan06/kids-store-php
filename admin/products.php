<?php
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        // Drop image files first
        foreach (DB::all('SELECT image_path FROM product_images WHERE product_id = ?', [$id]) as $img) {
            delete_upload($img['image_path']);
        }
        DB::run('DELETE FROM products WHERE id = ?', [$id]);
        flash_set('success', 'Product deleted.');
        redirect('admin/products.php');
    }
    if ($action === 'toggle_featured') {
        $id = (int)$_POST['id'];
        DB::run('UPDATE products SET is_featured = 1 - is_featured WHERE id = ?', [$id]);
        flash_set('success', 'Featured flag toggled.');
        redirect('admin/products.php');
    }
    if ($action === 'toggle_active') {
        $id = (int)$_POST['id'];
        DB::run('UPDATE products SET is_active = 1 - is_active WHERE id = ?', [$id]);
        flash_set('success', 'Visibility toggled.');
        redirect('admin/products.php');
    }
    if ($action === 'delete_image') {
        $img_id = (int)$_POST['image_id'];
        $row = DB::one('SELECT * FROM product_images WHERE id = ?', [$img_id]);
        if ($row) {
            delete_upload($row['image_path']);
            DB::run('DELETE FROM product_images WHERE id = ?', [$img_id]);
        }
        redirect('admin/product_form.php?id=' . (int)($_POST['product_id'] ?? 0));
    }
}

// Filtering
$q          = trim((string)($_GET['q'] ?? ''));
$section_id = (int)($_GET['section_id'] ?? 0);
$page       = max(1, (int)($_GET['page'] ?? 1));
$per_page   = 15;

$where = ['1=1'];
$params = [];
if ($q !== '') { $where[] = '(p.name_en LIKE ? OR p.name_ar LIKE ? OR p.sku LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%"); }
if ($section_id > 0) { $where[] = 'p.section_id = ?'; $params[] = $section_id; }
$wsql  = 'WHERE ' . implode(' AND ', $where);

$total  = (int) DB::scalar("SELECT COUNT(*) FROM products p $wsql", $params);
$pages  = max(1, (int) ceil($total / $per_page));
$offset = ($page - 1) * $per_page;
$rows = DB::all(
    "SELECT p.*, s.name_en AS section_name, c.name_en AS category_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS thumb
     FROM products p
     JOIN sections s ON s.id = p.section_id
     JOIN categories c ON c.id = p.category_id
     $wsql
     ORDER BY p.id DESC
     LIMIT $per_page OFFSET $offset",
    $params
);

$sections = DB::all('SELECT id, name_en FROM sections ORDER BY sort_order');

$admin_page  = 'products';
$admin_title = 'Products';
include __DIR__ . '/includes/header.php';
?>
<section class="panel">
  <header class="panel__head">
    <form method="get" class="filterbar">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search products…">
      <select name="section_id">
        <option value="0">All sections</option>
        <?php foreach ($sections as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= $section_id===(int)$s['id']?'selected':'' ?>><?= e($s['name_en']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn--ghost" type="submit">Filter</button>
    </form>
    <a class="btn btn--primary" href="<?= url('admin/product_form.php') ?>">+ New product</a>
  </header>
  <div class="panel__body">
    <table class="data-table data-table--products">
      <thead>
        <tr>
          <th></th>
          <th>Product</th>
          <th>Section / Category</th>
          <th class="num">Price</th>
          <th class="num">Stock</th>
          <th>Flags</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r):
          $img = $r['thumb'] ? upload_url($r['thumb']) : url('assets/images/placeholder-1.svg');
        ?>
        <tr>
          <td><img class="row-thumb" src="<?= e($img) ?>" alt=""></td>
          <td>
            <strong><?= e($r['name_en']) ?></strong><br>
            <small class="muted" dir="rtl"><?= e($r['name_ar']) ?></small>
            <?php if (!empty($r['sku'])): ?><br><small class="muted">SKU: <?= e($r['sku']) ?></small><?php endif; ?>
          </td>
          <td><?= e($r['section_name']) ?><br><small class="muted"><?= e($r['category_name']) ?></small></td>
          <td class="num">
            <?php if ($r['discount_price'] !== null && $r['discount_price'] < $r['price']): ?>
              <strong><?= number_format((float)$r['discount_price'], 2) ?></strong>
              <s class="muted"><?= number_format((float)$r['price'], 2) ?></s>
            <?php else: ?>
              <strong><?= number_format((float)$r['price'], 2) ?></strong>
            <?php endif; ?>
          </td>
          <td class="num">
            <span class="<?= ((int)$r['stock'] < 5) ? 'danger' : '' ?>"><?= (int)$r['stock'] ?></span>
          </td>
          <td>
            <?= $r['is_active']   ? '<span class="pill pill--ok">Active</span>'   : '<span class="pill pill--off">Hidden</span>' ?>
            <?= $r['is_featured'] ? '<span class="pill pill--feat">Featured</span>' : '' ?>
          </td>
          <td class="row-actions">
            <a class="btn btn--xs btn--ghost" href="<?= url('admin/product_form.php?id=' . (int)$r['id']) ?>">Edit</a>
            <form method="post" class="inline-form">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle_featured">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn--xs" type="submit">★</button>
            </form>
            <form method="post" class="inline-form">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle_active">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn--xs" type="submit">👁</button>
            </form>
            <form method="post" class="inline-form" onsubmit="return confirm('Delete product?')">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn--xs btn--danger" type="submit">×</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="7" class="muted">No products yet — create one with the button above.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php if ($pages > 1): ?>
      <nav class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++):
          $qs = $_GET; $qs['page'] = $i;
        ?>
          <a href="?<?= e(http_build_query($qs)) ?>" class="<?= $i === $page ? 'is-active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
