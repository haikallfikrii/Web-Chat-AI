<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/i18n_auth.php';
require_once __DIR__ . '/../includes/brand.php';
require_once __DIR__ . '/../includes/seo.php';
require_once __DIR__ . '/../includes/blog/posts.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$post = $slug !== '' ? blog_post_by_slug($slug) : null;

if ($post === null) {
    http_response_code(404);
    header('Location: ' . app_url('/blog/'));
    exit;
}

$lang  = get_lang();
$t     = lang_strings($lang);
$lmeta = lang_meta();
$at    = auth_strings($lang);
$pub_active = '';

$body = (string) $post['body'];
$body = str_replace(
    ['/register.php', '/pricing.php', '/blog/post.php?slug=add-ai-chat-widget-5-minutes'],
    [app_url('/register.php'), app_url('/pricing.php'), blog_post_url('add-ai-chat-widget-5-minutes')],
    $body
);
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
    'title'              => $post['title'] . ' | ' . APP_NAME,
    'description'        => $post['description'],
    'path'               => '/blog/' . $post['slug'],
    'type'               => 'article',
    'article_published'  => $post['published'] . 'T00:00:00+00:00',
    'json_ld'            => [[
        '@context'        => 'https://schema.org',
        '@type'           => 'BlogPosting',
        'headline'        => $post['title'],
        'description'     => $post['description'],
        'datePublished'   => $post['published'],
        'dateModified'    => $post['updated'],
        'author'          => ['@type' => 'Organization', 'name' => APP_NAME],
        'publisher'       => [
            '@type' => 'Organization',
            'name'  => APP_NAME,
            'logo'  => ['@type' => 'ImageObject', 'url' => seo_default_image()],
        ],
        'mainEntityOfPage' => seo_absolute_url('/blog/' . $post['slug']),
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

  <main class="blog-main">
    <a class="blog-back" href="<?= e(app_url('/blog/')) ?>"><?= icon('arrow-left', 16) ?> <?= $lang === 'id' ? 'Semua artikel' : 'All articles' ?></a>
    <article class="blog-article">
      <h1 style="font-size:clamp(26px,4vw,36px);font-weight:900;color:var(--text);margin-bottom:12px;letter-spacing:-.5px"><?= e($post['title']) ?></h1>
      <p class="blog-meta" style="margin-bottom:28px"><?= e($post['published']) ?> · <?= e($post['keywords']) ?></p>
      <?= $body ?>
    </article>
    <div class="blog-cta">
      <p style="margin-bottom:16px;color:var(--text-2)"><?= $lang === 'id' ? 'Siap pasang widget di website Anda?' : 'Ready to add the widget to your site?' ?></p>
      <a href="<?= e(app_url('/register.php')) ?>" class="btn btn-primary btn-lg"><?= e($at['register_link']) ?> <?= icon('arrow-right', 16) ?></a>
    </div>
  </main>
</div>
<script src="/js/ui.js" defer></script>
<?php require __DIR__ . '/../includes/partials/widget_embed.php'; ?>
</body>
</html>
