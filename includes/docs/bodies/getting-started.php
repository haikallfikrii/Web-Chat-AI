<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

function docs_body_quick_start(string $lang): string
{
    return docs_pick([
        'en' => <<<'HTML'
<p>This guide walks you through going from zero to a live <strong>AI chat widget</strong> on your website in about ten minutes.</p>
<h2>Before you start</h2>
<ul>
<li>An email address for your ChatLM account</li>
<li>An API key from <a href="https://openrouter.ai/">OpenRouter</a>, OpenAI, Google Gemini, or DeepSeek</li>
<li>Ability to edit your website HTML (or WordPress footer)</li>
</ul>
<h2>Step 1 — Create your account</h2>
<ol>
<li>Go to <a href="/register.php">Sign up free</a> (no credit card).</li>
<li>Enter your name, business name, email, and password (min. 8 characters).</li>
<li>After registration you land on the <strong>Dashboard</strong>.</li>
</ol>
<h2>Step 2 — Configure appearance &amp; AI</h2>
<ol>
<li>Open <strong>Appearance</strong>: set bot name, welcome message, and brand color.</li>
<li>Open <strong>AI Configuration</strong>: choose provider, paste your API key, pick a model (e.g. <code>openai/gpt-4o-mini</code> on OpenRouter).</li>
<li>Write a short <strong>system prompt</strong> describing your business and tone.</li>
</ol>
<h2>Step 3 — Set allowed origins</h2>
<p>Under <strong>Domain security</strong>, add your live URL with <code>https://</code>, for example <code>https://yourdomain.com</code>. Add both <code>www</code> and non-<code>www</code> if needed. For local testing only, you may temporarily use <code>*</code>.</p>
<h2>Step 4 — Copy embed code</h2>
<p>In the sidebar, copy the script tag and paste it just before <code>&lt;/body&gt;</code> on every page where you want the chat bubble.</p>
<pre><code>&lt;script src="https://chatlm.tech/widget/widget.js"
  data-api-key="YOUR_WIDGET_KEY"
  data-base-url="https://chatlm.tech"
  defer&gt;&lt;/script&gt;</code></pre>
<h2>Step 5 — Test</h2>
<ol>
<li>Open your site in a <strong>private/incognito</strong> window (not logged in as WordPress admin).</li>
<li>Click the chat bubble bottom-right and send a test message.</li>
<li>Optional: enable <a href="/docs/telegram">Telegram notifications</a> for new visitor messages.</li>
</ol>
<p>Next: <a href="/docs/embed-widget">Embed widget guide</a> · <a href="/docs/ai-providers">AI providers</a> · <a href="/pricing.php">Compare plans</a></p>
HTML,
        'id' => <<<'HTML'
<p>Panduan ini membawa Anda dari nol hingga <strong>widget chat AI</strong> aktif di website dalam sekitar sepuluh menit.</p>
<h2>Yang perlu disiapkan</h2>
<ul>
<li>Email untuk akun ChatLM</li>
<li>API key dari <a href="https://openrouter.ai/">OpenRouter</a>, OpenAI, Google Gemini, atau DeepSeek</li>
<li>Akses edit HTML website (atau footer WordPress)</li>
</ul>
<h2>Langkah 1 — Buat akun</h2>
<ol>
<li>Buka <a href="/register.php">Daftar gratis</a> (tanpa kartu kredit).</li>
<li>Isi nama, nama bisnis, email, dan password (min. 8 karakter).</li>
<li>Setelah daftar Anda masuk ke <strong>Dashboard</strong>.</li>
</ol>
<h2>Langkah 2 — Atur tampilan &amp; AI</h2>
<ol>
<li><strong>Appearance</strong>: nama bot, pesan sambutan, warna brand.</li>
<li><strong>AI Configuration</strong>: pilih provider, tempel API key, pilih model (mis. <code>openai/gpt-4o-mini</code> di OpenRouter).</li>
<li>Tulis <strong>system prompt</strong> singkat tentang bisnis dan gaya jawaban.</li>
</ol>
<h2>Langkah 3 — Allowed origins</h2>
<p>Di <strong>Domain security</strong>, tambahkan URL live dengan <code>https://</code>, mis. <code>https://domainanda.com</code>. Tambahkan <code>www</code> dan non-<code>www</code> jika keduanya dipakai. Untuk uji lokal sementara bisa <code>*</code>.</p>
<h2>Langkah 4 — Salin kode embed</h2>
<p>Di sidebar, salin script tag dan tempel tepat sebelum <code>&lt;/body&gt;</code> di setiap halaman yang ingin menampilkan chat.</p>
<pre><code>&lt;script src="https://chatlm.tech/widget/widget.js"
  data-api-key="KUNCI_WIDGET_ANDA"
  data-base-url="https://chatlm.tech"
  defer&gt;&lt;/script&gt;</code></pre>
<h2>Langkah 5 — Uji</h2>
<ol>
<li>Buka situs di jendela <strong>incognito</strong> (bukan login admin WordPress).</li>
<li>Klik gelembung chat kanan bawah dan kirim pesan uji.</li>
<li>Opsional: aktifkan <a href="/docs/telegram">notifikasi Telegram</a>.</li>
</ol>
<p>Lanjut: <a href="/docs/embed-widget">Panduan embed</a> · <a href="/docs/ai-providers">Provider AI</a> · <a href="/pricing.php">Lihat paket</a></p>
HTML,
        'es' => <<<'HTML'
<p>Guía para publicar un <strong>widget de chat IA</strong> en unos diez minutos.</p>
<h2>Paso 1 — Cuenta</h2>
<ol><li><a href="/register.php">Regístrate gratis</a>.</li><li>Accede al <strong>Panel</strong>.</li></ol>
<h2>Paso 2 — Apariencia e IA</h2>
<ol><li><strong>Apariencia</strong>: nombre del bot y color.</li><li><strong>IA</strong>: proveedor, clave API y modelo.</li></ol>
<h2>Paso 3 — Orígenes permitidos</h2>
<p>Añade <code>https://tudominio.com</code> en seguridad de dominio.</p>
<h2>Paso 4 — Código embed</h2>
<p>Pega el script antes de <code>&lt;/body&gt;</code>.</p>
<h2>Paso 5 — Prueba</h2>
<p>Abre el sitio en incógnito y envía un mensaje de prueba.</p>
<p><a href="/docs/embed-widget">Insertar widget</a> · <a href="/pricing.php">Precios</a></p>
HTML,
        'fr' => <<<'HTML'
<p>Guide pour mettre en ligne un <strong>widget chat IA</strong> en environ dix minutes.</p>
<h2>Étape 1 — Compte</h2>
<ol><li><a href="/register.php">Inscription gratuite</a>.</li><li>Ouvrez le <strong>Tableau de bord</strong>.</li></ol>
<h2>Étape 2 — Apparence et IA</h2>
<ol><li>Configurez le bot et la couleur.</li><li>Ajoutez la clé API et le modèle.</li></ol>
<h2>Étape 3 — Origines autorisées</h2>
<p>Ajoutez <code>https://votredomaine.com</code>.</p>
<h2>Étape 4 — Code d'intégration</h2>
<p>Collez le script avant <code>&lt;/body&gt;</code>.</p>
<h2>Étape 5 — Test</h2>
<p>Testez en navigation privée.</p>
<p><a href="/docs/embed-widget">Intégration</a> · <a href="/pricing.php">Tarifs</a></p>
HTML,
        'pt' => <<<'HTML'
<p>Guia para colocar um <strong>widget de chat IA</strong> no ar em cerca de dez minutos.</p>
<h2>Passo 1 — Conta</h2>
<ol><li><a href="/register.php">Cadastre-se grátis</a>.</li><li>Acesse o <strong>Painel</strong>.</li></ol>
<h2>Passo 2 — Aparência e IA</h2>
<ol><li>Configure bot e cor.</li><li>Provedor, chave API e modelo.</li></ol>
<h2>Passo 3 — Origens permitidas</h2>
<p>Adicione <code>https://seusite.com</code>.</p>
<h2>Passo 4 — Código embed</h2>
<p>Cole o script antes de <code>&lt;/body&gt;</code>.</p>
<h2>Passo 5 — Teste</h2>
<p>Abra em aba anônima e envie uma mensagem.</p>
<p><a href="/docs/embed-widget">Incorporar</a> · <a href="/pricing.php">Preços</a></p>
HTML,
        'ja' => <<<'HTML'
<p>約10分で<strong>AIチャットウィジェット</strong>を公開する手順です。</p>
<h2>ステップ1 — アカウント</h2>
<ol><li><a href="/register.php">無料登録</a></li><li><strong>ダッシュボード</strong>を開く</li></ol>
<h2>ステップ2 — 見た目とAI</h2>
<ol><li>ボット名と色を設定</li><li>プロバイダ、APIキー、モデルを選択</li></ol>
<h2>ステップ3 — 許可ドメイン</h2>
<p><code>https://yourdomain.com</code> を追加</p>
<h2>ステップ4 — 埋め込みコード</h2>
<p><code>&lt;/body&gt;</code> の直前にスクリプトを貼り付け</p>
<h2>ステップ5 — テスト</h2>
<p>シークレットウィンドウでメッセージ送信</p>
<p><a href="/docs/embed-widget">埋め込みガイド</a> · <a href="/pricing.php">料金</a></p>
HTML,
    ], $lang);
}

function docs_body_create_account(string $lang): string
{
    return docs_pick([
        'en' => <<<'HTML'
<p>Your ChatLM account is the control center for every widget you embed. This tour explains each dashboard area.</p>
<h2>Sign up</h2>
<ol>
<li>Visit <a href="/register.php">Create account</a>.</li>
<li>Use a real email — password reset links are sent there.</li>
<li>Business name appears in emails and can be referenced in your bot prompt.</li>
</ol>
<h2>Dashboard sections</h2>
<h3>Appearance</h3>
<p>Bot display name, welcome message, primary color, and optional avatar styling. Changes apply to the widget within seconds after you save.</p>
<h3>AI configuration</h3>
<p>Provider dropdown (OpenAI, Gemini, DeepSeek, OpenRouter), encrypted API key storage, model ID, temperature, and system prompt. See <a href="/docs/ai-providers">AI providers guide</a>.</p>
<h3>Domain security</h3>
<p><strong>Allowed origins</strong> restrict which websites may load your widget API key. Always use full URLs with <code>https://</code> in production.</p>
<h3>Telegram</h3>
<p>Optional alerts when a visitor sends a message. Requires bot token in server config and numeric chat ID in dashboard. See <a href="/docs/telegram">Telegram setup</a>.</p>
<h3>Sidebar — Widget key &amp; embed code</h3>
<p>Your unique <code>data-api-key</code> and ready-to-paste script. Never share this key publicly in GitHub repos.</p>
<h2>Save settings</h2>
<p>Click <strong>Save all settings</strong> at the bottom after changes. A green confirmation appears when saved.</p>
<p><a href="/docs/quick-start">Quick start</a> · <a href="/login.php">Log in</a></p>
HTML,
        'id' => <<<'HTML'
<p>Akun ChatLM adalah pusat kontrol untuk setiap widget yang Anda pasang. Tur ini menjelaskan setiap bagian dashboard.</p>
<h2>Daftar</h2>
<ol>
<li>Kunjungi <a href="/register.php">Buat akun</a>.</li>
<li>Gunakan email aktif — link reset password dikirim ke sana.</li>
<li>Nama bisnis bisa dipakai di prompt bot.</li>
</ol>
<h2>Bagian dashboard</h2>
<h3>Appearance</h3>
<p>Nama bot, pesan sambutan, warna utama. Perubahan terlihat di widget setelah disimpan.</p>
<h3>AI configuration</h3>
<p>Provider, API key terenkripsi, model, temperature, system prompt. Lihat <a href="/docs/ai-providers">panduan provider AI</a>.</p>
<h3>Domain security</h3>
<p><strong>Allowed origins</strong> membatasi situs mana yang boleh memuat API key widget. Produksi: selalu <code>https://</code> lengkap.</p>
<h3>Telegram</h3>
<p>Notifikasi opsional saat pengunjung mengirim pesan. Lihat <a href="/docs/telegram">setup Telegram</a>.</p>
<h3>Sidebar — Widget key &amp; embed</h3>
<p><code>data-api-key</code> unik dan script siap tempel. Jangan commit key ke repositori publik.</p>
<h2>Simpan pengaturan</h2>
<p>Klik <strong>Save all settings</strong> setelah mengubah data.</p>
<p><a href="/docs/quick-start">Quick start</a> · <a href="/login.php">Masuk</a></p>
HTML,
        'es' => <<<'HTML'
<p>Su cuenta ChatLM controla todos los widgets. Recorrido del panel:</p>
<ul>
<li><strong>Apariencia</strong> — nombre y color del bot</li>
<li><strong>IA</strong> — proveedor, clave API, modelo, prompt</li>
<li><strong>Dominio</strong> — orígenes permitidos</li>
<li><strong>Telegram</strong> — alertas opcionales</li>
<li><strong>Barra lateral</strong> — clave del widget y código embed</li>
</ul>
<p><a href="/register.php">Registrarse</a> · <a href="/docs/quick-start">Inicio rápido</a></p>
HTML,
        'fr' => <<<'HTML'
<p>Votre compte ChatLM pilote tous les widgets. Sections du tableau de bord :</p>
<ul>
<li><strong>Apparence</strong> — nom et couleur</li>
<li><strong>IA</strong> — fournisseur, clé API, modèle</li>
<li><strong>Domaine</strong> — origines autorisées</li>
<li><strong>Telegram</strong> — alertes</li>
<li><strong>Barre latérale</strong> — clé widget et script</li>
</ul>
<p><a href="/register.php">S'inscrire</a> · <a href="/docs/quick-start">Démarrage</a></p>
HTML,
        'pt' => <<<'HTML'
<p>Sua conta ChatLM controla todos os widgets. Seções do painel:</p>
<ul>
<li><strong>Aparência</strong> — bot e cores</li>
<li><strong>IA</strong> — provedor, chave, modelo</li>
<li><strong>Domínio</strong> — origens permitidas</li>
<li><strong>Telegram</strong> — alertas</li>
<li><strong>Barra lateral</strong> — chave e código embed</li>
</ul>
<p><a href="/register.php">Cadastrar</a> · <a href="/docs/quick-start">Início rápido</a></p>
HTML,
        'ja' => <<<'HTML'
<p>ChatLMアカウントはすべてのウィジェットを管理します。ダッシュボードの構成:</p>
<ul>
<li><strong>外観</strong> — ボット名と色</li>
<li><strong>AI設定</strong> — プロバイダとAPIキー</li>
<li><strong>ドメイン</strong> — 許可オリジン</li>
<li><strong>Telegram</strong> — 通知</li>
<li><strong>サイドバー</strong> — ウィジェットキーと埋め込みコード</li>
</ul>
<p><a href="/register.php">登録</a> · <a href="/docs/quick-start">クイックスタート</a></p>
HTML,
    ], $lang);
}
