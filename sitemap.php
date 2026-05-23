<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/blog/posts.php';

header('Content-Type: application/xml; charset=utf-8');

$base      = rtrim(app_base_url(), '/');
$posts     = blog_all_posts();
$today     = date('Y-m-d');
$languages = ['en', 'id', 'es', 'fr', 'pt', 'ja'];

$paths = [
    ['loc' => '/', 'file' => __DIR__ . '/index.php', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => '/pricing.php', 'file' => __DIR__ . '/pricing.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/blog/', 'file' => __DIR__ . '/blog/index.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

foreach ($paths as $entry) {
    $lastmod = is_file($entry['file']) ? date('Y-m-d', (int) filemtime($entry['file'])) : $today;
    foreach ($languages as $lang) {
        $loc = htmlspecialchars($base . app_url($entry['loc'], ['lang' => $lang]), ENT_XML1, 'UTF-8');
        echo "  <url>\n";
        echo "    <loc>{$loc}</loc>\n";
        echo "    <lastmod>{$lastmod}</lastmod>\n";
        echo "    <changefreq>{$entry['changefreq']}</changefreq>\n";
        echo "    <priority>{$entry['priority']}</priority>\n";
        foreach ($languages as $alt) {
            $altLoc = htmlspecialchars($base . app_url($entry['loc'], ['lang' => $alt]), ENT_XML1, 'UTF-8');
            echo '    <xhtml:link rel="alternate" hreflang="' . $alt . '" href="' . $altLoc . '"/>' . "\n";
        }
        echo '    <xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($base . app_url($entry['loc'], ['lang' => 'en']), ENT_XML1, 'UTF-8') . '"/>' . "\n";
        echo "  </url>\n";
    }
}

foreach ($posts as $post) {
    $lastmod = substr((string) ($post['updated'] ?? $post['published']), 0, 10);
    $path    = '/blog/' . rawurlencode((string) $post['slug']);
    $loc     = htmlspecialchars($base . $path, ENT_XML1, 'UTF-8');
    echo "  <url>\n";
    echo "    <loc>{$loc}</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.7</priority>\n";
    echo "  </url>\n";
}

echo "</urlset>\n";
