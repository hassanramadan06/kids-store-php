<?php
/**
 * Bootstrap file. Every public-facing PHP entry-point starts with:
 *
 *     require_once __DIR__ . '/includes/init.php';
 *
 * It sets up sessions, database, helpers, and exposes a few globals
 * that templates expect (`$lang`, `$rtl`, `$site_name`).
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';

$lang        = current_lang();
$rtl         = is_ar();
$site_name   = $rtl ? SITE_NAME_AR : SITE_NAME_EN;

// Flash messages helpers
function flash_set(string $type, string $message): void { $_SESSION['flash_' . $type] = $message; }
function flash_pop(string $type): ?string {
    if (empty($_SESSION['flash_' . $type])) return null;
    $m = $_SESSION['flash_' . $type];
    unset($_SESSION['flash_' . $type]);
    return $m;
}
