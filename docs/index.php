<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/i18n_auth.php';
require_once __DIR__ . '/../includes/i18n_docs.php';
require_once __DIR__ . '/../includes/brand.php';
require_once __DIR__ . '/../includes/seo.php';
require_once __DIR__ . '/../includes/docs/articles.php';

$lang   = get_lang();
$t      = lang_strings($lang);
$lmeta  = lang_meta();
$at     = auth_strings($lang);
$ds     = docs_strings($lang);
$posts  = docs_all_articles($lang);
$pub_active = 'docs';

$byCat = [];
foreach ($posts as $post) {
    $byCat[$post['category']][] = $post;
}

$seoTitles = [
    'en' => 'Help Center & Tutorials | ChatLM',
    'id' => 'Pusat Bantuan & Tutorial | ChatLM',
    'es' => 'Centro de ayuda | ChatLM',
    'fr' => 'Centre d\'aide | ChatLM',
    'pt' => 'Central de ajuda | ChatLM',
    'ja' => 'ヘルプセンター | ChatLM',
];
$seoDescs = [
    'en' => 'Step-by-step guides: account setup, embed widget, AI providers, domains, Telegram, billing, WordPress, and troubleshooting.',
    'id' => 'Panduan langkah demi langkah: akun, embed widget, provider AI, domain, Telegram, billing, WordPress, troubleshooting.',
    'es' => 'Guías paso a paso para configurar ChatLM en su sitio web.',
    'fr' => 'Guides pas à pas pour configurer ChatLM.',
    'pt' => 'Guias passo a passo para configurar o ChatLM.',
    'ja' => 'ChatLMの設定手順ガイド。',
];
$seoTitle = $seoTitles[$lang] ?? $seoTitles['en'];
$seoDesc  = $seoDescs[$lang] ?? $seoDescs['en'];
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
    'title'       => $seoTitle,
    'description' => $seoDesc,
    'path'        => '/docs/',
    'type'        => 'website',
    'json_ld'     => [[
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => APP_NAME . ' Help Center',
        'url'      => seo_absolute_url('/docs/'),
    ]],
]);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<link rel="stylesheet" href="/css/public-header.css">
<link rel="stylesheet" href="/css/docs.css">
</head>
<body>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="docs-shell">
  <?php require __DIR__ . '/../includes/partials/public_header.php'; ?>

  <main class="docs-main">
    <div class="docs-hero">
      <h1><?= e($ds['hero_h1']) ?></h1>
      <p><?= e($ds['hero_p']) ?></p>
    </div>

    <div class="docs-layout" style="margin-top:36px">
      <aside class="docs-sidebar" aria-label="<?= e($ds['nav_aria']) ?>">
        <?php foreach ($byCat as $cat => $items): ?>
        <h2><?= e(docs_category_label($cat, $lang)) ?></h2>
        <nav class="docs-nav">
          <?php foreach ($items as $item): ?>
          <a href="<?= e(docs_post_url($item['slug'])) ?>"><?= e($item['title']) ?></a>
          <?php endforeach; ?>
        </nav>
        <?php endforeach; ?>
      </aside>

      <div class="docs-list">
        <?php foreach ($posts as $post): ?>
        <a class="docs-card" href="<?= e(docs_post_url($post['slug'])) ?>">
          <span class="docs-cat-badge"><?= e(docs_category_label($post['category'], $lang)) ?></span>
          <h3><?= e($post['title']) ?></h3>
          <p><?= e($post['description']) ?></p>
          <div class="docs-card-meta">
            <span><?= (int) $post['minutes'] ?> <?= e($ds['min_read']) ?></span>
            <span><?= e($ds['read_guide']) ?> →</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</div>
<script src="/js/ui.js" defer></script>
<?php require __DIR__ . '/../includes/partials/widget_embed.php'; ?>
</body>
</html>
