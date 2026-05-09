<?php
require_once __DIR__ . '/includes/init.php';

$totals = cart_totals();
if (empty($totals['items'])) {
    flash_set('error', t('Your cart is empty.', 'سلتك فارغة.'));
    redirect('cart.php');
}

$user = current_user();
$err  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $full_name = trim((string)($_POST['full_name'] ?? ''));
    $email     = trim((string)($_POST['email']     ?? ''));
    $phone     = trim((string)($_POST['phone']     ?? ''));
    $address   = trim((string)($_POST['address']   ?? ''));
    $city      = trim((string)($_POST['city']      ?? ''));
    $notes     = trim((string)($_POST['notes']     ?? ''));
    $payment   = $_POST['payment_method'] ?? 'cod';

    if (!v_required($full_name) || !v_email($email) || !v_required($phone) || !v_required($address) || !v_required($city)) {
        $err = t('Please fill all required fields.', 'الرجاء ملء جميع الحقول المطلوبة.');
    } else {
        DB::pdo()->beginTransaction();
        try {
            DB::run(
                'INSERT INTO orders (user_id, full_name, email, phone, address, city, notes, subtotal, shipping, total, payment_method, status)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $user['id'] ?? null,
                    $full_name, $email, $phone, $address, $city, $notes,
                    $totals['subtotal'], $totals['shipping'], $totals['total'],
                    in_array($payment, ['cod','card','wallet'], true) ? $payment : 'cod',
                    'pending',
                ]
            );
            $order_id = DB::lastId();

            foreach ($totals['items'] as $it) {
                $price = ($it['discount_price'] !== null && $it['discount_price'] < $it['price'])
                    ? (float)$it['discount_price'] : (float)$it['price'];
                $line = $price * (int)$it['quantity'];
                DB::run(
                    'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, size, color, line_total)
                     VALUES (?,?,?,?,?,?,?,?)',
                    [
                        $order_id,
                        (int)$it['product_id'],
                        tr($it, 'name'),
                        $price,
                        (int)$it['quantity'],
                        $it['size']  ?: null,
                        $it['color'] ?: null,
                        $line,
                    ]
                );
                // Decrement stock
                DB::run('UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?',
                    [(int)$it['quantity'], (int)$it['product_id']]);
            }
            cart_clear();
            DB::pdo()->commit();
            $_SESSION['last_order_id'] = $order_id;
            flash_set('success', t('Order placed successfully!', 'تم تسجيل طلبك بنجاح!'));
            redirect('order_success.php?id=' . $order_id);
        } catch (Throwable $e) {
            DB::pdo()->rollBack();
            $err = t('Something went wrong. Please try again.', 'حدث خطأ، حاول مرة أخرى.');
        }
    }
}

$page_title = t('Checkout', 'إتمام الشراء');
include __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="container"><h1><?= e($page_title) ?></h1></div></section>
<section class="section">
  <div class="container checkout-grid">
    <form method="post" class="checkout-form">
      <?= csrf_field() ?>
      <?php if ($err): ?><div class="alert alert--err"><?= e($err) ?></div><?php endif; ?>

      <h2><?= t('Shipping details', 'بيانات الشحن') ?></h2>
      <div class="row">
        <label><?= t('Full name', 'الاسم الكامل') ?> *
          <input type="text" name="full_name" value="<?= e($_POST['full_name'] ?? ($user['full_name'] ?? '')) ?>" required>
        </label>
        <label><?= t('Email', 'البريد') ?> *
          <input type="email" name="email" value="<?= e($_POST['email'] ?? ($user['email'] ?? '')) ?>" required>
        </label>
      </div>
      <div class="row">
        <label><?= t('Phone', 'الهاتف') ?> *
          <input type="tel" name="phone" value="<?= e($_POST['phone'] ?? ($user['phone'] ?? '')) ?>" required>
        </label>
        <label><?= t('City', 'المدينة') ?> *
          <input type="text" name="city" value="<?= e($_POST['city'] ?? ($user['city'] ?? '')) ?>" required>
        </label>
      </div>
      <label><?= t('Address', 'العنوان') ?> *
        <input type="text" name="address" value="<?= e($_POST['address'] ?? ($user['address'] ?? '')) ?>" required>
      </label>
      <label><?= t('Order notes', 'ملاحظات') ?>
        <textarea name="notes" rows="3"><?= e($_POST['notes'] ?? '') ?></textarea>
      </label>

      <h2><?= t('Payment method', 'طريقة الدفع') ?></h2>
      <div class="payment-options">
        <label><input type="radio" name="payment_method" value="cod" checked> <?= t('Cash on delivery', 'الدفع عند الاستلام') ?></label>
        <label><input type="radio" name="payment_method" value="card"> <?= t('Card', 'بطاقة ائتمانية') ?></label>
        <label><input type="radio" name="payment_method" value="wallet"> <?= t('Mobile wallet', 'محفظة إلكترونية') ?></label>
      </div>

      <button class="btn btn--primary btn--lg btn--block" type="submit"><?= t('Place order', 'تأكيد الطلب') ?></button>
    </form>

    <aside class="cart-summary">
      <h3><?= t('Order summary', 'ملخص الطلب') ?></h3>
      <ul class="checkout-items">
        <?php foreach ($totals['items'] as $it):
          $price = ($it['discount_price'] !== null && $it['discount_price'] < $it['price']) ? (float)$it['discount_price'] : (float)$it['price'];
        ?>
          <li>
            <img src="<?= e(product_primary_image((int)$it['product_id'])) ?>" alt="">
            <span class="checkout-items__title"><?= e(tr($it, 'name')) ?> × <?= (int)$it['quantity'] ?></span>
            <span><?= money($price * (int)$it['quantity']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="cart-summary__row"><span><?= t('Subtotal', 'الإجمالي الفرعي') ?></span><strong><?= money($totals['subtotal']) ?></strong></div>
      <div class="cart-summary__row"><span><?= t('Shipping', 'الشحن') ?></span><strong><?= $totals['shipping'] > 0 ? money($totals['shipping']) : t('Free', 'مجاني') ?></strong></div>
      <div class="cart-summary__row cart-summary__total"><span><?= t('Total', 'الإجمالي') ?></span><strong><?= money($totals['total']) ?></strong></div>
    </aside>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
