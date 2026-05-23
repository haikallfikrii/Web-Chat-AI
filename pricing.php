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
$pt    = pricing_strings($lang);
$seoMeta = seo_pricing_meta($lang);
$user  = current_user();
$pub_active = 'pricing';
$pub_user   = $user;

$client       = $user ? billing_fetch_client((int) $user['client_id']) : null;
$current_plan = (string) ($client['plan_code'] ?? 'free');
$status       = (string) ($user['subscription_status'] ?? 'trial');
$plans        = billing_plans_for_lang($lang);

$byokMonthly    = array_merge(['free'], billing_plan_codes_for_track('byok', 'month'));
$byokYearly     = billing_plan_codes_for_track('byok', 'year');
$managedMonthly = billing_plan_codes_for_track('managed', 'month');
$managedYearly  = billing_plan_codes_for_track('managed', 'year');

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

      <div class="pricing-toggles">
        <div class="track-toggle" role="tablist" aria-label="<?= e($pt['track_aria'] ?? 'Plan type') ?>">
          <button type="button" class="active" data-track="byok" id="tabByok"><?= e($pt['track_byok']) ?></button>
          <button type="button" data-track="managed" id="tabManaged"><?= e($pt['track_managed']) ?></button>
        </div>
        <div class="billing-toggle" role="tablist" aria-label="<?= e($pt['cycle_aria'] ?? 'Billing cycle') ?>">
          <button type="button" class="active" data-cycle="monthly" id="tabMonthly"><?= e($pt['monthly']) ?></button>
          <button type="button" data-cycle="yearly" id="tabYearly">
            <?= e($pt['yearly']) ?>
            <span class="save-pill"><?= e(sprintf($pt['annual_save'] ?? 'Save %d%%', billing_annual_savings_percent())) ?></span>
          </button>
        </div>
      </div>

      <p class="pricing-track-desc" id="trackDescByok"><?= e($pt['byok_desc']) ?></p>
      <p class="pricing-track-desc" id="trackDescManaged" style="display:none"><?= e($pt['managed_desc']) ?></p>
    </div>

    <div class="plan-grid" id="gridByokMonthly" data-panel="byok-monthly">
      <?php $plan_codes = $byokMonthly; require __DIR__ . '/includes/partials/pricing_plan_cards.php'; ?>
    </div>
    <div class="plan-grid" id="gridByokYearly" data-panel="byok-yearly" style="display:none">
      <?php $plan_codes = $byokYearly; require __DIR__ . '/includes/partials/pricing_plan_cards.php'; ?>
    </div>
    <div class="plan-grid" id="gridManagedMonthly" data-panel="managed-monthly" style="display:none">
      <?php $plan_codes = $managedMonthly; require __DIR__ . '/includes/partials/pricing_plan_cards.php'; ?>
    </div>
    <div class="plan-grid" id="gridManagedYearly" data-panel="managed-yearly" style="display:none">
      <?php $plan_codes = $managedYearly; require __DIR__ . '/includes/partials/pricing_plan_cards.php'; ?>
    </div>

    <section class="pricing-compare glass" style="margin-top:48px;padding:28px;border-radius:16px">
      <h2 style="font-size:20px;font-weight:800;margin-bottom:16px"><?= e($pt['compare_h2'] ?? 'BYOK vs Managed AI') ?></h2>
      <div class="compare-table-wrap">
        <table class="compare-table">
          <thead>
            <tr>
              <th></th>
              <th><?= e($pt['track_byok']) ?></th>
              <th><?= e($pt['track_managed']) ?></th>
            </tr>
          </thead>
          <tbody>
            <tr><td><?= e($pt['cmp_ai_key'] ?? 'AI API key') ?></td><td><?= e($pt['cmp_you_bring'] ?? 'You bring') ?></td><td><?= e($pt['cmp_included'] ?? 'Included') ?></td></tr>
            <tr><td><?= e($pt['cmp_token_cost'] ?? 'Token cost') ?></td><td><?= e($pt['cmp_direct'] ?? 'Pay provider directly') ?></td><td><?= e($pt['cmp_quota'] ?? 'Fixed monthly messages') ?></td></tr>
            <tr><td><?= e($pt['cmp_setup'] ?? 'Setup friction') ?></td><td><?= e($pt['cmp_dev'] ?? 'Need API key') ?></td><td><?= e($pt['cmp_plug'] ?? 'Plug & play') ?></td></tr>
            <tr><td><?= e($pt['cmp_price'] ?? 'From') ?></td><td>$19/mo</td><td>$29/mo</td></tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>
<script src="/js/ui.js" defer></script>
<?php require __DIR__ . '/includes/partials/widget_embed.php'; ?>
<script>
(function(){
  var track='byok', cycle='monthly';
  var panels={
    'byok-monthly':document.getElementById('gridByokMonthly'),
    'byok-yearly':document.getElementById('gridByokYearly'),
    'managed-monthly':document.getElementById('gridManagedMonthly'),
    'managed-yearly':document.getElementById('gridManagedYearly')
  };
  function refresh(){
    Object.keys(panels).forEach(function(k){
      if(panels[k]) panels[k].style.display=(k===track+'-'+cycle)?'grid':'none';
    });
    document.getElementById('tabByok').classList.toggle('active',track==='byok');
    document.getElementById('tabManaged').classList.toggle('active',track==='managed');
    document.getElementById('tabMonthly').classList.toggle('active',cycle==='monthly');
    document.getElementById('tabYearly').classList.toggle('active',cycle==='yearly');
    document.getElementById('trackDescByok').style.display=track==='byok'?'block':'none';
    document.getElementById('trackDescManaged').style.display=track==='managed'?'block':'none';
  }
  document.getElementById('tabByok').addEventListener('click',function(){track='byok';refresh();});
  document.getElementById('tabManaged').addEventListener('click',function(){track='managed';refresh();});
  document.getElementById('tabMonthly').addEventListener('click',function(){cycle='monthly';refresh();});
  document.getElementById('tabYearly').addEventListener('click',function(){cycle='yearly';refresh();});
  refresh();
})();
</script>
</body>
</html>
