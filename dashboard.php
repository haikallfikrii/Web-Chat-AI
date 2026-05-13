<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$user = require_login();
$flash = get_flash();
$settings = fetch_dashboard_settings((int) $user['client_id']);

if ($settings === null) {
    http_response_code(500);
    exit('Pengaturan client belum ditemukan.');
}

$baseUrl = dashboard_base_url();
$embedCode = '<script'
    . "\n  src=\"" . $baseUrl . '/widget/widget.js"'
    . "\n  data-api-key=\"" . $user['client_api_key'] . '"'
    . "\n  data-base-url=\"" . $baseUrl . '"'
    . "\n  async"
    . "\n></script>";
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Widget</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; }
        .layout { max-width: 1200px; margin: 0 auto; padding: 24px 18px 50px; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
        .card { background: #fff; border-radius: 18px; padding: 22px; box-shadow: 0 10px 30px rgba(15,23,42,.08); margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 20px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .stat { background: #eff6ff; border-radius: 14px; padding: 14px; }
        h1, h2, h3, p { margin-top: 0; }
        .muted { color: #64748b; }
        .badge { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge.active, .badge.trial { background: #dcfce7; color: #166534; }
        .badge.inactive { background: #fee2e2; color: #991b1b; }
        .flash { padding: 12px 14px; border-radius: 12px; margin-bottom: 18px; }
        .flash.success { background: #dcfce7; color: #166534; }
        .flash.error { background: #fee2e2; color: #991b1b; }
        label { display: block; font-weight: 700; margin-bottom: 6px; }
        input, select, textarea { width: 100%; box-sizing: border-box; padding: 12px 14px; border-radius: 12px; border: 1px solid #cbd5e1; margin-bottom: 16px; font: inherit; background: #fff; }
        textarea { min-height: 110px; resize: vertical; }
        .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .row3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .checkbox { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .checkbox input { width: auto; margin: 0; }
        .btn { display: inline-block; padding: 12px 16px; border: 0; border-radius: 12px; background: #2563eb; color: #fff; font-weight: 700; cursor: pointer; text-decoration: none; }
        pre { background: #0f172a; color: #e2e8f0; border-radius: 14px; padding: 16px; white-space: pre-wrap; overflow-wrap: anywhere; }
        .small { font-size: 13px; }
        .list { margin: 0; padding-left: 18px; color: #334155; }
        .actions { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        @media (max-width: 960px) {
            .grid, .stats, .row2, .row3 { grid-template-columns: 1fr; }
            .topbar { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <div class="topbar">
            <div>
                <h1>Dashboard Client</h1>
                <p class="muted"><?= e($user['client_name']) ?> · <?= e($user['email']) ?></p>
            </div>
            <div class="actions">
                <span class="badge <?= e((string) $user['subscription_status']) ?>"><?= e((string) $user['subscription_status']) ?></span>
                <a class="btn" href="/logout.php">Logout</a>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat">
                <h3>Domain Dashboard</h3>
                <p class="small"><?= e($baseUrl) ?></p>
            </div>
            <div class="stat">
                <h3>Widget API Key</h3>
                <p class="small"><?= e($user['client_api_key']) ?></p>
            </div>
            <div class="stat">
                <h3>Provider Saat Ini</h3>
                <p class="small"><?= e((string) $settings['ai_provider']) ?> · <?= e((string) $settings['ai_model']) ?></p>
            </div>
        </div>

        <div class="grid">
            <div>
                <div class="card">
                    <h2>Pengaturan Widget & AI</h2>
                    <p class="muted">Kosongkan field AI API key jika tidak ingin mengganti key yang sudah tersimpan.</p>

                    <form method="post" action="/api/save-settings.php">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                        <div class="row3">
                            <div>
                                <label for="primary_color">Primary Color</label>
                                <input id="primary_color" name="primary_color" value="<?= e((string) $settings['primary_color']) ?>" required>
                            </div>
                            <div>
                                <label for="bot_name">Bot Name</label>
                                <input id="bot_name" name="bot_name" value="<?= e((string) $settings['bot_name']) ?>" required>
                            </div>
                            <div>
                                <label for="bot_avatar_url">Bot Avatar URL</label>
                                <input id="bot_avatar_url" name="bot_avatar_url" value="<?= e((string) $settings['bot_avatar_url']) ?>">
                            </div>
                        </div>

                        <label for="welcome_message">Welcome Message</label>
                        <textarea id="welcome_message" name="welcome_message" required><?= e((string) $settings['welcome_message']) ?></textarea>

                        <label for="allowed_origins">Allowed Origins</label>
                        <textarea id="allowed_origins" name="allowed_origins"><?= e((string) $settings['allowed_origins']) ?></textarea>

                        <div class="row2">
                            <div>
                                <label for="ai_provider">AI Provider</label>
                                <select id="ai_provider" name="ai_provider" required>
                                    <?php foreach (['openrouter', 'openai', 'google', 'deepseek'] as $provider): ?>
                                        <option value="<?= e($provider) ?>"<?= $settings['ai_provider'] === $provider ? ' selected' : '' ?>><?= e($provider) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="ai_model">AI Model</label>
                                <input id="ai_model" name="ai_model" value="<?= e((string) $settings['ai_model']) ?>" required>
                            </div>
                        </div>

                        <label for="ai_system_prompt">System Prompt</label>
                        <textarea id="ai_system_prompt" name="ai_system_prompt"><?= e((string) $settings['ai_system_prompt']) ?></textarea>

                        <label for="ai_api_key">AI API Key Baru</label>
                        <input id="ai_api_key" name="ai_api_key" type="password" autocomplete="new-password" placeholder="Kosongkan jika tidak ingin mengganti">

                        <label for="n8n_webhook_url">n8n Webhook URL (opsional fallback)</label>
                        <input id="n8n_webhook_url" name="n8n_webhook_url" value="<?= e((string) $settings['n8n_webhook_url']) ?>">

                        <div class="checkbox">
                            <input id="telegram_notify_enabled" name="telegram_notify_enabled" type="checkbox" value="1"<?= (int) $settings['telegram_notify_enabled'] === 1 ? ' checked' : '' ?>>
                            <label for="telegram_notify_enabled" style="margin:0;">Aktifkan notifikasi Telegram saat ada pesan baru</label>
                        </div>

                        <label for="telegram_chat_id">Telegram Chat ID</label>
                        <input id="telegram_chat_id" name="telegram_chat_id" value="<?= e((string) $settings['telegram_chat_id']) ?>">

                        <button class="btn" type="submit">Simpan Pengaturan</button>
                    </form>
                </div>
            </div>

            <div>
                <div class="card">
                    <h2>Kode Embed</h2>
                    <p class="muted">Tempel kode ini sebelum tag <code>&lt;/body&gt;</code> di website client.</p>
                    <pre><?= e($embedCode) ?></pre>
                </div>

                <div class="card">
                    <h2>Checklist Go-Live</h2>
                    <ol class="list">
                        <li>Isi domain di <code>Allowed Origins</code>, misalnya <code>https://jomsite.com</code>.</li>
                        <li>Pilih provider AI dan model yang ingin dipakai.</li>
                        <li>Masukkan AI API key baru jika belum pernah disimpan.</li>
                        <li>Simpan lalu tes widget di website client.</li>
                        <li>Jika perlu fallback, isi <code>n8n_webhook_url</code>.</li>
                    </ol>
                </div>

                <div class="card">
                    <h2>Alur Pembelian MVP</h2>
                    <ol class="list">
                        <li>Admin membuat row baru di <code>clients</code>, <code>widget_settings</code>, dan <code>users</code>.</li>
                        <li>Admin kirim email login dashboard ke client.</li>
                        <li>Client login lalu isi prompt, provider, dan domain sendiri.</li>
                        <li>Client copy embed code dari panel ini.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
