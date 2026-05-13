<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';

$user     = require_login();
$flash    = get_flash();
$settings = fetch_dashboard_settings((int) $user['client_id']);

if ($settings === null) {
    set_flash('error', 'Data client tidak ditemukan. Hubungi administrator.');
    header('Location: /login.php');
    exit;
}

$baseUrl = dashboard_base_url();
$welcome = isset($_GET['welcome']);

$aiKeySet   = trim((string) ($settings['ai_api_key'] ?? '')) !== '';
$domainSet  = trim((string) $settings['allowed_origins']) !== '' && $settings['allowed_origins'] !== '*';
$providerOk = trim((string) $settings['ai_model']) !== '';
$tgSet      = !empty($settings['telegram_notify_enabled']) && !empty($settings['telegram_chat_id']);

$status      = $user['subscription_status'] ?? 'trial';
$statusLabel = ['active' => 'Aktif', 'trial' => 'Trial', 'inactive' => 'Nonaktif'][$status] ?? $status;
$statusBadge = match($status) { 'active' => 'badge-green', 'inactive' => 'badge-red', default => 'badge-yellow' };

$checklist = [
    ['ok' => $aiKeySet,   'label' => 'API Key AI terisi'],
    ['ok' => $providerOk, 'label' => 'Model AI dipilih'],
    ['ok' => $domainSet,  'label' => 'Domain origins diatur'],
    ['ok' => $tgSet,      'label' => 'Notifikasi Telegram (opsional)'],
];
$checkScore = count(array_filter(array_column($checklist, 'ok')));
$checkTotal = count($checklist);
$checkPct   = (int) round($checkScore / $checkTotal * 100);

$primaryColor = (string) ($settings['primary_color'] ?? '#00E59A');
$firstName    = explode(' ', (string) $user['name'])[0];

$embedSnippet = '<script src="' . $baseUrl . '/widget/widget.js"' . "\n"
    . '  data-api-key="' . (string) $user['client_api_key'] . '"' . "\n"
    . '  data-base-url="' . $baseUrl . '"' . "\n"
    . '  async' . "\n"
    . '></script>';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<title>Dashboard — <?= e((string) $user['client_name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<style>
/* ── DASHBOARD TOPBAR ───────────────────────────────────────── */
.dash-nav{position:sticky;top:0;z-index:50;
  background:rgba(3,7,18,.78);backdrop-filter:blur(20px) saturate(140%);
  -webkit-backdrop-filter:blur(20px) saturate(140%);
  border-bottom:1px solid var(--border-2);height:62px;
  display:flex;align-items:center;gap:14px;padding:0 24px}
.dash-user{display:flex;align-items:center;gap:9px;font-size:13.5px;color:var(--text-2);margin-left:auto}
.dash-user-avatar{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--green),var(--cyan));color:#031018;font-weight:800;font-size:13px;
  box-shadow:0 4px 14px rgba(0,229,154,.3),inset 0 1px 0 rgba(255,255,255,.4)}
.dash-user-info{line-height:1.25;display:flex;flex-direction:column}
.dash-user-info strong{color:var(--text);font-weight:600;font-size:13.5px}
.dash-user-info span{font-size:11.5px;color:var(--muted)}
.dash-burger{display:none;width:38px;height:38px;border-radius:10px;border:1px solid var(--border-2);
  background:transparent;color:var(--text);cursor:pointer;align-items:center;justify-content:center}

/* ── LAYOUT ─────────────────────────────────────────────────── */
.dash-page{position:relative;z-index:1;max-width:1280px;margin:0 auto;
  padding:26px 24px 80px;
  display:grid;grid-template-columns:1fr 340px;gap:22px;align-items:start}

/* ── HERO STRIP ─────────────────────────────────────────────── */
.hero-strip{grid-column:1/-1;
  display:flex;align-items:center;gap:20px;
  padding:22px 26px;border-radius:var(--r-lg);
  background:
    radial-gradient(circle at 0% 0%,rgba(0,229,154,.10),transparent 50%),
    radial-gradient(circle at 100% 100%,rgba(34,211,238,.06),transparent 50%),
    var(--glass-2);
  border:1px solid var(--border-2);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  animation:fadeUp .6s cubic-bezier(.22,1,.36,1);
  position:relative;overflow:hidden}
.hero-strip::before{content:'';position:absolute;inset:-1px;border-radius:inherit;pointer-events:none;
  background:linear-gradient(135deg,rgba(0,229,154,.3),transparent 40%);
  -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
  -webkit-mask-composite:xor;mask-composite:exclude;padding:1px}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1}}
.hero-avatar{width:56px;height:56px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--green),var(--green-2));color:#031018;
  box-shadow:0 8px 24px rgba(0,229,154,.35),inset 0 1px 0 rgba(255,255,255,.4)}
.hero-avatar svg{width:26px;height:26px;stroke-width:2.5}
.hero-text h2{font-size:20px;font-weight:800;letter-spacing:-.4px;margin-bottom:3px}
.hero-text p{color:var(--text-2);font-size:13.5px}
.hero-progress{margin-left:auto;text-align:right;flex-shrink:0}
.hero-progress-label{font-size:12px;color:var(--text-2);margin-bottom:5px;font-weight:500}
.hero-progress-label strong{color:var(--text)}
.hero-bar{width:180px;height:6px;border-radius:3px;background:rgba(255,255,255,.06);overflow:hidden;border:1px solid var(--border)}
.hero-bar-fill{height:100%;border-radius:inherit;
  background:linear-gradient(90deg,var(--green),var(--cyan));
  box-shadow:0 0 12px rgba(0,229,154,.5);
  transition:width .9s cubic-bezier(.22,1,.36,1)}

/* ── FLASH ──────────────────────────────────────────────────── */
.flash{grid-column:1/-1}

/* ── SECTION CARDS ──────────────────────────────────────────── */
.sec{margin-bottom:18px}
.sec:last-child{margin-bottom:0}
.sec-head{padding:18px 22px;display:flex;align-items:center;gap:12px;cursor:pointer;
  border-bottom:1px solid var(--border);user-select:none;transition:background .2s}
button.sec-head{
  width:100%;text-align:left;background:transparent;border:0;font:inherit;color:inherit;
  appearance:none;-webkit-appearance:none;padding:18px 22px;
}
.glass.sec{position:relative;isolation:isolate}
.glass.sec > .sec-head{position:relative;z-index:5}
.glass.sec > .sec-body{position:relative;z-index:4}
.sec-head:hover{background:rgba(255,255,255,.02)}
.sec-icon{width:38px;height:38px;border-radius:10px;flex-shrink:0;display:grid;place-items:center;
  background:var(--green-dim);border:1px solid var(--green-line);color:var(--green)}
.sec-icon svg{width:18px;height:18px}
.sec-title{flex:1;font-size:15.5px;font-weight:700;color:var(--text)}
.sec-chev{color:var(--muted);transition:transform .3s}
.sec.open .sec-chev{transform:rotate(180deg)}
.sec-body{padding:22px;display:none;animation:secIn .35s ease}
.sec.open .sec-body{display:block}
@keyframes secIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}

.head-badge{padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700;
  border:1px solid}
.hb-ok{background:var(--green-dim);color:var(--green);border-color:var(--green-line)}
.hb-warn{background:var(--yellow-dim);color:var(--yellow);border-color:var(--yellow-line)}

/* ── COLOR PICKER ──────────────────────────────────────────── */
.color-row{display:grid;grid-template-columns:1fr 56px;gap:10px;align-items:center}
.color-swatch{width:56px;height:46px;border-radius:11px;border:1.5px solid var(--border-2);overflow:hidden;cursor:pointer;
  position:relative}
.color-swatch input[type=color]{width:140%;height:140%;margin:-20%;border:none;cursor:pointer;background:none;padding:0}

/* ── TOGGLE SWITCH ─────────────────────────────────────────── */
.tg{display:flex;align-items:center;justify-content:space-between;gap:14px}
.tg-info{flex:1}
.tg-label{font-size:14.5px;font-weight:600;color:var(--text)}
.tg-sub{font-size:12.5px;color:var(--muted);margin-top:2px}
.tg-switch{position:relative;width:44px;height:24px;flex-shrink:0}
.tg-switch input{opacity:0;position:absolute;inset:0;width:100%;height:100%;margin:0;cursor:pointer;z-index:2}
.tg-slider{position:absolute;inset:0;background:rgba(255,255,255,.06);border:1px solid var(--border-2);
  border-radius:12px;transition:.25s}
.tg-slider::before{content:'';position:absolute;width:18px;height:18px;border-radius:50%;
  background:var(--muted);top:2px;left:2px;transition:.25s}
.tg-switch input:checked + .tg-slider{background:var(--green-dim);border-color:var(--green-line)}
.tg-switch input:checked + .tg-slider::before{transform:translateX(20px);background:var(--green);
  box-shadow:0 0 10px rgba(0,229,154,.5)}

/* ── SIDEBAR CARDS ─────────────────────────────────────────── */
.side{display:flex;flex-direction:column;gap:18px;position:sticky;top:78px}
.side-card{padding:18px 20px}
.side-card-head{display:flex;align-items:center;gap:9px;margin-bottom:13px}
.side-card-icon{width:30px;height:30px;border-radius:8px;display:grid;place-items:center;
  background:var(--green-dim);border:1px solid var(--green-line);color:var(--green);flex-shrink:0}
.side-card-icon svg{width:16px;height:16px}
.side-card-title{font-size:14px;font-weight:700}

/* ── API KEY DISPLAY ───────────────────────────────────────── */
.api-key-box{display:flex;align-items:center;gap:8px;
  background:rgba(10,15,26,.7);border:1px solid var(--border-2);border-radius:10px;padding:10px 12px}
.api-key-text{flex:1;font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--text-2);
  word-break:break-all;line-height:1.4}
.btn-icon-sm{padding:6px 10px;border-radius:8px;border:1px solid var(--green-line);
  background:var(--green-dim);color:var(--green);font-size:11.5px;font-weight:700;cursor:pointer;
  display:inline-flex;align-items:center;gap:4px;transition:all .2s;flex-shrink:0}
.btn-icon-sm svg{width:13px;height:13px}
.btn-icon-sm:hover{background:rgba(0,229,154,.2)}

/* ── EMBED CODE ────────────────────────────────────────────── */
.embed-pre{background:#050810;border:1px solid var(--border-2);border-radius:10px;padding:14px 16px;
  font-family:'JetBrains Mono',monospace;font-size:11.5px;color:#7DD3FC;line-height:1.7;
  white-space:pre-wrap;word-break:break-all;overflow-x:auto;max-height:160px;overflow-y:auto}
.embed-pre .tn{color:#F472B6}
.embed-pre .an{color:#86EFAC}
.embed-pre .av{color:#FDE68A}

/* ── CHECKLIST ─────────────────────────────────────────────── */
.cl-item{display:flex;align-items:center;gap:10px;padding:5px 0;font-size:13px}
.cl-ico{width:22px;height:22px;border-radius:7px;display:grid;place-items:center;flex-shrink:0;
  border:1px solid}
.cl-ico svg{width:13px;height:13px;stroke-width:3}
.cl-ico.ok{background:var(--green-dim);color:var(--green);border-color:var(--green-line)}
.cl-ico.no{background:var(--yellow-dim);color:var(--yellow);border-color:var(--yellow-line)}
.cl-item-text{color:var(--text-2)}
.cl-item.done .cl-item-text{color:var(--text)}

/* ── PROVIDER PILLS ────────────────────────────────────────── */
.prov-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:14px}
.prov-pill{
  padding:12px 12px;border-radius:10px;cursor:pointer;text-align:center;font-size:13px;font-weight:600;
  background:rgba(10,15,26,.5);border:1.5px solid var(--border-2);color:var(--text-2);
  transition:all .2s;position:relative;
}
.prov-pill:hover{border-color:rgba(0,229,154,.35);color:var(--text);background:rgba(255,255,255,.04)}
.prov-pill.active{
  background:rgba(0,229,154,.18);
  border:2px solid var(--green);
  color:var(--green);
  box-shadow:0 0 0 3px rgba(0,229,154,.12),0 4px 16px rgba(0,229,154,.2);
}
.prov-pill.active::before{
  content:'✓';
  position:absolute;top:6px;right:8px;
  font-size:11px;font-weight:900;
  color:var(--green);opacity:1;
}
.prov-pill.active > div:first-child{color:var(--green);font-weight:800}

/* ── HINT BOX ──────────────────────────────────────────────── */
.hint-box{margin-top:8px;font-size:12px;color:var(--muted);line-height:1.55;
  padding:9px 12px;background:rgba(255,255,255,.025);border-radius:8px;border:1px solid var(--border)}
.hint-box code{color:var(--green);background:var(--green-dim);padding:1px 5px;border-radius:4px;font-size:.9em;font-family:'JetBrains Mono',monospace}

/* ── RESPONSIVE ────────────────────────────────────────────── */
@media (max-width:980px){
  .dash-page{grid-template-columns:1fr;padding:20px 16px 60px}
  .side{position:static}
  .hero-progress{display:none}
}
@media (max-width:600px){
  .dash-nav{padding:0 16px}
  .dash-user-info{display:none}
  .hero-strip{padding:18px;flex-direction:column;align-items:flex-start;text-align:left}
  .prov-grid{grid-template-columns:1fr 1fr}
  .sec-head,button.sec-head{padding:14px 16px}
  .sec-body{padding:16px}
  .head-badge{display:none}
}
@media (max-width:420px){
  .dash-nav{height:auto;min-height:56px;padding:10px 12px;flex-wrap:wrap;gap:8px}
  .dash-user{margin-left:0;width:100%;justify-content:space-between;flex-wrap:wrap}
  .dash-page{padding:16px 12px 48px}
}
</style>
</head>
<body>

<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<!-- TOPBAR -->
<header class="dash-nav">
  <a href="/" class="brand">
    <span class="brand-mark"><?= icon('sparkles', 18) ?></span>
    <span class="brand-text">ChatPopup.AI</span>
  </a>

  <div class="dash-user">
    <span class="badge <?= $statusBadge ?>"><span class="badge-dot"></span> <?= e($statusLabel) ?></span>
    <div class="dash-user-info">
      <strong><?= e((string) $user['name']) ?></strong>
      <span><?= e((string) $user['client_name']) ?></span>
    </div>
    <div class="dash-user-avatar"><?= e(mb_strtoupper(mb_substr($firstName, 0, 1))) ?></div>
    <form method="POST" action="/logout.php" style="margin:0">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <button type="submit" class="btn btn-danger" style="padding:8px 14px">
        <?= icon('log-out', 14) ?> Keluar
      </button>
    </form>
  </div>
</header>

<div class="dash-page">

  <?php if ($flash): ?>
    <div class="alert <?= e($flash['type']) === 'success' ? 'alert-success' : 'alert-error' ?> flash">
      <?= $flash['type'] === 'success' ? icon('check-circle', 18) : icon('alert', 18) ?>
      <span><?= e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <?php if ($welcome): ?>
    <div class="alert alert-success flash">
      <?= icon('sparkles', 18) ?>
      <span>Selamat datang! Akun Anda berhasil dibuat. Mari konfigurasi widget di bawah ini.</span>
    </div>
  <?php endif; ?>

  <!-- WELCOME STRIP -->
  <div class="hero-strip">
    <div class="hero-avatar"><?= icon('rocket', 26) ?></div>
    <div class="hero-text">
      <h2>Halo, <?= e($firstName) ?>!</h2>
      <p>Dashboard widget untuk <strong style="color:var(--text)"><?= e((string) $user['client_name']) ?></strong></p>
    </div>
    <div class="hero-progress">
      <div class="hero-progress-label"><strong><?= $checkScore ?></strong>/<?= $checkTotal ?> Setup selesai</div>
      <div class="hero-bar"><div class="hero-bar-fill" style="width:<?= $checkPct ?>%"></div></div>
    </div>
  </div>

  <!-- ── MAIN FORM (LEFT) ── -->
  <div>
    <form method="POST" action="/api/save-settings.php">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <!-- TAMPILAN -->
      <div class="glass sec open" data-sec="1">
        <button type="button" class="sec-head" data-sec-toggle="1">
          <span class="sec-icon"><?= icon('palette', 18) ?></span>
          <span class="sec-title">Tampilan Widget</span>
          <span class="sec-chev"><?= icon('chevron-down', 16) ?></span>
        </button>
        <div class="sec-body">
          <div class="field">
            <label class="field-label" for="bot_name"><?= icon('bot', 14) ?> Nama Bot</label>
            <input type="text" id="bot_name" name="bot_name" class="input"
                   placeholder="e.g. Asisten Jomsite" required maxlength="80"
                   value="<?= e((string) $settings['bot_name']) ?>">
          </div>
          <div class="field">
            <label class="field-label" for="welcome_message"><?= icon('message', 14) ?> Pesan Sambutan</label>
            <textarea id="welcome_message" name="welcome_message" class="textarea"
                      placeholder="Halo! Ada yang bisa saya bantu hari ini?"
                      rows="2"><?= e((string) $settings['welcome_message']) ?></textarea>
          </div>
          <div class="field">
            <label class="field-label" for="color-hex"><?= icon('palette', 14) ?> Warna Utama</label>
            <div class="color-row">
              <input type="text" id="color-hex" name="primary_color" class="input"
                     value="<?= e($primaryColor) ?>" placeholder="#00E59A"
                     maxlength="9" pattern="#[0-9a-fA-F]{6}">
              <div class="color-swatch" title="Klik untuk buka palet warna">
                <input type="color" id="color-picker" value="<?= e($primaryColor) ?>"
                  oninput="(function(v){var h=document.getElementById('color-hex');if(h)h.value=v.toUpperCase();})(this.value)"
                  onchange="(function(v){var h=document.getElementById('color-hex');if(h)h.value=v.toUpperCase();})(this.value)">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- AI -->
      <div class="glass sec open" data-sec="1">
        <button type="button" class="sec-head" data-sec-toggle="1">
          <span class="sec-icon"><?= icon('bot', 18) ?></span>
          <span class="sec-title">Konfigurasi AI</span>
          <span class="head-badge <?= ($aiKeySet && $providerOk) ? 'hb-ok' : 'hb-warn' ?>">
            <?= ($aiKeySet && $providerOk) ? 'Aktif' : 'Belum lengkap' ?>
          </span>
          <span class="sec-chev"><?= icon('chevron-down', 16) ?></span>
        </button>
        <div class="sec-body">

          <div class="field">
            <label class="field-label"><?= icon('layers', 14) ?> Provider AI</label>
            <div class="prov-grid">
              <?php
              $providers = [
                'openrouter' => ['OpenRouter', 'Rekomendasi'],
                'openai'     => ['OpenAI', 'GPT-4o family'],
                'google'     => ['Gemini', 'Google AI'],
                'deepseek'   => ['DeepSeek', 'Murah & cepat'],
              ];
              $currentProvider = $settings['ai_provider'] ?? 'openrouter';
              foreach ($providers as $val => [$label, $desc]):
                $active = $currentProvider === $val ? 'active' : '';
              ?>
                <label class="prov-pill <?= $active ?>" data-prov="<?= $val ?>">
                  <input type="radio" name="ai_provider" value="<?= $val ?>" <?= $currentProvider === $val ? 'checked' : '' ?> style="display:none">
                  <div><?= e($label) ?></div>
                  <div style="font-size:11px;color:var(--muted);font-weight:500;margin-top:2px"><?= e($desc) ?></div>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="field">
            <label class="field-label" for="ai_model">
              <?= icon('brain', 14) ?> Model AI
              <span class="field-hint">Sesuaikan dengan provider</span>
            </label>
            <input type="text" id="ai_model" name="ai_model" class="input"
                   placeholder="e.g. openai/gpt-4o-mini"
                   value="<?= e((string) $settings['ai_model']) ?>" maxlength="120">
            <div class="hint-box" id="modelHint"></div>
          </div>

          <div class="field">
            <label class="field-label" for="ai_api_key"><?= icon('key', 14) ?> API Key Provider</label>
            <div class="input-wrap">
              <span class="input-icon"><?= icon('key', 16) ?></span>
              <input type="password" id="ai_api_key" name="ai_api_key" class="input"
                     placeholder="<?= $aiKeySet ? '••••••• (tersimpan, kosongkan untuk tidak mengubah)' : 'sk-...' ?>"
                     autocomplete="new-password">
              <button type="button" class="input-action pw-toggle-btn" data-pw-target="ai_api_key" aria-label="Tampilkan API key" aria-pressed="false">
                <span class="pw-ico-show"><?= icon('eye', 18) ?></span>
                <span class="pw-ico-hide pw-hidden"><?= icon('eye-off', 18) ?></span>
              </button>
            </div>
          </div>

          <div class="field">
            <label class="field-label" for="ai_system_prompt">
              <?= icon('sparkles', 14) ?> System Prompt
              <span class="field-hint">Kepribadian dan instruksi bot</span>
            </label>
            <textarea id="ai_system_prompt" name="ai_system_prompt" class="textarea" rows="5"
                      placeholder="Kamu adalah asisten ramah untuk [Nama Bisnis]. Jawab dalam bahasa Indonesia yang sopan. Fokus hanya pada produk kami."><?= e((string) $settings['ai_system_prompt']) ?></textarea>
          </div>
        </div>
      </div>

      <!-- DOMAIN -->
      <div class="glass sec" data-sec="1">
        <button type="button" class="sec-head" data-sec-toggle="1">
          <span class="sec-icon"><?= icon('shield', 18) ?></span>
          <span class="sec-title">Keamanan Domain</span>
          <span class="head-badge <?= $domainSet ? 'hb-ok' : 'hb-warn' ?>">
            <?= $domainSet ? 'Diatur' : 'Dibuka' ?>
          </span>
          <span class="sec-chev"><?= icon('chevron-down', 16) ?></span>
        </button>
        <div class="sec-body">
          <div class="field">
            <label class="field-label" for="allowed_origins">
              <?= icon('globe', 14) ?> Allowed Origins
              <span class="field-hint">Pisahkan dengan koma</span>
            </label>
            <input type="text" id="allowed_origins" name="allowed_origins" class="input"
                   value="<?= e((string) $settings['allowed_origins']) ?>"
                   placeholder="https://website-anda.com, https://www.website-anda.com">
            <div class="hint-box">
              Kosongkan atau isi <code>*</code> untuk izinkan semua domain (kurang aman).
            </div>
          </div>
        </div>
      </div>

      <!-- TELEGRAM -->
      <div class="glass sec" data-sec="1">
        <button type="button" class="sec-head" data-sec-toggle="1">
          <span class="sec-icon"><?= icon('phone', 18) ?></span>
          <span class="sec-title">Notifikasi Telegram</span>
          <?php if ($tgSet): ?><span class="head-badge hb-ok">Aktif</span><?php endif; ?>
          <span class="sec-chev"><?= icon('chevron-down', 16) ?></span>
        </button>
        <div class="sec-body">
          <div class="tg" style="margin-bottom:18px;padding-bottom:18px;border-bottom:1px solid var(--border)">
            <div class="tg-info">
              <div class="tg-label">Kirim notifikasi ke Telegram</div>
              <div class="tg-sub">Terima ping setiap ada pesan baru di widget Anda</div>
            </div>
            <label class="tg-switch">
              <input type="checkbox" name="telegram_notify_enabled" value="1"
                     <?= !empty($settings['telegram_notify_enabled']) ? 'checked' : '' ?>>
              <span class="tg-slider"></span>
            </label>
          </div>
          <div class="field">
            <label class="field-label" for="telegram_chat_id"><?= icon('message', 14) ?> Telegram Chat ID</label>
            <input type="text" id="telegram_chat_id" name="telegram_chat_id" class="input"
                   value="<?= e((string) $settings['telegram_chat_id']) ?>"
                   placeholder="Contoh: -100123456789">
            <div class="hint-box">
              Kirim <code>/start</code> ke <strong>@userinfobot</strong> di Telegram untuk dapat Chat ID Anda.
            </div>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">
        <?= icon('check', 18) ?> Simpan Semua Pengaturan
      </button>
    </form>
  </div>

  <!-- ── SIDEBAR (RIGHT) ── -->
  <aside class="side">

    <!-- API KEY -->
    <div class="glass side-card">
      <div class="side-card-head">
        <span class="side-card-icon"><?= icon('key', 16) ?></span>
        <span class="side-card-title">Widget API Key</span>
      </div>
      <div class="api-key-box">
        <span class="api-key-text">
          <?= e(substr((string) $user['client_api_key'], 0, 8)) ?>••••••••••••••••<?= e(substr((string) $user['client_api_key'], -4)) ?>
        </span>
        <button type="button" class="btn-icon-sm" data-copy="<?= htmlspecialchars((string) $user['client_api_key'], ENT_QUOTES, 'UTF-8') ?>">
          <?= icon('copy', 13) ?> Salin
        </button>
      </div>
      <p style="font-size:11.5px;color:var(--muted);margin-top:10px;line-height:1.5">
        Digunakan di embed code. Jangan bagikan ke publik.
      </p>
    </div>

    <!-- EMBED -->
    <div class="glass side-card">
      <div class="side-card-head">
        <span class="side-card-icon"><?= icon('code', 16) ?></span>
        <span class="side-card-title">Kode Embed</span>
      </div>
      <div class="embed-pre" id="embedPre"><span class="tn">&lt;script</span>
  <span class="an">src</span>=<span class="av">"<?= e($baseUrl) ?>/widget/widget.js"</span>
  <span class="an">data-api-key</span>=<span class="av">"<?= e((string) $user['client_api_key']) ?>"</span>
  <span class="an">data-base-url</span>=<span class="av">"<?= e($baseUrl) ?>"</span>
  <span class="an">async</span>
<span class="tn">&gt;&lt;/script&gt;</span></div>
      <button type="button" class="btn btn-primary btn-block" style="margin-top:12px;padding:10px;font-size:13px" id="btnCopyEmbed">
        <?= icon('copy', 14) ?> Salin Embed Code
      </button>
      <p style="font-size:11.5px;color:var(--muted);margin-top:10px;line-height:1.5">
        Tempel sebelum <code style="color:var(--green);background:var(--green-dim);padding:1px 5px;border-radius:4px;font-family:'JetBrains Mono',monospace">&lt;/body&gt;</code> di website Anda.
      </p>
    </div>

    <!-- CHECKLIST -->
    <div class="glass side-card">
      <div class="side-card-head">
        <span class="side-card-icon"><?= icon('check-circle', 16) ?></span>
        <span class="side-card-title">Checklist Setup</span>
        <span class="head-badge <?= $checkScore === $checkTotal ? 'hb-ok' : 'hb-warn' ?>" style="margin-left:auto">
          <?= $checkScore ?>/<?= $checkTotal ?>
        </span>
      </div>
      <?php foreach ($checklist as $item): ?>
        <div class="cl-item <?= $item['ok'] ? 'done' : '' ?>">
          <span class="cl-ico <?= $item['ok'] ? 'ok' : 'no' ?>">
            <?= $item['ok'] ? icon('check', 13) : icon('alert', 13) ?>
          </span>
          <span class="cl-item-text"><?= e($item['label']) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="cl-item done">
        <span class="cl-ico ok"><?= icon('check', 13) ?></span>
        <span class="cl-item-text">Embed code siap disalin</span>
      </div>
    </div>

  </aside>

</div>

<script src="/js/ui.js"></script>
<script>
/* ======================================================
 * Dashboard interactive scripts
 * ====================================================== */

/* ── 1. ACCORDION ── */
(function () {
  function initAccordion() {
    var btns = document.querySelectorAll('[data-sec-toggle]');
    btns.forEach(function (btn) {
      if (btn._secBound) return;
      btn._secBound = true;
      btn.addEventListener('click', function (ev) {
        ev.preventDefault();
        ev.stopPropagation();
        /* climb up until we find element with data-sec attribute */
        var el = btn.parentElement;
        while (el && !el.hasAttribute('data-sec')) el = el.parentElement;
        if (el) el.classList.toggle('open');
      });
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAccordion);
  } else {
    initAccordion();
  }
})();

/* ── 2. COLOR PICKER ↔ HEX TEXT ── */
(function () {
  var hex  = document.getElementById('color-hex');
  var pick = document.getElementById('color-picker');
  if (!hex || !pick) return;

  function validHex(v) {
    v = String(v || '').trim();
    return /^#[0-9a-fA-F]{6}$/.test(v) ? v.toUpperCase() : null;
  }

  /* picker → hex field */
  ['input', 'change'].forEach(function (ev) {
    pick.addEventListener(ev, function () {
      hex.value = String(pick.value).toUpperCase();
    });
  });

  /* hex field → picker */
  ['input', 'blur', 'change'].forEach(function (ev) {
    hex.addEventListener(ev, function () {
      var h = validHex(hex.value);
      if (h) { pick.value = h; hex.value = h; }
    });
  });
})();

/* ── 3. PROVIDER PILLS ── */
(function () {
  document.querySelectorAll('.prov-pill').forEach(function (pill) {
    pill.addEventListener('click', function () {
      document.querySelectorAll('.prov-pill').forEach(function (p) {
        p.classList.remove('active');
      });
      pill.classList.add('active');
      var inp = pill.querySelector('input[type=radio]');
      if (inp) { inp.checked = true; }
      updateModelHint();
    });
  });
})();

/* ── 4. MODEL HINTS ── */
var hints = {
  openrouter: 'Contoh: <code>openai/gpt-4o-mini</code>, <code>meta-llama/llama-3.1-8b-instruct</code>. Lihat <strong>openrouter.ai/models</strong>',
  openai:     'Contoh: <code>gpt-4o</code> atau <code>gpt-4o-mini</code>',
  google:     'Contoh: <code>gemini-1.5-flash</code> atau <code>gemini-1.5-pro</code>',
  deepseek:   'Contoh: <code>deepseek-chat</code> atau <code>deepseek-coder</code>'
};
function updateModelHint() {
  var checked = document.querySelector('input[name=ai_provider]:checked');
  var box = document.getElementById('modelHint');
  if (box) box.innerHTML = checked ? (hints[checked.value] || '') : '';
}
updateModelHint();

/* ── 5. COPY EMBED (reliable, cross-browser) ── */
(function () {
  var embedText = <?= json_encode($embedSnippet, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  var btn = document.getElementById('btnCopyEmbed');
  if (!btn) return;

  function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;left:-9999px;top:0;opacity:0';
    document.body.appendChild(ta);
    ta.focus(); ta.select();
    try { document.execCommand('copy'); } catch(e) {}
    document.body.removeChild(ta);
  }

  btn.addEventListener('click', function (e) {
    e.preventDefault();
    var orig = btn.innerHTML;
    var checkSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:-2px;margin-right:5px"><polyline points="20 6 9 17 4 12"/></svg>';

    function onSuccess() {
      btn.innerHTML = checkSvg + ' Tersalin!';
      btn.style.background = 'linear-gradient(135deg,rgba(0,229,154,.4),rgba(0,229,154,.25))';
      btn.style.borderColor = 'var(--green)';
      setTimeout(function () {
        btn.innerHTML = orig;
        btn.style.background = '';
        btn.style.borderColor = '';
      }, 2200);
    }
    function onFail() {
      fallbackCopy(embedText);
      onSuccess(); /* assume execCommand worked */
    }

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(embedText).then(onSuccess).catch(onFail);
    } else {
      fallbackCopy(embedText);
      onSuccess();
    }
  });
})();
</script>
</body>
</html>
