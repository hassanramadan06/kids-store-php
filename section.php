<?php
/** Section landing page. */
require_once __DIR__ . '/includes/init.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$section = $slug ? get_section_by_slug($slug) : null;
if (!$section) {
    http_response_code(404);
    $page_title = t('Not found', 'غير موجود');
    include __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:4rem 0;text-align:center;"><h1>404</h1><p>'.t('Section not found.', 'القسم غير موجود.').'</p></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$filters = [
    'section_id'  => (int)$section['id'],
    'category_id' => (int)         ($_GET['category_id'] ?? 0),
    'q'           => trim((string)  ($_GET['q'] ?? '')),
    'min_price'   => (float)        ($_GET['min_price'] ?? 0),
    'max_price'   => (float)        ($_GET['max_price'] ?? 0),
    'on_sale'     => !empty($_GET['on_sale']) ? 1 : 0,
    'sort'        => $_GET['sort'] ?? 'newest',
];
$page    = max(1, (int)($_GET['page'] ?? 1));
$result  = search_products($filters, $page, 12);
$section_title = tr($section, 'name');
$page_title    = $section_title;
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/listing.php';
include __DIR__ . '/includes/footer.php';
