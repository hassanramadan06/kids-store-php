<?php
require_once __DIR__ . '/includes/init.php';
user_logout();
flash_set('success', t('Logged out.', 'تم تسجيل الخروج.'));
redirect('index.php');
