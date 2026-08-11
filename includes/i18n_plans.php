<?php
declare(strict_types=1);

/** Label interval harga (/month, /year, dll.) */
function plan_interval_labels(string $lang): array
{
    $all = [
        'en' => ['month' => '/mo', 'year' => '/yr'],
        'id' => ['month' => '/bulan', 'year' => '/tahun'],
        'es' => ['month' => '/mes', 'year' => '/año'],
        'fr' => ['month' => '/mois', 'year' => '/an'],
        'pt' => ['month' => '/mês', 'year' => '/ano'],
        'ja' => ['month' => '/月', 'year' => '/年'],
    ];

    return $all[$lang] ?? $all['en'];
}

/**
 * Terjemahan tagline, fitur, dan CTA per kode paket.
 *
 * @return array<string, array{tagline:string, features:list<string>, cta:string}>
 */
function plan_strings(string $lang): array
{
    $all = [
        'en' => [
            'free' => [
                'tagline'  => 'Try the widget with a watermark',
                'features' => [
                    'AI chat widget on 1 website',
                    'Watermark "Powered by ChatLM"',
                    'Full 14-day trial',
                    'Telegram notifications',
                    'Multi-provider AI (bring your own API key)',
                ],
                'cta' => 'Start Free',
            ],
            'byok_starter' => [
                'tagline'  => 'Your AI keys · 1 website · no watermark',
                'features' => ['1 website', 'No watermark', 'Your OpenAI / Gemini / DeepSeek keys', 'Zero token markup', 'Telegram alerts'],
                'cta' => 'Start Starter',
            ],
            'byok_pro' => [
                'tagline'  => 'Agencies & growing sites · 5 websites',
                'features' => ['5 websites', 'Whitelabel widget', 'Custom CSS & branding', 'Chat history & AI memory', 'Email support'],
                'cta' => 'Start Pro',
            ],
            'byok_agency' => [
                'tagline'  => '25 websites · multi-client',
                'features' => ['25 websites', 'Multi-client workflows', 'Priority support', 'Advanced allowed origins', 'Zero token markup'],
                'cta' => 'Start Agency',
            ],
            'managed_starter' => [
                'tagline'  => '3,000 AI messages/mo · plug & play',
                'features' => ['3,000 AI messages / month', '1 website · plug & play', 'GPT-4o-mini class models', 'No API key required', 'Telegram alerts'],
                'cta' => 'Start Starter',
            ],
            'managed_pro' => [
                'tagline'  => '4,000 messages · 3 sites · whitelabel',
                'features' => ['4,000 AI messages / month', '3 websites', 'Whitelabel (no watermark)', 'Model routing via OpenRouter', 'Lead-friendly chat memory'],
                'cta' => 'Start Growth',
            ],
            'managed_agency' => [
                'tagline'  => '12,000 messages · 10 websites',
                'features' => ['12,000 AI messages / month', '10 websites', 'Whitelabel', 'Priority support', 'Best for agencies & multi-brand'],
                'cta' => 'Start Business',
            ],
        ],
        'id' => [
            'free' => [
                'tagline'  => 'Coba widget dengan watermark',
                'features' => [
                    'Widget chat AI di 1 website',
                    'Watermark "Powered by ChatLM"',
                    'Trial 14 hari penuh',
                    'Notifikasi Telegram',
                    'Multi-provider AI (BYOK)',
                ],
                'cta' => 'Mulai Gratis',
            ],
            'byok_starter' => [
                'tagline'  => 'API key Anda · 1 website · tanpa watermark',
                'features' => ['1 website', 'Tanpa watermark', 'OpenAI / Gemini / DeepSeek key Anda', 'Tanpa markup token', 'Notifikasi Telegram'],
                'cta' => 'Mulai Starter',
            ],
            'byok_pro' => [
                'tagline'  => '5 website · agensi & bisnis',
                'features' => ['5 website', 'Widget whitelabel', 'Custom CSS & branding', 'Riwayat chat & memori AI', 'Dukungan email'],
                'cta' => 'Mulai Pro',
            ],
            'byok_agency' => [
                'tagline'  => '25 website · multi-klien',
                'features' => ['25 website', 'Workflow multi-klien', 'Prioritas dukungan', 'Origins lanjutan', 'Tanpa markup token'],
                'cta' => 'Mulai Agency',
            ],
            'managed_starter' => [
                'tagline'  => '3.000 pesan AI/bulan · langsung pakai',
                'features' => ['3.000 pesan AI / bulan', '1 website · plug & play', 'Model kelas GPT-4o-mini', 'Tanpa setup API key', 'Notifikasi Telegram'],
                'cta' => 'Mulai Starter',
            ],
            'managed_pro' => [
                'tagline'  => '4.000 pesan · 3 situs · whitelabel',
                'features' => ['4.000 pesan AI / bulan', '3 website', 'Whitelabel (tanpa watermark)', 'Routing via OpenRouter', 'Memori chat siap lead'],
                'cta' => 'Mulai Growth',
            ],
            'managed_agency' => [
                'tagline'  => '12.000 pesan · 10 website',
                'features' => ['12.000 pesan AI / bulan', '10 website', 'Whitelabel', 'Prioritas dukungan', 'Untuk agency & multi-brand'],
                'cta' => 'Mulai Business',
            ],
        ],
        'es' => [
            'free' => [
                'tagline'  => 'Prueba el widget con marca de agua',
                'features' => ['Widget IA en 1 sitio', 'Marca "Powered by ChatLM"', 'Prueba de 14 días', 'Notificaciones Telegram', 'Multi-proveedor IA (tu API key)'],
                'cta' => 'Empezar Gratis',
            ],
            'byok_starter' => [
                'tagline'  => 'Tu API key · 1 sitio · sin marca',
                'features' => ['1 sitio', 'Sin marca de agua', 'Tu OpenAI / Gemini / DeepSeek', 'Sin markup de tokens', 'Alertas Telegram'],
                'cta' => 'Empezar Starter',
            ],
            'byok_pro' => [
                'tagline'  => '5 sitios · agencias y negocios',
                'features' => ['5 sitios', 'Widget whitelabel', 'CSS y branding custom', 'Historial y memoria IA', 'Soporte email'],
                'cta' => 'Empezar Pro',
            ],
            'byok_agency' => [
                'tagline'  => '25 sitios · multi-cliente',
                'features' => ['25 sitios', 'Workflows multi-cliente', 'Soporte prioritario', 'Origins avanzados', 'Sin markup'],
                'cta' => 'Empezar Agency',
            ],
            'managed_starter' => [
                'tagline'  => '3,000 mensajes IA/mes · plug & play',
                'features' => ['3,000 mensajes IA / mes', '1 sitio · plug & play', 'Modelos GPT-4o-mini', 'Sin API key', 'Alertas Telegram'],
                'cta' => 'Empezar Starter',
            ],
            'managed_pro' => [
                'tagline'  => '4,000 mensajes · 3 sitios · whitelabel',
                'features' => ['4,000 mensajes IA / mes', '3 sitios', 'Whitelabel (sin marca)', 'Routing OpenRouter', 'Memoria para leads'],
                'cta' => 'Empezar Growth',
            ],
            'managed_agency' => [
                'tagline'  => '12,000 mensajes · 10 sitios',
                'features' => ['12,000 mensajes IA / mes', '10 sitios', 'Whitelabel', 'Soporte prioritario', 'Para agencias'],
                'cta' => 'Empezar Business',
            ],
        ],
        'fr' => [
            'free' => [
                'tagline'  => 'Essai avec filigrane',
                'features' => ['Widget IA sur 1 site', 'Filigrane "Powered by ChatLM"', 'Essai 14 jours', 'Notifications Telegram', 'Multi-fournisseurs IA (votre clé API)'],
                'cta' => 'Démarrer Gratuit',
            ],
            'byok_starter' => [
                'tagline'  => 'Votre clé API · 1 site · sans filigrane',
                'features' => ['1 site', 'Sans filigrane', 'Votre OpenAI / Gemini / DeepSeek', 'Sans markup tokens', 'Alertes Telegram'],
                'cta' => 'Démarrer Starter',
            ],
            'byok_pro' => [
                'tagline'  => '5 sites · agences et business',
                'features' => ['5 sites', 'Widget whitelabel', 'CSS et branding custom', 'Historique et mémoire IA', 'Support email'],
                'cta' => 'Démarrer Pro',
            ],
            'byok_agency' => [
                'tagline'  => '25 sites · multi-clients',
                'features' => ['25 sites', 'Workflows multi-clients', 'Support prioritaire', 'Origins avancés', 'Sans markup'],
                'cta' => 'Démarrer Agency',
            ],
            'managed_starter' => [
                'tagline'  => '3 000 messages IA/mois · plug & play',
                'features' => ['3 000 messages IA / mois', '1 site · plug & play', 'Modèles GPT-4o-mini', 'Sans clé API', 'Alertes Telegram'],
                'cta' => 'Démarrer Starter',
            ],
            'managed_pro' => [
                'tagline'  => '4 000 messages · 3 sites · whitelabel',
                'features' => ['4 000 messages IA / mois', '3 sites', 'Whitelabel (sans filigrane)', 'Routing OpenRouter', 'Mémoire pour leads'],
                'cta' => 'Démarrer Growth',
            ],
            'managed_agency' => [
                'tagline'  => '12 000 messages · 10 sites',
                'features' => ['12 000 messages IA / mois', '10 sites', 'Whitelabel', 'Support prioritaire', 'Pour agences'],
                'cta' => 'Démarrer Business',
            ],
        ],
        'pt' => [
            'free' => [
                'tagline'  => 'Teste o widget com marca d\'água',
                'features' => ['Widget IA em 1 site', 'Marca "Powered by ChatLM"', 'Trial de 14 dias', 'Notificações Telegram', 'Multi-provedor IA (sua API key)'],
                'cta' => 'Começar Grátis',
            ],
            'byok_starter' => [
                'tagline'  => 'Sua API key · 1 site · sem marca',
                'features' => ['1 site', 'Sem marca d\'água', 'Seu OpenAI / Gemini / DeepSeek', 'Sem markup de tokens', 'Alertas Telegram'],
                'cta' => 'Começar Starter',
            ],
            'byok_pro' => [
                'tagline'  => '5 sites · agências e negócios',
                'features' => ['5 sites', 'Widget whitelabel', 'CSS e branding custom', 'Histórico e memória IA', 'Suporte email'],
                'cta' => 'Começar Pro',
            ],
            'byok_agency' => [
                'tagline'  => '25 sites · multi-cliente',
                'features' => ['25 sites', 'Workflows multi-cliente', 'Suporte prioritário', 'Origins avançados', 'Sem markup'],
                'cta' => 'Começar Agency',
            ],
            'managed_starter' => [
                'tagline'  => '3.000 mensagens IA/mês · plug & play',
                'features' => ['3.000 mensagens IA / mês', '1 site · plug & play', 'Modelos GPT-4o-mini', 'Sem API key', 'Alertas Telegram'],
                'cta' => 'Começar Starter',
            ],
            'managed_pro' => [
                'tagline'  => '4.000 mensagens · 3 sites · whitelabel',
                'features' => ['4.000 mensagens IA / mês', '3 sites', 'Whitelabel (sem marca)', 'Routing OpenRouter', 'Memória para leads'],
                'cta' => 'Começar Growth',
            ],
            'managed_agency' => [
                'tagline'  => '12.000 mensagens · 10 sites',
                'features' => ['12.000 mensagens IA / mês', '10 sites', 'Whitelabel', 'Suporte prioritário', 'Para agências'],
                'cta' => 'Começar Business',
            ],
        ],
        'ja' => [
            'free' => [
                'tagline'  => '透かし付きでお試し',
                'features' => ['1サイトにAIウィジェット', '「Powered by ChatLM」透かし', '14日間フルトライアル', 'Telegram通知', 'マルチAIプロバイダ（APIキー持込）'],
                'cta' => '無料で開始',
            ],
            'byok_starter' => [
                'tagline'  => 'あなたのAPIキー · 1サイト · 透かしなし',
                'features' => ['1サイト', '透かしなし', 'OpenAI / Gemini / DeepSeekキー', 'トークンマークアップなし', 'Telegram通知'],
                'cta' => 'Starterを開始',
            ],
            'byok_pro' => [
                'tagline'  => '5サイト · 代理店とビジネス',
                'features' => ['5サイト', 'ホワイトラベルウィジェット', 'カスタムCSS＆ブランディング', 'チャット履歴とAIメモリ', 'メールサポート'],
                'cta' => 'Proを開始',
            ],
            'byok_agency' => [
                'tagline'  => '25サイト · マルチクライアント',
                'features' => ['25サイト', 'マルチクライアントワークフロー', '優先サポート', '高度なオリジン設定', 'マークアップなし'],
                'cta' => 'Agencyを開始',
            ],
            'managed_starter' => [
                'tagline'  => '3,000 AIメッセージ/月 · プラグ＆プレイ',
                'features' => ['3,000 AIメッセージ / 月', '1サイト · 設定不要', 'GPT-4o-miniクラス', 'APIキー不要', 'Telegram通知'],
                'cta' => 'Starterを開始',
            ],
            'managed_pro' => [
                'tagline'  => '4,000メッセージ · 3サイト · ホワイトラベル',
                'features' => ['4,000 AIメッセージ / 月', '3サイト', 'ホワイトラベル（透かしなし）', 'OpenRouterルーティング', 'リード向けチャットメモリ'],
                'cta' => 'Growthを開始',
            ],
            'managed_agency' => [
                'tagline'  => '12,000メッセージ · 10サイト',
                'features' => ['12,000 AIメッセージ / 月', '10サイト', 'ホワイトラベル', '優先サポート', '代理店向け'],
                'cta' => 'Businessを開始',
            ],
        ],
    ];

    return $all[$lang] ?? $all['en'];
}
