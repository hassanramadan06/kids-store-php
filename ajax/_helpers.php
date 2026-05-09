<?php
/**
 * Bootstrap shared by every AJAX endpoint.
 * Sets JSON content type and validates CSRF on writes.
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
}

function ajax_ok(array $data = []): void
{
    echo json_encode(array_merge(['ok' => true], $data));
    exit;
}
function ajax_err(string $msg, int $code = 400): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}
