<?php
require_once __DIR__ . '/includes/init.php';

$next = $_GET['next'] ?? 'index.php';
if (current_user()) redirect($next);

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    if (!$email || !$pass) {
        $err = t('Please fill all fields.', 'الرجاء تعبئة جميع الحقول.');
    } else {
        $u = user_login($email, $pass);
        if ($u) {
            flash_set('success', t('Welcome back!', 'أهلًا بعودتك!'));
            redirect($next);
        }
        $err = t('Invalid credentials.', 'بيانات الدخول غير صحيحة.');
    }
}

$page_title = t('Login', 'تسجيل الدخول');
include __DIR__ . '/includes/header.php';
?>
<section class="auth">
  <div class="container">
    <div class="auth__card">
      <h1><?= t('Welcome back', 'مرحبًا بعودتك') ?></h1>
      <p><?= t('Sign in to track orders and access your wishlist.', 'سجل دخولك لمتابعة طلباتك والوصول للمفضلة.') ?></p>
      <?php if ($err): ?><div class="alert alert--err"><?= e($err) ?></div><?php endif; ?>
      <form method="post" class="auth__form">
        <?= csrf_field() ?>
        <label><?= t('Email', 'البريد الإلكتروني') ?>
          <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
        </label>
        <label><?= t('Password', 'كلمة المرور') ?>
          <input type="password" name="password" required>
        </label>
        <button class="btn btn--primary btn--block btn--lg" type="submit"><?= t('Login', 'دخول') ?></button>
      </form>
      <p class="auth__alt"><?= t("Don't have an account?", 'ليس لديك حساب؟') ?>
        <a href="<?= url('register.php') ?>"><?= t('Create one', 'أنشئ حسابًا') ?></a>
      </p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
