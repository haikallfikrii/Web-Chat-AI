<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/lang.php';

if (current_user() !== null) { header('Location: ' . app_url('/dashboard.php')); exit; }

$lang = get_lang();
$t = lang_strings($lang);

$error    = '';
$success  = '';
$prefill  = trim((string) ($_POST['email'] ?? $_GET['email'] ?? ''));

if (isset($_GET['logged_out']))     $success = 'Anda berhasil keluar. Sampai jumpa!';
if (isset($_GET['password_reset'])) $success = 'Password baru tersimpan. Silakan masuk kembali.';
if (isset($_GET['registered']))     $success = 'Akun berhasil dibuat. Selamat datang!';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        $result = attempt_login($prefill, (string) ($_POST['password'] ?? ''));
        if ($result['ok']) {
            header('Location: ' . app_url('/dashboard.php'));
            exit;
        }
        $error = $result['error'] ?? 'Login gagal.';
    }
}
?>
<!doctype html>
<html lang="<?= e($t['html_lang']) ?>" dir="<?= e($t['dir']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<title>Masuk — ChatPopup.AI</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<script src="/js/ui.js" defer></script>
<style>
.auth-shell{min-height:100vh;display:flex;flex-direction:column;padding:0 20px}
.auth-top{position:sticky;top:0;z-index:30;
  background:rgba(3,7,18,.72);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
  margin:0 -20px;padding:0 24px;height:64px;
  display:flex;align-items:center;justify-content:space-between}
.auth-back{display:inline-flex;align-items:center;gap:6px;font-size:14px;color:var(--text-2);transition:color .2s}
.auth-back:hover{color:var(--green)}
.auth-back svg{width:16px;height:16px}
.auth-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 0}
.auth-card{
  position:relative;z-index:1;width:100%;max-width:440px;
  background:var(--glass-2);
  backdrop-filter:blur(28px) saturate(150%);
  -webkit-backdrop-filter:blur(28px) saturate(150%);
  border:1px solid var(--border-2);border-radius:var(--r-xl);
  padding:42px 38px;
  box-shadow:0 30px 80px rgba(0,0,0,.5),0 0 0 1px rgba(0,229,154,.04);
  animation:cardIn .7s cubic-bezier(.22,1,.36,1) both;
}
.auth-card::before{
  content:'';position:absolute;inset:-1px;border-radius:inherit;pointer-events:none;
  background:linear-gradient(135deg,rgba(0,229,154,.3),transparent 40%,transparent 60%,rgba(34,211,238,.18));
  -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
  -webkit-mask-composite:xor;mask-composite:exclude;padding:1px;
}
@keyframes cardIn{from{opacity:0;transform:translateY(28px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.auth-head{text-align:center;margin-bottom:30px}
.auth-icon{
  width:60px;height:60px;border-radius:18px;margin:0 auto 18px;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--green),var(--green-2));color:#031018;
  box-shadow:0 12px 32px rgba(0,229,154,.35),inset 0 1px 0 rgba(255,255,255,.4);
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

  <div class="auth-top">
    <a href="<?= e(app_url('/')) ?>" class="brand">
      <span class="brand-mark"><?= icon('sparkles', 18) ?></span>
      <span class="brand-text">ChatPopup.AI</span>
    </a>
    <a href="<?= e(app_url('/')) ?>" class="auth-back"><?= icon('arrow-left', 16) ?> Beranda</a>
  </div>

  <div class="auth-wrap">
    <div class="auth-card">
      <div class="auth-head">
        <div class="auth-icon"><?= icon('rocket', 28) ?></div>
        <h1 class="auth-title">Selamat Datang</h1>
        <p class="auth-sub">Masuk ke dashboard Anda</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= icon('alert', 18) ?> <span><?= e($error) ?></span></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= icon('check-circle', 18) ?> <span><?= e($success) ?></span></div>
      <?php endif; ?>

      <form method="POST" action="/login.php" autocomplete="on" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="field">
          <label class="field-label" for="email"><?= icon('mail', 14) ?> Email</label>
          <div class="input-wrap">
            <span class="input-icon"><?= icon('mail', 16) ?></span>
            <input type="email" id="email" name="email" class="input"
                   placeholder="anda@email.com" value="<?= e($prefill) ?>"
                   required autocomplete="email" autofocus>
          </div>
        </div>

        <div class="field">
          <label class="field-label" for="password"><?= icon('lock', 14) ?> Password</label>
          <div class="input-wrap">
            <span class="input-icon"><?= icon('lock', 16) ?></span>
            <input type="password" id="password" name="password" class="input"
                   placeholder="••••••••" required autocomplete="current-password">
            <button type="button" class="input-action pw-toggle-btn" data-pw-target="password" aria-label="Tampilkan password" aria-pressed="false">
              <span class="pw-ico-show"><?= icon('eye', 18) ?></span>
              <span class="pw-ico-hide pw-hidden"><?= icon('eye-off', 18) ?></span>
            </button>
          </div>
        </div>

        <div class="auth-row-meta">
          <label class="auth-checkbox">
            <input type="checkbox" name="remember" value="1"> Ingat saya
          </label>
          <a href="<?= e(app_url('/forgot-password.php', $prefill ? ['email' => $prefill] : [])) ?>">Lupa password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
          Masuk ke Dashboard <?= icon('arrow-right', 16) ?>
        </button>
      </form>

      <div class="divider">Belum punya akun?</div>
      <p class="auth-foot">
        <a href="<?= e(app_url('/register.php')) ?>">Daftar gratis sekarang <?= icon('arrow-right', 14) ?></a>
      </p>
    </div>
  </div>

</div>
</body>
</html>
