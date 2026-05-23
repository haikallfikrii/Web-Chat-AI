<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/bodies/getting-started.php';
require_once __DIR__ . '/bodies/widget.php';
require_once __DIR__ . '/bodies/configuration.php';

/**
 * @return list<array{slug:string,category:string,order:int,minutes:int}>
 */
function docs_registry(): array
{
    return [
        ['slug' => 'quick-start',       'category' => 'getting-started', 'order' => 1, 'minutes' => 4],
        ['slug' => 'create-account',    'category' => 'getting-started', 'order' => 2, 'minutes' => 3],
        ['slug' => 'embed-widget',      'category' => 'widget',          'order' => 3, 'minutes' => 5],
        ['slug' => 'wordpress',         'category' => 'widget',          'order' => 4, 'minutes' => 5],
        ['slug' => 'ai-providers',      'category' => 'ai',              'order' => 5, 'minutes' => 6],
        ['slug' => 'allowed-domains',   'category' => 'ai',              'order' => 6, 'minutes' => 4],
        ['slug' => 'telegram',          'category' => 'ai',              'order' => 7, 'minutes' => 4],
        ['slug' => 'billing-plans',     'category' => 'account',       'order' => 8, 'minutes' => 4],
        ['slug' => 'troubleshooting',   'category' => 'widget',          'order' => 9, 'minutes' => 5],
    ];
}

function docs_article_meta(string $slug, string $lang): ?array
{
    $meta = docs_all_meta();
    if (!isset($meta[$slug][$lang]) && !isset($meta[$slug]['en'])) {
        return null;
    }

    $row = $meta[$slug][$lang] ?? $meta[$slug]['en'];

    return [
        'slug'        => $slug,
        'title'       => $row['title'],
        'description' => $row['description'],
        'keywords'    => $row['keywords'],
    ];
}

/**
 * @return array<string, array<string, array{title:string,description:string,keywords:string}>>
 */
function docs_all_meta(): array
{
    return [
        'quick-start' => [
            'en' => ['title' => 'Quick Start — Go Live in 10 Minutes', 'description' => 'Register, configure your bot, copy the embed code, and test the AI chat widget on your site.', 'keywords' => 'quick start, setup, tutorial'],
            'id' => ['title' => 'Quick Start — Live dalam 10 Menit', 'description' => 'Daftar, atur bot, salin kode embed, dan uji widget chat AI di website Anda.', 'keywords' => 'quick start, setup, tutorial'],
            'es' => ['title' => 'Inicio rápido — En vivo en 10 minutos', 'description' => 'Regístrate, configura el bot, copia el código e inserta el widget.', 'keywords' => 'inicio rápido'],
            'fr' => ['title' => 'Démarrage rapide — En ligne en 10 minutes', 'description' => 'Inscrivez-vous, configurez le bot, copiez le code d\'intégration.', 'keywords' => 'démarrage'],
            'pt' => ['title' => 'Início rápido — No ar em 10 minutos', 'description' => 'Cadastre-se, configure o bot e copie o código de incorporação.', 'keywords' => 'início rápido'],
            'ja' => ['title' => 'クイックスタート — 10分で公開', 'description' => '登録、ボット設定、埋め込みコードのコピーとテスト。', 'keywords' => 'クイックスタート'],
        ],
        'create-account' => [
            'en' => ['title' => 'Create Your Account & Dashboard Tour', 'description' => 'Sign up for ChatLM, verify your email, and learn each section of the dashboard.', 'keywords' => 'register, account, dashboard'],
            'id' => ['title' => 'Buat Akun & Tur Dashboard', 'description' => 'Daftar ChatLM dan kenali setiap bagian dashboard.', 'keywords' => 'daftar, akun, dashboard'],
            'es' => ['title' => 'Crear cuenta y tour del panel', 'description' => 'Registro y recorrido por el panel de ChatLM.', 'keywords' => 'cuenta'],
            'fr' => ['title' => 'Créer un compte et visite du tableau de bord', 'description' => 'Inscription et présentation du dashboard.', 'keywords' => 'compte'],
            'pt' => ['title' => 'Criar conta e tour do painel', 'description' => 'Cadastro e visão geral do painel ChatLM.', 'keywords' => 'conta'],
            'ja' => ['title' => 'アカウント作成とダッシュボード', 'description' => '登録とダッシュボードの各セクションの説明。', 'keywords' => 'アカウント'],
        ],
        'embed-widget' => [
            'en' => ['title' => 'Embed the Chat Widget on Any Website', 'description' => 'Copy the script tag, place it before </body>, and test in incognito mode.', 'keywords' => 'embed, script, widget'],
            'id' => ['title' => 'Pasang Widget Chat di Website', 'description' => 'Salin script tag, tempel sebelum </body>, uji di mode incognito.', 'keywords' => 'embed, widget'],
            'es' => ['title' => 'Insertar el widget en cualquier web', 'description' => 'Copia el script y colócalo antes de </body>.', 'keywords' => 'insertar'],
            'fr' => ['title' => 'Intégrer le widget sur votre site', 'description' => 'Copiez le script avant </body>.', 'keywords' => 'intégration'],
            'pt' => ['title' => 'Incorporar o widget em qualquer site', 'description' => 'Copie o script antes de </body>.', 'keywords' => 'incorporar'],
            'ja' => ['title' => '任意のサイトにウィジェットを埋め込む', 'description' => '</body>直前にスクリプトを配置。', 'keywords' => '埋め込み'],
        ],
        'wordpress' => [
            'en' => ['title' => 'Install ChatLM on WordPress (No Plugin)', 'description' => 'Add the embed via theme footer or a header/footer plugin. Fix cache and admin-only visibility.', 'keywords' => 'WordPress'],
            'id' => ['title' => 'Pasang ChatLM di WordPress Tanpa Plugin', 'description' => 'Footer tema atau plugin header/footer. Atasi cache dan widget hanya untuk admin.', 'keywords' => 'WordPress'],
            'es' => ['title' => 'ChatLM en WordPress sin plugin', 'description' => 'Footer del tema o plugin de inserción.', 'keywords' => 'WordPress'],
            'fr' => ['title' => 'ChatLM sur WordPress sans plugin', 'description' => 'Pied de page ou extension header/footer.', 'keywords' => 'WordPress'],
            'pt' => ['title' => 'ChatLM no WordPress sem plugin', 'description' => 'Rodapé do tema ou plugin de inserção.', 'keywords' => 'WordPress'],
            'ja' => ['title' => 'WordPressにプラグインなしで設置', 'description' => 'フッターまたはヘッダー/フッター系プラグイン。', 'keywords' => 'WordPress'],
        ],
        'ai-providers' => [
            'en' => ['title' => 'Connect OpenAI, Gemini, DeepSeek & OpenRouter', 'description' => 'Add your API key, pick a model, and write a system prompt for your bot.', 'keywords' => 'OpenAI, API key, models'],
            'id' => ['title' => 'Hubungkan OpenAI, Gemini, DeepSeek & OpenRouter', 'description' => 'Masukkan API key, pilih model, dan tulis system prompt.', 'keywords' => 'API key, model'],
            'es' => ['title' => 'Conectar proveedores de IA', 'description' => 'Clave API, modelo y prompt del sistema.', 'keywords' => 'IA'],
            'fr' => ['title' => 'Connecter les fournisseurs IA', 'description' => 'Clé API, modèle et prompt système.', 'keywords' => 'IA'],
            'pt' => ['title' => 'Conectar provedores de IA', 'description' => 'Chave API, modelo e prompt do sistema.', 'keywords' => 'IA'],
            'ja' => ['title' => 'AIプロバイダを接続する', 'description' => 'APIキー、モデル、システムプロンプト。', 'keywords' => 'AI'],
        ],
        'allowed-domains' => [
            'en' => ['title' => 'Allowed Origins & Domain Security', 'description' => 'Whitelist your live domain with https://. Use * only for testing.', 'keywords' => 'CORS, domain, security'],
            'id' => ['title' => 'Allowed Origins & Keamanan Domain', 'description' => 'Whitelist domain dengan https://. Gunakan * hanya untuk testing.', 'keywords' => 'domain, CORS'],
            'es' => ['title' => 'Orígenes permitidos y seguridad', 'description' => 'Lista blanca de dominios con https://.', 'keywords' => 'dominio'],
            'fr' => ['title' => 'Origines autorisées et sécurité', 'description' => 'Liste blanche des domaines en https://.', 'keywords' => 'domaine'],
            'pt' => ['title' => 'Origens permitidas e segurança', 'description' => 'Lista de domínios com https://.', 'keywords' => 'domínio'],
            'ja' => ['title' => '許可ドメインとセキュリティ', 'description' => 'https:// で本番ドメインを登録。', 'keywords' => 'ドメイン'],
        ],
        'telegram' => [
            'en' => ['title' => 'Telegram Notifications for New Chats', 'description' => 'Create a bot with BotFather, paste the token in config, and add your numeric Chat ID.', 'keywords' => 'Telegram, alerts'],
            'id' => ['title' => 'Notifikasi Telegram untuk Chat Baru', 'description' => 'BotFather, token di config, Chat ID numerik di dashboard.', 'keywords' => 'Telegram'],
            'es' => ['title' => 'Notificaciones de Telegram', 'description' => 'BotFather, token y Chat ID numérico.', 'keywords' => 'Telegram'],
            'fr' => ['title' => 'Notifications Telegram', 'description' => 'BotFather, jeton et Chat ID.', 'keywords' => 'Telegram'],
            'pt' => ['title' => 'Notificações no Telegram', 'description' => 'BotFather, token e Chat ID.', 'keywords' => 'Telegram'],
            'ja' => ['title' => 'Telegram通知の設定', 'description' => 'BotFather、トークン、Chat ID。', 'keywords' => 'Telegram'],
        ],
        'billing-plans' => [
            'en' => ['title' => 'Plans, Billing & Removing the Watermark', 'description' => 'Compare Free, Starter, and Pro. Upgrade via Stripe checkout.', 'keywords' => 'pricing, plans, Stripe'],
            'id' => ['title' => 'Paket, Billing & Hilangkan Watermark', 'description' => 'Bandingkan Free, Starter, Pro. Upgrade lewat Stripe.', 'keywords' => 'harga, paket'],
            'es' => ['title' => 'Planes y facturación', 'description' => 'Free, Starter, Pro y pago con Stripe.', 'keywords' => 'precios'],
            'fr' => ['title' => 'Forfaits et facturation', 'description' => 'Free, Starter, Pro et paiement Stripe.', 'keywords' => 'tarifs'],
            'pt' => ['title' => 'Planos e cobrança', 'description' => 'Free, Starter, Pro e checkout Stripe.', 'keywords' => 'preços'],
            'ja' => ['title' => 'プランと請求', 'description' => 'Free / Starter / Pro とStripe決済。', 'keywords' => '料金'],
        ],
        'troubleshooting' => [
            'en' => ['title' => 'Troubleshooting — Widget Not Showing or 5xx', 'description' => 'Fix blank widget, CORS errors, cache plugins, and common setup mistakes.', 'keywords' => 'troubleshooting, fix'],
            'id' => ['title' => 'Troubleshooting — Widget Tidak Muncul', 'description' => 'Atasi widget kosong, error CORS, cache, dan kesalahan umum.', 'keywords' => 'troubleshooting'],
            'es' => ['title' => 'Solución de problemas', 'description' => 'Widget no visible, CORS y caché.', 'keywords' => 'ayuda'],
            'fr' => ['title' => 'Dépannage', 'description' => 'Widget invisible, CORS et cache.', 'keywords' => 'aide'],
            'pt' => ['title' => 'Solução de problemas', 'description' => 'Widget invisível, CORS e cache.', 'keywords' => 'ajuda'],
            'ja' => ['title' => 'トラブルシューティング', 'description' => '表示されない、CORS、キャッシュ。', 'keywords' => 'トラブル'],
        ],
    ];
}

/**
 * @return list<array{slug:string,category:string,order:int,minutes:int,title:string,description:string,keywords:string}>
 */
function docs_all_articles(string $lang): array
{
    $out = [];
    foreach (docs_registry() as $row) {
        $meta = docs_article_meta($row['slug'], $lang);
        if ($meta === null) {
            continue;
        }
        $out[] = array_merge($row, $meta);
    }

    usort($out, static fn ($a, $b) => $a['order'] <=> $b['order']);

    return $out;
}

/**
 * @return array{slug:string,category:string,order:int,minutes:int,title:string,description:string,keywords:string,body:string}|null
 */
function docs_article_by_slug(string $slug, string $lang): ?array
{
    $meta = docs_article_meta($slug, $lang);
    if ($meta === null) {
        return null;
    }

    $fn = 'docs_body_' . str_replace('-', '_', $slug);
    if (!function_exists($fn)) {
        return null;
    }

    $registry = docs_registry();
    $row      = null;
    foreach ($registry as $r) {
        if ($r['slug'] === $slug) {
            $row = $r;
            break;
        }
    }
    if ($row === null) {
        return null;
    }

    return array_merge($row, $meta, ['body' => $fn($lang)]);
}

function docs_post_url(string $slug): string
{
    return app_url('/docs/' . $slug);
}
