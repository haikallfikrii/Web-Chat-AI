<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

if (current_user() !== null) { header('Location: /dashboard.php'); exit; }

$error   = '';
$success = '';

if (isset($_GET['logged_out'])) {
    $success = 'Anda berhasil keluar. Sampai jumpa! 👋';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi tidak valid. Muat ulang halaman dan coba lagi.';
    } else {
        $result = attempt_login(
            trim($_POST['email'] ?? ''),
            $_POST['password'] ?? ''
        );
        if ($result['ok']) {
            header('Location: /dashboard.php');
            exit;
        }
        $error = $result['error'];
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Masuk — ChatPopup.AI</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#030712;--bg2:#0D1117;--bg3:#161B22;--green:#00D68F;--green-dark:#00B077;
  --green-dim:rgba(0,214,143,.15);--blue:#3B82F6;--purple:#8B5CF6;
  --text:#E6EDF3;--muted:#7D8590;--border:rgba(255,255,255,.08);--card:rgba(22,27,34,.85);--r:18px}
html{height:100%}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif;
  background:var(--bg);color:var(--text);min-height:100vh;
  display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;overflow-x:hidden}
body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:linear-gradient(rgba(0,214,143,.025) 1px,transparent 1px),
    linear-gradient(90deg,rgba(0,214,143,.025) 1px,transparent 1px);
  background-size:64px 64px}
.orb{position:fixed;border-radius:50%;filter:blur(100px);pointer-events:none;z-index:0}
.orb1{width:600px;height:600px;top:-180px;right:-150px;
  background:radial-gradient(circle,rgba(0,214,143,.12),transparent 70%);
  animation:orb1 16s ease-in-out infinite}
.orb2{width:500px;height:500px;bottom:-180px;left:-120px;
  background:radial-gradient(circle,rgba(59,130,246,.1),transparent 70%);
  animation:orb2 20s ease-in-out infinite}
@keyframes orb1{0%,100%{transform:translate(0,0)}50%{transform:translate(-30px,20px)}}
@keyframes orb2{0%,100%{transform:translate(0,0)}50%{transform:translate(30px,-20px)}}
/* ── LOGO ── */
.logo{position:fixed;top:0;left:0;right:0;z-index:10;
  display:flex;align-items:center;justify-content:space-between;
  padding:16px 32px;background:rgba(3,7,18,.7);backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border)}
.logo-text{font-size:18px;font-weight:900;letter-spacing:-.5px;
  background:linear-gradient(135deg,var(--green),var(--blue));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.logo-link{font-size:14px;color:var(--muted);transition:color .2s}
.logo-link:hover{color:var(--green)}
/* ── CARD ── */
.card{position:relative;z-index:1;
  width:100%;max-width:420px;
  background:var(--card);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
  border:1px solid rgba(0,214,143,.15);border-radius:24px;
  padding:40px 36px;
  box-shadow:0 24px 80px rgba(0,0,0,.5),0 0 0 1px rgba(0,214,143,.05);
  animation:cardIn .7s cubic-bezier(.22,1,.36,1) both}
@keyframes cardIn{from{opacity:0;transform:translateY(32px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.card-head{text-align:center;margin-bottom:32px}
.card-icon{width:56px;height:56px;border-radius:18px;margin:0 auto 16px;
  display:flex;align-items:center;justify-content:center;font-size:26px;
  background:var(--green-dim);border:1px solid rgba(0,214,143,.25)}
.card-head h1{font-size:26px;font-weight:800;letter-spacing:-.5px;margin-bottom:6px}
.card-head p{color:var(--muted);font-size:14px}
/* ── ALERTS ── */
.alert{padding:12px 16px;border-radius:12px;font-size:14px;margin-bottom:20px;
  display:flex;align-items:flex-start;gap:8px;line-height:1.5}
.alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#FCA5A5}
.alert-success{background:rgba(0,214,143,.08);border:1px solid rgba(0,214,143,.2);color:var(--green)}
/* ── FORM ── */
.form-group{margin-bottom:18px}
label{display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px}
input{width:100%;background:rgba(13,17,23,.8);border:1.5px solid var(--border);
  border-radius:12px;padding:12px 16px;font-size:15px;color:var(--text);
  outline:none;transition:all .2s;font-family:inherit}
input:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(0,214,143,.12);background:rgba(22,27,34,.9)}
input::placeholder{color:var(--muted)}
.pw-wrap{position:relative}
.pw-wrap input{padding-right:44px}
.pw-toggle{position:absolute;right:14px;top:50%;transform:translateY(-50%);
  cursor:pointer;color:var(--muted);background:none;border:none;padding:4px;font-size:16px;
  transition:color .2s}
.pw-toggle:hover{color:var(--text)}
.forgot{font-size:13px;color:var(--muted);margin-top:6px;display:block;
  text-align:right}
.forgot:hover{color:var(--green)}
/* ── BUTTON ── */
.btn-submit{width:100%;padding:14px;border-radius:12px;border:none;cursor:pointer;
  font-size:16px;font-weight:700;color:#030712;
  background:linear-gradient(135deg,var(--green),var(--green-dark));
  position:relative;overflow:hidden;transition:all .25s;font-family:inherit;margin-top:8px}
.btn-submit::after{content:'';position:absolute;inset:0;
  background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,.3) 50%,transparent 60%);
  background-size:200% 100%;animation:shimmer 3s infinite}
@keyframes shimmer{0%{background-position:-200% center}100%{background-position:200% center}}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(0,214,143,.3)}
.btn-submit:active{transform:translateY(0)}
/* ── DIVIDER ── */
.divider{display:flex;align-items:center;gap:12px;margin:24px 0}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border)}
.divider span{font-size:12px;color:var(--muted)}
/* ── REGISTER LINK ── */
.register-link{text-align:center;font-size:14px;color:var(--muted)}
.register-link a{color:var(--green);font-weight:700;transition:opacity .2s}
.register-link a:hover{opacity:.8}
</style>
</head>
<body>
<div class="orb orb1"></div>
<div class="orb orb2"></div>

<div class="logo">
  <a href="/" class="logo-text">ChatPopup.AI</a>
  <a href="/" class="logo-link">← Kembali</a>
</div>

<div class="card">
  <div class="card-head">
    <div class="card-icon">🚀</div>
    <h1>Selamat Datang</h1>
    <p>Masuk ke dashboard Anda</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= e($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success">✓ <?= e($success) ?></div>
  <?php endif; ?>

  <form method="POST" action="/login.php" autocomplete="on">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="anda@email.com"
             value="<?= e($_POST['email'] ?? '') ?>" required autocomplete="email">
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <div class="pw-wrap">
        <input type="password" id="password" name="password" placeholder="••••••••"
               required autocomplete="current-password">
        <button type="button" class="pw-toggle" onclick="togglePw()">👁</button>
      </div>
    </div>
    <button type="submit" class="btn-submit">Masuk ke Dashboard</button>
  </form>

  <div class="divider"><span>Belum punya akun?</span></div>
  <p class="register-link">
    <a href="/register.php">Daftar gratis sekarang →</a>
  </p>
</div>

<script>
function togglePw(){
  var f=document.getElementById('password');
  f.type = f.type==='password' ? 'text' : 'password';
}
</script>
</body>
</html>
