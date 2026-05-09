<?php
/**
 * Homepage.
 *
 *  - Hero slider (offers)
 *  - "Shop by section" tiles
 *  - Featured products
 *  - On-sale products
 *  - Newest arrivals
 */
require_once __DIR__ . '/includes/init.php';

$page_title = is_ar() ? 'الرئيسية' : 'Home';

$offers = DB::all(
    'SELECT * FROM offers WHERE is_active = 1
       AND (start_date IS NULL OR start_date <= CURDATE())
       AND (end_date IS NULL OR end_date >= CURDATE())
     ORDER BY id DESC LIMIT 5'
);
$sections = get_sections();
$featured = search_products(['featured' => 1], 1, 8)['rows'];
$on_sale  = search_products(['on_sale' => 1], 1, 8)['rows'];
$newest   = search_products(['sort' => 'newest'], 1, 8)['rows'];

include __DIR__ . '/includes/header.php';
?>

<!-- HERO SLIDER ---------------------------------------------------- -->
<section class="hero" aria-label="<?= e(t('Special offers', 'عروض حصرية')) ?>">
  <div class="hero__slider js-slider" data-autoplay="5000">
    <?php
      $hero_palettes = [
        ['#fff1f5', '#ffd6e7'],
        ['#e9f5ff', '#bee3ff'],
        ['#fff8e1', '#ffe7a8'],
      ];
      foreach ($offers as $i => $o):
        $palette = $hero_palettes[$i % count($hero_palettes)];
    ?>
    <div class="hero__slide" style="background:linear-gradient(135deg, <?= e($palette[0]) ?>, <?= e($palette[1]) ?>);">
      <div class="container hero__inner">
        <div class="hero__text">
          <small><?= t('Limited time offer', 'عرض لفترة محدودة') ?></small>
          <h1><?= e(tr($o, 'title')) ?></h1>
          <p><?= e(tr($o, 'description')) ?></p>
          <a class="btn btn--primary btn--lg" href="<?= url('shop.php?on_sale=1') ?>"><?= t('Shop now', 'تسوق الآن') ?></a>
        </div>
        <div class="hero__art" aria-hidden="true">
          <svg viewBox="0 0 200 200" width="280" height="280">
            <circle cx="100" cy="100" r="90" fill="#fff" opacity=".5"/>
            <circle cx="100" cy="86" r="42" fill="#fff"/>
            <circle cx="86" cy="84" r="6" fill="#3a2a3f"/>
            <circle cx="114" cy="84" r="6" fill="#3a2a3f"/>
            <path d="M85 100 q15 12 30 0" stroke="#3a2a3f" stroke-width="4" fill="none" stroke-linecap="round"/>
            <path d="M55 165 q45 -38 90 0 v25 H55 z" fill="#a7e8d8"/>
            <?php if ((float)$o['discount_percent'] > 0): ?>
              <g transform="translate(150,40)">
                <circle r="34" fill="#ff6b9b"/>
                <text text-anchor="middle" y="6" fill="#fff" font-family="Poppins" font-weight="700" font-size="18">
                  -<?= (int) $o['discount_percent'] ?>%
                </text>
              </g>
            <?php endif; ?>
          </svg>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($offers)): ?>
      <div class="hero__slide" style="background:linear-gradient(135deg,#fff1f5,#ffd6e7);">
        <div class="container hero__inner">
          <div class="hero__text">
            <h1><?= e($site_name) ?></h1>
            <p><?= e(get_setting(is_ar() ? 'site_tagline_ar' : 'site_tagline_en')) ?></p>
            <a class="btn btn--primary btn--lg" href="<?= url('shop.php') ?>"><?= t('Browse the shop', 'تصفح المتجر') ?></a>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <button class="slider-nav slider-nav--prev" aria-label="<?= e(t('Previous', 'السابق')) ?>">‹</button>
    <button class="slider-nav slider-nav--next" aria-label="<?= e(t('Next', 'التالي')) ?>">›</button>
    <div class="slider-dots" role="tablist"></div>
  </div>
</section>

<!-- TRUST BADGES -------------------------------------------------- -->
<section class="trust">
  <div class="container trust__grid">
    <div class="trust__item"><span class="ic ic-truck"></span><div><strong><?= t('Free shipping', 'شحن مجاني') ?></strong><small><?= t('On orders over 1000 EGP', 'للطلبات فوق 1000 ج.م') ?></small></div></div>
    <div class="trust__item"><span class="ic ic-return"></span><div><strong><?= t('Easy returns', 'استرجاع مرن') ?></strong><small><?= t('14-day return policy', 'سياسة استرجاع 14 يوم') ?></small></div></div>
    <div class="trust__item"><span class="ic ic-shield"></span><div><strong><?= t('Safe materials', 'مواد آمنة') ?></strong><small><?= t('Skin-friendly cotton', 'قطن لطيف على البشرة') ?></small></div></div>
    <div class="trust__item"><span class="ic ic-headset"></span><div><strong><?= t('24/7 support', 'دعم 24/7') ?></strong><small><?= t('We are always here', 'نحن هنا دائمًا') ?></small></div></div>
  </div>
</section>

<!-- SHOP BY SECTION ---------------------------------------------- -->
<section class="section">
  <div class="container">
    <header class="section__head">
      <h2 class="section__title"><?= t('Shop by section', 'تسوق حسب القسم') ?></h2>
      <p class="section__sub"><?= t('Hand-picked categories your kid will love', 'فئات منتقاة بعناية يحبها أطفالك') ?></p>
    </header>
    <div class="sections-grid">
      <?php foreach ($sections as $i => $sec):
        $palettes = ['#fff1f5','#e9f5ff','#fff8e1','#e8f7ee','#f3e8ff','#fff0e6','#e6fffb'];
        $bg = $palettes[$i % count($palettes)];
      ?>
      <a class="section-tile" href="<?= url('section.php?slug=' . urlencode($sec['slug'])) ?>" style="background:<?= $bg ?>;">
        <span class="section-tile__icon" aria-hidden="true"><?= section_icon_svg($sec['icon'] ?? '') ?></span>
        <h3><?= e(tr($sec, 'name')) ?></h3>
        <span class="section-tile__cta"><?= t('Browse', 'تصفح') ?> →</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
/* Tiny inline helper, scoped to this page only */
function section_icon_svg(string $icon): string {
    $stroke = '#3a2a3f';
    switch ($icon) {
        case 'baby':    return '<svg viewBox="0 0 24 24" width="40" height="40"><circle cx="12" cy="9" r="5" fill="none" stroke="'.$stroke.'" stroke-width="2"/><path d="M5 21q7-7 14 0" fill="none" stroke="'.$stroke.'" stroke-width="2"/></svg>';
        case 'bottle':  return '<svg viewBox="0 0 24 24" width="40" height="40"><rect x="8" y="6" width="8" height="14" rx="2" fill="none" stroke="'.$stroke.'" stroke-width="2"/><rect x="9" y="3" width="6" height="3" rx="1" fill="'.$stroke.'"/></svg>';
        case 'boy':     return '<svg viewBox="0 0 24 24" width="40" height="40"><circle cx="12" cy="7" r="4" fill="none" stroke="'.$stroke.'" stroke-width="2"/><path d="M6 21v-3a6 6 0 0 1 12 0v3" fill="none" stroke="'.$stroke.'" stroke-width="2"/></svg>';
        case 'girl':    return '<svg viewBox="0 0 24 24" width="40" height="40"><circle cx="12" cy="7" r="4" fill="none" stroke="'.$stroke.'" stroke-width="2"/><path d="M5 21l3-8h8l3 8" fill="none" stroke="'.$stroke.'" stroke-width="2"/></svg>';
        case 'gift':    return '<svg viewBox="0 0 24 24" width="40" height="40"><rect x="3" y="9" width="18" height="12" rx="2" fill="none" stroke="'.$stroke.'" stroke-width="2"/><path d="M3 13h18M12 9v12" stroke="'.$stroke.'" stroke-width="2"/><path d="M12 9c-3-3 1-6 3-3s-3 3-3 3zM12 9c3-3-1-6-3-3s3 3 3 3z" fill="none" stroke="'.$stroke.'" stroke-width="2"/></svg>';
        case 'bed':     return '<svg viewBox="0 0 24 24" width="40" height="40"><path d="M3 18V8h6v6h12v4M3 18h18" fill="none" stroke="'.$stroke.'" stroke-width="2"/></svg>';
        case 'tag':     return '<svg viewBox="0 0 24 24" width="40" height="40"><path d="M3 12V4h8l10 10-8 8L3 12z" fill="none" stroke="'.$stroke.'" stroke-width="2"/><circle cx="8" cy="9" r="1.5" fill="'.$stroke.'"/></svg>';
        default:        return '<svg viewBox="0 0 24 24" width="40" height="40"><circle cx="12" cy="12" r="9" fill="none" stroke="'.$stroke.'" stroke-width="2"/></svg>';
    }
}
?>

<!-- FEATURED PRODUCTS -------------------------------------------- -->
<?php if ($featured): ?>
<section class="section section--alt">
  <div class="container">
    <header class="section__head">
      <h2 class="section__title"><?= t('Featured products', 'منتجات مميزة') ?></h2>
      <a class="section__more" href="<?= url('shop.php?featured=1') ?>"><?= t('View all', 'عرض الكل') ?> →</a>
    </header>
    <div class="products-grid">
      <?php foreach ($featured as $p) include __DIR__ . '/includes/product_card.php'; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- BANNER STRIP -------------------------------------------------- -->
<section class="banner-strip">
  <div class="container banner-strip__inner">
    <div class="banner-strip__text">
      <h2><?= t('Sweet dreams start here', 'الأحلام الحلوة تبدأ هنا') ?></h2>
      <p><?= t('Soft cotton sheets, breathable fabrics & cozy crib essentials.', 'مفارش قطن ناعمة، أقمشة قابلة للتنفس وأساسيات سرير مريح.') ?></p>
      <a class="btn btn--white" href="<?= url('section.php?slug=baby-bedroom') ?>"><?= t('Shop the bedroom', 'تسوق غرفة النوم') ?></a>
    </div>
    <div class="banner-strip__art" aria-hidden="true">
      <svg viewBox="0 0 220 160" width="320" height="220">
        <rect x="20" y="80" width="180" height="60" rx="10" fill="#a7e8d8"/>
        <rect x="30" y="60" width="60" height="40" rx="6" fill="#fff"/>
        <circle cx="170" cy="50" r="14" fill="#ffd6e7"/>
        <path d="M150 80q20-30 40 0" stroke="#fff" stroke-width="4" fill="none"/>
      </svg>
    </div>
  </div>
</section>

<!-- ON SALE ------------------------------------------------------- -->
<?php if ($on_sale): ?>
<section class="section">
  <div class="container">
    <header class="section__head">
      <h2 class="section__title"><?= t('On sale', 'تخفيضات') ?></h2>
      <a class="section__more" href="<?= url('shop.php?on_sale=1') ?>"><?= t('View all', 'عرض الكل') ?> →</a>
    </header>
    <div class="products-grid">
      <?php foreach ($on_sale as $p) include __DIR__ . '/includes/product_card.php'; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- NEWEST -------------------------------------------------------- -->
<?php if ($newest): ?>
<section class="section section--alt">
  <div class="container">
    <header class="section__head">
      <h2 class="section__title"><?= t('New arrivals', 'وصل حديثًا') ?></h2>
      <a class="section__more" href="<?= url('shop.php') ?>"><?= t('View all', 'عرض الكل') ?> →</a>
    </header>
    <div class="products-grid">
      <?php foreach ($newest as $p) include __DIR__ . '/includes/product_card.php'; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
