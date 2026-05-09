<?php
/**
 * Shared product listing layout for shop/section/category/search.
 * Expects:
 *   $section        (optional row, when listing inside a section)
 *   $section_title  (optional override of heading)
 *   $filters        (associative array)
 *   $result         (return value of search_products())
 */
$active_section_id  = (int)($filters['section_id']  ?? 0);
$active_category_id = (int)($filters['category_id'] ?? 0);
$cats_for_filter    = $active_section_id ? get_categories($active_section_id) : [];
$heading            = $section_title ?? t('Shop all', 'كل المنتجات');
$total              = (int)($result['total'] ?? 0);
?>
<section class="page-head">
  <div class="container">
    <nav class="crumbs" aria-label="breadcrumbs">
      <a href="<?= url('') ?>"><?= t('Home', 'الرئيسية') ?></a>
      <span>/</span>
      <a href="<?= url('shop.php') ?>"><?= t('Shop', 'المتجر') ?></a>
      <?php if (!empty($section)): ?>
        <span>/</span>
        <span><?= e(tr($section, 'name')) ?></span>
      <?php endif; ?>
    </nav>
    <h1><?= e($heading) ?></h1>
    <p><?= sprintf(t('%d products found', 'تم العثور على %d منتج'), $total) ?></p>
  </div>
</section>

<section class="listing">
  <div class="container listing__grid">
    <aside class="filters" aria-label="<?= e(t('Filters', 'الفلاتر')) ?>">
      <form method="get" class="filters__form">
        <?php if ($active_section_id): ?><input type="hidden" name="slug" value="<?= e($_GET['slug'] ?? '') ?>"><?php endif; ?>
        <h3><?= t('Search', 'بحث') ?></h3>
        <input type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="<?= e(t('Find product…', 'ابحث عن منتج…')) ?>">

        <?php if ($cats_for_filter): ?>
          <h3><?= t('Categories', 'الفئات') ?></h3>
          <ul class="filters__list">
            <li><label><input type="radio" name="category_id" value="0" <?= $active_category_id===0?'checked':'' ?>> <?= t('All', 'الكل') ?></label></li>
            <?php foreach ($cats_for_filter as $c): ?>
              <li><label><input type="radio" name="category_id" value="<?= (int)$c['id'] ?>" <?= $active_category_id===(int)$c['id']?'checked':'' ?>> <?= e(tr($c, 'name')) ?></label></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <h3><?= t('Section', 'القسم') ?></h3>
          <select name="section_id">
            <option value="0"><?= t('All sections', 'كل الأقسام') ?></option>
            <?php foreach (get_sections() as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= ((int)($filters['section_id']??0)===(int)$s['id'])?'selected':'' ?>>
                <?= e(tr($s, 'name')) ?>
              </option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>

        <h3><?= t('Price', 'السعر') ?></h3>
        <div class="filters__price">
          <input type="number" name="min_price" min="0" step="1" placeholder="<?= e(t('Min', 'من')) ?>" value="<?= e((string)($filters['min_price'] ?: '')) ?>">
          <input type="number" name="max_price" min="0" step="1" placeholder="<?= e(t('Max', 'إلى')) ?>" value="<?= e((string)($filters['max_price'] ?: '')) ?>">
        </div>

        <h3><?= t('Quick filters', 'فلاتر سريعة') ?></h3>
        <label><input type="checkbox" name="on_sale"  value="1" <?= !empty($filters['on_sale']) ?'checked':'' ?>> <?= t('On sale', 'تخفيضات') ?></label>
        <?php if (empty($filters['section_id']) && empty($filters['category_id'])): ?>
          <label><input type="checkbox" name="featured" value="1" <?= !empty($filters['featured'])?'checked':'' ?>> <?= t('Featured', 'مميز') ?></label>
        <?php endif; ?>

        <button type="submit" class="btn btn--primary btn--block"><?= t('Apply filters', 'تطبيق الفلاتر') ?></button>
        <a href="?slug=<?= e($_GET['slug'] ?? '') ?>" class="btn btn--ghost btn--block"><?= t('Reset', 'إعادة تعيين') ?></a>
      </form>
    </aside>

    <div class="listing__main">
      <div class="listing__toolbar">
        <span><?= sprintf(t('%d results', '%d نتيجة'), $total) ?></span>
        <form method="get" class="listing__sort">
          <?php foreach ($_GET as $k => $v): if ($k === 'sort' || $k === 'page' || is_array($v)) continue; ?>
            <input type="hidden" name="<?= e((string)$k) ?>" value="<?= e((string)$v) ?>">
          <?php endforeach; ?>
          <label><?= t('Sort by', 'ترتيب حسب') ?>:
            <select name="sort" onchange="this.form.submit()">
              <option value="newest"     <?= ($filters['sort']??'')==='newest'    ?'selected':'' ?>><?= t('Newest', 'الأحدث') ?></option>
              <option value="price_asc"  <?= ($filters['sort']??'')==='price_asc' ?'selected':'' ?>><?= t('Price: low to high', 'السعر: من الأقل') ?></option>
              <option value="price_desc" <?= ($filters['sort']??'')==='price_desc'?'selected':'' ?>><?= t('Price: high to low', 'السعر: من الأعلى') ?></option>
              <option value="name"       <?= ($filters['sort']??'')==='name'      ?'selected':'' ?>><?= t('Name', 'الاسم') ?></option>
              <option value="popular"    <?= ($filters['sort']??'')==='popular'   ?'selected':'' ?>><?= t('Most popular', 'الأكثر مشاهدة') ?></option>
            </select>
          </label>
        </form>
      </div>

      <?php if (!empty($result['rows'])): ?>
        <div class="products-grid">
          <?php foreach ($result['rows'] as $p) include __DIR__ . '/product_card.php'; ?>
        </div>

        <?php if ($result['pages'] > 1):
          $build = function(int $p) {
              $q = $_GET; $q['page'] = $p;
              return '?' . http_build_query($q);
          };
        ?>
        <nav class="pagination" aria-label="pagination">
          <?php if ($result['page'] > 1): ?>
            <a href="<?= e($build($result['page']-1)) ?>"><?= t('Prev', 'السابق') ?></a>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
            <a href="<?= e($build($i)) ?>" class="<?= $i === $result['page'] ? 'is-active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <?php if ($result['page'] < $result['pages']): ?>
            <a href="<?= e($build($result['page']+1)) ?>"><?= t('Next', 'التالي') ?></a>
          <?php endif; ?>
        </nav>
        <?php endif; ?>

      <?php else: ?>
        <div class="empty">
          <h3><?= t('No products match your filters.', 'لا توجد منتجات تطابق الفلاتر.') ?></h3>
          <p><?= t('Try widening your search or browsing all products.', 'حاول توسيع البحث أو تصفح كل المنتجات.') ?></p>
          <a class="btn btn--primary" href="<?= url('shop.php') ?>"><?= t('Browse all', 'تصفح الكل') ?></a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
