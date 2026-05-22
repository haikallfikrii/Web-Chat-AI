<?php
declare(strict_types=1);
/**
 * Language switcher compact (desktop dropdown).
 * Expects: $lang, $lmeta
 */
if (!isset($lang, $lmeta) || !is_array($lmeta)) {
    return;
}
$lwExtra = (string) ($langSwitcherExtraClass ?? 'lang-wrap--compact');
if (!empty($langDesktopOnly)) {
    $lwExtra .= ' lang-wrap--desktop';
}
?>
<div class="lang-wrap <?= e($lwExtra) ?>" data-lang-compact>
  <button class="lang-btn" type="button" aria-haspopup="true" aria-expanded="false">
    <span class="lang-flag"><?= $lmeta[$lang]['flag'] ?? '🌐' ?></span>
    <span class="lang-label-text"><?= htmlspecialchars($lmeta[$lang]['label'] ?? $lang, ENT_QUOTES, 'UTF-8') ?></span>
    <svg class="chv" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
  </button>
  <div class="lang-drop" role="menu">
    <?php foreach ($lmeta as $code => $info): ?>
    <a class="lang-opt <?= $code === $lang ? 'cur' : '' ?>"
       href="<?= htmlspecialchars(lang_switch_url($code), ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
      <span class="lang-opt-flag"><?= $info['flag'] ?></span>
      <?= htmlspecialchars($info['label'], ENT_QUOTES, 'UTF-8') ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>
