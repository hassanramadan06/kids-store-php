<?php
require __DIR__ . '/_helpers.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') ajax_err('Method not allowed', 405);
$pid = (int)($_POST['product_id'] ?? 0);
if ($pid <= 0) ajax_err('Invalid product');
$added = wishlist_toggle($pid);
ajax_ok(['added' => $added, 'count' => wishlist_count()]);
