<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

$user     = require_login();
$flash    = get_flash();
$settings = fetch_dashboard_settings((int) $user['client_id']);

if ($settings === null) {
    set_flash('error', 'Data client tidak ditemukan. Hubungi administrator.');
    header('Location: /login.php');
    exit;
}

$baseUrl   = dashboard_base_url();
$embedCode = '<script' . "\n"
    . '  src="' . $baseUrl . '/widget/widget.js"' . "\n"
    . '  data-api-key="' . $user['client_api_key'] . '"' . "\n"
    . '  data-base-url="' . $baseUrl . '"' . "\n"
    . '  async' . "\n"
    . '></script>';

$welcome = isset($_GET['welcome']);

$aiKeySet    = trim((string) $settings['ai_api_key']) !== '';
$domainSet   = trim((string) $settings['allowed_origins']) !== '' && $settings['allowed_origins'] !== '*';
$providerOk  = trim((string) $settings['ai_model']) !== '';

$status = $user['subscription_status'] ?? 'trial';
$statusLabel = ['active' => 'Active', 'trial' => 'Trial', 'inactive' => 'Inactive'][$status] ?? $status;
$statusColor = $status === 'active' ? '#16A34A' : ($status === 'inactive' ? '#DC2626' : '#D97706');
$statusBg    = $status === 'active' ? '#DCFCE7' : ($status === 'inactive' ? '#FEF2F2' : '#FEF9C3');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — <?= e((string) $user['client_name']) ?></title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--blue:#2563EB;--blue-dark:#1D4ED8;--purple:#7C3AED;--slate:#0F172A;--muted:#64748B;--border:#E2E8F0;--bg:#F1F5F9;--white:#fff;--red:#DC2626;--green:#16A34A}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--slate);min-height:100vh}
a{text-decoration:none;color:inherit}
/* TOP NAV */
.topnav{background:var(--white);border-bottom:1px solid var(--border);height:60px;display:flex;align-items:center;padding:0 24px;gap:16px;position:sticky;top:0;z-index:50}
.logo{font-size:18px;font-weight:800;color:var(--blue);margin-right:auto}
.logo span{color:var(--purple)}
.nav-user{font-size:13px;color:var(--muted)}
.nav-user strong{color:var(--slate)}
.badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700}
.btn-sm{padding:8px 14px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;border:0;transition:background .15s}
.btn-ghost{background:#F1F5F9;color:var(--muted)}.btn-ghost:hover{color:var(--slate);background:#E2E8F0}
.btn-primary{background:var(--blue);color:#fff}.btn-primary:hover{background:var(--blue-dark)}
.btn-danger{background:#FEF2F2;color:var(--red)}.btn-danger:hover{background:#FEE2E2}
/* LAYOUT */
.layout{max-width:1280px;margin:0 auto;padding:28px 24px 60px;display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start}
/* FLASH */
.flash{padding:14px 18px;border-radius:14px;margin-bottom:20px;font-size:14px;font-weight:600;grid-column:1/-1}
.flash.success{background:#DCFCE7;color:var(--green)}.flash.error{background:#FEF2F2;color:var(--red)}
.flash.info{background:#EFF6FF;color:var(--blue)}
/* WELCOME BANNER */
.welcome{background:linear-gradient(135deg,#1E3A8A,var(--purple));border-radius:18px;padding:22px 24px;color:#fff;grid-column:1/-1;display:flex;align-items:center;justify-content:space-between;gap:16px}
.welcome h2{font-size:20px;font-weight:800;margin-bottom:4px}
.welcome p{font-size:14px;opacity:.85}
/* STATS */
.stats{grid-column:1/-1;display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.stat-card{background:var(--white);border-radius:16px;padding:18px;border:1px solid var(--border)}
.stat-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:6px}
.stat-val{font-size:14px;font-weight:600;color:var(--slate);word-break:break-all}
.stat-val code{font-size:12px;background:#F1F5F9;padding:2px 6px;border-radius:6px;display:inline-block;word-break:break-all;max-width:100%}
/* CARD */
.card{background:var(--white);border-radius:18px;border:1px solid var(--border);overflow:hidden}
/* SECTION TOGGLE */
.sec-head{display:flex;align-items:center;justify-content:space-between;padding:18px 20px;cursor:pointer;user-select:none;border-bottom:1px solid var(--border)}
.sec-head:hover{background:#F8FAFC}
.sec-title{font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px}
.chevron{font-size:12px;color:var(--muted)}
.sec-body{padding:20px}
/* FORMS */
.field{margin-bottom:18px}
.field:last-child{margin-bottom:0}
label{display:block;font-size:13px;font-weight:700;margin-bottom:6px}
.hint{font-size:12px;color:var(--muted);font-weight:400;margin-left:4px}
input,select,textarea{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:12px;font:inherit;font-size:14px;color:var(--slate);background:#F8FAFC;transition:border-color .15s;appearance:none}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--blue);background:var(--white)}
textarea{resize:vertical;min-height:100px;line-height:1.6}
.color-row{display:flex;align-items:center;gap:10px}
.color-row input[type=color]{width:44px;height:44px;padding:2px;border-radius:10px;cursor:pointer;flex-shrink:0}
.color-row input[type=text]{flex:1}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.checkbox-row{display:flex;align-items:center;gap:10px}
.checkbox-row input[type=checkbox]{width:18px;height:18px;margin:0;flex-shrink:0}
.checkbox-row label{margin:0;font-weight:600}
.key-row{display:flex;gap:8px}
.key-row input{flex:1}
/* SAVE BUTTON */
.save-bar{padding:16px 20px;border-top:1px solid var(--border);background:#F8FAFC;display:flex;gap:12px;align-items:center}
.btn-save{padding:12px 28px;background:var(--blue);color:#fff;border:0;border-radius:12px;font:inherit;font-size:15px;font-weight:700;cursor:pointer;transition:background .15s}
.btn-save:hover{background:var(--blue-dark)}
.save-note{font-size:12px;color:var(--muted)}
/* RIGHT COLUMN */
.embed-card .sec-body{padding:20px}
pre{background:#0F172A;color:#E2E8F0;border-radius:12px;padding:16px;font-size:12px;line-height:1.7;white-space:pre-wrap;overflow-wrap:anywhere;margin-bottom:12px}
.btn-copy{width:100%;padding:11px;background:var(--blue);color:#fff;border:0;border-radius:12px;font:inherit;font-size:14px;font-weight:700;cursor:pointer;transition:background .15s}
.btn-copy:hover{background:var(--blue-dark)}
/* CHECKLIST */
.checklist{display:flex;flex-direction:column;gap:10px;padding:20px}
.check-item{display:flex;align-items:center;gap:10px;font-size:14px}
.check-icon{width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0}
.check-icon.ok{background:#DCFCE7;color:var(--green)}
.check-icon.no{background:#FEF2F2;color:var(--red)}
/* PROVIDER BADGE */
.provider-row{display:flex;gap:8px;flex-wrap:wrap;padding:0 20px 20px}
.p-chip{padding:6px 12px;border-radius:999px;font-size:12px;font-weight:700;border:1.5px solid var(--border);color:var(--muted);cursor:default}
.p-chip.active{border-color:var(--blue);background:#EFF6FF;color:var(--blue)}
@media(max-width:1000px){.layout{grid-template-columns:1fr}.stats{grid-template-columns:1fr 1fr}}
@media(max-width:600px){.stats{grid-template-columns:1fr}.topnav .nav-user{display:none}.row2{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="topnav">
  <a class="logo" href="/dashboard.php">Chat<span>Popup</span>.AI</a>
  <div class="nav-user">Halo, <strong><?= e((string) $user['name']) ?></strong></div>
  <span class="badge" style="background:<?= e($statusBg) ?>;color:<?= e($statusColor) ?>"><?= e($statusLabel) ?></span>
  <a class="btn-sm btn-danger" href="/logout.php">Logout</a>
</div>

<div class="layout">

  <?php if ($welcome): ?>
  <div class="welcome" style="grid-column:1/-1">
    <div>
      <h2>🎉 Akun berhasil dibuat!</h2>
      <p>Sekarang atur AI provider, system prompt, dan domain website Anda. Lalu copy embed code dan tempel ke website.</p>
    </div>
    <a class="btn-sm btn-sm" style="background:rgba(255,255,255,.2);color:#fff;white-space:nowrap" href="/dashboard.php">Mulai Atur →</a>
  </div>
  <?php endif; ?>

  <?php if ($flash): ?>
  <div class="flash <?= e($flash['type']) ?>" style="grid-column:1/-1"><?= e($flash['message']) ?></div>
  <?php endif; ?>

  <div class="stats">
    <div class="stat-card">
      <div class="stat-label">Widget API Key</div>
      <div class="stat-val"><code><?= e(substr($user['client_api_key'], 0, 20)) ?>...</code></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Provider Aktif</div>
      <div class="stat-val"><?= e((string) $settings['ai_provider']) ?> &middot; <?= e((string) $settings['ai_model']) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Status Akun</div>
      <div class="stat-val"><span class="badge" style="background:<?= e($statusBg) ?>;color:<?= e($statusColor) ?>"><?= e($statusLabel) ?></span></div>
    </div>
  </div>

  <!-- LEFT: SETTINGS FORM -->
  <div>
    <form method="post" action="/api/save-settings.php">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

      <!-- TAMPILAN -->
      <div class="card" style="margin-bottom:16px">
        <div class="sec-head section-toggle" data-target="sec-tampilan">
          <div class="sec-title">🎨 Tampilan Widget</div>
          <span class="chevron">▼</span>
        </div>
        <div class="sec-body" id="sec-tampilan">
          <div class="row2">
            <div class="field">
              <label for="primary_color">Warna Utama</label>
              <div class="color-row">
                <input id="primary_color_pick" type="color" value="<?= e((string) $settings['primary_color']) ?>" tabindex="-1">
                <input id="primary_color" name="primary_color" type="text" value="<?= e((string) $settings['primary_color']) ?>" placeholder="#2563EB" required>
              </div>
            </div>
            <div class="field">
              <label for="bot_name">Nama Bot</label>
              <input id="bot_name" name="bot_name" type="text" value="<?= e((string) $settings['bot_name']) ?>" required placeholder="Assistant">
            </div>
          </div>
          <div class="field">
            <label for="bot_avatar_url">URL Avatar Bot <span class="hint">(opsional)</span></label>
            <input id="bot_avatar_url" name="bot_avatar_url" type="url" value="<?= e((string) $settings['bot_avatar_url']) ?>" placeholder="https://...gambar.png">
          </div>
          <div class="field">
            <label for="welcome_message">Welcome Message</label>
            <textarea id="welcome_message" name="welcome_message" required><?= e((string) $settings['welcome_message']) ?></textarea>
          </div>
        </div>
      </div>

      <!-- AI PROVIDER -->
      <div class="card" style="margin-bottom:16px">
        <div class="sec-head section-toggle" data-target="sec-ai">
          <div class="sec-title">🤖 AI Provider &amp; Model</div>
          <span class="chevron">▼</span>
        </div>
        <div class="sec-body" id="sec-ai">
          <div class="row2">
            <div class="field">
              <label for="ai_provider">Provider</label>
              <select id="ai_provider" name="ai_provider" required>
                <?php foreach (['openrouter' => '🌐 OpenRouter (Rekomendasi)', 'openai' => '⚡ OpenAI', 'google' => '💎 Google Gemini', 'deepseek' => '🌊 DeepSeek'] as $val => $label): ?>
                  <option value="<?= e($val) ?>"<?= $settings['ai_provider'] === $val ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label for="ai_model">Model <span class="hint">contoh: openai/gpt-4o-mini</span></label>
              <input id="ai_model" name="ai_model" type="text" value="<?= e((string) $settings['ai_model']) ?>" required placeholder="openai/gpt-4o-mini">
            </div>
          </div>
          <div class="field">
            <label for="ai_api_key">
              AI API Key
              <span class="hint">(<?= $aiKeySet ? '✅ Sudah tersimpan' : '⚠️ Belum diisi' ?>)</span>
            </label>
            <div class="key-row">
              <input id="ai_api_key" name="ai_api_key" type="password" autocomplete="new-password"
                     placeholder="<?= $aiKeySet ? 'Kosongkan untuk tetap pakai key lama' : 'Masukkan API key provider Anda' ?>">
              <button type="button" id="toggle-key-btn" class="btn-sm btn-ghost">👁 Tampilkan</button>
            </div>
          </div>
          <div class="field">
            <label for="ai_system_prompt">System Prompt <span class="hint">(instruksi untuk AI)</span></label>
            <textarea id="ai_system_prompt" name="ai_system_prompt" placeholder="Contoh: Anda adalah asisten customer service ramah untuk toko saya. Jawab singkat dan helpful dalam bahasa Indonesia."><?= e((string) $settings['ai_system_prompt']) ?></textarea>
          </div>
        </div>
        <div class="provider-row">
          <?php foreach (['openrouter', 'openai', 'google', 'deepseek'] as $p): ?>
            <span class="p-chip<?= $settings['ai_provider'] === $p ? ' active' : '' ?>"><?= e($p) ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- DOMAIN -->
      <div class="card" style="margin-bottom:16px">
        <div class="sec-head section-toggle" data-target="sec-domain">
          <div class="sec-title">🌐 Domain &amp; Akses</div>
          <span class="chevron">▼</span>
        </div>
        <div class="sec-body" id="sec-domain">
          <div class="field">
            <label for="allowed_origins">Allowed Origins</label>
            <textarea id="allowed_origins" name="allowed_origins" style="min-height:70px"
                      placeholder="https://website-anda.com&#10;https://www.website-anda.com"><?= e((string) $settings['allowed_origins']) ?></textarea>
            <p style="font-size:12px;color:var(--muted);margin-top:6px">
              Pisahkan dengan koma atau baris baru. Gunakan <code>*</code> untuk mengizinkan semua domain (tidak disarankan untuk produksi).
            </p>
          </div>
          <div class="field">
            <label for="n8n_webhook_url">n8n Webhook URL <span class="hint">(opsional — fallback jika AI key belum diisi)</span></label>
            <input id="n8n_webhook_url" name="n8n_webhook_url" type="url" value="<?= e((string) $settings['n8n_webhook_url']) ?>" placeholder="https://n8n.yourdomain.com/webhook/...">
          </div>
        </div>
      </div>

      <!-- TELEGRAM -->
      <div class="card" style="margin-bottom:16px">
        <div class="sec-head section-toggle" data-target="sec-tg">
          <div class="sec-title">📱 Notifikasi Telegram</div>
          <span class="chevron">▶</span>
        </div>
        <div class="sec-body" id="sec-tg" style="display:none">
          <div class="field">
            <div class="checkbox-row">
              <input id="telegram_notify_enabled" name="telegram_notify_enabled" type="checkbox" value="1"<?= (int) $settings['telegram_notify_enabled'] === 1 ? ' checked' : '' ?>>
              <label for="telegram_notify_enabled">Kirim notifikasi ke Telegram setiap ada pesan masuk</label>
            </div>
          </div>
          <div class="field">
            <label for="telegram_chat_id">Telegram Chat ID</label>
            <input id="telegram_chat_id" name="telegram_chat_id" type="text" value="<?= e((string) $settings['telegram_chat_id']) ?>" placeholder="-1001234567890 atau 123456789">
          </div>
          <p style="font-size:12px;color:var(--muted)">
            Pastikan <code>TELEGRAM_BOT_TOKEN</code> sudah diisi di <code>config.php</code>.
            Dapatkan Chat ID dengan mengirim pesan ke bot Anda lalu buka <code>https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code>.
          </p>
        </div>
      </div>

      <div class="save-bar" style="border-radius:0 0 18px 18px;margin-top:-16px">
        <button class="btn-save" type="submit">💾 Simpan Semua Pengaturan</button>
        <span class="save-note">AI API Key kosong = pakai key lama</span>
      </div>

    </form>
  </div>

  <!-- RIGHT: EMBED + STATUS -->
  <div>
    <div class="card embed-card" style="margin-bottom:16px">
      <div class="sec-head" style="cursor:default">
        <div class="sec-title">🚀 Kode Embed Widget</div>
      </div>
      <div class="sec-body">
        <p style="font-size:13px;color:var(--muted);margin-bottom:12px">
          Tempel kode ini sebelum tag <code>&lt;/body&gt;</code> di website Anda.
          Di WordPress: <strong>Appearance → Theme Editor → footer.php</strong> atau pakai plugin <strong>Insert Headers and Footers</strong>.
        </p>
        <pre id="embed-code-box"><?= e($embedCode) ?></pre>
        <button class="btn-copy" id="copy-embed-btn">📋 Copy Kode Embed</button>
      </div>
    </div>

    <div class="card" style="margin-bottom:16px">
      <div class="sec-head" style="cursor:default">
        <div class="sec-title">✅ Checklist Go-Live</div>
      </div>
      <div class="checklist">
        <div class="check-item">
          <div class="check-icon <?= $aiKeySet ? 'ok' : 'no' ?>"><?= $aiKeySet ? '✓' : '!' ?></div>
          <span><?= $aiKeySet ? 'AI API key sudah diisi' : 'Isi AI API key di bagian AI Provider' ?></span>
        </div>
        <div class="check-item">
          <div class="check-icon <?= $providerOk ? 'ok' : 'no' ?>"><?= $providerOk ? '✓' : '!' ?></div>
          <span><?= $providerOk ? 'AI model sudah dipilih' : 'Pilih AI provider dan isi model' ?></span>
        </div>
        <div class="check-item">
          <div class="check-icon <?= $domainSet ? 'ok' : 'no' ?>"><?= $domainSet ? '✓' : '!' ?></div>
          <span><?= $domainSet ? 'Domain website sudah terdaftar' : 'Isi Allowed Origins dengan domain website' ?></span>
        </div>
        <div class="check-item">
          <div class="check-icon ok">✓</div>
          <span>Kode embed siap dicopy dan ditempel</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="sec-head" style="cursor:default">
        <div class="sec-title">🔑 Widget API Key Lengkap</div>
      </div>
      <div class="sec-body">
        <p style="font-size:12px;color:var(--muted);margin-bottom:10px">
          Key ini sudah otomatis ada di embed code. Catat jika perlu.
        </p>
        <textarea style="font-size:11px;min-height:60px;font-family:monospace" readonly><?= e($user['client_api_key']) ?></textarea>
      </div>
    </div>
  </div>

</div>

<script src="/js/dashboard.js"></script>
</body>
</html>
