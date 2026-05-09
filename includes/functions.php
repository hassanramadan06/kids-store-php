<?php
/**
 * Shared helpers: escaping, money, language, slug, image, cart helpers, etc.
 */
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// ---------------------------------------------------------------
// HTML / output helpers
// ---------------------------------------------------------------

/** Escape any string before writing it into HTML. */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build a URL relative to the site BASE_URL. */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Build a URL pointing at /uploads. */
function upload_url(string $path): string
{
    if ($path === '' || $path === null) {
        return url('assets/images/placeholder-1.svg');
    }
    // Already absolute or already under assets/uploads
    if (preg_match('~^(https?:)?//~i', $path) || strpos($path, '/') === 0) {
        return $path;
    }
    if (strpos($path, 'assets/') === 0 || strpos($path, 'uploads/') === 0) {
        return url($path);
    }
    return UPLOAD_URL . '/' . ltrim($path, '/');
}

/** Redirect to a relative path. */
function redirect(string $to): void
{
    header('Location: ' . (preg_match('~^https?://~i', $to) ? $to : url($to)));
    exit;
}

// ---------------------------------------------------------------
// Language
// ---------------------------------------------------------------
function current_lang(): string
{
    if (!empty($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}
function is_ar(): bool { return current_lang() === 'ar'; }

/** Pick the right language column from a row (e.g. name_ar/name_en). */
function tr(array $row, string $base): string
{
    $lang = current_lang();
    $key  = $base . '_' . $lang;
    if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
        return (string)$row[$key];
    }
    foreach ([$base . '_ar', $base . '_en', $base] as $k) {
        if (isset($row[$k]) && $row[$k] !== '') return (string)$row[$k];
    }
    return '';
}

/** Translate a UI string. */
function t(string $en, ?string $ar = null): string
{
    return is_ar() ? ($ar ?? $en) : $en;
}

// ---------------------------------------------------------------
// Money
// ---------------------------------------------------------------
function money(float $amount): string
{
    $symbol = is_ar() ? CURRENCY_AR : CURRENCY_EN;
    return number_format($amount, 2) . ' ' . $symbol;
}

// ---------------------------------------------------------------
// Slug & strings
// ---------------------------------------------------------------
function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('~[\s/_]+~u', '-', $text);
    $text = preg_replace('~[^\p{L}\p{N}\-]+~u', '', $text);
    $text = preg_replace('~-+~', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? mb_strtolower($text, 'UTF-8') : (string) time();
}

function str_truncate(string $text, int $limit = 120): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)));
    if (mb_strlen($text, 'UTF-8') <= $limit) return $text;
    return mb_substr($text, 0, $limit, 'UTF-8') . '…';
}

// ---------------------------------------------------------------
// Image upload
// ---------------------------------------------------------------
/**
 * Validate and move an uploaded image. Returns the relative path
 * (e.g. "products/abc123.jpg") on success, or null on failure.
 */
function save_uploaded_image(array $file, string $sub_dir = 'products'): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if (($file['size'] ?? 0) > 8 * 1024 * 1024) { // 8 MB
        return null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']) ?: '';
    finfo_close($finfo);
    if (!isset($allowed[$mime])) return null;

    if (!@getimagesize($file['tmp_name'])) {
        return null;
    }

    $dir = UPLOAD_PATH . '/' . $sub_dir;
    if (!is_dir($dir)) @mkdir($dir, 0775, true);

    $name = bin2hex(random_bytes(10)) . '.' . $allowed[$mime];
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return null;

    @chmod($dest, 0644);
    return $sub_dir . '/' . $name;
}

function delete_upload(?string $relative_path): void
{
    if (!$relative_path) return;
    if (strpos($relative_path, '..') !== false) return;
    if (strpos($relative_path, 'assets/') === 0) return; // never delete bundled assets
    $abs = UPLOAD_PATH . '/' . ltrim($relative_path, '/');
    if (is_file($abs)) @unlink($abs);
}

// ---------------------------------------------------------------
// Validation
// ---------------------------------------------------------------
function v_required($value): bool { return is_string($value) ? trim($value) !== '' : !empty($value); }
function v_email(string $email): bool { return (bool) filter_var($email, FILTER_VALIDATE_EMAIL); }
function v_int($value): bool { return is_numeric($value) && (int)$value == $value; }

// ---------------------------------------------------------------
// Common queries
// ---------------------------------------------------------------
function get_sections(bool $only_active = true): array
{
    $sql = 'SELECT * FROM sections';
    if ($only_active) $sql .= ' WHERE is_active = 1';
    $sql .= ' ORDER BY sort_order ASC, id ASC';
    return DB::all($sql);
}

function get_categories(?int $section_id = null, bool $only_active = true): array
{
    $sql = 'SELECT * FROM categories WHERE 1=1';
    $params = [];
    if ($only_active) $sql .= ' AND is_active = 1';
    if ($section_id)  { $sql .= ' AND section_id = ?'; $params[] = $section_id; }
    $sql .= ' ORDER BY sort_order ASC, id ASC';
    return DB::all($sql, $params);
}

function get_section(int $id): ?array
{
    return DB::one('SELECT * FROM sections WHERE id = ?', [$id]);
}
function get_section_by_slug(string $slug): ?array
{
    return DB::one('SELECT * FROM sections WHERE slug = ?', [$slug]);
}
function get_category_by_slug(string $slug): ?array
{
    return DB::one('SELECT * FROM categories WHERE slug = ?', [$slug]);
}

function get_product(int $id): ?array
{
    return DB::one('SELECT * FROM products WHERE id = ?', [$id]);
}

function get_product_by_slug(string $slug): ?array
{
    return DB::one('SELECT * FROM products WHERE slug = ? AND is_active = 1', [$slug]);
}

function get_product_images(int $product_id): array
{
    return DB::all(
        'SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC',
        [$product_id]
    );
}

function product_primary_image(int $product_id): string
{
    $img = DB::scalar(
        'SELECT image_path FROM product_images WHERE product_id = ?
         ORDER BY is_primary DESC, sort_order ASC, id ASC LIMIT 1',
        [$product_id]
    );
    return $img ? upload_url((string)$img) : url('assets/images/placeholder-1.svg');
}

/**
 * Generic product search/listing.
 * @return array { rows, total, pages, page }
 */
function search_products(array $filters = [], int $page = 1, int $per_page = 12): array
{
    $page     = max(1, $page);
    $per_page = max(1, min(48, $per_page));
    $where    = ['p.is_active = 1'];
    $params   = [];

    if (!empty($filters['section_id'])) {
        $where[] = 'p.section_id = ?';
        $params[] = (int)$filters['section_id'];
    }
    if (!empty($filters['category_id'])) {
        $where[] = 'p.category_id = ?';
        $params[] = (int)$filters['category_id'];
    }
    if (!empty($filters['featured'])) {
        $where[] = 'p.is_featured = 1';
    }
    if (!empty($filters['on_sale'])) {
        $where[] = 'p.discount_price IS NOT NULL AND p.discount_price < p.price';
    }
    if (!empty($filters['q'])) {
        $where[]  = '(p.name_ar LIKE ? OR p.name_en LIKE ? OR p.description_ar LIKE ? OR p.description_en LIKE ? OR p.sku LIKE ?)';
        $like     = '%' . $filters['q'] . '%';
        array_push($params, $like, $like, $like, $like, $like);
    }
    if (!empty($filters['min_price'])) {
        $where[] = 'COALESCE(p.discount_price, p.price) >= ?';
        $params[] = (float)$filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $where[] = 'COALESCE(p.discount_price, p.price) <= ?';
        $params[] = (float)$filters['max_price'];
    }

    $sql_where = 'WHERE ' . implode(' AND ', $where);

    $order = 'p.created_at DESC';
    switch ($filters['sort'] ?? '') {
        case 'price_asc':  $order = 'COALESCE(p.discount_price, p.price) ASC';  break;
        case 'price_desc': $order = 'COALESCE(p.discount_price, p.price) DESC'; break;
        case 'name':       $order = (is_ar() ? 'p.name_ar' : 'p.name_en') . ' ASC'; break;
        case 'popular':    $order = 'p.views DESC'; break;
    }

    $total = (int) DB::scalar("SELECT COUNT(*) FROM products p $sql_where", $params);
    $pages = max(1, (int) ceil($total / $per_page));
    $page  = min($page, $pages);
    $offset = ($page - 1) * $per_page;

    $rows = DB::all(
        "SELECT p.* FROM products p $sql_where ORDER BY $order LIMIT $per_page OFFSET $offset",
        $params
    );

    return ['rows' => $rows, 'total' => $total, 'pages' => $pages, 'page' => $page, 'per_page' => $per_page];
}

// ---------------------------------------------------------------
// Cart helpers (works for both guests + logged-in users)
// ---------------------------------------------------------------
function cart_owner(): array
{
    if (!empty($_SESSION['user_id'])) {
        return ['user_id' => (int)$_SESSION['user_id'], 'session_id' => null];
    }
    if (empty($_SESSION['cart_sid'])) {
        $_SESSION['cart_sid'] = bin2hex(random_bytes(16));
    }
    return ['user_id' => null, 'session_id' => $_SESSION['cart_sid']];
}

function cart_count(): int
{
    $o = cart_owner();
    if ($o['user_id']) {
        return (int) DB::scalar('SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?', [$o['user_id']]);
    }
    return (int) DB::scalar('SELECT COALESCE(SUM(quantity),0) FROM cart WHERE session_id = ?', [$o['session_id']]);
}

function cart_items(): array
{
    $o = cart_owner();
    if ($o['user_id']) {
        return DB::all(
            'SELECT c.*, p.name_ar, p.name_en, p.slug, p.price, p.discount_price, p.stock
             FROM cart c JOIN products p ON p.id = c.product_id
             WHERE c.user_id = ? ORDER BY c.id DESC',
            [$o['user_id']]
        );
    }
    return DB::all(
        'SELECT c.*, p.name_ar, p.name_en, p.slug, p.price, p.discount_price, p.stock
         FROM cart c JOIN products p ON p.id = c.product_id
         WHERE c.session_id = ? ORDER BY c.id DESC',
        [$o['session_id']]
    );
}

function cart_add(int $product_id, int $qty = 1, ?string $size = null, ?string $color = null): bool
{
    $product = get_product($product_id);
    if (!$product || !$product['is_active']) return false;
    $qty = max(1, $qty);
    $o = cart_owner();

    // Try to merge with an existing line that has same size/color
    $sql = 'SELECT id, quantity FROM cart WHERE product_id = ? AND ' .
           ($o['user_id'] ? 'user_id = ?' : 'session_id = ?') .
           ' AND COALESCE(size,"") = COALESCE(?, "") AND COALESCE(color,"") = COALESCE(?, "") LIMIT 1';
    $row = DB::one($sql, [
        $product_id,
        $o['user_id'] ?? $o['session_id'],
        $size, $color,
    ]);
    if ($row) {
        DB::run('UPDATE cart SET quantity = quantity + ? WHERE id = ?', [$qty, $row['id']]);
    } else {
        DB::run(
            'INSERT INTO cart (user_id, session_id, product_id, quantity, size, color)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$o['user_id'], $o['session_id'], $product_id, $qty, $size, $color]
        );
    }
    return true;
}

function cart_update(int $cart_id, int $qty): void
{
    $o = cart_owner();
    $qty = max(0, $qty);
    if ($qty === 0) { cart_remove($cart_id); return; }
    if ($o['user_id']) {
        DB::run('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?', [$qty, $cart_id, $o['user_id']]);
    } else {
        DB::run('UPDATE cart SET quantity = ? WHERE id = ? AND session_id = ?', [$qty, $cart_id, $o['session_id']]);
    }
}

function cart_remove(int $cart_id): void
{
    $o = cart_owner();
    if ($o['user_id']) {
        DB::run('DELETE FROM cart WHERE id = ? AND user_id = ?', [$cart_id, $o['user_id']]);
    } else {
        DB::run('DELETE FROM cart WHERE id = ? AND session_id = ?', [$cart_id, $o['session_id']]);
    }
}

function cart_clear(): void
{
    $o = cart_owner();
    if ($o['user_id']) DB::run('DELETE FROM cart WHERE user_id = ?', [$o['user_id']]);
    else DB::run('DELETE FROM cart WHERE session_id = ?', [$o['session_id']]);
}

function cart_totals(): array
{
    $items = cart_items();
    $subtotal = 0;
    foreach ($items as $it) {
        $price = $it['discount_price'] !== null && $it['discount_price'] < $it['price']
            ? (float)$it['discount_price'] : (float)$it['price'];
        $subtotal += $price * (int)$it['quantity'];
    }
    $threshold = (float) (get_setting('free_shipping_threshold') ?: 1000);
    $shipping  = $subtotal > 0 && $subtotal < $threshold ? (float) (get_setting('shipping_flat') ?: 40) : 0;
    return ['subtotal' => $subtotal, 'shipping' => $shipping, 'total' => $subtotal + $shipping, 'items' => $items];
}

// ---------------------------------------------------------------
// Wishlist
// ---------------------------------------------------------------
function wishlist_toggle(int $product_id): bool
{
    $o = cart_owner();
    $where = $o['user_id']
        ? ['user_id = ? AND product_id = ?', [$o['user_id'], $product_id]]
        : ['session_id = ? AND product_id = ?', [$o['session_id'], $product_id]];
    $exists = DB::scalar('SELECT id FROM wishlist WHERE ' . $where[0], $where[1]);
    if ($exists) {
        DB::run('DELETE FROM wishlist WHERE id = ?', [$exists]);
        return false; // removed
    }
    DB::run('INSERT INTO wishlist (user_id, session_id, product_id) VALUES (?, ?, ?)',
        [$o['user_id'], $o['session_id'], $product_id]);
    return true; // added
}

function wishlist_count(): int
{
    $o = cart_owner();
    return (int) DB::scalar(
        'SELECT COUNT(*) FROM wishlist WHERE ' .
        ($o['user_id'] ? 'user_id = ?' : 'session_id = ?'),
        [$o['user_id'] ?? $o['session_id']]
    );
}
function wishlist_has(int $product_id): bool
{
    $o = cart_owner();
    return (bool) DB::scalar(
        'SELECT 1 FROM wishlist WHERE product_id = ? AND ' .
        ($o['user_id'] ? 'user_id = ?' : 'session_id = ?'),
        [$product_id, $o['user_id'] ?? $o['session_id']]
    );
}
function wishlist_items(): array
{
    $o = cart_owner();
    return DB::all(
        'SELECT w.id, p.* FROM wishlist w JOIN products p ON p.id = w.product_id
         WHERE ' . ($o['user_id'] ? 'w.user_id = ?' : 'w.session_id = ?') . ' ORDER BY w.id DESC',
        [$o['user_id'] ?? $o['session_id']]
    );
}

// ---------------------------------------------------------------
// Settings
// ---------------------------------------------------------------
function get_setting(string $key, ?string $default = null): ?string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (DB::all('SELECT key_name, value FROM settings') as $row) {
            $cache[$row['key_name']] = $row['value'];
        }
    }
    return $cache[$key] ?? $default;
}
