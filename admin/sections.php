<?php
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id        = (int)($_POST['id'] ?? 0);
        $name_ar   = trim((string)($_POST['name_ar'] ?? ''));
        $name_en   = trim((string)($_POST['name_en'] ?? ''));
        $slug      = trim((string)($_POST['slug']    ?? '')) ?: slugify($name_en ?: $name_ar);
        $icon      = trim((string)($_POST['icon']    ?? ''));
        $sort      = (int)($_POST['sort_order'] ?? 0);
        $active    = empty($_POST['is_active']) ? 0 : 1;

        if (!$name_ar || !$name_en) {
            flash_set('error', 'Section names (AR + EN) are required.');
            redirect('admin/sections.php');
        }
        $img_path = null;
        if (!empty($_FILES['image']['name'])) {
            $img_path = save_uploaded_image($_FILES['image'], 'sections');
        }
        if ($id > 0) {
            $sql = 'UPDATE sections SET name_ar=?, name_en=?, slug=?, icon=?, sort_order=?, is_active=?'
                 . ($img_path ? ', image=?' : '') . ' WHERE id=?';
            $params = [$name_ar, $name_en, $slug, $icon, $sort, $active];
            if ($img_path) $params[] = $img_path;
            $params[] = $id;
            DB::run($sql, $params);
            flash_set('success', 'Section updated.');
        } else {
            DB::run('INSERT INTO sections (name_ar, name_en, slug, icon, image, sort_order, is_active) VALUES (?,?,?,?,?,?,?)',
                [$name_ar, $name_en, $slug, $icon, $img_path, $sort, $active]);
            flash_set('success', 'Section created.');
        }
        redirect('admin/sections.php');
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $existing = DB::one('SELECT image FROM sections WHERE id = ?', [$id]);
        if ($existing) delete_upload($existing['image']);
        DB::run('DELETE FROM sections WHERE id = ?', [$id]);
        flash_set('success', 'Section deleted.');
        redirect('admin/sections.php');
    }
}

$edit = null;
if (!empty($_GET['edit'])) {
    $edit = DB::one('SELECT * FROM sections WHERE id = ?', [(int)$_GET['edit']]);
}

$sections = DB::all('SELECT s.*, (SELECT COUNT(*) FROM categories c WHERE c.section_id = s.id) AS cat_count
                     FROM sections s ORDER BY sort_order ASC, id ASC');

$admin_page  = 'sections';
$admin_title = 'Sections';
include __DIR__ . '/includes/header.php';
?>
<div class="grid-2">
  <section class="panel">
    <header class="panel__head"><h2><?= $edit ? 'Edit section' : 'New section' ?></h2></header>
    <form method="post" enctype="multipart/form-data" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">

      <label>Name (Arabic) *
        <input type="text" name="name_ar" value="<?= e($edit['name_ar'] ?? '') ?>" required dir="rtl">
      </label>
      <label>Name (English) *
        <input type="text" name="name_en" value="<?= e($edit['name_en'] ?? '') ?>" required>
      </label>
      <label>Slug
        <input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="auto from name">
      </label>
      <label>Icon key (baby, bottle, boy, girl, gift, bed, tag)
        <input type="text" name="icon" value="<?= e($edit['icon'] ?? '') ?>">
      </label>
      <label>Sort order
        <input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>">
      </label>
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
        <button class="btn btn--primary" type="submit"><?= $edit ? 'Save changes' : 'Create section' ?></button>
        <?php if ($edit): ?><a class="btn btn--ghost" href="<?= url('admin/sections.php') ?>">Cancel</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="panel">
    <header class="panel__head"><h2>All sections</h2></header>
    <div class="panel__body">
      <table class="data-table">
        <thead><tr><th>#</th><th>Name</th><th>Slug</th><th class="num">Categories</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($sections as $s): ?>
          <tr>
            <td><?= (int)$s['id'] ?></td>
            <td><strong><?= e($s['name_en']) ?></strong><br><small class="muted" dir="rtl"><?= e($s['name_ar']) ?></small></td>
            <td><code><?= e($s['slug']) ?></code></td>
            <td class="num"><?= (int)$s['cat_count'] ?></td>
            <td><?= $s['is_active'] ? '<span class="pill pill--ok">Active</span>' : '<span class="pill pill--off">Hidden</span>' ?></td>
            <td class="row-actions">
              <a href="?edit=<?= (int)$s['id'] ?>" class="btn btn--xs btn--ghost">Edit</a>
              <form method="post" onsubmit="return confirm('Delete section and its categories?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                <button class="btn btn--xs btn--danger" type="submit">Delete</button>
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
