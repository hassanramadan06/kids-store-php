<?php
/** Search results page (renders the same listing layout). */
require_once __DIR__ . '/includes/init.php';

$filters = [
    'q'    => trim((string)($_GET['q'] ?? '')),
    'sort' => $_GET['sort'] ?? 'newest',
];
$page    = max(1, (int)($_GET['page'] ?? 1));
$result  = search_products($filters, $page, 12);
$section_title = sprintf(t('Search: %s', 'البحث: %s'), $filters['q'] ?: t('all', 'الكل'));
$page_title    = $section_title;

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/listing.php';
include __DIR__ . '/includes/footer.php';
