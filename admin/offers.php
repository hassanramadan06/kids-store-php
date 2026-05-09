<?php
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id          = (int)($_POST['id'] ?? 0);
        $title_ar    = trim((string)($_POST['title_ar'] ?? ''));
        $title_en    = trim((string)($_POST['title_en'] ?? ''));
        $desc_ar     = trim((string)($_POST['description_ar'] ?? ''));
        $desc_en     = trim((string)($_POST['description_en'] ?? ''));
        $discount    = (float)($_POST['discount_percent'] ?? 0);
        $start       = $_POST['start_date'] ?: null;
        $end         = $_POST['end_date']   ?: null;
        $link        = trim((string)($_POST['link_url'] ?? ''));
        $active      = empty($_POST['is_active']) ? 0 : 1;

        if (!$title_ar || !$title_en) {
            flash_set('error', 'Title (AR + EN) is required.');
            redirect('admin/offers.php');
        }
        $img = null;
        if (!empty($_FILES['image']['name'])) $img = save_uploaded_image($_FILES['image'], 'offers');

        if ($id > 0) {
            $sql = 'UPDATE offers SET title_ar=?, title_en=?, description_ar=?, description_en=?,
                    discount_percent=?, start_date=?, end_date=?, link_url=?, is_active=?'
                 . ($img ? ', image=?' : '') . ' WHERE id = ?';
            $params = [$title_ar, $title_en, $desc_ar, $desc_en, $discount, $start, $end, $link, $active];
            if ($img) $params[] = $img;
            $params[] = $id;
            DB::run($sql, $params);
            flash_set('success', 'Offer updated.');
        } else {
            DB::run('INSERT INTO offers (title_ar, title_en, description_ar, description_en, image, discount_percent, link_url, start_date, end_date, is_active)
                     VALUES (?,?,?,?,?,?,?,?,?,?)',
                [$title_ar, $title_en, $desc_ar, $desc_en, $img, $discount, $link, $start, $end, $active]);
            flash_set('success', 'Offer created.');
        }
        redirect('admin/offers.php');
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $row = DB::one('SELECT image FROM offers WHERE id = ?', [$id]);
        if ($row) delete_upload($row['image']);
        DB::run('DELETE FROM offers WHERE id = ?', [$id]);
        flash_set('success', 'Offer deleted.');
        redirect('admin/offers.php');
    }
}

$edit = !empty($_GET['edit']) ? DB::one('SELECT * FROM offers WHERE id = ?', [(int)$_GET['edit']]) : null;
$offers = DB::all('SELECT * FROM offers ORDER BY id DESC');

$admin_page  = 'offers';
$admin_title = 'Offers';
include __DIR__ . '/includes/header.php';
?>
<div class="grid-2">
  <section class="panel">
    <header class="panel__head"><h2><?= $edit ? 'Edit offer' : 'New offer' ?></h2></header>
    <form method="post" enctype="multipart/form-data" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <label>Title (Arabic) *
        <input type="text" name="title_ar" required dir="rtl" value="<?= e($edit['title_ar'] ?? '') ?>">
      </label>
      <label>Title (English) *
        <input type="text" name="title_en" required value="<?= e($edit['title_en'] ?? '') ?>">
      </label>
      <label>Description (Arabic)
        <textarea name="description_ar" rows="3" dir="rtl"><?= e($edit['description_ar'] ?? '') ?></textarea>
      </label>
      <label>Description (English)
        <textarea name="description_en" rows="3"><?= e($edit['description_en'] ?? '') ?></textarea>
      </label>
      <div class="row">
        <label>Discount % <input type="number" step="0.01" min="0" max="100" name="discount_percent" value="<?= e((string)($edit['discount_percent'] ?? 0)) ?>"></label>
        <label>Start <input type="date" name="start_date" value="<?= e($edit['start_date'] ?? '') ?>"></label>
        <label>End <input type="date" name="end_date" value="<?= e($edit['end_date'] ?? '') ?>"></label>
      </div>
      <label>Link URL <input type="url" name="link_url" value="<?= e($edit['link_url'] ?? '') ?>"></label>
      <label>Image
        <input type="file" name="image" accept="image/*">
        <?php if (!empty($edit['image'])): ?>
          <img class="thumb-preview" src="<?= e(upload_url($edit['image'])) ?>" alt="">
        <?php endif; ?>
      </label>
      <label class="inline">
        <input type="checkbox" name="is_active" value="1" <?= empty($edit) || !empty($edit['is_active']) ? 'checked' : '' ?>>
        Active
      </label>
      <div class="form__actions">
        <button class="btn btn--primary" type="submit"><?= $edit ? 'Save' : 'Create' ?></button>
        <?php if ($edit): ?><a class="btn btn--ghost" href="<?= url('admin/offers.php') ?>">Cancel</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="panel">
    <header class="panel__head"><h2>All offers</h2></header>
    <div class="panel__body">
      <table class="data-table">
        <thead><tr><th>#</th><th>Title</th><th class="num">Discount</th><th>Active dates</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($offers as $o): ?>
            <tr>
              <td><?= (int)$o['id'] ?></td>
              <td><strong><?= e($o['title_en']) ?></strong><br><small class="muted" dir="rtl"><?= e($o['title_ar']) ?></small></td>
              <td class="num"><?= (float)$o['discount_percent'] ?>%</td>
              <td><?= e($o['start_date'] ?: '—') ?> → <?= e($o['end_date'] ?: '—') ?></td>
              <td><?= $o['is_active'] ? '<span class="pill pill--ok">Active</span>' : '<span class="pill pill--off">Hidden</span>' ?></td>
              <td class="row-actions">
                <a class="btn btn--xs btn--ghost" href="?edit=<?= (int)$o['id'] ?>">Edit</a>
                <form method="post" onsubmit="return confirm('Delete offer?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                  <button class="btn btn--xs btn--danger" type="submit">×</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
