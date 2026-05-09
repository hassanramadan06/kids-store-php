<?php
require __DIR__ . '/_helpers.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') ajax_err('Method not allowed', 405);

$cid = (int)($_POST['cart_id'] ?? 0);
if ($cid <= 0) ajax_err('Invalid cart item');
cart_remove($cid);
ajax_ok(['count' => cart_count()]);
