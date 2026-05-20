<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

logout_user();
header('Location: ' . app_url('/login.php', ['logged_out' => 1]));
exit;
