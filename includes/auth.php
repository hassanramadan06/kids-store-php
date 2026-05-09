<?php
/**
 * Authentication helpers for both storefront users and admins.
 */
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// -----------------------------------------------------------------
// Storefront users
// -----------------------------------------------------------------

function user_login(string $email, string $password): ?array
{
    $user = DB::one('SELECT * FROM users WHERE email = ? AND is_active = 1', [$email]);
    if (!$user) return null;
    if (!password_verify($password, $user['password_hash'])) return null;

    session_regenerate_id(true);
    $_SESSION['user_id']   = (int)$user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    return $user;
}

function user_register(string $name, string $email, string $password, string $phone = ''): array
{
    if (DB::one('SELECT id FROM users WHERE email = ?', [$email])) {
        return ['ok' => false, 'error' => 'Email already registered.'];
    }
    DB::run(
        'INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)',
        [$name, $email, $phone, password_hash($password, PASSWORD_BCRYPT)]
    );
    $id = DB::lastId();
    session_regenerate_id(true);
    $_SESSION['user_id']   = $id;
    $_SESSION['user_name'] = $name;
    return ['ok' => true, 'id' => $id];
}

function user_logout(): void
{
    unset($_SESSION['user_id'], $_SESSION['user_name']);
    session_regenerate_id(true);
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) return null;
    return DB::one('SELECT * FROM users WHERE id = ?', [(int)$_SESSION['user_id']]) ?: null;
}

function require_user(): void
{
    if (empty($_SESSION['user_id'])) {
        $_SESSION['flash_error'] = 'Please log in to continue.';
        redirect('login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
    }
}

// -----------------------------------------------------------------
// Admin
// -----------------------------------------------------------------

function admin_login(string $username, string $password): ?array
{
    $admin = DB::one(
        'SELECT * FROM admins WHERE (username = ? OR email = ?) AND is_active = 1',
        [$username, $username]
    );
    if (!$admin) return null;
    if (!password_verify($password, $admin['password_hash'])) return null;

    session_regenerate_id(true);
    $_SESSION['admin_id']   = (int)$admin['id'];
    $_SESSION['admin_name'] = $admin['full_name'];
    DB::run('UPDATE admins SET last_login = NOW() WHERE id = ?', [(int)$admin['id']]);
    return $admin;
}

function admin_logout(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_name']);
    session_regenerate_id(true);
}

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) return null;
    return DB::one('SELECT * FROM admins WHERE id = ?', [(int)$_SESSION['admin_id']]);
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        redirect('admin/login.php');
    }
}
