<?php
/** Required by every admin page. Authenticates and exposes layout helpers. */
declare(strict_types=1);
require_once __DIR__ . '/../../includes/init.php';
require_admin();
$current_admin = current_admin();
if (!$current_admin) {
    redirect('admin/login.php');
}
