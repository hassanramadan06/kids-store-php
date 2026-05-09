<?php
require_once __DIR__ . '/includes/init.php';
$id = (int)($_GET['id'] ?? 0);
$order = $id ? DB::one('SELECT * FROM orders WHERE id = ?', [$id]) : null;
if (!$order) redirect('index.php');
$page_title = t('Order placed', 'تم استلام طلبك');
include __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="success-card">
      <div class="success-card__icon">
        <svg viewBox="0 0 64 64" width="80" height="80"><circle cx="32" cy="32" r="30" fill="#a7e8d8"/><path d="M20 33l8 8 16-18" fill="none" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <h1><?= t('Thank you for your order!', 'شكرًا لطلبك!') ?></h1>
      <p><?= sprintf(t('Order #%d has been received. We will contact you shortly.', 'تم استلام الطلب رقم #%d وسنتواصل معك قريبًا.'), (int)$order['id']) ?></p>
      <a class="btn btn--primary btn--lg" href="<?= url('shop.php') ?>"><?= t('Continue shopping', 'متابعة التسوق') ?></a>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
