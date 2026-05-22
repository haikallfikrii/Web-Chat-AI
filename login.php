<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/i18n_auth.php';
require_once __DIR__ . '/includes/brand.php';

if (current_user() !== null) { header('Location: ' . app_url('/dashboard.php')); exit; }

$lang  = get_lang();
$t     = lang_strings($lang);
$at    = auth_strings($lang);
$lmeta = lang_meta();

$error   = '';
$success = '';
$prefill = trim((string) ($_POST['email'] ?? $_GET['email'] ?? ''));

if (isset($_GET['logged_out']))     $success = $at['logged_out'];
if (isset($_GET['password_reset'])) $success = $at['password_reset_ok'];
if (isset($_GET['registered']))     $success = $at['registered_ok'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = $at['csrf_error'];
    } else {
        $result = attempt_login($prefill, (string) ($_POST['password'] ?? ''));
        if ($result['ok']) {
            header('Location: ' . after_login_url());
            exit;
        }
        $error = $result['error'] ?? 'Login failed.';
    }
}
?>
<!doctype html>
<html lang="<?= e($t['html_lang']) ?>" dir="<?= e($t['dir']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<?= brand_favicon_tags() ?>
<title><?= e($at['login_btn']) ?> — <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<link rel="stylesheet" href="/css/public-header.css">
<script src="/js/ui.js" defer></script>
<?php $pub_active = 'login'; ?>
<style>
.auth-shell{min-height:100vh;display:flex;flex-direction:column;padding:0 20px}
.auth-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 0}
.auth-card{
  position:relative;z-index:1;width:100%;max-width:440px;
  background:var(--glass-2);
  backdrop-filter:blur(28px) saturate(150%);
  -webkit-backdrop-filter:blur(28px) saturate(150%);
  border:1px solid var(--border-2);border-radius:var(--r-xl);
  padding:42px 38px;
  box-shadow:0 30px 80px rgba(0,0,0,.5),0 0 0 1px rgba(20,184,166,.04);
  animation:cardIn .7s cubic-bezier(.22,1,.36,1) both;
}
.auth-card::before{
  content:'';position:absolute;inset:-1px;border-radius:inherit;pointer-events:none;
  background:linear-gradient(135deg,rgba(20,184,166,.3),transparent 40%,transparent 60%,rgba(45,212,191,.18));
  -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
  -webkit-mask-composite:xor;mask-composite:exclude;padding:1px;
}
@keyframes cardIn{from{opacity:0;transform:translateY(28px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.auth-head{text-align:center;margin-bottom:30px}
.auth-icon{
  width:60px;height:60px;border-radius:18px;margin:0 auto 18px;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--green),var(--green-2));color:#031018;
  box-shadow:0 12px 32px var(--green-glow),inset 0 1px 0 rgba(255,255,255,.4);
}
.auth-icon svg{width:28px;height:28px;stroke-width:2.4}
.auth-title{font-size:26px;font-weight:800;letter-spacing:-.5px;margin-bottom:7px}
.auth-sub{color:var(--text-2);font-size:14.5px}
.auth-row-meta{display:flex;align-items:center;justify-content:space-between;margin-top:-6px;margin-bottom:18px;flex-wrap:wrap;gap:8px}
.auth-row-meta a{font-size:13px;color:var(--green);font-weight:600;transition:opacity .2s}
.auth-row-meta a:hover{opacity:.8}
.auth-checkbox{display:flex;align-items:center;gap:7px;font-size:13px;color:var(--text-2);cursor:pointer;user-select:none}
.auth-checkbox input{width:16px;height:16px;accent-color:var(--green);cursor:pointer}
.auth-foot{text-align:center;font-size:14px;color:var(--text-2);margin-top:4px}
.auth-foot a{color:var(--green);font-weight:700}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="auth-shell">
  <?php require __DIR__ . '/includes/partials/public_header.php'; ?>

  <div class="auth-wrap">
    <div class="auth-card">
      <div class="auth-head">
        <div class="auth-icon"><?= icon('rocket', 28) ?></div>
        <h1 class="auth-title"><?= e($at['login_title']) ?></h1>
        <p class="auth-sub"><?= e($at['login_sub']) ?></p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= icon('alert', 18) ?> <span><?= e($error) ?></span></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= icon('check-circle', 18) ?> <span><?= e($success) ?></span></div>
      <?php endif; ?>

      <form method="POST" action="<?= e(app_url('/login.php')) ?>" autocomplete="on" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <?php if (!empty($_GET['redirect'])): ?>
        <input type="hidden" name="redirect" value="<?= e((string) $_GET['redirect']) ?>">
        <?php endif; ?>

        <div class="field">
          <label class="field-label" for="email"><?= icon('mail', 14) ?> <?= e($at['email']) ?></label>
          <div class="input-wrap">
            <span class="input-icon"><?= icon('mail', 16) ?></span>
            <input type="email" id="email" name="email" class="input"
                   placeholder="<?= e($at['ph_email']) ?>" value="<?= e($prefill) ?>"
                   required autocomplete="email" autofocus>
          </div>
        </div>

        <div class="field">
          <label class="field-label" for="password"><?= icon('lock', 14) ?> <?= e($at['password']) ?></label>
          <div class="input-wrap">
            <span class="input-icon"><?= icon('lock', 16) ?></span>
            <input type="password" id="password" name="password" class="input"
                   placeholder="<?= e($at['ph_password']) ?>" required autocomplete="current-password">
            <button type="button" class="input-action pw-toggle-btn" data-pw-target="password"
                    aria-label="<?= e($at['show_pw']) ?>" aria-pressed="false">
              <span class="pw-ico-show"><?= icon('eye', 18) ?></span>
              <span class="pw-ico-hide pw-hidden"><?= icon('eye-off', 18) ?></span>
            </button>
          </div>
        </div>

        <div class="auth-row-meta">
          <label class="auth-checkbox">
            <input type="checkbox" name="remember" value="1"> <?= e($at['remember']) ?>
          </label>
          <a href="<?= e(app_url('/forgot-password.php', $prefill ? ['email' => $prefill] : [])) ?>"><?= e($at['forgot_pw']) ?></a>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
          <?= e($at['login_btn']) ?> <?= icon('arrow-right', 16) ?>
        </button>
      </form>

      <div class="divider"><?= e($at['divider_register']) ?></div>
      <p class="auth-foot">
        <a href="<?= e(app_url('/register.php')) ?>"><?= e($at['register_link']) ?> <?= icon('arrow-right', 14) ?></a>
      </p>
      <p class="auth-foot" style="margin-top:10px">
        <a href="<?= e(app_url('/pricing.php')) ?>"><?= e($at['pricing_link']) ?></a>
      </p>
    </div>
  </div>
</div>
</body>
</html>
