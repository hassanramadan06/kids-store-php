<?php
require_once __DIR__ . '/includes/init.php';
$page_title = t('About us', 'من نحن');
include __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="container"><h1><?= e($page_title) ?></h1></div></section>
<section class="section">
  <div class="container about">
    <div class="about__intro">
      <h2><?= t('A store built for happy childhoods', 'متجر مصمم لطفولة سعيدة') ?></h2>
      <p>
        <?= t(
          'We curate soft, safe and stylish clothing and essentials for newborns, babies and kids. Every product passes our skin-friendliness test before it reaches your home.',
          'نختار بعناية ملابس ومستلزمات ناعمة وآمنة وعصرية للمواليد والأطفال. كل منتج يمر بفحصنا الصارم لراحة بشرة طفلك قبل أن يصل لبيتك.'
        ) ?>
      </p>
    </div>
    <div class="about__values">
      <div class="value-card"><h3><?= t('Soft on skin', 'ناعم على البشرة') ?></h3><p><?= t('Premium organic cotton and breathable fabrics.', 'قطن عضوي فاخر وأقمشة قابلة للتنفس.') ?></p></div>
      <div class="value-card"><h3><?= t('Tested for safety', 'مختبر للأمان') ?></h3><p><?= t('All toys & accessories meet strict safety standards.', 'كل الإكسسوارات والألعاب تلبي معايير الأمان.') ?></p></div>
      <div class="value-card"><h3><?= t('Made for parents', 'مصمم للأهل') ?></h3><p><?= t('Easy returns, fast delivery, and 24/7 support.', 'استرجاع سهل وتوصيل سريع ودعم 24/7.') ?></p></div>
      <div class="value-card"><h3><?= t('Earth-friendly', 'صديق للبيئة') ?></h3><p><?= t('Recyclable packaging and ethical sourcing.', 'تغليف قابل للتدوير ومصادر أخلاقية.') ?></p></div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
