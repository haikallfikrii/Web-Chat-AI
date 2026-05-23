<?php
declare(strict_types=1);

function docs_strings(string $lang): array
{
    $all = [
        'en' => [
            'page_title'       => 'Help Center & Tutorials',
            'hero_h1'          => 'Knowledge Base',
            'hero_p'           => 'Step-by-step guides to set up ChatLM, embed the widget, connect AI providers, and grow your site.',
            'all_articles'     => 'All guides',
            'back_index'       => 'All guides',
            'read_guide'       => 'Read guide',
            'min_read'         => 'min read',
            'cta_ready'        => 'Ready to set up your widget?',
            'cat_getting_started' => 'Getting started',
            'cat_widget'       => 'Widget & embed',
            'cat_ai'           => 'AI configuration',
            'cat_account'      => 'Account & billing',
            'nav_aria'         => 'Documentation',
        ],
        'id' => [
            'page_title'       => 'Pusat Bantuan & Tutorial',
            'hero_h1'          => 'Knowledge Base',
            'hero_p'           => 'Panduan langkah demi langkah: daftar, pasang widget, hubungkan AI, domain, Telegram, dan billing.',
            'all_articles'     => 'Semua panduan',
            'back_index'       => 'Semua panduan',
            'read_guide'       => 'Baca panduan',
            'min_read'         => 'menit baca',
            'cta_ready'        => 'Siap pasang widget di website Anda?',
            'cat_getting_started' => 'Memulai',
            'cat_widget'       => 'Widget & embed',
            'cat_ai'           => 'Konfigurasi AI',
            'cat_account'      => 'Akun & billing',
            'nav_aria'         => 'Dokumentasi',
        ],
        'es' => [
            'page_title' => 'Centro de ayuda y tutoriales', 'hero_h1' => 'Base de conocimiento',
            'hero_p' => 'Guías paso a paso para configurar ChatLM, insertar el widget y conectar proveedores IA.',
            'all_articles' => 'Todas las guías', 'back_index' => 'Todas las guías', 'read_guide' => 'Leer guía',
            'min_read' => 'min de lectura', 'cta_ready' => '¿Listo para configurar tu widget?',
            'cat_getting_started' => 'Primeros pasos', 'cat_widget' => 'Widget e inserción',
            'cat_ai' => 'Configuración IA', 'cat_account' => 'Cuenta y facturación', 'nav_aria' => 'Documentación',
        ],
        'fr' => [
            'page_title' => 'Centre d\'aide et tutoriels', 'hero_h1' => 'Base de connaissances',
            'hero_p' => 'Guides pas à pas pour configurer ChatLM, intégrer le widget et connecter les fournisseurs IA.',
            'all_articles' => 'Tous les guides', 'back_index' => 'Tous les guides', 'read_guide' => 'Lire le guide',
            'min_read' => 'min de lecture', 'cta_ready' => 'Prêt à configurer votre widget ?',
            'cat_getting_started' => 'Démarrage', 'cat_widget' => 'Widget et intégration',
            'cat_ai' => 'Configuration IA', 'cat_account' => 'Compte et facturation', 'nav_aria' => 'Documentation',
        ],
        'pt' => [
            'page_title' => 'Central de ajuda e tutoriais', 'hero_h1' => 'Base de conhecimento',
            'hero_p' => 'Guias passo a passo para configurar o ChatLM, incorporar o widget e conectar provedores de IA.',
            'all_articles' => 'Todos os guias', 'back_index' => 'Todos os guias', 'read_guide' => 'Ler guia',
            'min_read' => 'min de leitura', 'cta_ready' => 'Pronto para configurar seu widget?',
            'cat_getting_started' => 'Primeiros passos', 'cat_widget' => 'Widget e incorporação',
            'cat_ai' => 'Configuração de IA', 'cat_account' => 'Conta e cobrança', 'nav_aria' => 'Documentação',
        ],
        'ja' => [
            'page_title' => 'ヘルプセンターとチュートリアル', 'hero_h1' => 'ナレッジベース',
            'hero_p' => 'ChatLMの設定、ウィジェット埋め込み、AIプロバイダ接続のステップガイド。',
            'all_articles' => 'すべてのガイド', 'back_index' => 'すべてのガイド', 'read_guide' => 'ガイドを読む',
            'min_read' => '分で読める', 'cta_ready' => 'ウィジェットの設定を始めますか？',
            'cat_getting_started' => 'はじめに', 'cat_widget' => 'ウィジェットと埋め込み',
            'cat_ai' => 'AI設定', 'cat_account' => 'アカウントと請求', 'nav_aria' => 'ドキュメント',
        ],
    ];

    return $all[$lang] ?? $all['en'];
}

function docs_category_label(string $cat, string $lang): string
{
    $ds = docs_strings($lang);

    return match ($cat) {
        'getting-started' => $ds['cat_getting_started'],
        'widget'          => $ds['cat_widget'],
        'ai'              => $ds['cat_ai'],
        'account'         => $ds['cat_account'],
        default           => $ds['all_articles'],
    };
}
