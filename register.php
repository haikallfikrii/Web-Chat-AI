<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';

if (current_user() !== null) { header('Location: /dashboard.php'); exit; }

$errors = [];
$fields = ['name' => '', 'business_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        $fields['name']          = trim((string) ($_POST['name']          ?? ''));
        $fields['business_name'] = trim((string) ($_POST['business_name'] ?? ''));
        $fields['email']         = trim((string) ($_POST['email']         ?? ''));

        $result = register_user(
            $fields['name'],
            $fields['email'],
            (string) ($_POST['password']         ?? ''),
            (string) ($_POST['password_confirm'] ?? ''),
            $fields['business_name']
        );

        if ($result['ok']) {
            header('Location: /dashboard.php?welcome=1');
            exit;
        }
        $errors[] = $result['error'] ?? 'Registrasi gagal.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<title>Daftar Gratis — ChatPopup.AI</title>
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
.auth-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:32px 0}
.auth-card{
  position:relative;z-index:1;width:100%;max-width:480px;
  background:var(--glass-2);
  backdrop-filter:blur(28px) saturate(150%);
  -webkit-backdrop-filter:blur(28px) saturate(150%);
  border:1px solid var(--border-2);border-radius:var(--r-xl);
  padding:38px 36px;
  box-shadow:0 30px 80px rgba(0,0,0,.5),0 0 0 1px rgba(0,229,154,.04);
  animation:cardIn .7s cubic-bezier(.22,1,.36,1) both;
}
.auth-card::before{
  content:'';position:absolute;inset:-1px;border-radius:inherit;pointer-events:none;
  background:linear-gradient(135deg,rgba(0,229,154,.3),transparent 40%,transparent 60%,rgba(168,85,247,.2));
  -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
  -webkit-mask-composite:xor;mask-composite:exclude;padding:1px;
}
@keyframes cardIn{from{opacity:0;transform:translateY(28px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.auth-head{text-align:center;margin-bottom:26px}
.auth-icon{
  width:60px;height:60px;border-radius:18px;margin:0 auto 16px;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--green),var(--cyan));color:#031018;
  box-shadow:0 12px 32px rgba(0,229,154,.35),inset 0 1px 0 rgba(255,255,255,.4);
}
.auth-icon svg{width:28px;height:28px;stroke-width:2.4}
.auth-title{font-size:25px;font-weight:800;letter-spacing:-.5px;margin-bottom:7px}
.auth-sub{color:var(--text-2);font-size:14px;line-height:1.55}
.row-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.pw-meter{height:4px;border-radius:2px;background:var(--border-2);margin-top:8px;overflow:hidden}
.pw-meter-fill{height:100%;width:0%;border-radius:2px;transition:width .3s,background .3s}
.pw-hint{font-size:11.5px;color:var(--muted);margin-top:5px}
.tos{font-size:12px;color:var(--muted);text-align:center;margin-top:14px;line-height:1.55}
.tos a{color:var(--green)}
.auth-foot{text-align:center;font-size:14px;color:var(--text-2)}
.auth-foot a{color:var(--green);font-weight:700}
@media (max-width:520px){.row-2{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="auth-shell">

  <div class="auth-top">
    <a href="/" class="brand">
      <span class="brand-mark"><?= icon('sparkles', 18) ?></span>
      <span class="brand-text">ChatPopup.AI</span>
    </a>
    <a href="/login.php" class="auth-back">Sudah punya akun <?= icon('arrow-right', 16) ?></a>
  </div>

  <div class="auth-wrap">
    <div class="auth-card">
      <div class="auth-head">
        <div class="auth-icon"><?= icon('sparkles', 28) ?></div>
        <h1 class="auth-title">Buat Akun Gratis</h1>
        <p class="auth-sub">Setup selesai dalam 5 menit.<br>Tidak perlu kartu kredit.</p>
      </div>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= icon('alert', 18) ?> <span><?= e($err) ?></span></div>
      <?php endforeach; ?>

      <form method="POST" action="/register.php" autocomplete="on" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="row-2">
          <div class="field">
            <label class="field-label" for="name"><?= icon('user', 14) ?> Nama Anda</label>
            <div class="input-wrap">
              <span class="input-icon"><?= icon('user', 16) ?></span>
              <input type="text" id="name" name="name" class="input"
                     placeholder="Ahmad Fauzi" value="<?= e($fields['name']) ?>"
                     required autocomplete="given-name" maxlength="120">
            </div>
          </div>
          <div class="field">
            <label class="field-label" for="business_name"><?= icon('building', 14) ?> Bisnis</label>
            <div class="input-wrap">
              <span class="input-icon"><?= icon('building', 16) ?></span>
              <input type="text" id="business_name" name="business_name" class="input"
                     placeholder="Toko Jomsite" value="<?= e($fields['business_name']) ?>"
                     required autocomplete="organization" maxlength="150">
            </div>
          </div>
        </div>

        <div class="field">
          <label class="field-label" for="email"><?= icon('mail', 14) ?> Alamat Email</label>
          <div class="input-wrap">
            <span class="input-icon"><?= icon('mail', 16) ?></span>
            <input type="email" id="email" name="email" class="input"
                   placeholder="anda@email.com" value="<?= e($fields['email']) ?>"
                   required autocomplete="email">
          </div>
        </div>

        <div class="field">
          <label class="field-label" for="password"><?= icon('lock', 14) ?> Password</label>
          <div class="input-wrap">
            <span class="input-icon"><?= icon('lock', 16) ?></span>
            <input type="password" id="password" name="password" class="input"
                   placeholder="Minimal 8 karakter" required autocomplete="new-password"
                   oninput="checkStrength(this.value)">
            <button type="button" class="input-action pw-toggle-btn" data-pw-target="password" aria-label="Tampilkan password" aria-pressed="false">
              <span class="pw-ico-show"><?= icon('eye', 18) ?></span>
              <span class="pw-ico-hide pw-hidden"><?= icon('eye-off', 18) ?></span>
            </button>
          </div>
          <div class="pw-meter"><div class="pw-meter-fill" id="pwBar"></div></div>
          <div class="pw-hint" id="pwHint">Masukkan password Anda</div>
        </div>

        <div class="field">
          <label class="field-label" for="password_confirm"><?= icon('lock', 14) ?> Konfirmasi Password</label>
          <div class="input-wrap">
            <span class="input-icon"><?= icon('lock', 16) ?></span>
            <input type="password" id="password_confirm" name="password_confirm" class="input"
                   placeholder="Ulangi password" required autocomplete="new-password">
            <button type="button" class="input-action pw-toggle-btn" data-pw-target="password_confirm" aria-label="Tampilkan password" aria-pressed="false">
              <span class="pw-ico-show"><?= icon('eye', 18) ?></span>
              <span class="pw-ico-hide pw-hidden"><?= icon('eye-off', 18) ?></span>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
          Buat Akun &amp; Mulai <?= icon('arrow-right', 16) ?>
        </button>
        <p class="tos">Dengan mendaftar, Anda menyetujui <a href="#">Syarat Layanan</a>.</p>
      </form>

      <div class="divider">Sudah punya akun?</div>
      <p class="auth-foot"><a href="/login.php">Masuk ke dashboard <?= icon('arrow-right', 14) ?></a></p>
    </div>
  </div>

</div>

<script>
function checkStrength(v){
  var bar = document.getElementById('pwBar');
  var hint = document.getElementById('pwHint');
  var score = 0;
  if(v.length >= 8) score++;
  if(/[A-Z]/.test(v)) score++;
  if(/[0-9]/.test(v)) score++;
  if(/[^A-Za-z0-9]/.test(v)) score++;
  var colors = ['var(--red)','#F97316','var(--yellow)','var(--green)'];
  var labels = ['Sangat lemah','Cukup','Kuat','Sangat kuat'];
  bar.style.width = (score * 25) + '%';
  bar.style.background = colors[score - 1] || colors[0];
  hint.textContent = v.length === 0 ? 'Masukkan password Anda' : (labels[score - 1] || labels[0]);
  hint.style.color = score === 0 ? 'var(--muted)' : (colors[score - 1] || 'var(--muted)');
}
</script>
</body>
</html>
