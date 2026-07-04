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

    require_once __DIR__ . '/includes/db_schema.php';
    $out['managed_ai_schema'] = clients_managed_ai_ready($pdo);

    $headFile = __DIR__ . '/.git/HEAD';
    if (is_file($headFile)) {
        $head = trim((string) file_get_contents($headFile));
        if (str_starts_with($head, 'ref: ')) {
            $ref = __DIR__ . '/.git/' . substr($head, 5);
            if (is_file($ref)) {
                $out['git_commit'] = substr(trim((string) file_get_contents($ref)), 0, 7);
            }
        } else {
            $out['git_commit'] = substr($head, 0, 7);
        }
    }
} catch (Throwable $e) {
    $out['ok'] = false;
    $out['db_connected'] = false;
    $out['error'] = APP_ENV === 'staging' ? $e->getMessage() : 'Database connection failed';
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
