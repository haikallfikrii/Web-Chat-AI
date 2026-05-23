<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/i18n_auth.php';
require_once __DIR__ . '/includes/plans.php';
require_once __DIR__ . '/includes/billing.php';
require_once __DIR__ . '/includes/brand.php';
require_once __DIR__ . '/includes/seo.php';

$lang  = get_lang();
$t     = lang_strings($lang);
$lmeta = lang_meta();
$at    = auth_strings($lang);
$pt      = pricing_strings($lang);
$seoMeta = seo_pricing_meta($lang);
$user  = current_user();
$pub_active = 'pricing';
$pub_user   = $user;

$client       = $user ? billing_fetch_client((int) $user['client_id']) : null;
$current_plan = (string) ($client['plan_code'] ?? 'free');
$status       = (string) ($user['subscription_status'] ?? 'trial');
$plans        = billing_plans_for_lang($lang);
$base         = app_site_url();

$monthly = ['free', 'starter_monthly', 'pro_monthly'];
$yearly  = ['starter_yearly', 'pro_yearly'];

function pricing_checkout_url(?array $user, string $plan_code): string
{
    if ($user !== null) {
        return app_url('/checkout.php', ['plan' => $plan_code]);
    }
    return app_url('/login.php', ['redirect' => '/checkout.php?plan=' . rawurlencode($plan_code)]);
}
?>
<!doctype html>
<html lang="<?= e($t['html_lang']) ?>" dir="<?= e($t['dir']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<?= brand_favicon_tags() ?>
<?php
seo_render_head([
    'title'       => $seoMeta['title'],
    'description' => $seoMeta['description'],
    'path'        => '/pricing.php',
]);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<link rel="stylesheet" href="/css/public-header.css">
<link rel="stylesheet" href="/css/pricing.css">
</head>
<body>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="pricing-shell">
  <?php require __DIR__ . '/includes/partials/public_header.php'; ?>

  <main class="pricing-main">
    <div class="pricing-hero">
      <h1><?= e($pt['hero_h1']) ?></h1>
      <p><?= e($pt['hero_p']) ?></p>
      <div class="billing-toggle" role="tablist">
        <button type="button" class="active" data-cycle="monthly" id="tabMonthly"><?= e($pt['monthly']) ?></button>
        <button type="button" data-cycle="yearly" id="tabYearly"><?= e($pt['yearly']) ?></button>
      </div>
    </div>

    <div class="plan-grid" id="planGridMonthly">
      <?php foreach ($monthly as $code):
          $p = $plans[$code] ?? null;
          if ($p === null) continue;
          $is_current = $user && $current_plan === $code && $status === 'active';
          $featured = !empty($p['highlight']);
      ?>
      <article class="plan-card <?= $featured ? 'featured' : '' ?>">
        <?php if ($featured): ?><span class="plan-badge"><?= e($pt['popular']) ?></span><?php endif; ?>
        <h2 class="plan-name"><?= e($p['name']) ?></h2>
        <p class="plan-tag"><?= e($p['tagline']) ?></p>
        <div class="plan-price"><?= e($p['price_display']) ?><span><?= e(billing_interval_label($p['interval'] ?? null, $lang)) ?></span></div>
        <ul class="plan-features">
          <?php foreach ($p['features'] as $feat): ?>
          <li><?= icon('check', 18) ?> <span><?= e($feat) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($is_current): ?>
          <span class="btn btn-ghost btn-block" style="pointer-events:none;opacity:.7"><?= e($pt['active_plan']) ?></span>
        <?php else: ?>
          <a href="<?= e(pricing_checkout_url($user, $code)) ?>" class="btn btn-primary btn-block btn-lg">
            <?= e($user ? $p['cta'] : $pt['login_to_buy']) ?>
          </a>
        <?php endif; ?>
        <?php if (!empty($p['show_watermark'])): ?>
        <p class="watermark-note"><?= e(sprintf($pt['watermark_note'], APP_NAME)) ?></p>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="plan-grid" id="planGridYearly" style="display:none">
      <?php foreach ($yearly as $code):
          $p = $plans[$code] ?? null;
          if ($p === null) continue;
          $is_current = $user && $current_plan === $code && $status === 'active';
      ?>
      <article class="plan-card">
        <h2 class="plan-name"><?= e($p['name']) ?> <span style="font-size:13px;color:var(--text-3)"><?= e($pt['yearly_label']) ?></span></h2>
        <p class="plan-tag"><?= e($p['tagline']) ?></p>
        <div class="plan-price"><?= e($p['price_display']) ?><span>/<?= e($pt['yearly_label']) ?></span></div>
        <ul class="plan-features">
          <?php foreach ($p['features'] as $feat): ?>
          <li><?= icon('check', 18) ?> <span><?= e($feat) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($is_current): ?>
          <span class="btn btn-ghost btn-block" style="pointer-events:none;opacity:.7"><?= e($pt['active_plan']) ?></span>
        <?php else: ?>
          <a href="<?= e(pricing_checkout_url($user, $code)) ?>" class="btn btn-primary btn-block btn-lg">
            <?= e($user ? $p['cta'] : $pt['login_to_buy']) ?>
          </a>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
      <article class="plan-card" style="justify-content:center;text-align:center">
        <p style="color:var(--text-2);font-size:14px"><?= e($pt['free_hint']) ?></p>
        <a href="#" class="btn btn-ghost" onclick="document.getElementById('tabMonthly').click();return false"><?= e($pt['see_free']) ?></a>
      </article>
    </div>
  </main>
</div>
<script src="/js/ui.js" defer></script>
<?php require __DIR__ . '/includes/partials/widget_embed.php'; ?>
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
