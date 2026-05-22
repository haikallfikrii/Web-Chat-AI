<?php
/**
 * Cek koneksi DB & lingkungan (staging/production).
 * https://staging.chatlm.tech/health.php
 * Hapus atau blokir setelah setup production selesai.
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$out = [
    'ok'           => true,
    'app_env'      => APP_ENV,
    'app_site_url' => APP_SITE_URL,
    'db_name'      => DB_NAME,
    'db_host'      => DB_HOST,
    'http_host'    => $_SERVER['HTTP_HOST'] ?? '',
    'detected'     => config_detect_environment(),
    'has_local'    => is_file(__DIR__ . '/config.local.php'),
];

try {
    $pdo = get_db();
    $out['db_connected'] = true;
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $required = ['clients', 'widget_settings', 'users', 'chat_messages'];
    $out['tables_ok'] = count(array_intersect($required, $tables)) === count($required);
    if (!$out['tables_ok']) {
        $out['ok'] = false;
        $out['hint'] = 'Jalankan schema/hostinger_install.sql di phpMyAdmin untuk DB ini.';
    }
} catch (Throwable $e) {
    $out['ok'] = false;
    $out['db_connected'] = false;
    $out['error'] = APP_ENV === 'staging' ? $e->getMessage() : 'Database connection failed';
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
