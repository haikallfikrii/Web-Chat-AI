<?php
/**
 * config.php
 * Konfigurasi koneksi database dan konstanta global.
 * Letakkan file ini DI LUAR public_html jika memungkinkan.
 */

declare(strict_types=1);

// ── Konfigurasi Database ─────────────────────────────────────
define('DB_HOST',    getenv('DB_HOST')    ?: '127.0.0.1');
define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
define('DB_NAME',    getenv('DB_NAME')    ?: 'u451240370_chatpopup');
define('DB_USER',    getenv('DB_USER')    ?: 'u451240370_khalfikrii');
define('DB_PASS',    getenv('DB_PASS')    ?: 'AellImehh10.');
define('DB_CHARSET', 'utf8mb4');

// ── Konfigurasi Aplikasi ─────────────────────────────────────
define('APP_ENV',   getenv('APP_ENV') ?: 'production'); // 'development' | 'production'
define('APP_SECRET', getenv('APP_SECRET') ?: 'ganti-dengan-secret-acak-panjang');

// Timeout HTTP untuk pemanggilan AI (detik) — shared hosting: sesuaikan dengan max_execution_time
define('AI_HTTP_TIMEOUT', (int) (getenv('AI_HTTP_TIMEOUT') ?: 55));

// Endpoint OpenAI-compatible (bisa dioverride lewat env)
define('OPENAI_API_URL',       getenv('OPENAI_API_URL')       ?: 'https://api.openai.com/v1/chat/completions');
define('DEEPSEEK_API_URL',     getenv('DEEPSEEK_API_URL')     ?: 'https://api.deepseek.com/v1/chat/completions');
define('OPENROUTER_API_URL',   getenv('OPENROUTER_API_URL')   ?: 'https://openrouter.ai/api/v1/chat/completions');

// URL dasar Gemini (model dan query ?key= ditambahkan di kode)
define('GEMINI_API_BASE', getenv('GEMINI_API_BASE') ?: 'https://generativelanguage.googleapis.com/v1beta/models');

// Telegram opsional: satu bot untuk semua tenant; chat_id per klien di widget_settings
define('TELEGRAM_BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN') ?: '');

// ── Branding & URL situs (watermark, email, Stripe redirect) ─
define('APP_NAME', getenv('APP_NAME') ?: 'ChatLM');
define('APP_SITE_URL', getenv('APP_SITE_URL') ?: 'https://chatlm.tech');

// ── Stripe Billing ───────────────────────────────────────────
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: '');
define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY') ?: '');
define('STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET') ?: '');
define('STRIPE_PRICE_STARTER_MONTHLY', getenv('STRIPE_PRICE_STARTER_MONTHLY') ?: '');
define('STRIPE_PRICE_PRO_MONTHLY', getenv('STRIPE_PRICE_PRO_MONTHLY') ?: '');
define('STRIPE_PRICE_STARTER_YEARLY', getenv('STRIPE_PRICE_STARTER_YEARLY') ?: '');
define('STRIPE_PRICE_PRO_YEARLY', getenv('STRIPE_PRICE_PRO_YEARLY') ?: '');

// Email transaksional (From header)
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: '');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: APP_NAME);
define('MAIL_SUPPORT', getenv('MAIL_SUPPORT') ?: '');
define('TRIAL_DAYS', (int) (getenv('TRIAL_DAYS') ?: 14));

// Header tambahan OpenRouter (disarankan oleh dokumentasi mereka)
define('OPENROUTER_HTTP_REFERER', getenv('OPENROUTER_HTTP_REFERER') ?: '');
define('OPENROUTER_APP_TITLE', getenv('OPENROUTER_APP_TITLE') ?: 'ChatLM');

// Timeout (detik) untuk webhook legacy n8n bila dipakai
define('WEBHOOK_TIMEOUT', (int) (getenv('WEBHOOK_TIMEOUT') ?: 30));

// ── Buat koneksi PDO (singleton sederhana) ───────────────────
function get_db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Jangan tampilkan detail error ke user di production
        if (APP_ENV === 'development') {
            error_log('[DB] ' . $e->getMessage());
        }
        http_response_code(500);
        die(json_encode(['error' => 'Database connection failed.']));
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
