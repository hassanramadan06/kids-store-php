<?php
/**
 * Site-wide configuration.
 *
 * Copy `.env.example` to a real `.env` (or just edit this file directly) and
 * change the database / site values to match your environment.
 *
 * The constants here are loaded by every page through includes/init.php.
 */
declare(strict_types=1);

// ---------------------------------------------------------------
// Database connection
// ---------------------------------------------------------------
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
define('DB_NAME', getenv('DB_NAME') ?: 'kids_store');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------
// Site
// ---------------------------------------------------------------
define('SITE_NAME_AR', 'متجر الأطفال');
define('SITE_NAME_EN', 'Kids Store');
define('DEFAULT_LANG', 'ar');                       // 'ar' or 'en'
define('CURRENCY_AR', 'ج.م');
define('CURRENCY_EN', 'EGP');

// Auto-detect base URL so the project works at any sub-folder.
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $self   = $_SERVER['SCRIPT_NAME'] ?? '';
    // Walk up if we're inside /admin or /ajax
    $base = rtrim(str_replace('\\', '/', dirname($self)), '/');
    foreach (['/admin/ajax', '/admin', '/ajax'] as $sub) {
        if (substr($base, -strlen($sub)) === $sub) {
            $base = substr($base, 0, -strlen($sub));
        }
    }
    define('BASE_URL', $scheme . '://' . $host . $base);
}

// ---------------------------------------------------------------
// Filesystem
// ---------------------------------------------------------------
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL',  BASE_URL  . '/uploads');

// ---------------------------------------------------------------
// Security / sessions
// ---------------------------------------------------------------
define('SESSION_NAME', 'KIDS_STORE_SESS');
define('CSRF_KEY',     'csrf_token');

// Errors -- show in dev, silence in prod.
$debug = getenv('APP_DEBUG');
if ($debug === '1' || $debug === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

date_default_timezone_set(getenv('APP_TZ') ?: 'Africa/Cairo');
