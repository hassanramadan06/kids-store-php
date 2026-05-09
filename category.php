<?php
/** Category landing page. */
require_once __DIR__ . '/includes/init.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$cat  = $slug ? get_category_by_slug($slug) : null;
if (!$cat) {
    http_response_code(404);
    $page_title = t('Not found', 'غير موجود');
    include __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:4rem 0;text-align:center;"><h1>404</h1><p>'.t('Category not found.', 'الفئة غير موجودة.').'</p></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
$section = get_section((int)$cat['section_id']);

$filters = [
    'section_id'  => (int)$cat['section_id'],
    'category_id' => (int)$cat['id'],
    'q'           => trim((string)($_GET['q'] ?? '')),
    'min_price'   => (float)      ($_GET['min_price'] ?? 0),
    'max_price'   => (float)      ($_GET['max_price'] ?? 0),
    'on_sale'     => !empty($_GET['on_sale']) ? 1 : 0,
    'sort'        => $_GET['sort'] ?? 'newest',
];
$page   = max(1, (int)($_GET['page'] ?? 1));
$result = search_products($filters, $page, 12);
$section_title = tr($cat, 'name');
$page_title    = $section_title;
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/listing.php';
include __DIR__ . '/includes/footer.php';
