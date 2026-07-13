<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/i18n_billing.php';
require_once __DIR__ . '/includes/plans.php';

$lang  = get_lang();
$t     = lang_strings($lang);
$lmeta = lang_meta();
$bt    = billing_strings($lang);

$user = require_login();
$plan_code = trim((string) ($_GET['plan'] ?? ''));
$plan = billing_plan($plan_code);
?>
<!doctype html>
<html lang="<?= e($t['html_lang']) ?>" dir="<?= e($t['dir']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($bt['cancel_title']) ?> — <?= e(APP_NAME) ?></title>
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
    <h1 style="font-size:26px;font-weight:800;margin-bottom:12px"><?= e($bt['cancel_title']) ?></h1>
    <p style="color:var(--text-2);line-height:1.6;margin-bottom:28px">
      <?php if ($plan): ?>
        <?= sprintf(e($bt['cancel_msg_plan']), '<strong>' . e($plan['name']) . '</strong>') ?>
      <?php else: ?>
        <?= e($bt['cancel_msg_generic']) ?>
      <?php endif; ?>
    </p>
    <a href="<?= e(app_url('/pricing.php')) ?>" class="btn btn-primary btn-lg"><?= e($bt['choose_other_plan']) ?></a>
    <a href="<?= e(app_url('/dashboard.php')) ?>" class="btn btn-ghost" style="margin-left:8px;margin-top:12px;display:inline-block"><?= e($bt['billing_dashboard_link']) ?></a>
  </div>
</main>
</body>
</html>
