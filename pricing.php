<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/plans.php';
require_once __DIR__ . '/includes/billing.php';
require_once __DIR__ . '/includes/brand.php';

$user = current_user();
if ($user === null) {
    header('Location: ' . app_url('/login.php', ['redirect' => '/pricing.php']));
    exit;
}

$client = billing_fetch_client((int) $user['client_id']);
$current_plan = (string) ($client['plan_code'] ?? 'free');
$status       = (string) ($user['subscription_status'] ?? 'trial');
$plans        = billing_plans();
$base         = app_site_url();

$monthly = ['free', 'starter_monthly', 'pro_monthly'];
$yearly  = ['starter_yearly', 'pro_yearly'];
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<title>Pilih Paket — <?= e(APP_NAME) ?></title>
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
    <a href="<?= e(app_url('/dashboard.php')) ?>" class="brand">
      <?= brand_mark_html(36) ?>
      <span class="brand-text"><?= brand_name_html() ?></span>
    </a>
    <div style="display:flex;gap:12px;align-items:center">
      <span class="badge <?= $status === 'active' ? 'badge-green' : 'badge-yellow' ?>">
        <?= e(ucfirst($status)) ?>
      </span>
      <a href="<?= e(app_url('/billing.php')) ?>" class="btn btn-ghost" style="padding:8px 14px">Kelola Langganan</a>
    </div>
  </header>

  <main class="pricing-main">
    <div class="pricing-hero">
      <h1>Pilih paket yang cocok</h1>
      <p>Mulai gratis dengan watermark, atau upgrade untuk menghilangkan branding dan mengaktifkan langganan penuh. Harga dalam USD — cocok untuk pasar global.</p>
      <div class="billing-toggle" role="tablist">
        <button type="button" class="active" data-cycle="monthly" id="tabMonthly">Bulanan</button>
        <button type="button" data-cycle="yearly" id="tabYearly">Tahunan (hemat)</button>
      </div>
    </div>

    <div class="plan-grid" id="planGridMonthly">
      <?php foreach ($monthly as $code):
          $p = $plans[$code] ?? null;
          if ($p === null) continue;
          $is_current = $current_plan === $code;
          $featured = !empty($p['highlight']);
      ?>
      <article class="plan-card <?= $featured ? 'featured' : '' ?>">
        <?php if ($featured): ?><span class="plan-badge">Populer</span><?php endif; ?>
        <h2 class="plan-name"><?= e($p['name']) ?></h2>
        <p class="plan-tag"><?= e($p['tagline']) ?></p>
        <div class="plan-price"><?= e($p['price_display']) ?><span><?= e(billing_interval_label($p['interval'] ?? null)) ?></span></div>
        <ul class="plan-features">
          <?php foreach ($p['features'] as $feat): ?>
          <li><?= icon('check', 18) ?> <span><?= e($feat) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($is_current && $status === 'active'): ?>
          <span class="btn btn-ghost btn-block" style="pointer-events:none;opacity:.7">Paket aktif</span>
        <?php else: ?>
          <a href="<?= e(app_url('/checkout.php', ['plan' => $code])) ?>" class="btn btn-primary btn-block btn-lg">
            <?= e($p['cta']) ?>
          </a>
        <?php endif; ?>
        <?php if (!empty($p['show_watermark'])): ?>
        <p class="watermark-note">Widget menampilkan <em>Powered by <?= e(APP_NAME) ?></em> dengan link ke <?= e($base) ?>.</p>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="plan-grid" id="planGridYearly" style="display:none">
      <?php foreach ($yearly as $code):
          $p = $plans[$code] ?? null;
          if ($p === null) continue;
      ?>
      <article class="plan-card">
        <h2 class="plan-name"><?= e($p['name']) ?> <span style="font-size:13px;color:var(--text-3)">Tahunan</span></h2>
        <p class="plan-tag"><?= e($p['tagline']) ?></p>
        <div class="plan-price"><?= e($p['price_display']) ?><span>/tahun</span></div>
        <ul class="plan-features">
          <?php foreach ($p['features'] as $feat): ?>
          <li><?= icon('check', 18) ?> <span><?= e($feat) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= e(app_url('/checkout.php', ['plan' => $code])) ?>" class="btn btn-primary btn-block btn-lg"><?= e($p['cta']) ?></a>
      </article>
      <?php endforeach; ?>
      <article class="plan-card" style="justify-content:center;text-align:center">
        <p style="color:var(--text-2);font-size:14px">Butuh paket Free? Tersedia di tab <strong>Bulanan</strong>.</p>
        <a href="#" class="btn btn-ghost" onclick="document.getElementById('tabMonthly').click();return false">Lihat Free</a>
      </article>
    </div>
  </main>
</div>
<script>
(function(){
  const m=document.getElementById('tabMonthly'),y=document.getElementById('tabYearly');
  const gm=document.getElementById('planGridMonthly'),gy=document.getElementById('planGridYearly');
  function show(cycle){
    const yearly=cycle==='yearly';
    m.classList.toggle('active',!yearly);y.classList.toggle('active',yearly);
    gm.style.display=yearly?'none':'grid';gy.style.display=yearly?'grid':'none';
  }
  m.addEventListener('click',()=>show('monthly'));
  y.addEventListener('click',()=>show('yearly'));
})();
</script>
</body>
</html>
