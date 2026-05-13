<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

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

$allowed_providers = ['openai', 'google', 'deepseek', 'openrouter'];

if (!preg_match('/^#[0-9a-fA-F]{6}$/', $primary_color)) {
    set_flash('error', 'Warna utama harus format HEX seperti #2563EB.');
    header('Location: /dashboard.php');
    exit;
}

if ($bot_name === '' || mb_strlen($bot_name, 'UTF-8') > 80) {
    set_flash('error', 'Nama bot wajib diisi dan maksimal 80 karakter.');
    header('Location: /dashboard.php');
    exit;
}

if ($welcome_message === '') {
    set_flash('error', 'Welcome message wajib diisi.');
    header('Location: /dashboard.php');
    exit;
}

if (!in_array($ai_provider, $allowed_providers, true)) {
    set_flash('error', 'Provider AI tidak valid.');
    header('Location: /dashboard.php');
    exit;
}

if ($ai_model === '' || mb_strlen($ai_model, 'UTF-8') > 120) {
    set_flash('error', 'Model AI wajib diisi dan maksimal 120 karakter.');
    header('Location: /dashboard.php');
    exit;
}

if ($bot_avatar_url !== '' && filter_var($bot_avatar_url, FILTER_VALIDATE_URL) === false) {
    set_flash('error', 'URL avatar bot tidak valid.');
    header('Location: /dashboard.php');
    exit;
}

if ($n8n_webhook_url !== '' && filter_var($n8n_webhook_url, FILTER_VALIDATE_URL) === false) {
    set_flash('error', 'n8n webhook URL tidak valid.');
    header('Location: /dashboard.php');
    exit;
}

$origins_clean = $allowed_origins !== '' ? $allowed_origins : '*';
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

    set_flash('success', $ai_api_key !== ''
        ? '✅ Pengaturan tersimpan dan AI API key berhasil diperbarui.'
        : '✅ Pengaturan tersimpan. AI API key lama tetap dipakai.');

} catch (Throwable $e) {
    error_log('[save-settings] ' . $e->getMessage());
    set_flash('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
}

header('Location: /dashboard.php');
exit;
