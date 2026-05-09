<?php
/**
 * Cross-Site Request Forgery protection.
 * Use csrf_field() inside <form> blocks and csrf_check() before
 * processing POST data.
 */
declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION[CSRF_KEY])) {
        $_SESSION[CSRF_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_KEY];
}

function csrf_field(): string
{
    return '<input type="hidden" name="' . CSRF_KEY . '" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function csrf_check(?string $token = null): bool
{
    $token = $token ?? ($_POST[CSRF_KEY] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($token) && !empty($_SESSION[CSRF_KEY]) && hash_equals($_SESSION[CSRF_KEY], $token);
}

function csrf_require(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_check()) {
        http_response_code(419);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
        } else {
            echo 'Invalid security token. Please reload the page and try again.';
        }
        exit;
    }
}
