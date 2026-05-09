<?php
require_once __DIR__ . '/includes/init.php';

$err = $ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $name    = trim((string)($_POST['name']    ?? ''));
    $email   = trim((string)($_POST['email']   ?? ''));
    $phone   = trim((string)($_POST['phone']   ?? ''));
    $subject = trim((string)($_POST['subject'] ?? ''));
    $msg     = trim((string)($_POST['message'] ?? ''));

    if (!v_required($name) || !v_email($email) || !v_required($subject) || !v_required($msg)) {
        $err = t('Please fill all required fields.', 'الرجاء ملء جميع الحقول المطلوبة.');
    } else {
        DB::run(
            'INSERT INTO contact_messages (name,email,phone,subject,message) VALUES (?,?,?,?,?)',
            [$name, $email, $phone, $subject, $msg]
        );
        $ok = t('Thanks! We will reply soon.', 'شكرًا لك! سنرد عليك قريبًا.');
        $_POST = [];
    }
}

$page_title = t('Contact us', 'تواصل معنا');
include __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="container"><h1><?= e($page_title) ?></h1></div></section>
<section class="section">
  <div class="container contact-grid">
    <div class="contact-info">
      <h2><?= t("We'd love to hear from you", 'يسعدنا تواصلك معنا') ?></h2>
      <ul class="contact-list">
        <li><span class="ic ic-pin"></span> <?= e(get_setting(is_ar() ? 'address_ar' : 'address_en')) ?></li>
        <li><span class="ic ic-phone"></span> <?= e(get_setting('contact_phone')) ?></li>
        <li><span class="ic ic-mail"></span> <?= e(get_setting('contact_email')) ?></li>
      </ul>
    </div>
    <form method="post" class="contact-form">
      <?= csrf_field() ?>
      <?php if ($err): ?><div class="alert alert--err"><?= e($err) ?></div><?php endif; ?>
      <?php if ($ok):  ?><div class="alert alert--ok"><?= e($ok) ?></div><?php endif; ?>
      <div class="row">
        <label><?= t('Name', 'الاسم') ?> <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required></label>
        <label><?= t('Email', 'البريد') ?> <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></label>
      </div>
      <div class="row">
        <label><?= t('Phone', 'الهاتف') ?> <input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>"></label>
        <label><?= t('Subject', 'الموضوع') ?> <input type="text" name="subject" value="<?= e($_POST['subject'] ?? '') ?>" required></label>
      </div>
      <label><?= t('Message', 'الرسالة') ?>
        <textarea name="message" rows="5" required><?= e($_POST['message'] ?? '') ?></textarea>
      </label>
      <button type="submit" class="btn btn--primary btn--lg"><?= t('Send message', 'إرسال') ?></button>
    </form>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
