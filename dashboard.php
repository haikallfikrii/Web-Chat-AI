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

$welcome    = isset($_GET['welcome']);
$aiKeySet   = trim((string) $settings['ai_api_key']) !== '';
$domainSet  = trim((string) $settings['allowed_origins']) !== '' && $settings['allowed_origins'] !== '*';
$providerOk = trim((string) $settings['ai_model']) !== '';
$tgSet      = !empty($settings['telegram_notify_enabled']) && !empty($settings['telegram_chat_id']);

$status      = $user['subscription_status'] ?? 'trial';
$statusLabel = ['active' => 'Aktif', 'trial' => 'Trial', 'inactive' => 'Nonaktif'][$status] ?? $status;

$checklist = [
    ['ok' => $aiKeySet,   'label' => 'API Key AI sudah diisi'],
    ['ok' => $providerOk, 'label' => 'Model AI sudah dipilih'],
    ['ok' => $domainSet,  'label' => 'Domain allowed diatur'],
    ['ok' => $tgSet,      'label' => 'Telegram notifikasi (opsional)'],
];
$checkScore  = count(array_filter(array_column($checklist, 'ok')));
$checkTotal  = count($checklist);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — <?= e((string) $user['client_name']) ?></title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#030712;--bg2:#0D1117;--bg3:#161B22;--bg4:#1C2333;
  --green:#00D68F;--green-dark:#00B077;--green-dim:rgba(0,214,143,.12);
  --blue:#3B82F6;--red:#EF4444;--yellow:#F59E0B;
  --text:#E6EDF3;--muted:#7D8590;--muted2:#4B5563;
  --border:rgba(255,255,255,.08);--card:rgba(22,27,34,.85);
  --r:16px;
}
html,body{height:100%}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif;
  background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden}
body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:linear-gradient(rgba(0,214,143,.018) 1px,transparent 1px),
    linear-gradient(90deg,rgba(0,214,143,.018) 1px,transparent 1px);
  background-size:64px 64px}
a{color:inherit;text-decoration:none}

/* ── TOPBAR ── */
.topbar{position:sticky;top:0;z-index:100;
  background:rgba(3,7,18,.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);height:60px;
  display:flex;align-items:center;gap:16px;padding:0 28px}
.tbar-logo{font-size:18px;font-weight:900;letter-spacing:-.5px;margin-right:auto;
  background:linear-gradient(135deg,var(--green),var(--blue));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.tbar-client{font-size:13px;color:var(--muted)}
.tbar-client strong{color:var(--text)}
.badge{display:inline-flex;align-items:center;gap:5px;
  padding:4px 11px;border-radius:999px;font-size:12px;font-weight:700}
.badge-trial{background:rgba(245,158,11,.15);color:var(--yellow);border:1px solid rgba(245,158,11,.25)}
.badge-active{background:var(--green-dim);color:var(--green);border:1px solid rgba(0,214,143,.25)}
.badge-inactive{background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.2)}
.badge-dot{width:6px;height:6px;border-radius:50%;background:currentColor;
  animation:pulse-dot 2s ease infinite}
@keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:.4}}
.btn-topbar{padding:8px 16px;border-radius:10px;font-size:13px;font-weight:700;
  cursor:pointer;border:1.5px solid var(--border);background:transparent;
  color:var(--muted);font-family:inherit;transition:all .2s}
.btn-topbar:hover{border-color:rgba(239,68,68,.4);color:var(--red);background:rgba(239,68,68,.06)}

/* ── LAYOUT ── */
.page{position:relative;z-index:1;max-width:1240px;margin:0 auto;padding:28px 24px 72px;
  display:grid;grid-template-columns:1fr 340px;gap:22px;align-items:start}

/* ── FLASH ── */
.flash{grid-column:1/-1;padding:14px 18px;border-radius:14px;font-size:14px;font-weight:600;
  display:flex;align-items:center;gap:10px}
.flash.success{background:var(--green-dim);color:var(--green);border:1px solid rgba(0,214,143,.25)}
.flash.error{background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.25)}

/* ── WELCOME STRIP ── */
.welcome{grid-column:1/-1;
  background:var(--card);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border:1px solid rgba(0,214,143,.15);border-radius:var(--r);
  padding:24px 28px;display:flex;align-items:center;gap:20px;
  animation:fadeIn .6s cubic-bezier(.22,1,.36,1)}
@keyframes fadeIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.welcome-avatar{width:52px;height:52px;border-radius:16px;flex-shrink:0;
  background:var(--green-dim);border:1px solid rgba(0,214,143,.25);
  display:flex;align-items:center;justify-content:center;font-size:22px}
.welcome h2{font-size:19px;font-weight:800;margin-bottom:2px}
.welcome p{color:var(--muted);font-size:13px}
.welcome-right{margin-left:auto;text-align:right}
.progress-label{font-size:12px;color:var(--muted);margin-bottom:4px}
.progress-bar{width:160px;height:6px;border-radius:3px;background:var(--border);overflow:hidden}
.progress-fill{height:100%;border-radius:3px;
  background:linear-gradient(90deg,var(--green),var(--blue));
  transition:width .8s cubic-bezier(.22,1,.36,1)}

/* ── GLASS CARD ── */
.card{background:var(--card);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border:1px solid var(--border);border-radius:var(--r);overflow:hidden;
  transition:border-color .3s}
.card:hover{border-color:rgba(0,214,143,.18)}
.card-head{padding:18px 24px 16px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:12px;cursor:pointer;user-select:none}
.card-icon{width:36px;height:36px;border-radius:10px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:16px;
  background:var(--green-dim);border:1px solid rgba(0,214,143,.2)}
.card-title{font-size:15px;font-weight:700;flex:1}
.card-chevron{color:var(--muted);font-size:18px;transition:transform .3s}
.card.open .card-chevron{transform:rotate(180deg)}
.card-body{padding:22px 24px;display:none}
.card.open .card-body{display:block}
.card-body.always{display:block}

/* ── FORM ── */
.form-group{margin-bottom:18px}
.form-group:last-child{margin-bottom:0}
label{display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px}
.label-hint{font-size:11px;color:var(--muted);font-weight:400;margin-left:5px}
input[type=text],input[type=url],input[type=password],input[type=email],textarea,select{
  width:100%;background:rgba(13,17,23,.8);border:1.5px solid var(--border);
  border-radius:12px;padding:11px 15px;font-size:14px;color:var(--text);
  outline:none;transition:all .2s;font-family:inherit;resize:vertical}
input:focus,textarea:focus,select:focus{
  border-color:var(--green);box-shadow:0 0 0 3px rgba(0,214,143,.1);
  background:rgba(22,27,34,.9)}
input::placeholder,textarea::placeholder{color:var(--muted)}
select option{background:var(--bg3);color:var(--text)}
textarea{min-height:100px;line-height:1.6}

.color-row{display:grid;grid-template-columns:1fr 54px;gap:8px;align-items:center}
.color-preview{width:54px;height:44px;border-radius:12px;border:1.5px solid var(--border);
  cursor:pointer;overflow:hidden}
.color-preview input[type=color]{width:130%;height:130%;margin:-15%;
  border:none;cursor:pointer;background:none}

.toggle-group{display:flex;align-items:center;justify-content:space-between;
  padding:14px 0;border-bottom:1px solid var(--border)}
.toggle-group:last-child{border-bottom:none;padding-bottom:0}
.toggle-label{font-size:14px;font-weight:600}
.toggle-sub{font-size:12px;color:var(--muted);margin-top:1px}
.toggle{position:relative;width:44px;height:24px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-slider{position:absolute;inset:0;background:var(--bg4);border-radius:12px;
  cursor:pointer;transition:.3s;border:1px solid var(--border)}
.toggle-slider::before{content:'';position:absolute;width:18px;height:18px;
  border-radius:50%;background:var(--muted);bottom:2px;left:2px;transition:.3s}
.toggle input:checked + .toggle-slider{background:var(--green-dim);border-color:rgba(0,214,143,.3)}
.toggle input:checked + .toggle-slider::before{transform:translateX(20px);background:var(--green)}

.pw-wrap{position:relative}
.pw-wrap input{padding-right:42px}
.pw-eye{position:absolute;right:14px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;color:var(--muted);font-size:15px;transition:color .2s}
.pw-eye:hover{color:var(--text)}

.btn-save{display:block;width:100%;margin-top:20px;padding:13px;
  border-radius:12px;border:none;cursor:pointer;font-size:15px;font-weight:700;
  color:#030712;background:linear-gradient(135deg,var(--green),var(--green-dark));
  font-family:inherit;transition:all .25s;position:relative;overflow:hidden}
.btn-save::after{content:'';position:absolute;inset:0;
  background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,.25) 50%,transparent 60%);
  background-size:200% 100%;animation:shimmer 3s infinite}
@keyframes shimmer{0%{background-position:-200% center}100%{background-position:200% center}}
.btn-save:hover{transform:translateY(-1px);box-shadow:0 6px 24px rgba(0,214,143,.3)}
.divider{height:1px;background:var(--border);margin:18px 0}

/* ── SIDEBAR CARDS ── */
.sidebar{display:flex;flex-direction:column;gap:18px}

.api-key-box{background:rgba(13,17,23,.9);border:1px solid var(--border);
  border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:8px}
.api-key-text{flex:1;font-family:monospace;font-size:13px;color:var(--muted);
  word-break:break-all;line-height:1.4}
.btn-copy{padding:7px 14px;border-radius:9px;font-size:12px;font-weight:700;
  cursor:pointer;border:1.5px solid rgba(0,214,143,.3);
  background:var(--green-dim);color:var(--green);font-family:inherit;transition:all .2s}
.btn-copy:hover{background:rgba(0,214,143,.2)}

.embed-wrap{background:rgba(7,11,18,.95);border-radius:12px;padding:16px;
  border:1px solid var(--border);font-family:monospace;font-size:12px;
  color:#7DD3FC;line-height:1.7;white-space:pre;overflow-x:auto;word-break:break-all;
  white-space:pre-wrap}
.embed-wrap .tag-name{color:#F472B6}
.embed-wrap .attr-name{color:#86EFAC}
.embed-wrap .attr-val{color:#FDE68A}

.checklist{display:flex;flex-direction:column;gap:8px;padding:20px 22px}
.check-item{display:flex;align-items:center;gap:10px;font-size:13px}
.check-icon{width:22px;height:22px;border-radius:6px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:12px}
.check-ok{background:var(--green-dim);color:var(--green);border:1px solid rgba(0,214,143,.25)}
.check-no{background:rgba(245,158,11,.1);color:var(--yellow);border:1px solid rgba(245,158,11,.2)}

/* ── STATUS BADGE in card head ── */
.head-badge{font-size:11px;font-weight:700;padding:3px 9px;border-radius:999px}
.hb-ok{background:var(--green-dim);color:var(--green)}
.hb-warn{background:rgba(245,158,11,.1);color:var(--yellow)}

@media(max-width:900px){.page{grid-template-columns:1fr}.welcome-right{display:none}}
</style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
  <a href="/" class="tbar-logo">ChatPopup.AI</a>
  <span class="tbar-client">
    <strong><?= e((string) $user['name']) ?></strong>
    &nbsp;·&nbsp;<?= e((string) $user['client_name']) ?>
  </span>
  <?php
    $badgeClass = match($status) { 'active' => 'badge-active', 'inactive' => 'badge-inactive', default => 'badge-trial' };
  ?>
  <span class="badge <?= $badgeClass ?>">
    <span class="badge-dot"></span> <?= e($statusLabel) ?>
  </span>
  <form method="POST" action="/logout.php">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <button type="submit" class="btn-topbar">Keluar</button>
  </form>
</header>

<div class="page">

  <!-- FLASH -->
  <?php if ($flash): ?>
    <div class="flash <?= e($flash['type']) ?>">
      <?= $flash['type'] === 'success' ? '✓' : '⚠️' ?> <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>
  <?php if ($welcome): ?>
    <div class="flash success">🎉 Selamat datang! Widget Anda siap dikonfigurasi.</div>
  <?php endif; ?>

  <!-- WELCOME STRIP -->
  <div class="welcome">
    <div class="welcome-avatar">👋</div>
    <div>
      <h2>Halo, <?= e(explode(' ', (string) $user['name'])[0]) ?>!</h2>
      <p>Dashboard widget untuk <strong><?= e((string) $user['client_name']) ?></strong></p>
    </div>
    <div class="welcome-right">
      <div class="progress-label"><?= $checkScore ?>/<?= $checkTotal ?> Setup selesai</div>
      <div class="progress-bar">
        <div class="progress-fill" style="width:<?= round($checkScore / $checkTotal * 100) ?>%"></div>
      </div>
    </div>
  </div>

  <!-- MAIN FORM (left) -->
  <div style="display:flex;flex-direction:column;gap:18px">
  <form method="POST" action="/api/save-settings.php">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <!-- TAMPILAN -->
    <div class="card open" id="sec-appearance">
      <div class="card-head" onclick="toggleCard('sec-appearance')">
        <div class="card-icon">🎨</div>
        <span class="card-title">Tampilan Widget</span>
        <span class="card-chevron">⌄</span>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label>Nama Bot / Asisten</label>
          <input type="text" name="bot_name" placeholder="e.g. Asisten Jomsite"
                 value="<?= e((string) $settings['bot_name']) ?>" maxlength="60" required>
        </div>
        <div class="form-group">
          <label>Pesan Sambutan</label>
          <textarea name="welcome_message" placeholder="Halo! Ada yang bisa saya bantu hari ini?"
                    rows="2"><?= e((string) $settings['welcome_message']) ?></textarea>
        </div>
        <div class="form-group">
          <label>Warna Utama</label>
          <div class="color-row">
            <input type="text" id="color-hex" name="primary_color"
                   value="<?= e((string) ($settings['primary_color'] ?? '#00D68F')) ?>"
                   placeholder="#00D68F" maxlength="9" pattern="#[0-9a-fA-F]{6}">
            <div class="color-preview">
              <input type="color" id="color-picker"
                     value="<?= e((string) ($settings['primary_color'] ?? '#00D68F')) ?>">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- AI SETTINGS -->
    <div class="card open" id="sec-ai">
      <div class="card-head" onclick="toggleCard('sec-ai')">
        <div class="card-icon">🤖</div>
        <span class="card-title">Konfigurasi AI</span>
        <span class="head-badge <?= ($aiKeySet && $providerOk) ? 'hb-ok' : 'hb-warn' ?>">
          <?= ($aiKeySet && $providerOk) ? '✓ Aktif' : '⚠ Belum lengkap' ?>
        </span>
        <span class="card-chevron">⌄</span>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label>Provider AI</label>
          <select name="ai_provider" id="ai-provider-select">
            <?php
            $providers = ['openrouter' => 'OpenRouter (Rekomendasi)', 'openai' => 'OpenAI', 'google' => 'Google Gemini', 'deepseek' => 'DeepSeek'];
            foreach ($providers as $val => $label):
              $sel = ($settings['ai_provider'] ?? 'openrouter') === $val ? 'selected' : '';
            ?>
              <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Model AI <span class="label-hint">Sesuaikan dengan provider</span></label>
          <input type="text" name="ai_model" id="ai-model-input"
                 placeholder="e.g. openai/gpt-4o-mini (OpenRouter)"
                 value="<?= e((string) $settings['ai_model']) ?>">
          <div style="margin-top:6px;font-size:12px;color:var(--muted)" id="model-hint"></div>
        </div>
        <div class="form-group">
          <label>API Key Provider</label>
          <div class="pw-wrap">
            <input type="password" name="ai_api_key" id="ai-api-key"
                   placeholder="<?= $aiKeySet ? '••••••• (tersimpan, kosongkan jika tidak ingin mengubah)' : 'sk-...' ?>"
                   autocomplete="new-password">
            <button type="button" class="pw-eye" onclick="toggleAiKey()">👁</button>
          </div>
        </div>
        <div class="divider"></div>
        <div class="form-group">
          <label>System Prompt <span class="label-hint">Kepribadian dan instruksi bot</span></label>
          <textarea name="ai_system_prompt" rows="5"
                    placeholder="Kamu adalah asisten ramah untuk [Nama Bisnis]. Jawab dalam bahasa Indonesia yang sopan. Fokus hanya pada produk kami."><?= e((string) $settings['ai_system_prompt']) ?></textarea>
        </div>
      </div>
    </div>

    <!-- DOMAIN -->
    <div class="card" id="sec-domain">
      <div class="card-head" onclick="toggleCard('sec-domain')">
        <div class="card-icon">🔒</div>
        <span class="card-title">Keamanan Domain</span>
        <span class="head-badge <?= $domainSet ? 'hb-ok' : 'hb-warn' ?>">
          <?= $domainSet ? '✓ Diatur' : '⚠ Dibuka' ?>
        </span>
        <span class="card-chevron">⌄</span>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label>Allowed Origins <span class="label-hint">Pisahkan dengan koma</span></label>
          <input type="text" name="allowed_origins"
                 value="<?= e((string) $settings['allowed_origins']) ?>"
                 placeholder="https://website-anda.com, https://www.website-anda.com">
          <div style="font-size:12px;color:var(--muted);margin-top:6px;line-height:1.5">
            Kosongkan atau isi <code style="background:rgba(0,214,143,.1);color:var(--green);padding:1px 5px;border-radius:4px">*</code> untuk izinkan semua domain.
          </div>
        </div>
      </div>
    </div>

    <!-- TELEGRAM -->
    <div class="card" id="sec-tg">
      <div class="card-head" onclick="toggleCard('sec-tg')">
        <div class="card-icon">📱</div>
        <span class="card-title">Notifikasi Telegram</span>
        <span class="head-badge <?= $tgSet ? 'hb-ok' : '' ?>" style="<?= $tgSet ? '' : 'display:none' ?>">✓ Aktif</span>
        <span class="card-chevron">⌄</span>
      </div>
      <div class="card-body">
        <div class="toggle-group">
          <div>
            <div class="toggle-label">Kirim notifikasi ke Telegram</div>
            <div class="toggle-sub">Menerima ping setiap ada pesan baru di widget</div>
          </div>
          <label class="toggle">
            <input type="checkbox" name="telegram_notify_enabled" value="1"
                   <?= !empty($settings['telegram_notify_enabled']) ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
          </label>
        </div>
        <div style="height:14px"></div>
        <div class="form-group">
          <label>Telegram Chat ID</label>
          <input type="text" name="telegram_chat_id"
                 value="<?= e((string) $settings['telegram_chat_id']) ?>"
                 placeholder="Contoh: -100123456789">
          <div style="font-size:12px;color:var(--muted);margin-top:5px">
            Kirim <code style="background:rgba(0,214,143,.1);color:var(--green);padding:1px 5px;border-radius:4px">/start</code> ke @userinfobot untuk dapat Chat ID Anda.
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn-save">Simpan Semua Pengaturan</button>
  </form>
  </div>

  <!-- SIDEBAR (right) -->
  <div class="sidebar">

    <!-- API KEY -->
    <div class="card">
      <div class="card-head" style="cursor:default">
        <div class="card-icon">🔑</div>
        <span class="card-title">Widget API Key</span>
      </div>
      <div class="card-body always" style="padding:14px 18px">
        <div class="api-key-box">
          <span class="api-key-text" id="apiKeyText">
            <?= e(substr((string) $user['client_api_key'], 0, 8)) ?>••••••••••••••••••••••••<?= e(substr((string) $user['client_api_key'], -4)) ?>
          </span>
          <button type="button" class="btn-copy" onclick="copyApiKey()">Salin</button>
        </div>
        <input type="hidden" id="apiKeyFull" value="<?= e((string) $user['client_api_key']) ?>">
        <p style="font-size:11px;color:var(--muted);margin-top:10px;line-height:1.5">
          Kunci ini digunakan di embed code. Jangan bagikan ke publik.
        </p>
      </div>
    </div>

    <!-- EMBED CODE -->
    <div class="card">
      <div class="card-head" style="cursor:default">
        <div class="card-icon">📋</div>
        <span class="card-title">Kode Embed</span>
      </div>
      <div class="card-body always" style="padding:14px 18px">
        <div class="embed-wrap" id="embedCodeBlock"><span class="tag-name">&lt;script</span>
  <span class="attr-name">src</span>=<span class="attr-val">"<?= e($baseUrl) ?>/widget/widget.js"</span>
  <span class="attr-name">data-api-key</span>=<span class="attr-val">"<?= e((string) $user['client_api_key']) ?>"</span>
  <span class="attr-name">data-base-url</span>=<span class="attr-val">"<?= e($baseUrl) ?>"</span>
  <span class="attr-name">async</span>
<span class="tag-name">&gt;&lt;/script&gt;</span></div>
        <button type="button" class="btn-save" style="margin-top:12px;font-size:13px;padding:10px"
                onclick="copyEmbed()">📋 Salin Kode</button>
        <p style="font-size:11px;color:var(--muted);margin-top:10px;line-height:1.5">
          Tempel sebelum <code style="color:var(--green)">&lt;/body&gt;</code> di website Anda.
        </p>
      </div>
    </div>

    <!-- CHECKLIST -->
    <div class="card">
      <div class="card-head" style="cursor:default">
        <div class="card-icon">✅</div>
        <span class="card-title">Checklist Setup</span>
        <span class="head-badge <?= $checkScore === $checkTotal ? 'hb-ok' : 'hb-warn' ?>">
          <?= $checkScore ?>/<?= $checkTotal ?>
        </span>
      </div>
      <div class="checklist">
        <?php foreach ($checklist as $item): ?>
          <div class="check-item">
            <span class="check-icon <?= $item['ok'] ? 'check-ok' : 'check-no' ?>">
              <?= $item['ok'] ? '✓' : '!' ?>
            </span>
            <?= e($item['label']) ?>
          </div>
        <?php endforeach; ?>
        <div class="check-item">
          <span class="check-icon check-ok">✓</span>
          Embed code siap disalin
        </div>
      </div>
    </div>

  </div><!-- /sidebar -->
</div><!-- /page -->

<script>
// Color picker sync
(function(){
  var hex=document.getElementById('color-hex');
  var picker=document.getElementById('color-picker');
  if(!hex||!picker)return;
  picker.addEventListener('input',function(){hex.value=this.value});
  hex.addEventListener('input',function(){
    if(/^#[0-9a-fA-F]{6}$/.test(this.value)) picker.value=this.value;
  });
})();

// Toggle card accordion
function toggleCard(id){
  var c=document.getElementById(id);
  if(c) c.classList.toggle('open');
}

// AI provider model hints
var hints={
  openrouter:'Format: openai/gpt-4o-mini atau meta-llama/llama-3.1-8b-instruct. Lihat openrouter.ai/models',
  openai:'Format: gpt-4o atau gpt-4o-mini',
  google:'Format: gemini-1.5-flash atau gemini-1.5-pro',
  deepseek:'Format: deepseek-chat atau deepseek-coder'
};
var sel=document.getElementById('ai-provider-select');
var hint=document.getElementById('model-hint');
if(sel&&hint){
  function updateHint(){hint.textContent=hints[sel.value]||''}
  sel.addEventListener('change',updateHint);
  updateHint();
}

// Toggle AI key visibility
function toggleAiKey(){
  var f=document.getElementById('ai-api-key');
  if(f) f.type=f.type==='password'?'text':'password';
}

// Copy helpers
function copyApiKey(){
  var v=document.getElementById('apiKeyFull').value;
  navigator.clipboard.writeText(v).then(function(){
    var btn=event.target;
    var orig=btn.textContent;
    btn.textContent='✓ Tersalin';
    btn.style.background='rgba(0,214,143,.25)';
    setTimeout(function(){btn.textContent=orig;btn.style.background='';},2000);
  });
}

function copyEmbed(){
  var raw='<script\n  src="<?= e($baseUrl) ?>/widget/widget.js"\n  data-api-key="<?= e((string) $user['client_api_key']) ?>"\n  data-base-url="<?= e($baseUrl) ?>"\n  async\n><\/script>';
  navigator.clipboard.writeText(raw).then(function(){
    var btn=event.target;
    var orig=btn.textContent;
    btn.textContent='✓ Berhasil Disalin!';
    setTimeout(function(){btn.textContent=orig;},2500);
  });
}
</script>
</body>
</html>
