<?php
/** Generic shop / listing page with filters. */
require_once __DIR__ . '/includes/init.php';

$filters = [
    'q'           => trim((string)($_GET['q']           ?? '')),
    'section_id'  => (int)         ($_GET['section_id'] ?? 0),
    'category_id' => (int)         ($_GET['category_id']?? 0),
    'min_price'   => (float)       ($_GET['min_price']  ?? 0),
    'max_price'   => (float)       ($_GET['max_price']  ?? 0),
    'featured'    => !empty($_GET['featured']) ? 1 : 0,
    'on_sale'     => !empty($_GET['on_sale'])  ? 1 : 0,
    'sort'        => $_GET['sort'] ?? 'newest',
];
$page = max(1, (int)($_GET['page'] ?? 1));
$result = search_products($filters, $page, 12);

$page_title = is_ar() ? 'المتجر' : 'Shop';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/listing.php';
include __DIR__ . '/includes/footer.php';
