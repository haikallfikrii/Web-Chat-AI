<?php
declare(strict_types=1);
/**
 * Header bersama: pricing, login, register, forgot/reset.
 * Wajib: $lang, $lmeta, $at, $t
 * Opsional: $pub_user (array user), $pub_active ('login'|'register'|'forgot'|'reset'|'pricing')
 */
if (!isset($lang, $lmeta, $at, $t) || !is_array($lmeta) || !is_array($at) || !is_array($t)) {
    return;
}
$pub_active = (string) ($pub_active ?? '');
$pub_user   = $pub_user ?? null;
$navPricing = (string) ($t['nav_pricing'] ?? 'Pricing');
$menuOpen   = (string) ($at['menu_open'] ?? 'Open menu');
$menuClose  = (string) ($at['menu_close'] ?? 'Close menu');
$langAria   = (string) ($at['lang_aria'] ?? 'Language');
?>
<header class="pub-hd" id="pubHd">
  <div class="pub-hd-in">
    <a href="<?= e(app_url('/')) ?>" class="brand">
      <?= brand_mark_html(32) ?>
      <span class="brand-text"><?= brand_name_html() ?></span>
    </a>

    <nav class="pub-hd-nav" aria-label="<?= e($at['nav_aria'] ?? 'Main') ?>">
      <a class="pub-hd-link" href="<?= e(app_url('/')) ?>"><?= e($at['home']) ?></a>
      <a class="pub-hd-link <?= $pub_active === 'pricing' ? 'is-active' : '' ?>"
         href="<?= e(app_url('/pricing.php')) ?>"><?= e($navPricing) ?></a>
      <a class="pub-hd-link <?= $pub_active === 'docs' ? 'is-active' : '' ?>"
         href="<?= e(app_url('/docs/')) ?>"><?= e($t['nav_docs'] ?? 'Docs') ?></a>
      <a class="pub-hd-link" href="<?= e(app_url('/blog/')) ?>"><?= e($t['nav_blog'] ?? 'Blog') ?></a>
      <?php if ($pub_user): ?>
        <?php
        $st = (string) ($pub_user['subscription_status'] ?? 'trial');
        $pt = $pt ?? pricing_strings($lang);
        ?>
        <span class="badge <?= $st === 'active' ? 'badge-green' : 'badge-yellow' ?> pub-hd-badge"><?= e(ucfirst($st)) ?></span>
        <a class="pub-hd-link" href="<?= e(app_url('/billing.php')) ?>"><?= e($pt['manage_billing'] ?? 'Billing') ?></a>
        <a class="btn btn-primary" href="<?= e(app_url('/dashboard.php')) ?>"><?= e($pt['dashboard'] ?? 'Dashboard') ?></a>
      <?php else: ?>
        <a class="pub-hd-link <?= $pub_active === 'login' ? 'is-active' : '' ?>"
           href="<?= e(app_url('/login.php')) ?>"><?= e($at['login_link']) ?></a>
        <?php $langDesktopOnly = true; require __DIR__ . '/lang_switcher.php'; unset($langDesktopOnly); ?>
        <a class="btn btn-primary" href="<?= e(app_url('/register.php')) ?>"><?= e($at['register_btn']) ?></a>
      <?php endif; ?>
    </nav>

    <button type="button" class="pub-hd-burger" id="pubHdBurger"
            aria-label="<?= e($menuOpen) ?>" data-close-label="<?= e($menuClose) ?>"
            aria-expanded="false" aria-controls="pubHdDrawer">
      <?= icon('menu', 20) ?>
    </button>
  </div>
</header>

<div class="pub-hd-backdrop" id="pubHdBackdrop" aria-hidden="true"></div>
<nav class="pub-hd-drawer" id="pubHdDrawer" aria-label="<?= e($at['nav_aria'] ?? 'Main') ?>">
  <div class="pub-hd-drawer-links">
    <a class="pub-hd-link" href="<?= e(app_url('/')) ?>"><?= e($at['home']) ?></a>
    <a class="pub-hd-link <?= $pub_active === 'pricing' ? 'is-active' : '' ?>"
       href="<?= e(app_url('/pricing.php')) ?>"><?= e($navPricing) ?></a>
    <a class="pub-hd-link <?= $pub_active === 'docs' ? 'is-active' : '' ?>"
       href="<?= e(app_url('/docs/')) ?>"><?= e($t['nav_docs'] ?? 'Docs') ?></a>
    <a class="pub-hd-link" href="<?= e(app_url('/blog/')) ?>"><?= e($t['nav_blog'] ?? 'Blog') ?></a>
    <?php if ($pub_user): ?>
      <?php $pt = $pt ?? pricing_strings($lang); ?>
      <a class="pub-hd-link" href="<?= e(app_url('/billing.php')) ?>"><?= e($pt['manage_billing'] ?? 'Billing') ?></a>
      <a class="btn btn-primary btn-block" href="<?= e(app_url('/dashboard.php')) ?>"><?= e($pt['dashboard'] ?? 'Dashboard') ?></a>
    <?php else: ?>
      <a class="pub-hd-link <?= $pub_active === 'login' ? 'is-active' : '' ?>"
         href="<?= e(app_url('/login.php')) ?>"><?= e($at['login_link']) ?></a>
      <a class="pub-hd-link <?= $pub_active === 'register' ? 'is-active' : '' ?>"
         href="<?= e(app_url('/register.php')) ?>"><?= e($at['register_btn']) ?></a>
      <a class="btn btn-primary btn-block" href="<?= e(app_url('/register.php')) ?>"><?= e($at['register_link']) ?></a>
    <?php endif; ?>
  </div>
  <div class="pub-hd-lang">
    <span class="pub-hd-lang-label"><?= e($langAria) ?></span>
    <div class="pub-hd-lang-grid">
      <?php foreach ($lmeta as $code => $info): ?>
      <a class="pub-hd-lang-opt <?= $code === $lang ? 'cur' : '' ?>"
         href="<?= e(lang_switch_url($code)) ?>">
        <span><?= $info['flag'] ?></span>
        <span><?= e($info['label']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</nav>
