<?php
require __DIR__ . '/_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') ajax_err('Method not allowed', 405);

$pid = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
$size  = $_POST['size']  ?? null;
$color = $_POST['color'] ?? null;

if ($pid <= 0) ajax_err('Invalid product');

if (!cart_add($pid, $qty, $size ?: null, $color ?: null)) {
    ajax_err('Could not add to cart');
}

ajax_ok([
    'count'  => cart_count(),
    'totals' => cart_totals_summary(),
]);

function cart_totals_summary(): array {
    $t = cart_totals();
    return [
        'subtotal' => money($t['subtotal']),
        'shipping' => $t['shipping'] > 0 ? money($t['shipping']) : t('Free', 'مجاني'),
        'total'    => money($t['total']),
    ];
}
