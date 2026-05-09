<?php /** Site footer.  Always paired with header.php */ ?>
</main>

<footer class="site-footer">
  <div class="container site-footer__grid">
    <div class="site-footer__col site-footer__brand">
      <div class="logo">
        <span class="logo__text">
          <strong><?= e($site_name) ?></strong>
          <small><?= t('Kids fashion & baby essentials', 'ملابس الأطفال ومستلزمات الرضع') ?></small>
        </span>
      </div>
      <p>
        <?= t(
          'Soft, safe and stylish — everything your little one needs from newborn to first day of school.',
          'منتجات ناعمة وآمنة وعصرية — كل ما يحتاجه طفلك من المولد حتى أول يوم في المدرسة.'
        ) ?>
      </p>
      <div class="socials">
        <?php foreach (['facebook', 'instagram', 'twitter', 'tiktok'] as $s): ?>
          <a href="<?= e(get_setting($s, '#') ?? '#') ?>" aria-label="<?= e($s) ?>" target="_blank" rel="noopener">
            <span class="ic ic-<?= e($s) ?>"></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="site-footer__col">
      <h4><?= t('Shop', 'تسوق') ?></h4>
      <ul>
        <?php foreach (get_sections() as $s): ?>
          <li><a href="<?= url('section.php?slug=' . urlencode($s['slug'])) ?>"><?= e(tr($s, 'name')) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="site-footer__col">
      <h4><?= t('Help', 'المساعدة') ?></h4>
      <ul>
        <li><a href="<?= url('about.php') ?>"><?= t('About us', 'من نحن') ?></a></li>
        <li><a href="<?= url('contact.php') ?>"><?= t('Contact', 'تواصل معنا') ?></a></li>
        <li><a href="<?= url('cart.php') ?>"><?= t('Shopping cart', 'سلة التسوق') ?></a></li>
        <li><a href="<?= url('wishlist.php') ?>"><?= t('Wishlist', 'المفضلة') ?></a></li>
        <li><a href="<?= url('account.php') ?>"><?= t('My account', 'حسابي') ?></a></li>
      </ul>
    </div>

    <div class="site-footer__col">
      <h4><?= t('Contact', 'تواصل معنا') ?></h4>
      <ul class="contact-list">
        <li><span class="ic ic-pin"></span> <?= e(get_setting(is_ar() ? 'address_ar' : 'address_en')) ?></li>
        <li><span class="ic ic-phone"></span> <?= e(get_setting('contact_phone')) ?></li>
        <li><span class="ic ic-mail"></span> <?= e(get_setting('contact_email')) ?></li>
      </ul>
      <form class="newsletter" onsubmit="event.preventDefault(); window.toast && toast('<?= e(t('Subscribed!', 'تم الاشتراك!')) ?>','success');">
        <input type="email" placeholder="<?= e(t('Your email', 'بريدك الإلكتروني')) ?>" required>
        <button type="submit"><?= t('Subscribe', 'اشترك') ?></button>
      </form>
    </div>
  </div>
  <div class="site-footer__bottom">
    <div class="container">
      <small>© <?= date('Y') ?> <?= e($site_name) ?>. <?= t('All rights reserved.', 'جميع الحقوق محفوظة.') ?></small>
    </div>
  </div>
</footer>

<script src="<?= url('assets/js/main.js') ?>" defer></script>
</body>
</html>
