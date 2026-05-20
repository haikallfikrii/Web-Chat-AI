<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/plans.php';
require_once __DIR__ . '/includes/billing.php';
require_once __DIR__ . '/includes/stripe_client.php';

$user = require_login();
$plan_code = trim((string) ($_GET['plan'] ?? $_POST['plan'] ?? ''));
$plan = billing_plan($plan_code);

if ($plan === null) {
    set_flash('error', 'Paket tidak valid.');
    header('Location: ' . app_url('/pricing.php'));
    exit;
}

$error = '';
$client = billing_fetch_client((int) $user['client_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi tidak valid. Muat ulang halaman.';
    } else {
        if (!billing_plan_is_paid($plan_code)) {
            billing_activate_free_plan((int) $user['client_id']);
            set_flash('success', 'Paket Free aktif. Widget menggunakan watermark ' . APP_NAME . '.');
            header('Location: ' . app_url('/billing-success.php', ['plan' => 'free']));
            exit;
        }

        $result = billing_create_checkout(
            (int) $user['client_id'],
            $plan_code,
            (string) $user['email']
        );

        if ($result['ok'] && !empty($result['url'])) {
            header('Location: ' . $result['url']);
            exit;
        }
        $error = $result['error'] ?? 'Checkout gagal.';
    }
}

$interval_label = billing_interval_label($plan['interval'] ?? null);
$is_paid = billing_plan_is_paid($plan_code);
$stripe_ok = stripe_configured();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<title>Checkout — <?= e($plan['name']) ?> — <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<link rel="stylesheet" href="/css/pricing.css">
</head>
<body>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="pricing-shell">
  <header class="pricing-top">
    <a href="<?= e(app_url('/pricing.php')) ?>" class="auth-back" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-2)">
      <?= icon('arrow-left', 16) ?> Kembali ke paket
    </a>
    <a href="<?= e(app_url('/dashboard.php')) ?>" class="brand">
      <span class="brand-mark"><?= icon('sparkles', 18) ?></span>
      <span class="brand-text"><?= e(APP_NAME) ?></span>
    </a>
  </header>

  <main class="pricing-main">
    <div class="checkout-card">
      <h1 style="font-size:24px;font-weight:800;margin-bottom:8px">Checkout</h1>
      <p style="color:var(--text-2);font-size:14px;margin-bottom:24px">Konfirmasi paket sebelum melanjutkan pembayaran.</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= icon('alert', 18) ?> <span><?= e($error) ?></span></div>
      <?php endif; ?>

      <?php if ($is_paid && !$stripe_ok): ?>
        <div class="alert alert-error">
          <?= icon('alert', 18) ?>
          <span>Stripe belum dikonfigurasi di server. Lihat <code>STRIPE_SETUP.md</code> atau hubungi administrator.</span>
        </div>
      <?php endif; ?>

      <div class="checkout-summary">
        <div class="checkout-row"><span>Paket</span><strong style="color:var(--text)"><?= e($plan['name']) ?></strong></div>
        <div class="checkout-row"><span>Tagihan</span><span><?= e($interval_label !== '' ? trim($interval_label, '/') : 'Sekali') ?></span></div>
        <div class="checkout-row"><span>Akun</span><span><?= e((string) $user['email']) ?></span></div>
        <div class="checkout-row"><span>Bisnis</span><span><?= e((string) ($client['name'] ?? $user['client_name'])) ?></span></div>
        <div class="checkout-row total"><span>Total</span><span><?= e($plan['price_display']) ?><?= e($interval_label) ?></span></div>
      </div>

      <?php if (!empty($plan['show_watermark'])): ?>
        <p class="watermark-note">
          Paket ini menyertakan watermark <strong>Powered by <?= e(APP_NAME) ?></strong> di widget dengan link ke situs kami.
        </p>
      <?php else: ?>
        <p style="font-size:13px;color:var(--text-3);margin-bottom:16px">
          Setelah pembayaran, watermark dihilangkan dan langganan diaktifkan otomatis via Stripe.
        </p>
      <?php endif; ?>

      <form method="POST" action="<?= e(app_url('/checkout.php', ['plan' => $plan_code])) ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="plan" value="<?= e($plan_code) ?>">

        <?php if ($is_paid): ?>
          <button type="submit" class="btn btn-primary btn-block btn-lg" <?= $stripe_ok ? '' : 'disabled' ?>>
            <?= icon('zap', 18) ?> Bayar dengan Stripe
          </button>
          <p style="text-align:center;font-size:12px;color:var(--text-3);margin-top:12px">
            Pembayaran aman oleh Stripe. Kartu internasional &amp; metode lokal (tergantung negara).
          </p>
        <?php else: ?>
          <button type="submit" class="btn btn-primary btn-block btn-lg">
            <?= icon('check-circle', 18) ?> Aktifkan Paket Gratis
          </button>
        <?php endif; ?>
      </form>
    </div>
  </main>
</div>
</body>
</html>
