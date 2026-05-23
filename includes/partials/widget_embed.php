<?php
declare(strict_types=1);
/**
 * Embed widget di halaman marketing (index, pricing, dll).
 * Set LANDING_WIDGET_API_KEY di config.local.php
 */
$key = defined('LANDING_WIDGET_API_KEY') ? LANDING_WIDGET_API_KEY : '';
if ($key === '' || !is_valid_api_key($key)) {
    return;
}
$widgetBase = app_base_url();
?>
<script src="<?= e($widgetBase) ?>/widget/widget.js"
  data-api-key="<?= e($key) ?>"
  data-base-url="<?= e($widgetBase) ?>"
  defer></script>
