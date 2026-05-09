<?php
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';
    if ($action === 'set_status') {
        $id = (int)$_POST['id'];
        $st = $_POST['status'] ?? 'pending';
        if (in_array($st, ['pending','processing','shipped','delivered','cancelled'], true)) {
            DB::run('UPDATE orders SET status = ? WHERE id = ?', [$st, $id]);
            flash_set('success', 'Order updated.');
        }
        redirect('admin/orders.php' . (!empty($_POST['return_id']) ? '?id=' . (int)$_POST['return_id'] : ''));
    }
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        DB::run('DELETE FROM orders WHERE id = ?', [$id]);
        flash_set('success', 'Order deleted.');
        redirect('admin/orders.php');
    }
}

$status = $_GET['status'] ?? '';
$where = []; $params = [];
if ($status && in_array($status, ['pending','processing','shipped','delivered','cancelled'], true)) {
    $where[] = 'status = ?'; $params[] = $status;
}
$wsql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$orders = DB::all("SELECT * FROM orders $wsql ORDER BY id DESC LIMIT 200", $params);

$admin_page  = 'orders';
$admin_title = 'Orders';
include __DIR__ . '/includes/header.php';
?>
<section class="panel">
  <header class="panel__head">
    <form method="get" class="filterbar">
      <select name="status" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $st): ?>
          <option value="<?= $st ?>" <?= $status===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </header>
  <div class="panel__body">
    <table class="data-table">
      <thead><tr><th>#</th><th>Customer</th><th>Phone</th><th class="num">Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><a href="?id=<?= (int)$o['id'] ?>">#<?= (int)$o['id'] ?></a></td>
            <td><strong><?= e($o['full_name']) ?></strong><br><small class="muted"><?= e($o['email']) ?></small></td>
            <td><?= e($o['phone']) ?></td>
            <td class="num"><?= number_format((float)$o['total'], 2) ?></td>
            <td><span class="pill"><?= e($o['payment_method']) ?></span></td>
            <td><span class="status status--<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
            <td><?= e(date('Y-m-d H:i', strtotime($o['created_at']))) ?></td>
            <td class="row-actions">
              <a class="btn btn--xs btn--ghost" href="?id=<?= (int)$o['id'] ?>">View</a>
              <form method="post" class="inline-form" onsubmit="return confirm('Delete this order permanently?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                <button type="submit" class="btn btn--xs btn--danger">×</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?>
          <tr><td colspan="8" class="muted">No orders.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php
$selected_id = (int)($_GET['id'] ?? 0);
if ($selected_id) {
    $order = DB::one('SELECT * FROM orders WHERE id = ?', [$selected_id]);
    $items = DB::all('SELECT * FROM order_items WHERE order_id = ?', [$selected_id]);
    if ($order):
?>
<section class="panel">
  <header class="panel__head"><h2>Order #<?= (int)$order['id'] ?></h2></header>
  <div class="panel__body">
    <div class="grid-2">
      <div>
        <h3>Customer</h3>
        <p>
          <strong><?= e($order['full_name']) ?></strong><br>
          <?= e($order['email']) ?> · <?= e($order['phone']) ?><br>
          <?= e($order['address']) ?>, <?= e($order['city']) ?>
        </p>
        <?php if (!empty($order['notes'])): ?><p><strong>Notes:</strong> <?= e($order['notes']) ?></p><?php endif; ?>
      </div>
      <div>
        <h3>Update status</h3>
        <form method="post" class="inline-form">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="set_status">
          <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
          <input type="hidden" name="return_id" value="<?= (int)$order['id'] ?>">
          <select name="status">
            <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $st): ?>
              <option value="<?= $st ?>" <?= $order['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn--primary" type="submit">Save</button>
        </form>
        <p class="muted" style="margin-top:1rem;">Created: <?= e($order['created_at']) ?></p>
      </div>
    </div>

    <h3>Items</h3>
    <table class="data-table">
      <thead><tr><th>Product</th><th>Size</th><th>Color</th><th class="num">Unit</th><th class="num">Qty</th><th class="num">Total</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= e($it['product_name']) ?></td>
            <td><?= e($it['size']) ?: '—' ?></td>
            <td><?= e($it['color']) ?: '—' ?></td>
            <td class="num"><?= number_format((float)$it['unit_price'], 2) ?></td>
            <td class="num"><?= (int)$it['quantity'] ?></td>
            <td class="num"><?= number_format((float)$it['line_total'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="5" class="num">Subtotal</td><td class="num"><?= number_format((float)$order['subtotal'], 2) ?></td></tr>
        <tr><td colspan="5" class="num">Shipping</td><td class="num"><?= number_format((float)$order['shipping'], 2) ?></td></tr>
        <tr><td colspan="5" class="num"><strong>Total</strong></td><td class="num"><strong><?= number_format((float)$order['total'], 2) ?></strong></td></tr>
      </tfoot>
    </table>
  </div>
</section>
<?php endif; } ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
