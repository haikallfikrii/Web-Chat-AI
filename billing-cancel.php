<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/plans.php';

$user = require_login();
$plan_code = trim((string) ($_GET['plan'] ?? ''));
$plan = billing_plan($plan_code);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Checkout Dibatalkan — <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="/css/theme.css">
<link rel="stylesheet" href="/css/pricing.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="bg-grid"></div>
<main class="pricing-main" style="text-align:center;padding-top:80px">
  <div class="checkout-card">
    <div style="width:64px;height:64px;margin:0 auto 20px;border-radius:50%;background:rgba(251,191,36,.12);display:grid;place-items:center;color:#fbbf24">
      <?= icon('alert', 36) ?>
    </div>
    <h1 style="font-size:26px;font-weight:800;margin-bottom:12px">Checkout dibatalkan</h1>
    <p style="color:var(--text-2);line-height:1.6;margin-bottom:28px">
      <?php if ($plan): ?>
        Anda membatalkan checkout untuk paket <strong><?= e($plan['name']) ?></strong>. Tidak ada biaya yang dikenakan.
      <?php else: ?>
        Tidak ada pembayaran yang diproses.
      <?php endif; ?>
    </p>
    <a href="<?= e(app_url('/pricing.php')) ?>" class="btn btn-primary btn-lg">Pilih Paket Lain</a>
    <a href="<?= e(app_url('/dashboard.php')) ?>" class="btn btn-ghost" style="margin-left:8px;margin-top:12px;display:inline-block">Dashboard</a>
  </div>
</main>
</body>
</html>
