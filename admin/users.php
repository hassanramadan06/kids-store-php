<?php
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';
    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        DB::run('UPDATE users SET is_active = 1 - is_active WHERE id = ?', [$id]);
        flash_set('success', 'Customer status updated.');
        redirect('admin/users.php');
    }
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        DB::run('DELETE FROM users WHERE id = ?', [$id]);
        flash_set('success', 'Customer deleted.');
        redirect('admin/users.php');
    }
}

$users = DB::all(
    'SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS orders_count,
                 (SELECT COALESCE(SUM(o.total),0) FROM orders o WHERE o.user_id = u.id AND o.status != "cancelled") AS spent
       FROM users u ORDER BY u.id DESC LIMIT 500'
);

$admin_page  = 'users';
$admin_title = 'Customers';
include __DIR__ . '/includes/header.php';
?>
<section class="panel">
  <div class="panel__body">
    <table class="data-table">
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th class="num">Orders</th><th class="num">Spent</th><th>Status</th><th>Joined</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><strong><?= e($u['full_name']) ?></strong></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['phone']) ?: '—' ?></td>
            <td class="num"><?= (int)$u['orders_count'] ?></td>
            <td class="num"><?= number_format((float)$u['spent'], 2) ?></td>
            <td><?= $u['is_active'] ? '<span class="pill pill--ok">Active</span>' : '<span class="pill pill--off">Disabled</span>' ?></td>
            <td><?= e(date('Y-m-d', strtotime($u['created_at']))) ?></td>
            <td class="row-actions">
              <form method="post" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn--xs" type="submit">Toggle</button>
              </form>
              <form method="post" class="inline-form" onsubmit="return confirm('Delete customer?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn--xs btn--danger" type="submit">×</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
          <tr><td colspan="9" class="muted">No customers.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
