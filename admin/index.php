<?php
require __DIR__ . '/includes/bootstrap.php';

$stats = [
    'sales_total'  => (float) DB::scalar('SELECT COALESCE(SUM(total),0) FROM orders WHERE status != "cancelled"'),
    'orders_total' => (int)   DB::scalar('SELECT COUNT(*) FROM orders'),
    'orders_pending'=> (int)  DB::scalar('SELECT COUNT(*) FROM orders WHERE status = "pending"'),
    'products'     => (int)   DB::scalar('SELECT COUNT(*) FROM products'),
    'low_stock'    => (int)   DB::scalar('SELECT COUNT(*) FROM products WHERE stock < 5'),
    'users'        => (int)   DB::scalar('SELECT COUNT(*) FROM users'),
    'messages_new' => (int)   DB::scalar('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0'),
];

// Sales for the past 14 days, grouped per day
$sales_rows = DB::all(
    'SELECT DATE(created_at) AS d, COALESCE(SUM(total),0) AS s
       FROM orders
      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
        AND status != "cancelled"
      GROUP BY DATE(created_at)'
);
$sales_index = [];
foreach ($sales_rows as $r) $sales_index[$r['d']] = (float)$r['s'];

$labels = $values = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $labels[] = date('M d', strtotime($d));
    $values[] = $sales_index[$d] ?? 0;
}

$status_breakdown = DB::all('SELECT status, COUNT(*) AS c FROM orders GROUP BY status');

$recent_orders = DB::all('SELECT * FROM orders ORDER BY id DESC LIMIT 6');
$top_products  = DB::all(
    'SELECT p.id, p.name_en, p.name_ar, COALESCE(SUM(oi.quantity),0) AS sold
       FROM products p
       LEFT JOIN order_items oi ON oi.product_id = p.id
       LEFT JOIN orders o       ON o.id = oi.order_id AND o.status != "cancelled"
      GROUP BY p.id ORDER BY sold DESC LIMIT 6'
);

$admin_page  = 'dashboard';
$admin_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>
<div class="cards">
  <div class="card card--accent-pink">
    <div class="card__label">Total revenue</div>
    <div class="card__value"><?= number_format($stats['sales_total'], 2) ?> <?= e(CURRENCY_EN) ?></div>
    <div class="card__foot">All-time</div>
  </div>
  <div class="card card--accent-blue">
    <div class="card__label">Orders</div>
    <div class="card__value"><?= number_format($stats['orders_total']) ?></div>
    <div class="card__foot"><?= number_format($stats['orders_pending']) ?> pending</div>
  </div>
  <div class="card card--accent-mint">
    <div class="card__label">Products</div>
    <div class="card__value"><?= number_format($stats['products']) ?></div>
    <div class="card__foot"><?= $stats['low_stock'] ?> low stock</div>
  </div>
  <div class="card card--accent-amber">
    <div class="card__label">Customers</div>
    <div class="card__value"><?= number_format($stats['users']) ?></div>
    <div class="card__foot"><?= $stats['messages_new'] ?> new messages</div>
  </div>
</div>

<div class="grid-2">
  <section class="panel">
    <header class="panel__head"><h2>Sales — last 14 days</h2></header>
    <div class="panel__body">
      <canvas id="salesChart" height="120"
              data-labels='<?= e(json_encode($labels)) ?>'
              data-values='<?= e(json_encode($values)) ?>'></canvas>
    </div>
  </section>

  <section class="panel">
    <header class="panel__head"><h2>Order status</h2></header>
    <div class="panel__body">
      <ul class="status-bars">
        <?php
        $total_for_bars = max(1, array_sum(array_column($status_breakdown, 'c')));
        foreach ($status_breakdown as $b):
            $pct = round(((int)$b['c'] / $total_for_bars) * 100);
        ?>
          <li>
            <span class="status status--<?= e($b['status']) ?>"><?= e($b['status']) ?></span>
            <div class="status-bars__track"><i style="width:<?= $pct ?>%"></i></div>
            <strong><?= (int)$b['c'] ?></strong>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
</div>

<div class="grid-2">
  <section class="panel">
    <header class="panel__head"><h2>Recent orders</h2><a href="<?= url('admin/orders.php') ?>" class="link">View all →</a></header>
    <div class="panel__body">
      <table class="data-table">
        <thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($recent_orders as $o): ?>
            <tr>
              <td><a href="<?= url('admin/order_view.php?id=' . (int)$o['id']) ?>">#<?= (int)$o['id'] ?></a></td>
              <td><?= e($o['full_name']) ?></td>
              <td><?= number_format((float)$o['total'], 2) ?> <?= e(CURRENCY_EN) ?></td>
              <td><span class="status status--<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
              <td><?= e(date('Y-m-d', strtotime($o['created_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($recent_orders)): ?>
            <tr><td colspan="5" class="muted">No orders yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="panel">
    <header class="panel__head"><h2>Top products</h2></header>
    <div class="panel__body">
      <table class="data-table">
        <thead><tr><th>Product</th><th class="num">Units sold</th></tr></thead>
        <tbody>
          <?php foreach ($top_products as $p): ?>
            <tr>
              <td><?= e($p['name_en']) ?></td>
              <td class="num"><?= (int)$p['sold'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
