<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/i18n_auth.php';
require_once __DIR__ . '/includes/i18n_billing.php';
require_once __DIR__ . '/includes/plans.php';
require_once __DIR__ . '/includes/billing.php';
require_once __DIR__ . '/includes/stripe_client.php';
require_once __DIR__ . '/includes/brand.php';

$lang  = get_lang();
$t     = lang_strings($lang);
$lmeta = lang_meta();
$at    = auth_strings($lang);
$pt    = pricing_strings($lang);
$bt    = billing_strings($lang);

$user = require_login();
$client = billing_fetch_client((int) $user['client_id']);
$error = '';
$portal_url = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'portal') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = $bt['session_invalid'];
    } elseif (empty($client['stripe_customer_id'])) {
        $error = $bt['billing_no_stripe_sub'];
    } elseif (!stripe_configured()) {
        $error = $bt['billing_stripe_not_conf'];
    } else {
        $session = stripe_create_portal_session(
            (string) $client['stripe_customer_id'],
            app_site_url() . '/billing.php'
        );
        if ($session !== null && !empty($session['url'])) {
            header('Location: ' . $session['url']);
            exit;
        }
        $error = $bt['billing_portal_fail'];
    }
}

$plan = billing_plan((string) ($client['plan_code'] ?? 'free'));
$days_left = billing_trial_days_left($client['trial_ends_at'] ?? null);
$sub_ends = $client['subscription_ends_at'] ?? null;
$pub_user   = $user;
$pub_active = 'pricing';
?>
<!doctype html>
<html lang="<?= e($t['html_lang']) ?>" dir="<?= e($t['dir']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($bt['billing_title']) ?> — <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="/css/theme.css">
<link rel="stylesheet" href="/css/public-header.css">
<link rel="stylesheet" href="/css/pricing.css">
<script src="/js/ui.js" defer></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="bg-grid"></div>
<div class="pricing-shell">
  <?php require __DIR__ . '/includes/partials/public_header.php'; ?>
  <main class="pricing-main">
    <div class="checkout-card" style="max-width:640px">
      <h1 style="font-size:24px;font-weight:800;margin-bottom:8px"><?= e($bt['billing_title']) ?></h1>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= icon('alert', 18) ?> <?= e($error) ?></div>
      <?php endif; ?>

      <div class="checkout-summary">
        <div class="checkout-row"><span><?= e($bt['row_status']) ?></span><strong><?= e(ucfirst((string) $client['subscription_status'])) ?></strong></div>
        <div class="checkout-row"><span><?= e($bt['row_plan']) ?></span><span><?= e($plan['name'] ?? $client['plan_code']) ?></span></div>
        <?php if ($days_left !== null && ($client['subscription_status'] ?? '') === 'trial'): ?>
        <div class="checkout-row"><span><?= e($bt['row_trial_left']) ?></span><span><?= (int) $days_left ?> <?= e($bt['days_suffix']) ?></span></div>
        <?php endif; ?>
        <?php if ($sub_ends): ?>
        <div class="checkout-row"><span><?= e($bt['row_ends']) ?></span><span><?= e((new DateTime((string) $sub_ends))->format('d M Y')) ?></span></div>
        <?php endif; ?>
        <div class="checkout-row"><span><?= e($bt['row_watermark']) ?></span><span><?= billing_should_show_watermark($client) ? e($bt['watermark_yes']) : e($bt['watermark_no']) ?></span></div>
      </div>

      <?php if (!empty($client['stripe_customer_id'])): ?>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="portal">
        <button type="submit" class="btn btn-ghost btn-block">
          <?= icon('shield', 16) ?> <?= e($bt['open_portal_btn']) ?>
        </button>
        <p style="font-size:12px;color:var(--text-3);margin-top:10px;text-align:center">
          <?= e($bt['portal_hint']) ?>
        </p>
      </form>
      <?php else: ?>
      <a href="<?= e(app_url('/pricing.php')) ?>" class="btn btn-primary btn-block btn-lg"><?= e($bt['choose_pay_btn']) ?></a>
      <?php endif; ?>
    </div>
  </main>
</div>
</body>
</html>
