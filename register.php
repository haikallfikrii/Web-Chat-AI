<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

if (current_user() !== null) {
    header('Location: /dashboard.php');
    exit;
}

$errors  = [];
$old     = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    $old = [
        'name'          => (string) ($_POST['name'] ?? ''),
        'business_name' => (string) ($_POST['business_name'] ?? ''),
        'email'         => (string) ($_POST['email'] ?? ''),
    ];

    $result = register_user(
        $old['name'],
        $old['email'],
        (string) ($_POST['password']         ?? ''),
        (string) ($_POST['password_confirm'] ?? ''),
        $old['business_name']
    );

    if ($result['ok']) {
        header('Location: /dashboard.php?welcome=1');
        exit;
    }

    $errors[] = $result['error'];
}

$flash = get_flash();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daftar — ChatPopup.AI</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--blue:#2563EB;--blue-dark:#1D4ED8;--purple:#7C3AED;--slate:#0F172A;--muted:#64748B;--border:#E2E8F0;--bg:#F8FAFC;--white:#FFFFFF;--red:#DC2626;--green:#16A34A}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:linear-gradient(135deg,#EFF6FF 0%,#F5F3FF 100%);min-height:100vh;display:grid;place-items:center;padding:20px;color:var(--slate)}
.wrap{width:100%;max-width:500px}
.logo{text-align:center;margin-bottom:24px;font-size:24px;font-weight:800;color:var(--blue)}
.logo span{color:var(--purple)}
.card{background:var(--white);border-radius:20px;padding:36px;box-shadow:0 16px 48px rgba(15,23,42,.10)}
h1{font-size:26px;font-weight:800;letter-spacing:-.5px;margin-bottom:4px}
.sub{color:var(--muted);font-size:14px;margin-bottom:28px}
.sub a{color:var(--blue);font-weight:600}
.field{margin-bottom:18px}
label{display:block;font-size:13px;font-weight:700;color:var(--slate);margin-bottom:6px}
.hint{font-size:12px;color:var(--muted);font-weight:400;margin-left:4px}
input{width:100%;padding:12px 14px;border:1.5px solid var(--border);border-radius:12px;font:inherit;font-size:15px;color:var(--slate);background:var(--bg);transition:border-color .15s}
input:focus{outline:none;border-color:var(--blue);background:var(--white)}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.btn{width:100%;padding:14px;border:0;border-radius:14px;background:var(--blue);color:#fff;font:inherit;font-size:16px;font-weight:700;cursor:pointer;margin-top:4px;transition:background .15s}
.btn:hover{background:var(--blue-dark)}
.errors{background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:14px 16px;margin-bottom:20px}
.errors p{color:var(--red);font-size:14px;font-weight:600}
.flash{padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:14px;font-weight:600}
.flash.success{background:#DCFCE7;color:var(--green)}
.divider{text-align:center;color:var(--muted);font-size:13px;margin:20px 0 4px}
.terms{font-size:12px;color:var(--muted);margin-top:14px;text-align:center}
.terms a{color:var(--blue)}
@media(max-width:480px){.row2{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo"><a href="/" style="text-decoration:none;color:inherit">Chat<span>Popup</span>.AI</a></div>
  <div class="card">
    <h1>Buat Akun Gratis</h1>
    <p class="sub">Sudah punya akun? <a href="/login.php">Masuk di sini</a></p>

    <?php if ($errors): ?>
      <div class="errors">
        <?php foreach ($errors as $err): ?>
          <p>⚠️ <?= e($err) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($flash): ?>
      <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <form method="post" action="/register.php">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

      <div class="row2">
        <div class="field">
          <label for="name">Nama Anda</label>
          <input id="name" name="name" type="text" autocomplete="name" required
                 placeholder="Budi Santoso"
                 value="<?= e($old['name'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="business_name">Nama Bisnis / Website</label>
          <input id="business_name" name="business_name" type="text" autocomplete="organization" required
                 placeholder="Toko Saya"
                 value="<?= e($old['business_name'] ?? '') ?>">
        </div>
      </div>

      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="email" required
               placeholder="anda@email.com"
               value="<?= e($old['email'] ?? '') ?>">
      </div>

      <div class="row2">
        <div class="field">
          <label for="password">Password <span class="hint">(min. 8 karakter)</span></label>
          <input id="password" name="password" type="password" autocomplete="new-password" required
                 placeholder="••••••••">
        </div>
        <div class="field">
          <label for="password_confirm">Konfirmasi Password</label>
          <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required
                 placeholder="••••••••">
        </div>
      </div>

      <button class="btn" type="submit">Buat Akun &amp; Mulai →</button>
    </form>

    <p class="terms">
      Dengan mendaftar, Anda menyetujui bahwa layanan ini masih dalam tahap Trial.
    </p>
  </div>
</div>
</body>
</html>
