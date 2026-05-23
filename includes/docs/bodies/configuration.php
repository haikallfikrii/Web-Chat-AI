<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

function docs_body_ai_providers(string $lang): string
{
    return docs_pick([
        'en' => <<<'HTML'
<p>ChatLM uses <strong>your</strong> AI provider keys (BYOK). You pay the model provider directly; ChatLM charges only for the widget platform plan.</p>
<h2>Supported providers</h2>
<ul>
<li><strong>OpenAI</strong> — platform.openai.com API keys</li>
<li><strong>Google Gemini</strong> — Google AI Studio key</li>
<li><strong>DeepSeek</strong> — DeepSeek API</li>
<li><strong>OpenRouter</strong> — one key for many models (recommended for testing)</li>
</ul>
<h2>Step-by-step</h2>
<ol>
<li>Create an API key at your provider.</li>
<li>Dashboard → <strong>AI Configuration</strong> → select provider.</li>
<li>Paste the key and click <strong>Save all settings</strong>.</li>
<li>Enter a model ID, e.g. <code>openai/gpt-4o-mini</code> (OpenRouter) or <code>gpt-4o-mini</code> (OpenAI).</li>
<li>Set temperature (0.3 = focused, 0.8 = creative).</li>
<li>Write a system prompt: who the bot is, what it may answer, and when to suggest human contact.</li>
</ol>
<h2>Example system prompt</h2>
<pre><code>You are the support assistant for Acme Shop.
Answer in the same language as the user.
Keep replies under 120 words.
For refunds, ask for order ID and email support@acme.com.</code></pre>
<p><a href="/docs/allowed-domains">Domain security</a> · <a href="/pricing.php">Plans</a></p>
HTML,
        'id' => <<<'HTML'
<p>ChatLM memakai <strong>API key Anda sendiri</strong> (BYOK). Biaya model dibayar ke provider; ChatLM hanya menagih paket platform widget.</p>
<h2>Provider yang didukung</h2>
<ul>
<li><strong>OpenAI</strong></li>
<li><strong>Google Gemini</strong></li>
<li><strong>DeepSeek</strong></li>
<li><strong>OpenRouter</strong> (disarankan untuk uji coba)</li>
</ul>
<h2>Langkah demi langkah</h2>
<ol>
<li>Buat API key di provider.</li>
<li>Dashboard → <strong>AI Configuration</strong> → pilih provider.</li>
<li>Tempel key → <strong>Save all settings</strong>.</li>
<li>Isi model ID, mis. <code>openai/gpt-4o-mini</code> (OpenRouter).</li>
<li>Atur temperature dan system prompt.</li>
</ol>
<h2>Contoh system prompt</h2>
<pre><code>Anda asisten dukungan Toko Acme.
Jawab dalam bahasa yang dipakai pengunjung.
Maksimal 120 kata per balasan.
Untuk refund, minta nomor pesanan dan arahkan ke email support.</code></pre>
<p><a href="/docs/allowed-domains">Keamanan domain</a> · <a href="/pricing.php">Paket</a></p>
HTML,
        'es' => <<<'HTML'
<p>Conecte OpenAI, Gemini, DeepSeek u OpenRouter con su clave API en el panel. Elija modelo y prompt del sistema. Guarde los cambios.</p>
<p><a href="/pricing.php">Precios</a></p>
HTML,
        'fr' => <<<'HTML'
<p>Connectez OpenAI, Gemini, DeepSeek ou OpenRouter avec votre clé API. Choisissez le modèle et le prompt système.</p>
<p><a href="/pricing.php">Tarifs</a></p>
HTML,
        'pt' => <<<'HTML'
<p>Conecte OpenAI, Gemini, DeepSeek ou OpenRouter com sua chave API. Escolha modelo e prompt do sistema.</p>
<p><a href="/pricing.php">Preços</a></p>
HTML,
        'ja' => <<<'HTML'
<p>OpenAI、Gemini、DeepSeek、OpenRouterのAPIキーをダッシュボードに設定。モデルとシステムプロンプトを保存。</p>
<p><a href="/pricing.php">料金</a></p>
HTML,
    ], $lang);
}

function docs_body_allowed_domains(string $lang): string
{
    return docs_pick([
        'en' => <<<'HTML'
<p><strong>Allowed origins</strong> protect your widget API key. Only listed domains can call ChatLM APIs from the browser.</p>
<h2>How to add a domain</h2>
<ol>
<li>Dashboard → <strong>Domain security</strong>.</li>
<li>Enter one origin per line, full URL with protocol, e.g. <code>https://shop.example.com</code></li>
<li>Include staging if you test there: <code>https://staging.example.com</code></li>
<li>Save settings.</li>
</ol>
<h2>www vs non-www</h2>
<p>Browsers treat <code>https://example.com</code> and <code>https://www.example.com</code> as different origins. Add both if you use both.</p>
<h2>Wildcard *</h2>
<p><code>*</code> allows any domain — convenient for development, risky in production. Remove before launch.</p>
<h2>Local development</h2>
<p>Add <code>http://localhost:8080</code> or your local Vite URL if you test locally.</p>
<p><a href="/docs/embed-widget">Embed guide</a></p>
HTML,
        'id' => <<<'HTML'
<p><strong>Allowed origins</strong> melindungi API key widget. Hanya domain terdaftar yang boleh memanggil API ChatLM dari browser.</p>
<h2>Cara menambah domain</h2>
<ol>
<li>Dashboard → <strong>Domain security</strong>.</li>
<li>Satu origin per baris, URL lengkap: <code>https://tokoanda.com</code></li>
<li>Tambahkan staging jika perlu.</li>
<li>Simpan.</li>
</ol>
<h2>www vs non-www</h2>
<p>Keduanya dianggap origin berbeda — tambahkan keduanya jika dipakai.</p>
<h2>Wildcard *</h2>
<p>Hanya untuk development; hapus sebelum production.</p>
<p><a href="/docs/embed-widget">Panduan embed</a></p>
HTML,
        'es' => <<<'HTML'
<p>Liste cada dominio con <code>https://</code>. Evite <code>*</code> en producción.</p>
HTML,
        'fr' => <<<'HTML'
<p>Listez chaque domaine avec <code>https://</code>. Évitez <code>*</code> en production.</p>
HTML,
        'pt' => <<<'HTML'
<p>Liste cada domínio com <code>https://</code>. Evite <code>*</code> em produção.</p>
HTML,
        'ja' => <<<'HTML'
<p>各ドメインを <code>https://</code> 付きで登録。本番では <code>*</code> を避けてください。</p>
HTML,
    ], $lang);
}

function docs_body_telegram(string $lang): string
{
    return docs_pick([
        'en' => <<<'HTML'
<p>Get a Telegram message whenever a visitor chats on your widget.</p>
<h2>Step 1 — Create a bot</h2>
<ol>
<li>Open Telegram and message <strong>@BotFather</strong>.</li>
<li>Send <code>/newbot</code> and follow the name instructions.</li>
<li>Copy the <strong>HTTP API token</strong> (looks like <code>123456:ABC-DEF...</code>).</li>
</ol>
<h2>Step 2 — Server config (hosting)</h2>
<p>On your ChatLM server, edit <code>config.local.php</code> and set:</p>
<pre><code>define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');</code></pre>
<p>Do <strong>not</strong> paste the bot token in the dashboard “Chat ID” field.</p>
<h2>Step 3 — Get your Chat ID</h2>
<ol>
<li>Start a chat with your new bot (tap Start).</li>
<li>Send any message.</li>
<li>Visit <code>https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code> and find <code>"chat":{"id":</code> — a number, often negative for groups.</li>
<li>Paste that number in dashboard → <strong>Telegram Chat ID</strong>.</li>
</ol>
<h2>Step 4 — Save &amp; test</h2>
<p>Save dashboard settings and send a test message on your website.</p>
<p><a href="/docs/troubleshooting">Troubleshooting</a></p>
HTML,
        'id' => <<<'HTML'
<p>Terima pesan Telegram setiap pengunjung chat di widget Anda.</p>
<h2>Langkah 1 — Buat bot</h2>
<ol>
<li>Buka Telegram, chat <strong>@BotFather</strong>.</li>
<li>Kirim <code>/newbot</code>, ikuti petunjuk nama.</li>
<li>Salin <strong>token API</strong> (format <code>123456:ABC...</code>).</li>
</ol>
<h2>Langkah 2 — Config server</h2>
<p>Di hosting, edit <code>config.local.php</code>:</p>
<pre><code>define('TELEGRAM_BOT_TOKEN', 'TOKEN_BOT_ANDA');</code></pre>
<p>Jangan tempel token bot di kolom Chat ID dashboard.</p>
<h2>Langkah 3 — Chat ID</h2>
<ol>
<li>Start chat dengan bot Anda.</li>
<li>Kirim pesan apa saja.</li>
<li>Buka <code>getUpdates</code> di API Telegram, cari angka <code>chat.id</code>.</li>
<li>Tempel angka itu di dashboard → <strong>Telegram Chat ID</strong>.</li>
</ol>
<h2>Langkah 4 — Simpan &amp; uji</h2>
<p>Simpan pengaturan dan kirim pesan uji dari website.</p>
HTML,
        'es' => <<<'HTML'
<p>Token del bot en <code>config.local.php</code>. ID de chat numérico en el panel. No confunda token con ID.</p>
HTML,
        'fr' => <<<'HTML'
<p>Jeton du bot dans <code>config.local.php</code>. ID de chat numérique dans le tableau de bord.</p>
HTML,
        'pt' => <<<'HTML'
<p>Token do bot em <code>config.local.php</code>. Chat ID numérico no painel.</p>
HTML,
        'ja' => <<<'HTML'
<p>ボットトークンは <code>config.local.php</code>。Chat IDは数値のみをダッシュボードに入力。</p>
HTML,
    ], $lang);
}

function docs_body_billing_plans(string $lang): string
{
    return docs_pick([
        'en' => <<<'HTML'
<p>ChatLM offers a free trial tier and paid plans in <strong>USD</strong> via Stripe.</p>
<h2>Plans overview</h2>
<ul>
<li><strong>Free</strong> — full features with a small “Powered by ChatLM” watermark. Great for testing.</li>
<li><strong>Starter</strong> — removes watermark, 1 site, chat history, Telegram alerts.</li>
<li><strong>Pro</strong> — everything in Starter plus priority support; ideal for agencies.</li>
</ul>
<h2>Monthly vs yearly</h2>
<p>Yearly billing charges once per year and saves roughly two months compared to monthly.</p>
<h2>How to upgrade</h2>
<ol>
<li>Log in and open <a href="/pricing.php">Pricing</a>.</li>
<li>Choose Monthly or Yearly tab.</li>
<li>Click your plan — Stripe Checkout opens securely.</li>
<li>After payment, watermark disappears automatically on Starter/Pro.</li>
</ol>
<h2>Manage subscription</h2>
<p>Dashboard → <strong>Billing</strong> or the subscription badge in the header. Cancel anytime; access continues until period end.</p>
<p>AI usage is billed by your provider (OpenAI, etc.) separately from ChatLM.</p>
HTML,
        'id' => <<<'HTML'
<p>ChatLM punya tier gratis dan paket berbayar dalam <strong>USD</strong> via Stripe.</p>
<h2>Ringkasan paket</h2>
<ul>
<li><strong>Free</strong> — fitur lengkap dengan watermark “Powered by ChatLM”.</li>
<li><strong>Starter</strong> — tanpa watermark, 1 situs, riwayat chat, Telegram.</li>
<li><strong>Pro</strong> — semua Starter + dukungan prioritas; cocok untuk agensi.</li>
</ul>
<h2>Bulanan vs tahunan</h2>
<p>Tahunan ditagih sekali per tahun, hemat sekitar dua bulan dibanding bulanan.</p>
<h2>Cara upgrade</h2>
<ol>
<li>Masuk → <a href="/pricing.php">Harga</a>.</li>
<li>Pilih tab Bulanan atau Tahunan.</li>
<li>Klik paket — Checkout Stripe terbuka.</li>
<li>Setelah bayar, watermark hilang di Starter/Pro.</li>
</ol>
<h2>Kelola langganan</h2>
<p>Dashboard → <strong>Billing</strong>. Batalkan kapan saja; akses tetap sampai akhir periode.</p>
<p>Biaya pemakaian AI ditagih terpisah oleh provider (OpenAI, dll.).</p>
HTML,
        'es' => <<<'HTML'
<p>Free con marca de agua; Starter y Pro sin marca. Pago con Stripe en USD. <a href="/pricing.php">Ver precios</a>.</p>
HTML,
        'fr' => <<<'HTML'
<p>Gratuit avec filigrane ; Starter et Pro sans filigrane. Paiement Stripe en USD. <a href="/pricing.php">Tarifs</a>.</p>
HTML,
        'pt' => <<<'HTML'
<p>Grátis com marca d'água; Starter e Pro sem marca. Pagamento Stripe em USD. <a href="/pricing.php">Preços</a>.</p>
HTML,
        'ja' => <<<'HTML'
<p>無料は透かし付き。Starter/Proは透かしなし。StripeでUSD決済。<a href="/pricing.php">料金</a></p>
HTML,
    ], $lang);
}
