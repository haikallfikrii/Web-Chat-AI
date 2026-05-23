<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/i18n_auth.php';
require_once __DIR__ . '/../includes/brand.php';
require_once __DIR__ . '/../includes/seo.php';
require_once __DIR__ . '/../includes/blog/posts.php';

$lang  = get_lang();
$t     = lang_strings($lang);
$lmeta = lang_meta();
$at    = auth_strings($lang);
$posts = blog_all_posts();
$pub_active = '';

$seoTitle = $lang === 'id'
    ? 'Blog ChatLM — Tips Widget Chat AI & SEO'
    : 'ChatLM Blog — AI Chat Widget Guides & Tips';
$seoDesc = $lang === 'id'
    ? 'Tutorial pasang widget chat AI, WordPress, perbandingan tools, dan tips support otomatis.'
    : 'Guides to embed AI chat on your website, WordPress setup, comparisons, and support automation tips.';
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
    'path'        => '/blog/',
    'type'        => 'website',
    'json_ld'     => [[
        '@context' => 'https://schema.org',
        '@type'    => 'Blog',
        'name'     => APP_NAME . ' Blog',
        'url'      => seo_absolute_url('/blog/'),
    ]],
]);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<link rel="stylesheet" href="/css/public-header.css">
<link rel="stylesheet" href="/css/blog.css">
</head>
<body>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="blog-shell">
  <?php require __DIR__ . '/../includes/partials/public_header.php'; ?>

  <main class="blog-main blog-main--wide">
    <div class="blog-hero">
      <h1><?= $lang === 'id' ? 'Blog &amp; Panduan' : 'Blog &amp; Guides' ?></h1>
      <p><?= e($seoDesc) ?></p>
    </div>

    <div class="blog-list">
      <?php foreach ($posts as $post): ?>
      <a class="blog-card" href="<?= e(blog_post_url($post['slug'])) ?>">
        <h2><?= e($post['title']) ?></h2>
        <p><?= e($post['description']) ?></p>
        <div class="blog-meta">
          <span><?= e($post['published']) ?></span>
          <span><?= e($post['keywords']) ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </main>
</div>
<script src="/js/ui.js" defer></script>
<?php require __DIR__ . '/../includes/partials/widget_embed.php'; ?>
</body>
</html>
