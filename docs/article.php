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

$slug = trim((string) ($_GET['slug'] ?? ''));
$post = $slug !== '' ? docs_article_by_slug($slug, get_lang()) : null;

if ($post === null) {
    http_response_code(404);
    header('Location: ' . app_url('/docs/'));
    exit;
}

$lang  = get_lang();
$t     = lang_strings($lang);
$lmeta = lang_meta();
$at    = auth_strings($lang);
$ds    = docs_strings($lang);
$pub_active = 'docs';
$all   = docs_all_articles($lang);

$body = (string) $post['body'];
$replacements = [
    '/register.php'  => app_url('/register.php'),
    '/login.php'     => app_url('/login.php'),
    '/pricing.php'   => app_url('/pricing.php'),
    '/docs/'         => app_url('/docs/'),
];
foreach ($replacements as $from => $to) {
    $body = str_replace('href="' . $from, 'href="' . $to, $body);
}
$body = preg_replace('#href="/docs/([a-z0-9-]+)"#', 'href="' . app_url('/docs/$1') . '"', $body) ?? $body;
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
    'title'       => $post['title'] . ' | ' . APP_NAME . ' Docs',
    'description' => $post['description'],
    'path'        => '/docs/' . $post['slug'],
    'type'        => 'article',
    'json_ld'     => [[
        '@context'        => 'https://schema.org',
        '@type'           => 'TechArticle',
        'headline'        => $post['title'],
        'description'     => $post['description'],
        'author'          => ['@type' => 'Organization', 'name' => APP_NAME],
        'mainEntityOfPage' => seo_absolute_url('/docs/' . $post['slug']),
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

  <main class="docs-main docs-layout" style="padding-top:32px">
    <aside class="docs-sidebar" aria-label="<?= e($ds['nav_aria']) ?>">
      <a class="docs-back" href="<?= e(app_url('/docs/')) ?>"><?= icon('arrow-left', 16) ?> <?= e($ds['back_index']) ?></a>
      <?php
      $byCat = [];
      foreach ($all as $item) {
          $byCat[$item['category']][] = $item;
      }
      foreach ($byCat as $cat => $items):
      ?>
      <h2><?= e(docs_category_label($cat, $lang)) ?></h2>
      <nav class="docs-nav">
        <?php foreach ($items as $item): ?>
        <a class="<?= $item['slug'] === $slug ? 'is-active' : '' ?>"
           href="<?= e(docs_post_url($item['slug'])) ?>"><?= e($item['title']) ?></a>
        <?php endforeach; ?>
      </nav>
      <?php endforeach; ?>
    </aside>

    <article>
      <span class="docs-cat-badge"><?= e(docs_category_label($post['category'], $lang)) ?></span>
      <h1 style="font-size:clamp(26px,4vw,36px);font-weight:900;color:var(--text);margin:12px 0 8px;letter-spacing:-.5px"><?= e($post['title']) ?></h1>
      <p class="docs-card-meta" style="margin-bottom:28px"><?= (int) $post['minutes'] ?> <?= e($ds['min_read']) ?></p>
      <div class="docs-article"><?= $body ?></div>
      <div class="docs-cta">
        <p style="margin-bottom:16px;color:var(--text-2)"><?= e($ds['cta_ready']) ?></p>
        <a href="<?= e(app_url('/register.php')) ?>" class="btn btn-primary btn-lg"><?= e($at['register_link']) ?> <?= icon('arrow-right', 16) ?></a>
        <a href="<?= e(app_url('/pricing.php')) ?>" class="btn btn-outline btn-lg" style="margin-left:8px"><?= e($t['nav_pricing']) ?></a>
      </div>
    </article>
  </main>
</div>
<script src="/js/ui.js" defer></script>
<?php require __DIR__ . '/../includes/partials/widget_embed.php'; ?>
</body>
</html>
