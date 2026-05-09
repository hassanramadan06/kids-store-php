<?php
require_once __DIR__ . '/includes/init.php';
require_user();
$user   = current_user();
$orders = DB::all('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC', [(int)$user['id']]);
$page_title = t('My account', 'حسابي');
include __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="container"><h1><?= e($page_title) ?></h1></div></section>
<section class="section">
  <div class="container account-grid">
    <aside class="account-side">
      <div class="account-card">
        <h3><?= e($user['full_name']) ?></h3>
        <p><?= e($user['email']) ?></p>
        <a class="btn btn--ghost btn--block" href="<?= url('logout.php') ?>"><?= t('Logout', 'تسجيل الخروج') ?></a>
      </div>
    </aside>
    <div>
      <h2><?= t('My orders', 'طلباتي') ?></h2>
      <?php if (empty($orders)): ?>
        <p class="empty"><?= t('No orders yet.', 'لا توجد طلبات بعد.') ?></p>
      <?php else: ?>
        <table class="data-table">
          <thead><tr><th>#</th><th><?= t('Date', 'التاريخ') ?></th><th><?= t('Total', 'الإجمالي') ?></th><th><?= t('Status', 'الحالة') ?></th></tr></thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td>#<?= (int)$o['id'] ?></td>
                <td><?= e(date('Y-m-d', strtotime($o['created_at']))) ?></td>
                <td><?= money((float)$o['total']) ?></td>
                <td><span class="status status--<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
