<?php
/**
 * config.php — Inti aplikasi (AMAN di Git, tanpa password DB).
 *
 * Rahasia per-server: buat config.local.php (lihat config.local.*.example.php).
 * File config.local.php di-ignore oleh .gitignore dan tidak tertimpa saat git pull.
 */
declare(strict_types=1);

// ── Loader konfigurasi ───────────────────────────────────────

function config_load_dotenv(): void
{
    $path = __DIR__ . '/.env';
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $eq = strpos($line, '=');
        if ($eq === false) {
            continue;
        }
        $key = trim(substr($line, 0, $eq));
        $val = trim(substr($line, $eq + 1));
        $val = trim($val, "\"'");
        if ($key === '' || getenv($key) !== false) {
            continue;
        }
        putenv($key . '=' . $val);
        $_ENV[$key] = $val;
    }
}

/** @return array<string, string> */
function config_load_local(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $file = __DIR__ . '/config.local.php';
    if (!is_file($file)) {
        $cache = [];
        return $cache;
    }

    $data = require $file;
    $cache = is_array($data) ? array_map('strval', $data) : [];
    return $cache;
}

function config_env(string $key, string $default = ''): string
{
    $v = getenv($key);
    if (is_string($v) && $v !== '') {
        return $v;
    }

    $local = config_load_local();
    if (isset($local[$key]) && $local[$key] !== '') {
        return $local[$key];
    }

    return $default;
}

/**
 * Deteksi staging vs production dari hostname (fallback jika APP_ENV kosong).
 */
function config_detect_environment(): string
{
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');

    if (
        str_contains($host, 'staging.')
        || str_contains($host, 'localhost')
        || str_contains($host, '127.0.0.1')
        || str_ends_with($host, '.local')
    ) {
        return 'staging';
    }

    return 'production';
}

function config_default_site_url(): string
{
    return config_detect_environment() === 'staging'
        ? 'https://staging.chatlm.tech'
        : 'https://chatlm.tech';
}

function config_is_configured(): bool
{
    return config_env('DB_NAME') !== '' && config_env('DB_USER') !== '';
}

function config_abort_if_missing(): void
{
    if (config_is_configured()) {
        return;
    }

    $msg = 'Konfigurasi database belum ada. Buat config.local.php di server '
        . '(salin dari config.local.staging.example.php atau config.local.production.example.php). '
        . 'Lihat DEPLOY_HOSTINGER.md';

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $msg . PHP_EOL);
        exit(1);
    }

    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:2rem;background:#030712;color:#e2e8f0">'
        . '<h1>ChatLM — setup diperlukan</h1><p>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p></body></html>';
    exit;
}

config_load_dotenv();
config_abort_if_missing();

// ── Konstanta aplikasi ───────────────────────────────────────

define('APP_ENV', config_env('APP_ENV', config_detect_environment()));
define('APP_SECRET', config_env('APP_SECRET', ''));
define('APP_NAME', config_env('APP_NAME', 'ChatLM'));
define('APP_SITE_URL', config_env('APP_SITE_URL', config_default_site_url()));

define('DB_HOST', config_env('DB_HOST', '127.0.0.1'));
define('DB_PORT', config_env('DB_PORT', '3306'));
define('DB_NAME', config_env('DB_NAME', ''));
define('DB_USER', config_env('DB_USER', ''));
define('DB_PASS', config_env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');

define('AI_HTTP_TIMEOUT', (int) config_env('AI_HTTP_TIMEOUT', '55'));
define('OPENAI_API_URL', config_env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'));
define('DEEPSEEK_API_URL', config_env('DEEPSEEK_API_URL', 'https://api.deepseek.com/v1/chat/completions'));
define('OPENROUTER_API_URL', config_env('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions'));
define('GEMINI_API_BASE', config_env('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta/models'));

define('TELEGRAM_BOT_TOKEN', config_env('TELEGRAM_BOT_TOKEN', ''));

define('STRIPE_SECRET_KEY', config_env('STRIPE_SECRET_KEY', ''));
define('STRIPE_PUBLISHABLE_KEY', config_env('STRIPE_PUBLISHABLE_KEY', ''));
define('STRIPE_WEBHOOK_SECRET', config_env('STRIPE_WEBHOOK_SECRET', ''));
define('STRIPE_PRICE_STARTER_MONTHLY', config_env('STRIPE_PRICE_STARTER_MONTHLY', ''));
define('STRIPE_PRICE_PRO_MONTHLY', config_env('STRIPE_PRICE_PRO_MONTHLY', ''));
define('STRIPE_PRICE_STARTER_YEARLY', config_env('STRIPE_PRICE_STARTER_YEARLY', ''));
define('STRIPE_PRICE_PRO_YEARLY', config_env('STRIPE_PRICE_PRO_YEARLY', ''));

define('MAIL_FROM_ADDRESS', config_env('MAIL_FROM_ADDRESS', ''));
define('MAIL_FROM_NAME', config_env('MAIL_FROM_NAME', APP_NAME));
define('MAIL_SUPPORT', config_env('MAIL_SUPPORT', ''));
define('TRIAL_DAYS', (int) config_env('TRIAL_DAYS', '14'));

/**
 * SMTP untuk email transaksional. Jika MAIL_SMTP_HOST kosong, sistem jatuh
 * kembali ke mail() bawaan PHP — yang di shared hosting sering gagal diam-diam.
 * MAIL_SMTP_SECURE: 'ssl' (port 465), 'tls' (port 587), atau 'none'.
 */
define('MAIL_SMTP_HOST', config_env('MAIL_SMTP_HOST', ''));
define('MAIL_SMTP_PORT', (int) config_env('MAIL_SMTP_PORT', '465'));
define('MAIL_SMTP_USER', config_env('MAIL_SMTP_USER', ''));
define('MAIL_SMTP_PASS', config_env('MAIL_SMTP_PASS', ''));
define('MAIL_SMTP_SECURE', config_env('MAIL_SMTP_SECURE', 'ssl'));
define('MAIL_SMTP_TIMEOUT', (int) config_env('MAIL_SMTP_TIMEOUT', '20'));

/** Email yang boleh akses /admin.php (pisahkan dengan koma). */
define('PLATFORM_ADMIN_EMAILS', config_env('PLATFORM_ADMIN_EMAILS', 'team@chatlm.tech'));
/** Inbox notifikasi registrasi & langganan baru. */
define('PLATFORM_NOTIFY_EMAIL', config_env('PLATFORM_NOTIFY_EMAIL', 'team@chatlm.tech'));

define('OPENROUTER_HTTP_REFERER', config_env('OPENROUTER_HTTP_REFERER', APP_SITE_URL));
define('OPENROUTER_APP_TITLE', config_env('OPENROUTER_APP_TITLE', APP_NAME));
define('WEBHOOK_TIMEOUT', (int) config_env('WEBHOOK_TIMEOUT', '30'));

/** API key widget demo di halaman marketing (index.php). Kosongkan untuk menonaktifkan. */
define('LANDING_WIDGET_API_KEY', config_env('LANDING_WIDGET_API_KEY', ''));

if (APP_SECRET === '' || strlen(APP_SECRET) < 16) {
    error_log('[config] APP_SECRET terlalu pendek atau kosong — set di config.local.php');
}

// ── Buat koneksi PDO (singleton sederhana) ───────────────────
function get_db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log('[DB] ' . APP_ENV . ' ' . DB_NAME . ' — ' . $e->getMessage());
        http_response_code(500);
        $detail = APP_ENV === 'staging' ? ' [' . APP_ENV . ' / ' . DB_NAME . ']' : '';
        die(json_encode(['error' => 'Database connection failed.' . $detail]));
    }

    return $pdo;
}

/** URL dasar aplikasi (tanpa trailing slash). */
function app_base_url(): string
{
    if (APP_SITE_URL !== '') {
        return rtrim(APP_SITE_URL, '/');
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

// ── Helper: kirim JSON response ─────────────────────────────
function send_json(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ── Helper: sanitasi string input ───────────────────────────
function clean_string(string $input, int $max_length = 5000): string
{
    $input = trim($input);
    $input = mb_substr($input, 0, $max_length, 'UTF-8');
    return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── Helper: validasi UUID v4 ────────────────────────────────
function is_valid_uuid(string $uuid): bool
{
    return (bool) preg_match(
        '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
        $uuid
    );
}

// ── Helper: validasi API Key (SHA-256 hex, 64 karakter) ──────
function is_valid_api_key(string $key): bool
{
    return (bool) preg_match('/^[0-9a-f]{64}$/i', $key);
}

// ── Set header CORS global ───────────────────────────────────
function set_cors_headers(string $allowed_origin = '*'): void
{
    header('Access-Control-Allow-Origin: ' . $allowed_origin);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');
    header('Access-Control-Max-Age: 86400');
}

/**
 * Kunci simetris 32-byte dari APP_SECRET (untuk AES-256-GCM).
 */
function app_crypto_key_binary(): string
{
    return hash('sha256', APP_SECRET, true);
}

/**
 * Enkripsi string sensitif untuk disimpan di DB (Base64: IV12 + tag16 + ciphertext).
 */
function encrypt_secret(string $plain): string
{
    if ($plain === '') {
        return '';
    }

    $key = app_crypto_key_binary();
    $iv  = random_bytes(12);
    $tag = '';

    $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '');
    if ($cipher === false) {
        throw new RuntimeException('encrypt_secret failed');
    }

    return base64_encode($iv . $tag . $cipher);
}

/**
 * Dekripsi nilai dari DB. Mengembalikan null jika format salah atau tag gagal.
 */
function decrypt_secret(string $stored): ?string
{
    if ($stored === '') {
        return '';
    }

    $raw = base64_decode($stored, true);
    if ($raw === false || strlen($raw) < 28) {
        return null;
    }

    $iv     = substr($raw, 0, 12);
    $tag    = substr($raw, 12, 16);
    $cipher = substr($raw, 28);
    $key    = app_crypto_key_binary();

    $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return $plain === false ? null : $plain;
}

/**
 * Chat ID Telegram (angka), bukan Bot Token dari BotFather.
 */
function is_valid_telegram_chat_id(string $chat_id): bool
{
    $chat_id = trim($chat_id);
    if ($chat_id === '') {
        return true;
    }
    // Bot token format: 123456789:AAHxxx...
    if (preg_match('/^\d+:[A-Za-z0-9_-]{20,}$/', $chat_id)) {
        return false;
    }

    return (bool) preg_match('/^-?\d{5,20}$/', $chat_id);
}

/**
 * Kirim notifikasi Telegram (sinkron, timeout pendek). Token dari TELEGRAM_BOT_TOKEN.
 */
function telegram_notify_new_message(
    string $chat_id,
    string $client_name,
    string $session_id,
    string $user_message,
    int $timeout_sec = 5
): void {
    $token = TELEGRAM_BOT_TOKEN;
    if ($token === '' || trim($chat_id) === '') {
        return;
    }

    $safe_name = htmlspecialchars(mb_substr($client_name, 0, 120), ENT_COMPAT | ENT_HTML5, 'UTF-8');
    $safe_sid  = htmlspecialchars($session_id, ENT_COMPAT | ENT_HTML5, 'UTF-8');
    $preview   = htmlspecialchars(mb_substr($user_message, 0, 3500), ENT_COMPAT | ENT_HTML5, 'UTF-8');

    $text = '<b>' . $safe_name . "</b>\n"
        . 'Sesi: <code>' . $safe_sid . "</code>\n\n"
        . $preview;

    $url     = 'https://api.telegram.org/bot' . $token . '/sendMessage';
    $payload = json_encode([
        'chat_id'                  => $chat_id,
        'text'                     => $text,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => true,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        return;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => max(1, min(30, $timeout_sec)),
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false,
    ]);

    $resp = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err !== '' || $code < 200 || $code >= 300) {
        error_log('[telegram] notify failed HTTP ' . $code . ' err=' . $err . ' body=' . mb_substr((string) $resp, 0, 500));
    }
}
