<?php
declare(strict_types=1);
/**
 * Render kartu paket. Expects: $plans, $plan_codes, $pt, $lang, $user, $current_plan, $status
 */
if (!isset($plans, $plan_codes, $pt, $lang)) {
    return;
}

foreach ($plan_codes as $code):
    $p = $plans[$code] ?? null;
    if ($p === null) {
        continue;
    }
    $is_current = !empty($user) && $current_plan === $code && ($status ?? '') === 'active';
    $featured   = !empty($p['highlight']);
    $interval   = $p['interval'] ?? null;
    ?>
<article class="plan-card <?= $featured ? 'featured' : '' ?>" data-track="<?= e((string) ($p['track'] ?? '')) ?>">
  <?php if ($featured): ?><span class="plan-badge"><?= e($pt['popular']) ?></span><?php endif; ?>
  <?php if (($p['track'] ?? '') === 'managed'): ?>
  <span class="plan-track-badge"><?= e($pt['managed_badge'] ?? 'AI included') ?></span>
  <?php else: ?>
  <span class="plan-track-badge plan-track-badge--byok"><?= e($pt['byok_badge'] ?? 'BYOK') ?></span>
  <?php endif; ?>
  <h2 class="plan-name"><?= e($p['name']) ?></h2>
  <p class="plan-tag"><?= e($p['tagline']) ?></p>
  <div class="plan-price">
    <?= e($p['price_display']) ?>
    <span><?= $interval === 'year' ? e($pt['yearly_label']) : e(billing_interval_label($interval, $lang)) ?></span>
  </div>
  <?php if ($interval === 'year' && !empty($pt['annual_note'])): ?>
  <p class="plan-annual-note"><?= e($pt['annual_note']) ?></p>
  <?php endif; ?>
  <ul class="plan-features">
    <?php foreach ($p['features'] as $feat): ?>
    <li><?= icon('check', 18) ?> <span><?= e($feat) ?></span></li>
    <?php endforeach; ?>
  </ul>
  <?php if ($is_current): ?>
    <span class="btn btn-ghost btn-block" style="pointer-events:none;opacity:.7"><?= e($pt['active_plan']) ?></span>
  <?php else: ?>
    <a href="<?= e(pricing_checkout_url($user ?? null, $code)) ?>" class="btn btn-primary btn-block btn-lg">
      <?= e(!empty($user) ? $p['cta'] : $pt['login_to_buy']) ?>
    </a>
  <?php endif; ?>
  <?php if (!empty($p['show_watermark'])): ?>
  <p class="watermark-note"><?= e(sprintf($pt['watermark_note'], APP_NAME)) ?></p>
  <?php endif; ?>
</article>
    <?php
endforeach;
