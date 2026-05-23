<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/billing.php';
require_once __DIR__ . '/../includes/managed_ai.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$user = require_login();
verify_csrf_or_die($_POST['csrf_token'] ?? null);

$primary_color           = trim((string) ($_POST['primary_color']           ?? ''));
$bot_name                = trim((string) ($_POST['bot_name']                ?? ''));
$bot_avatar_url          = trim((string) ($_POST['bot_avatar_url']          ?? ''));
$welcome_message         = trim((string) ($_POST['welcome_message']         ?? ''));
$allowed_origins         = trim((string) ($_POST['allowed_origins']         ?? ''));
$ai_provider             = trim((string) ($_POST['ai_provider']             ?? ''));
$ai_model                = trim((string) ($_POST['ai_model']                ?? ''));
$ai_system_prompt        = trim((string) ($_POST['ai_system_prompt']        ?? ''));
$ai_api_key              = trim((string) ($_POST['ai_api_key']              ?? ''));
$n8n_webhook_url         = trim((string) ($_POST['n8n_webhook_url']         ?? ''));
$telegram_notify_enabled = isset($_POST['telegram_notify_enabled']) ? 1 : 0;
$telegram_chat_id        = trim((string) ($_POST['telegram_chat_id']        ?? ''));
$avatar_file             = $_FILES['bot_avatar_file'] ?? null;

$allowed_providers = ['openai', 'google', 'deepseek', 'openrouter'];

$billing_client = billing_fetch_client((int) $user['client_id']);
$is_managed_plan = $billing_client && (string) ($billing_client['api_key_source'] ?? 'user') === 'system';

if (!preg_match('/^#[0-9a-fA-F]{6}$/', $primary_color)) {
    set_flash('error', 'Warna utama harus format HEX seperti #2563EB.');
    header('Location: ' . app_url('/dashboard.php'));
    exit;
}

if ($bot_name === '' || mb_strlen($bot_name, 'UTF-8') > 80) {
    set_flash('error', 'Nama bot wajib diisi dan maksimal 80 karakter.');
    header('Location: ' . app_url('/dashboard.php'));
    exit;
}

if ($welcome_message === '') {
    set_flash('error', 'Welcome message wajib diisi.');
    header('Location: ' . app_url('/dashboard.php'));
    exit;
}

if (!in_array($ai_provider, $allowed_providers, true)) {
    set_flash('error', 'Provider AI tidak valid.');
    header('Location: ' . app_url('/dashboard.php'));
    exit;
}

if ($ai_model === '' || mb_strlen($ai_model, 'UTF-8') > 120) {
    set_flash('error', 'Model AI wajib diisi dan maksimal 120 karakter.');
    header('Location: ' . app_url('/dashboard.php'));
    exit;
}

if (is_array($avatar_file) && (($avatar_file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE)) {
    $upload_error = (int) ($avatar_file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($upload_error !== UPLOAD_ERR_OK) {
        set_flash('error', 'Upload avatar gagal. Silakan coba lagi.');
        header('Location: ' . app_url('/dashboard.php'));
        exit;
    }

    $upload_size = (int) ($avatar_file['size'] ?? 0);
    if ($upload_size <= 0 || $upload_size > 2 * 1024 * 1024) {
        set_flash('error', 'Ukuran avatar maksimal 2 MB.');
        header('Location: ' . app_url('/dashboard.php'));
        exit;
    }

    $tmp_name = (string) ($avatar_file['tmp_name'] ?? '');
    if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
        set_flash('error', 'File avatar tidak valid.');
        header('Location: ' . app_url('/dashboard.php'));
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = (string) $finfo->file($tmp_name);
    $allowed_images = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($allowed_images[$mime])) {
        set_flash('error', 'Format avatar harus PNG, JPG, WEBP, atau GIF.');
        header('Location: ' . app_url('/dashboard.php'));
        exit;
    }

    $upload_dir = dirname(__DIR__) . '/uploads/bot-avatars';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true) && !is_dir($upload_dir)) {
        set_flash('error', 'Folder upload avatar tidak dapat dibuat.');
        header('Location: ' . app_url('/dashboard.php'));
        exit;
    }

    $filename = sprintf(
        'client-%d-%s.%s',
        (int) $user['client_id'],
        bin2hex(random_bytes(8)),
        $allowed_images[$mime]
    );
    $target_path = $upload_dir . '/' . $filename;
    if (!move_uploaded_file($tmp_name, $target_path)) {
        set_flash('error', 'Gagal menyimpan file avatar.');
        header('Location: ' . app_url('/dashboard.php'));
        exit;
    }

    $bot_avatar_url = dashboard_base_url() . '/uploads/bot-avatars/' . rawurlencode($filename);
}

if ($bot_avatar_url !== '' && filter_var($bot_avatar_url, FILTER_VALIDATE_URL) === false) {
    set_flash('error', 'URL avatar bot tidak valid.');
    header('Location: ' . app_url('/dashboard.php'));
    exit;
}

if ($n8n_webhook_url !== '' && filter_var($n8n_webhook_url, FILTER_VALIDATE_URL) === false) {
    set_flash('error', 'n8n webhook URL tidak valid.');
    header('Location: ' . app_url('/dashboard.php'));
    exit;
}

$origins_clean = cors_ensure_app_site_in_list(cors_normalize_allowed_field($allowed_origins));

if ($telegram_chat_id !== '' && !is_valid_telegram_chat_id($telegram_chat_id)) {
    set_flash(
        'error',
        'Telegram Chat ID salah: itu terlihat seperti Bot Token BotFather. '
        . 'Token bot dipasang di config.local.php (TELEGRAM_BOT_TOKEN). '
        . 'Di sini isi Chat ID angka dari @userinfobot (contoh: 123456789).'
    );
    header('Location: ' . app_url('/dashboard.php'));
    exit;
}

if ($telegram_notify_enabled && $telegram_chat_id === '') {
    set_flash('error', 'Aktifkan notifikasi Telegram hanya setelah mengisi Telegram Chat ID.');
    header('Location: ' . app_url('/dashboard.php'));
    exit;
}

$tg_chat_clean = $telegram_chat_id !== '' ? mb_substr($telegram_chat_id, 0, 64, 'UTF-8') : null;

try {
    $pdo = get_db();

    $params = [
        ':client_id'               => (int) $user['client_id'],
        ':primary_color'           => $primary_color,
        ':bot_name'                => mb_substr($bot_name, 0, 80, 'UTF-8'),
        ':bot_avatar_url'          => mb_substr($bot_avatar_url, 0, 500, 'UTF-8'),
        ':welcome_message'         => mb_substr($welcome_message, 0, 65535, 'UTF-8'),
        ':allowed_origins'         => mb_substr($origins_clean, 0, 5000, 'UTF-8'),
        ':ai_provider'             => $ai_provider,
        ':ai_model'                => mb_substr($ai_model, 0, 120, 'UTF-8'),
        ':ai_system_prompt'        => mb_substr($ai_system_prompt, 0, 65535, 'UTF-8'),
        ':n8n_webhook_url'         => mb_substr($n8n_webhook_url, 0, 500, 'UTF-8'),
        ':telegram_notify_enabled' => $telegram_notify_enabled,
        ':telegram_chat_id'        => $tg_chat_clean,
    ];

    if ($ai_api_key !== '') {
        $params[':ai_api_key'] = encrypt_secret($ai_api_key);

        $sql = 'INSERT INTO widget_settings
                    (client_id, primary_color, bot_name, bot_avatar_url, welcome_message,
                     allowed_origins, ai_provider, ai_model, ai_system_prompt, ai_api_key,
                     n8n_webhook_url, telegram_notify_enabled, telegram_chat_id)
                VALUES
                    (:client_id, :primary_color, :bot_name, :bot_avatar_url, :welcome_message,
                     :allowed_origins, :ai_provider, :ai_model, :ai_system_prompt, :ai_api_key,
                     :n8n_webhook_url, :telegram_notify_enabled, :telegram_chat_id)
                ON DUPLICATE KEY UPDATE
                    primary_color = VALUES(primary_color),
                    bot_name = VALUES(bot_name),
                    bot_avatar_url = VALUES(bot_avatar_url),
                    welcome_message = VALUES(welcome_message),
                    allowed_origins = VALUES(allowed_origins),
                    ai_provider = VALUES(ai_provider),
                    ai_model = VALUES(ai_model),
                    ai_system_prompt = VALUES(ai_system_prompt),
                    ai_api_key = VALUES(ai_api_key),
                    n8n_webhook_url = VALUES(n8n_webhook_url),
                    telegram_notify_enabled = VALUES(telegram_notify_enabled),
                    telegram_chat_id = VALUES(telegram_chat_id)';
    } else {
        $sql = 'INSERT INTO widget_settings
                    (client_id, primary_color, bot_name, bot_avatar_url, welcome_message,
                     allowed_origins, ai_provider, ai_model, ai_system_prompt,
                     n8n_webhook_url, telegram_notify_enabled, telegram_chat_id)
                VALUES
                    (:client_id, :primary_color, :bot_name, :bot_avatar_url, :welcome_message,
                     :allowed_origins, :ai_provider, :ai_model, :ai_system_prompt,
                     :n8n_webhook_url, :telegram_notify_enabled, :telegram_chat_id)
                ON DUPLICATE KEY UPDATE
                    primary_color = VALUES(primary_color),
                    bot_name = VALUES(bot_name),
                    bot_avatar_url = VALUES(bot_avatar_url),
                    welcome_message = VALUES(welcome_message),
                    allowed_origins = VALUES(allowed_origins),
                    ai_provider = VALUES(ai_provider),
                    ai_model = VALUES(ai_model),
                    ai_system_prompt = VALUES(ai_system_prompt),
                    n8n_webhook_url = VALUES(n8n_webhook_url),
                    telegram_notify_enabled = VALUES(telegram_notify_enabled),
                    telegram_chat_id = VALUES(telegram_chat_id)';
    }

    $pdo->prepare($sql)->execute($params);

    $pdo->prepare(
        'UPDATE clients SET whitelist_domains = :wl WHERE id = :id'
    )->execute([
        ':wl' => mb_substr($origins_clean, 0, 5000, 'UTF-8'),
        ':id' => (int) $user['client_id'],
    ]);

    set_flash('success', $ai_api_key !== ''
        ? '✅ Pengaturan tersimpan dan AI API key berhasil diperbarui.'
        : '✅ Pengaturan tersimpan. AI API key lama tetap dipakai.');

} catch (Throwable $e) {
    error_log('[save-settings] ' . $e->getMessage());
    set_flash('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
}

header('Location: ' . app_url('/dashboard.php'));
exit;
