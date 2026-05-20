<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/plans.php';
require_once __DIR__ . '/includes/billing.php';

$user = require_login();
$plan_code = trim((string) ($_GET['plan'] ?? ''));
$session_id = trim((string) ($_GET['session_id'] ?? ''));

billing_refresh_session((int) $user['client_id']);
$user = current_user() ?? $user;

$plan = $plan_code !== '' ? billing_plan($plan_code) : null;
$message = 'Terima kasih! Langganan Anda sedang diproses.';

if ($plan_code === 'free' || ($plan && ($plan['price_usd'] ?? 0) <= 0)) {
    $message = 'Paket Free aktif. Widget Anda menampilkan watermark ' . APP_NAME . '.';
} elseif ($session_id !== '') {
    $message = 'Pembayaran Stripe berhasil. Email konfirmasi akan dikirim ke ' . (string) $user['email'] . '.';
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pembayaran Berhasil — <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="/css/theme.css">
<link rel="stylesheet" href="/css/pricing.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<main class="pricing-main" style="text-align:center;padding-top:80px">
  <div class="checkout-card">
    <div style="width:64px;height:64px;margin:0 auto 20px;border-radius:50%;background:var(--green-dim);display:grid;place-items:center;color:var(--green)">
      <?= icon('check-circle', 36) ?>
    </div>
    <h1 style="font-size:26px;font-weight:800;margin-bottom:12px">Berhasil!</h1>
    <p style="color:var(--text-2);line-height:1.6;margin-bottom:28px"><?= $message ?></p>
    <a href="<?= e(app_url('/dashboard.php')) ?>" class="btn btn-primary btn-lg">Ke Dashboard</a>
    <a href="<?= e(app_url('/billing.php')) ?>" class="btn btn-ghost" style="margin-left:8px;margin-top:12px;display:inline-block">Kelola Langganan</a>
  </div>
</main>
</body>
</html>
