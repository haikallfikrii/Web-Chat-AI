<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

if (current_user() !== null) {
    header('Location: /dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    if (login_attempt((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
        header('Location: /dashboard.php');
        exit;
    }

    set_flash('error', 'Email atau password tidak cocok. Coba lagi.');
    header('Location: /login.php');
    exit;
}

$flash = get_flash();
if ($flash === null && isset($_GET['logged_out'])) {
    $flash = ['type' => 'success', 'message' => 'Anda sudah logout. Sampai jumpa!'];
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
:root{--blue:#2563EB;--blue-dark:#1D4ED8;--purple:#7C3AED;--slate:#0F172A;--muted:#64748B;--border:#E2E8F0;--bg:#F8FAFC;--white:#FFFFFF;--red:#DC2626;--green:#16A34A}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:linear-gradient(135deg,#EFF6FF 0%,#F5F3FF 100%);min-height:100vh;display:grid;place-items:center;padding:20px;color:var(--slate)}
.wrap{width:100%;max-width:440px}
.logo{text-align:center;margin-bottom:24px;font-size:24px;font-weight:800;color:var(--blue)}
.logo span{color:var(--purple)}
.card{background:var(--white);border-radius:20px;padding:36px;box-shadow:0 16px 48px rgba(15,23,42,.10)}
h1{font-size:26px;font-weight:800;letter-spacing:-.5px;margin-bottom:4px}
.sub{color:var(--muted);font-size:14px;margin-bottom:28px}
.sub a{color:var(--blue);font-weight:600}
.field{margin-bottom:18px}
label{display:block;font-size:13px;font-weight:700;color:var(--slate);margin-bottom:6px}
input{width:100%;padding:12px 14px;border:1.5px solid var(--border);border-radius:12px;font:inherit;font-size:15px;color:var(--slate);background:var(--bg);transition:border-color .15s}
input:focus{outline:none;border-color:var(--blue);background:var(--white)}
.btn{width:100%;padding:14px;border:0;border-radius:14px;background:var(--blue);color:#fff;font:inherit;font-size:16px;font-weight:700;cursor:pointer;margin-top:4px;transition:background .15s}
.btn:hover{background:var(--blue-dark)}
.flash{padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:14px;font-weight:600}
.flash.success{background:#DCFCE7;color:var(--green)}
.flash.error{background:#FEF2F2;color:var(--red)}
.divider{text-align:center;color:var(--muted);font-size:13px;margin:24px 0 0}
.divider a{color:var(--blue);font-weight:600}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo"><a href="/" style="text-decoration:none;color:inherit">Chat<span>Popup</span>.AI</a></div>
  <div class="card">
    <h1>Selamat Datang</h1>
    <p class="sub">Belum punya akun? <a href="/register.php">Daftar gratis</a></p>

    <?php if ($flash): ?>
      <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <form method="post" action="/login.php">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="username" required
               placeholder="anda@email.com">
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required
               placeholder="••••••••">
      </div>

      <button class="btn" type="submit">Masuk ke Dashboard →</button>
    </form>

    <p class="divider"><a href="/">← Kembali ke halaman utama</a></p>
  </div>
</div>
</body>
</html>
