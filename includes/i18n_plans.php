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
                'cta' => 'Use Free',
            ],
            'byok_starter' => [
                'tagline'  => 'Your AI keys · 1 website · no watermark',
                'features' => ['1 website', 'Zero token markup', 'Whitelabel widget', 'Telegram alerts', 'You control model costs'],
                'cta' => 'Start BYOK Starter',
            ],
            'byok_pro' => [
                'tagline'  => 'Agencies & growing sites · 5 websites',
                'features' => ['5 websites', 'Whitelabel + custom branding', 'Chat memory', 'Email support', 'BYOK — no markup'],
                'cta' => 'Start BYOK Pro',
            ],
            'byok_agency' => [
                'tagline'  => '25 websites · client work',
                'features' => ['25 websites', 'Priority support', 'Multi-brand setup', 'Advanced origins', 'Platform fee only'],
                'cta' => 'Start BYOK Agency',
            ],
            'managed_starter' => [
                'tagline'  => '3,000 AI messages/mo · plug & play',
                'features' => ['3,000 messages included', '1 website', 'No API key setup', 'GPT-4o-mini class models', 'ChatLM branding'],
                'cta' => 'Start Managed',
            ],
            'managed_pro' => [
                'tagline'  => '4,000 messages · 3 sites · whitelabel',
                'features' => ['4,000 messages/mo', '3 websites', 'No watermark', 'OpenRouter routing', 'Lead-ready chat'],
                'cta' => 'Start Growth',
            ],
            'managed_agency' => [
                'tagline'  => '12,000 messages · 10 websites',
                'features' => ['12,000 messages/mo', '10 websites', 'Whitelabel', 'Priority support', 'Best for agencies'],
                'cta' => 'Start Business',
            ],
            'starter_monthly' => [
                'tagline'  => 'For small businesses & solo founders',
                'features' => [
                    'No watermark',
                    '1 website / widget',
                    'Chat history & AI memory',
                    'Telegram notifications',
                    'Standard email support',
                ],
                'cta' => 'Start Starter',
            ],
            'pro_monthly' => [
                'tagline'  => 'Small teams & agencies',
                'features' => [
                    'Everything in Starter',
                    'Priority support',
                    'Full branding (no watermark)',
                    'Great for multiple brands',
                    'Global pricing in USD',
                ],
                'cta' => 'Upgrade to Pro',
            ],
            'starter_yearly' => [
                'tagline'  => 'Pay yearly — save ~2 months',
                'features' => [
                    'All Starter monthly features',
                    'Single yearly invoice',
                    'No watermark',
                    'Cheaper than paying monthly',
                ],
                'cta' => 'Starter Yearly',
            ],
            'pro_yearly' => [
                'tagline'  => 'Best for long-term retention',
                'features' => [
                    'All Pro monthly features',
                    'Yearly billing',
                    'No watermark',
                    'Priority support',
                ],
                'cta' => 'Pro Yearly',
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
                'cta' => 'Pakai Gratis',
            ],
            'byok_starter' => [
                'tagline'  => 'API key Anda · 1 website · tanpa watermark',
                'features' => ['1 website', 'Tanpa markup token', 'Widget whitelabel', 'Notifikasi Telegram', 'Biaya model ke provider'],
                'cta' => 'Mulai BYOK Starter',
            ],
            'byok_pro' => [
                'tagline'  => '5 website · agensi & bisnis',
                'features' => ['5 website', 'Whitelabel', 'Memori chat', 'Dukungan email', 'BYOK tanpa markup'],
                'cta' => 'Mulai BYOK Pro',
            ],
            'byok_agency' => [
                'tagline'  => '25 website · klien agency',
                'features' => ['25 website', 'Prioritas dukungan', 'Multi-brand', 'Origins lanjutan', 'Hanya biaya platform'],
                'cta' => 'Mulai BYOK Agency',
            ],
            'managed_starter' => [
                'tagline'  => '3.000 pesan AI/bulan · langsung pakai',
                'features' => ['3.000 pesan termasuk', '1 website', 'Tanpa setup API key', 'Model kelas GPT-4o-mini', 'Branding ChatLM'],
                'cta' => 'Mulai Managed',
            ],
            'managed_pro' => [
                'tagline'  => '4.000 pesan · 3 situs · whitelabel',
                'features' => ['4.000 pesan/bulan', '3 website', 'Tanpa watermark', 'Routing OpenRouter', 'Siap lead gen'],
                'cta' => 'Mulai Growth',
            ],
            'managed_agency' => [
                'tagline'  => '12.000 pesan · 10 website',
                'features' => ['12.000 pesan/bulan', '10 website', 'Whitelabel', 'Prioritas dukungan', 'Untuk agency'],
                'cta' => 'Mulai Business',
            ],
            'starter_monthly' => [
                'tagline'  => 'Untuk bisnis kecil & solo founder',
                'features' => [
                    'Tanpa watermark',
                    '1 website / widget',
                    'Riwayat chat & memori AI',
                    'Notifikasi Telegram',
                    'Dukungan email standar',
                ],
                'cta' => 'Mulai Starter',
            ],
            'pro_monthly' => [
                'tagline'  => 'Tim kecil & agensi',
                'features' => [
                    'Semua fitur Starter',
                    'Prioritas dukungan',
                    'Branding penuh (tanpa watermark)',
                    'Cocok untuk multi-brand',
                    'Harga global (USD)',
                ],
                'cta' => 'Upgrade ke Pro',
            ],
            'starter_yearly' => [
                'tagline'  => 'Bayar tahunan — hemat ~2 bulan',
                'features' => [
                    'Semua fitur Starter bulanan',
                    'Tagihan tahunan sekali',
                    'Tanpa watermark',
                    'Hemat dibanding bulanan',
                ],
                'cta' => 'Starter Tahunan',
            ],
            'pro_yearly' => [
                'tagline'  => 'Terbaik untuk retensi jangka panjang',
                'features' => [
                    'Semua fitur Pro bulanan',
                    'Tagihan tahunan',
                    'Tanpa watermark',
                    'Prioritas dukungan',
                ],
                'cta' => 'Pro Tahunan',
            ],
        ],
        'es' => [
            'free' => [
                'tagline'  => 'Prueba el widget con marca de agua',
                'features' => [
                    'Widget de chat IA en 1 sitio',
                    'Marca "Powered by ChatLM"',
                    'Prueba completa de 14 días',
                    'Notificaciones Telegram',
                    'Multi-proveedor IA (tu API key)',
                ],
                'cta' => 'Usar Gratis',
            ],
            'starter_monthly' => [
                'tagline'  => 'Para pequeños negocios y fundadores',
                'features' => [
                    'Sin marca de agua',
                    '1 sitio / widget',
                    'Historial y memoria IA',
                    'Notificaciones Telegram',
                    'Soporte email estándar',
                ],
                'cta' => 'Empezar Starter',
            ],
            'pro_monthly' => [
                'tagline'  => 'Equipos pequeños y agencias',
                'features' => [
                    'Todo lo de Starter',
                    'Soporte prioritario',
                    'Branding completo',
                    'Varias marcas',
                    'Precios en USD',
                ],
                'cta' => 'Subir a Pro',
            ],
            'starter_yearly' => [
                'tagline'  => 'Pago anual — ahorra ~2 meses',
                'features' => [
                    'Funciones Starter mensual',
                    'Factura anual única',
                    'Sin marca de agua',
                    'Más barato que mensual',
                ],
                'cta' => 'Starter Anual',
            ],
            'pro_yearly' => [
                'tagline'  => 'Ideal a largo plazo',
                'features' => [
                    'Funciones Pro mensual',
                    'Facturación anual',
                    'Sin marca de agua',
                    'Soporte prioritario',
                ],
                'cta' => 'Pro Anual',
            ],
        ],
        'fr' => [
            'free' => [
                'tagline'  => 'Essai avec filigrane',
                'features' => [
                    'Widget chat IA sur 1 site',
                    'Filigrane "Powered by ChatLM"',
                    'Essai 14 jours complet',
                    'Notifications Telegram',
                    'Multi-fournisseurs IA (votre clé API)',
                ],
                'cta' => 'Utiliser Gratuit',
            ],
            'starter_monthly' => [
                'tagline'  => 'PME et entrepreneurs solo',
                'features' => [
                    'Sans filigrane',
                    '1 site / widget',
                    'Historique & mémoire IA',
                    'Notifications Telegram',
                    'Support email standard',
                ],
                'cta' => 'Démarrer Starter',
            ],
            'pro_monthly' => [
                'tagline'  => 'Petites équipes & agences',
                'features' => [
                    'Tout Starter inclus',
                    'Support prioritaire',
                    'Branding complet',
                    'Multi-marques',
                    'Tarifs en USD',
                ],
                'cta' => 'Passer à Pro',
            ],
            'starter_yearly' => [
                'tagline'  => 'Paiement annuel — ~2 mois offerts',
                'features' => [
                    'Fonctions Starter mensuel',
                    'Facture annuelle unique',
                    'Sans filigrane',
                    'Moins cher que mensuel',
                ],
                'cta' => 'Starter Annuel',
            ],
            'pro_yearly' => [
                'tagline'  => 'Meilleur pour la fidélisation',
                'features' => [
                    'Fonctions Pro mensuel',
                    'Facturation annuelle',
                    'Sans filigrane',
                    'Support prioritaire',
                ],
                'cta' => 'Pro Annuel',
            ],
        ],
        'pt' => [
            'free' => [
                'tagline'  => 'Teste o widget com marca d\'água',
                'features' => [
                    'Widget de chat IA em 1 site',
                    'Marca "Powered by ChatLM"',
                    'Trial completo de 14 dias',
                    'Notificações Telegram',
                    'Multi-provedor IA (sua API key)',
                ],
                'cta' => 'Usar Grátis',
            ],
            'starter_monthly' => [
                'tagline'  => 'Pequenos negócios e fundadores',
                'features' => [
                    'Sem marca d\'água',
                    '1 site / widget',
                    'Histórico e memória IA',
                    'Notificações Telegram',
                    'Suporte email padrão',
                ],
                'cta' => 'Começar Starter',
            ],
            'pro_monthly' => [
                'tagline'  => 'Equipes pequenas e agências',
                'features' => [
                    'Tudo do Starter',
                    'Suporte prioritário',
                    'Branding completo',
                    'Várias marcas',
                    'Preços em USD',
                ],
                'cta' => 'Upgrade Pro',
            ],
            'starter_yearly' => [
                'tagline'  => 'Pagamento anual — economize ~2 meses',
                'features' => [
                    'Recursos Starter mensal',
                    'Fatura anual única',
                    'Sem marca d\'água',
                    'Mais barato que mensal',
                ],
                'cta' => 'Starter Anual',
            ],
            'pro_yearly' => [
                'tagline'  => 'Melhor para longo prazo',
                'features' => [
                    'Recursos Pro mensal',
                    'Cobrança anual',
                    'Sem marca d\'água',
                    'Suporte prioritário',
                ],
                'cta' => 'Pro Anual',
            ],
        ],
        'ja' => [
            'free' => [
                'tagline'  => '透かし付きでお試し',
                'features' => [
                    '1サイトにAIチャットウィジェット',
                    '「Powered by ChatLM」透かし',
                    '14日間フルトライアル',
                    'Telegram通知',
                    '複数AIプロバイダ（APIキー持込）',
                ],
                'cta' => '無料で使う',
            ],
            'starter_monthly' => [
                'tagline'  => '小規模ビジネス・個人向け',
                'features' => [
                    '透かしなし',
                    '1サイト / ウィジェット',
                    'チャット履歴とAIメモリ',
                    'Telegram通知',
                    '標準メールサポート',
                ],
                'cta' => 'Starterを始める',
            ],
            'pro_monthly' => [
                'tagline'  => '小規模チーム・代理店向け',
                'features' => [
                    'Starterの全機能',
                    '優先サポート',
                    'フルブランディング',
                    '複数ブランド向け',
                    'USD料金',
                ],
                'cta' => 'Proにアップグレード',
            ],
            'starter_yearly' => [
                'tagline'  => '年払いで約2ヶ月お得',
                'features' => [
                    'Starter月額の全機能',
                    '年1回の請求',
                    '透かしなし',
                    '月額よりお得',
                ],
                'cta' => 'Starter年額',
            ],
            'pro_yearly' => [
                'tagline'  => '長期利用に最適',
                'features' => [
                    'Pro月額の全機能',
                    '年額請求',
                    '透かしなし',
                    '優先サポート',
                ],
                'cta' => 'Pro年額',
            ],
        ],
    ];

    return $all[$lang] ?? $all['en'];
}
