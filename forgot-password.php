<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/brand.php';

if (current_user() !== null) { header('Location: ' . app_url('/dashboard.php')); exit; }

$lang = get_lang();
$t = lang_strings($lang);

$error    = '';
$info     = '';
$devLink  = '';
$prefill  = trim((string) ($_GET['email'] ?? $_POST['email'] ?? ''));
$emailSent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        $result = create_password_reset_token($prefill);
        if (!$result['ok']) {
            $error = $result['error'] ?? 'Gagal membuat token reset.';
        } else {
            $info = 'Jika email tersebut terdaftar, kami sudah mengirim instruksi reset password ke inbox Anda. Cek folder Spam jika belum muncul dalam 5 menit.';
            $emailSent = true;

            if ($result['exists'] && $result['token']) {
                $link = dashboard_base_url() . '/reset-password.php?token=' . $result['token'];
                $sent = send_password_reset_email((string) $result['user_email'], $link);

                if (!$sent && APP_ENV === 'development') {
                    $devLink = $link;
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="<?= e($t['html_lang']) ?>" dir="<?= e($t['dir']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<title>Lupa Password — <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<style>
.auth-shell{min-height:100vh;display:flex;flex-direction:column;padding:0 20px}
.auth-top{position:sticky;top:0;z-index:30;background:rgba(3,7,18,.72);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);margin:0 -20px;padding:0 24px;height:64px;
  display:flex;align-items:center;justify-content:space-between}
.auth-back{display:inline-flex;align-items:center;gap:6px;font-size:14px;color:var(--text-2);transition:color .2s}
.auth-back:hover{color:var(--green)}
.auth-back svg{width:16px;height:16px}
.auth-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 0}
.auth-card{position:relative;z-index:1;width:100%;max-width:440px;
  background:var(--glass-2);backdrop-filter:blur(28px) saturate(150%);-webkit-backdrop-filter:blur(28px) saturate(150%);
  border:1px solid var(--border-2);border-radius:var(--r-xl);padding:42px 38px;
  box-shadow:0 30px 80px rgba(0,0,0,.5),0 0 0 1px rgba(0,229,154,.04);
  animation:cardIn .7s cubic-bezier(.22,1,.36,1) both}
.auth-card::before{content:'';position:absolute;inset:-1px;border-radius:inherit;pointer-events:none;
  background:linear-gradient(135deg,rgba(0,229,154,.3),transparent 40%,transparent 60%,rgba(34,211,238,.18));
  -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
  -webkit-mask-composite:xor;mask-composite:exclude;padding:1px}
@keyframes cardIn{from{opacity:0;transform:translateY(28px) scale(.97)}to{opacity:1}}
.auth-head{text-align:center;margin-bottom:26px}
.auth-icon{width:60px;height:60px;border-radius:18px;margin:0 auto 16px;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--green),var(--cyan));color:#031018;
  box-shadow:0 12px 32px rgba(0,229,154,.35),inset 0 1px 0 rgba(255,255,255,.4)}
.auth-icon svg{width:28px;height:28px;stroke-width:2.4}
.auth-title{font-size:25px;font-weight:800;letter-spacing:-.5px;margin-bottom:7px}
.auth-sub{color:var(--text-2);font-size:14px;line-height:1.55}
.auth-foot{text-align:center;font-size:14px;color:var(--text-2)}
.auth-foot a{color:var(--green);font-weight:700}
.dev-link{margin-top:14px;padding:12px;background:rgba(34,211,238,.08);border:1px dashed rgba(34,211,238,.3);
  border-radius:10px;font-size:12px;color:var(--cyan);word-break:break-all;line-height:1.5}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="auth-shell">
  <div class="auth-top">
    <a href="<?= e(app_url('/')) ?>" class="brand">
      <?= brand_mark_html(36) ?>
      <span class="brand-text"><?= brand_name_html() ?></span>
    </a>
    <a href="<?= e(app_url('/login.php')) ?>" class="auth-back"><?= icon('arrow-left', 16) ?> Kembali ke Login</a>
  </div>

  <div class="auth-wrap">
    <div class="auth-card">
      <div class="auth-head">
        <div class="auth-icon"><?= icon('key', 28) ?></div>
        <h1 class="auth-title">Lupa Password?</h1>
        <p class="auth-sub">Masukkan email Anda. Kami akan mengirim link reset password yang berlaku 60 menit.</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= icon('alert', 18) ?> <span><?= e($error) ?></span></div>
      <?php endif; ?>

      <?php if ($info): ?>
        <div class="alert alert-success">
          <?= icon('check-circle', 18) ?>
          <span><?= e($info) ?></span>
        </div>
        <?php if ($devLink): ?>
          <div class="dev-link">
            <strong>DEV MODE — link reset:</strong><br>
            <a href="<?= e($devLink) ?>" style="color:var(--cyan)"><?= e($devLink) ?></a>
          </div>
        <?php endif; ?>
        <div style="margin-top:18px">
          <a href="<?= e(app_url('/login.php')) ?>" class="btn btn-outline btn-block">
            <?= icon('arrow-left', 16) ?> Kembali ke Login
          </a>
        </div>
      <?php else: ?>
        <form method="POST" action="<?= e(app_url('/forgot-password.php')) ?>" autocomplete="on" novalidate>
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

          <div class="field">
            <label class="field-label" for="email"><?= icon('mail', 14) ?> Alamat Email</label>
            <div class="input-wrap">
              <span class="input-icon"><?= icon('mail', 16) ?></span>
              <input type="email" id="email" name="email" class="input"
                     placeholder="anda@email.com" value="<?= e($prefill) ?>"
                     required autocomplete="email" autofocus>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg">
            Kirim Link Reset <?= icon('send', 16) ?>
          </button>
        </form>

        <div class="divider">atau</div>
        <p class="auth-foot">Ingat password Anda? <a href="<?= e(app_url('/login.php')) ?>">Masuk</a></p>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
