<?php
/**
 * Cek konfigurasi server (staging vs production).
 * Akses via browser: /scripts/check-config.php
 * Hapus atau blokir file ini di production setelah setup selesai.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$out = [
    'ok'              => true,
    'app_env'         => APP_ENV,
    'app_site_url'    => APP_SITE_URL,
    'db_name'         => DB_NAME,
    'db_host'         => DB_HOST,
    'config_local'    => is_file(__DIR__ . '/../config.local.php'),
    'env_file'        => is_readable(__DIR__ . '/../.env'),
    'http_host'       => $_SERVER['HTTP_HOST'] ?? '',
    'detected_env'    => config_detect_environment(),
];

try {
    $pdo = get_db();
    $out['db_connected'] = true;
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $out['table_count'] = count($tables);
    $required = ['clients', 'widget_settings', 'users', 'chat_messages'];
    $out['tables_ok'] = array_values(array_intersect($required, $tables)) === $required;
} catch (Throwable $e) {
    $out['ok'] = false;
    $out['db_connected'] = false;
    $out['db_error'] = APP_ENV === 'staging' ? $e->getMessage() : 'connection failed';
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
