<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (current_user() !== null) {
    header('Location: /dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (login_attempt($email, $password)) {
        set_flash('success', 'Login berhasil. Selamat datang di dashboard.');
        header('Location: /dashboard.php');
        exit;
    }

    set_flash('error', 'Email atau password tidak cocok.');
    header('Location: /login.php');
    exit;
}

$flash = get_flash();
if ($flash === null && isset($_GET['logged_out'])) {
    $flash = ['type' => 'success', 'message' => 'Anda sudah logout.'];
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Dashboard</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: linear-gradient(135deg,#eff6ff,#f8fafc); color: #0f172a; }
        .wrap { min-height: 100vh; display: grid; place-items: center; padding: 20px; }
        .card { width: 100%; max-width: 460px; background: #fff; border-radius: 18px; padding: 28px; box-shadow: 0 12px 32px rgba(15,23,42,.10); }
        h1, p { margin-top: 0; }
        .muted { color: #64748b; }
        label { display: block; font-weight: 700; margin-bottom: 6px; }
        input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 12px; margin-bottom: 16px; box-sizing: border-box; }
        button { width: 100%; padding: 13px 14px; border: 0; border-radius: 12px; background: #2563eb; color: #fff; font-weight: 700; cursor: pointer; }
        .flash { padding: 12px 14px; border-radius: 12px; margin-bottom: 18px; }
        .flash.success { background: #dcfce7; color: #166534; }
        .flash.error { background: #fee2e2; color: #991b1b; }
        a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <p class="muted"><a href="/">Kembali</a></p>
            <h1>Login Dashboard</h1>
            <p class="muted">Masuk untuk mengatur widget, AI provider, system prompt, dan kode embed.</p>

            <?php if ($flash): ?>
                <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <form method="post" action="/login.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <label for="email">Email</label>
                <input id="email" type="email" name="email" autocomplete="username" required>

                <label for="password">Password</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required>

                <button type="submit">Masuk</button>
            </form>
        </div>
    </div>
</body>
</html>
