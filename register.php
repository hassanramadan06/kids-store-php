<?php
require_once __DIR__ . '/includes/init.php';
if (current_user()) redirect('index.php');

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $name  = trim((string)($_POST['name']     ?? ''));
    $email = trim((string)($_POST['email']    ?? ''));
    $pass  = (string)      ($_POST['password'] ?? '');
    $phone = trim((string)($_POST['phone']    ?? ''));

    if (!v_required($name) || !v_email($email) || strlen($pass) < 6) {
        $err = t('Please fill all required fields. Password must be 6+ chars.', 'الرجاء ملء كل الحقول. كلمة المرور 6 أحرف على الأقل.');
    } else {
        $r = user_register($name, $email, $pass, $phone);
        if ($r['ok']) {
            flash_set('success', t('Account created.', 'تم إنشاء الحساب.'));
            redirect('index.php');
        }
        $err = $r['error'] ?? 'Failed';
    }
}

$page_title = t('Create account', 'إنشاء حساب');
include __DIR__ . '/includes/header.php';
?>
<section class="auth">
  <div class="container">
    <div class="auth__card">
      <h1><?= t('Create account', 'إنشاء حساب') ?></h1>
      <p><?= t('Join our family of happy parents.', 'انضم لعائلة الآباء السعداء.') ?></p>
      <?php if ($err): ?><div class="alert alert--err"><?= e($err) ?></div><?php endif; ?>
      <form method="post" class="auth__form">
        <?= csrf_field() ?>
        <label><?= t('Full name', 'الاسم الكامل') ?>
          <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
        </label>
        <label><?= t('Email', 'البريد الإلكتروني') ?>
          <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
        </label>
        <label><?= t('Phone', 'الهاتف') ?>
          <input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>">
        </label>
        <label><?= t('Password', 'كلمة المرور') ?>
          <input type="password" name="password" required minlength="6">
        </label>
        <button class="btn btn--primary btn--block btn--lg" type="submit"><?= t('Create account', 'إنشاء الحساب') ?></button>
      </form>
      <p class="auth__alt"><?= t('Already have an account?', 'لديك حساب بالفعل؟') ?>
        <a href="<?= url('login.php') ?>"><?= t('Login', 'تسجيل الدخول') ?></a>
      </p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
