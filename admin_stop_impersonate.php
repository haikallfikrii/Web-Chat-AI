<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['admin_impersonating']) && !empty($_SESSION['admin_original_user'])) {
    $_SESSION['auth_user'] = $_SESSION['admin_original_user'];
    unset($_SESSION['admin_impersonating'], $_SESSION['admin_original_user']);
    set_flash('success', 'Impersonate mode diakhiri.');
}

header('Location: ' . app_url('/admin.php'));
exit;
