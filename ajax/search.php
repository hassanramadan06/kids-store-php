<?php
require __DIR__ . '/_helpers.php';
$q = trim((string)($_GET['q'] ?? ''));
$len = function_exists('mb_strlen') ? mb_strlen($q) : strlen($q);
if ($q === '' || $len < 2) ajax_ok(['rows' => []]);

$result = search_products(['q' => $q], 1, 8);
$rows = [];
foreach ($result['rows'] as $p) {
    $rows[] = [
        'id'    => (int)$p['id'],
        'name'  => tr($p, 'name'),
        'url'   => url('product.php?slug=' . urlencode($p['slug'])),
        'price' => money($p['discount_price'] !== null && $p['discount_price'] < $p['price'] ? (float)$p['discount_price'] : (float)$p['price']),
        'img'   => product_primary_image((int)$p['id']),
    ];
}
ajax_ok(['rows' => $rows]);
