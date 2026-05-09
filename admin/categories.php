<?php
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id          = (int)($_POST['id'] ?? 0);
        $section_id  = (int)($_POST['section_id'] ?? 0);
        $name_ar     = trim((string)($_POST['name_ar'] ?? ''));
        $name_en     = trim((string)($_POST['name_en'] ?? ''));
        $slug        = trim((string)($_POST['slug']    ?? '')) ?: slugify($name_en ?: $name_ar);
        $sort        = (int)($_POST['sort_order'] ?? 0);
        $active      = empty($_POST['is_active']) ? 0 : 1;
        if (!$section_id || !$name_ar || !$name_en) {
            flash_set('error', 'Section + names are required.');
            redirect('admin/categories.php');
        }
        $img_path = null;
        if (!empty($_FILES['image']['name'])) {
            $img_path = save_uploaded_image($_FILES['image'], 'categories');
        }
        if ($id > 0) {
            $sql = 'UPDATE categories SET section_id=?, name_ar=?, name_en=?, slug=?, sort_order=?, is_active=?'
                 . ($img_path ? ', image=?' : '') . ' WHERE id=?';
            $params = [$section_id, $name_ar, $name_en, $slug, $sort, $active];
            if ($img_path) $params[] = $img_path;
            $params[] = $id;
            DB::run($sql, $params);
            flash_set('success', 'Category updated.');
        } else {
            DB::run('INSERT INTO categories (section_id, name_ar, name_en, slug, image, sort_order, is_active) VALUES (?,?,?,?,?,?,?)',
                [$section_id, $name_ar, $name_en, $slug, $img_path, $sort, $active]);
            flash_set('success', 'Category created.');
        }
        redirect('admin/categories.php');
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $existing = DB::one('SELECT image FROM categories WHERE id = ?', [$id]);
        if ($existing) delete_upload($existing['image']);
        DB::run('DELETE FROM categories WHERE id = ?', [$id]);
        flash_set('success', 'Category deleted.');
        redirect('admin/categories.php');
    }
}

$edit = null;
if (!empty($_GET['edit'])) $edit = DB::one('SELECT * FROM categories WHERE id = ?', [(int)$_GET['edit']]);

$filter_section = (int)($_GET['section'] ?? 0);
$where = '';
$params = [];
if ($filter_section > 0) { $where = ' WHERE c.section_id = ?'; $params[] = $filter_section; }
$cats = DB::all(
    'SELECT c.*, s.name_en AS section_name,
            (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS prod_count
       FROM categories c JOIN sections s ON s.id = c.section_id'
    . $where . ' ORDER BY c.section_id, c.sort_order ASC, c.id ASC',
    $params
);
$sections = DB::all('SELECT id, name_en, name_ar FROM sections ORDER BY sort_order ASC');

$admin_page  = 'categories';
$admin_title = 'Categories';
include __DIR__ . '/includes/header.php';
?>
<div class="grid-2">
  <section class="panel">
    <header class="panel__head"><h2><?= $edit ? 'Edit category' : 'New category' ?></h2></header>
    <form method="post" enctype="multipart/form-data" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <label>Section *
        <select name="section_id" required>
          <option value="">— Choose —</option>
          <?php foreach ($sections as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= ((int)($edit['section_id']??0)===(int)$s['id'])?'selected':'' ?>>
              <?= e($s['name_en']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Name (Arabic) *
        <input type="text" name="name_ar" value="<?= e($edit['name_ar'] ?? '') ?>" required dir="rtl">
      </label>
      <label>Name (English) *
        <input type="text" name="name_en" value="<?= e($edit['name_en'] ?? '') ?>" required>
      </label>
      <label>Slug
        <input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="auto from name">
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
        <button class="btn btn--primary" type="submit"><?= $edit ? 'Save changes' : 'Create category' ?></button>
        <?php if ($edit): ?><a class="btn btn--ghost" href="<?= url('admin/categories.php') ?>">Cancel</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="panel">
    <header class="panel__head">
      <h2>All categories</h2>
      <form method="get" class="inline-form">
        <select name="section" onchange="this.form.submit()">
          <option value="0">All sections</option>
          <?php foreach ($sections as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= $filter_section===(int)$s['id']?'selected':'' ?>><?= e($s['name_en']) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </header>
    <div class="panel__body">
      <table class="data-table">
        <thead><tr><th>#</th><th>Section</th><th>Name</th><th>Slug</th><th class="num">Products</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($cats as $c): ?>
          <tr>
            <td><?= (int)$c['id'] ?></td>
            <td><?= e($c['section_name']) ?></td>
            <td><strong><?= e($c['name_en']) ?></strong><br><small class="muted" dir="rtl"><?= e($c['name_ar']) ?></small></td>
            <td><code><?= e($c['slug']) ?></code></td>
            <td class="num"><?= (int)$c['prod_count'] ?></td>
            <td><?= $c['is_active'] ? '<span class="pill pill--ok">Active</span>' : '<span class="pill pill--off">Hidden</span>' ?></td>
            <td class="row-actions">
              <a href="?edit=<?= (int)$c['id'] ?>" class="btn btn--xs btn--ghost">Edit</a>
              <form method="post" onsubmit="return confirm('Delete category?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
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
