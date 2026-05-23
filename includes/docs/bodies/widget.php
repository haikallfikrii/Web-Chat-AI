<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

function docs_body_embed_widget(string $lang): string
{
    return docs_pick([
        'en' => <<<'HTML'
<p>The ChatLM widget loads from a single JavaScript file. No npm install, no React bundle — just one script tag.</p>
<h2>Step 1 — Copy from dashboard</h2>
<p>Log in and open <strong>Dashboard</strong>. In the right sidebar you will see <strong>Embed code</strong> with your personal <code>data-api-key</code>.</p>
<h2>Step 2 — Paste before &lt;/body&gt;</h2>
<p>Add the script on every page where visitors should see chat (or in your global footer template):</p>
<pre><code>&lt;script src="https://chatlm.tech/widget/widget.js"
  data-api-key="YOUR_KEY"
  data-base-url="https://chatlm.tech"
  defer&gt;&lt;/script&gt;</code></pre>
<p>Use <code>defer</code> (recommended). Do not use <code>async</code> unless you know the load order implications.</p>
<h2>Step 3 — Allowed origins</h2>
<p>In dashboard → <strong>Domain security</strong>, list each domain exactly as visitors type it in the browser, including <code>https://</code>.</p>
<h2>Step 4 — Verify</h2>
<ol>
<li>Hard-refresh your page (Ctrl+F5 / Cmd+Shift+R).</li>
<li>Open an incognito window — the bubble should appear bottom-right.</li>
<li>Send “Hello” and wait for the AI reply.</li>
</ol>
<h2>Common mistakes</h2>
<ul>
<li>Script placed in <code>&lt;head&gt;</code> only without footer — still works with <code>defer</code>, but footer is safer.</li>
<li>Wrong API key copied from another account.</li>
<li>Staging domain not added to allowed origins.</li>
</ul>
<p><a href="/docs/wordpress">WordPress guide</a> · <a href="/docs/troubleshooting">Troubleshooting</a></p>
HTML,
        'id' => <<<'HTML'
<p>Widget ChatLM dimuat dari satu file JavaScript — cukup satu tag script.</p>
<h2>Langkah 1 — Salin dari dashboard</h2>
<p>Masuk ke <strong>Dashboard</strong>. Di sidebar kanan ada <strong>Embed code</strong> dengan <code>data-api-key</code> Anda.</p>
<h2>Langkah 2 — Tempel sebelum &lt;/body&gt;</h2>
<pre><code>&lt;script src="https://chatlm.tech/widget/widget.js"
  data-api-key="KUNCI_ANDA"
  data-base-url="https://chatlm.tech"
  defer&gt;&lt;/script&gt;</code></pre>
<p>Gunakan <code>defer</code> (disarankan).</p>
<h2>Langkah 3 — Allowed origins</h2>
<p>Dashboard → <strong>Domain security</strong> — cantumkan domain persis seperti di browser, dengan <code>https://</code>.</p>
<h2>Langkah 4 — Verifikasi</h2>
<ol>
<li>Hard-refresh (Ctrl+F5).</li>
<li>Buka incognito — gelembung chat kanan bawah.</li>
<li>Kirim pesan uji.</li>
</ol>
<h2>Kesalahan umum</h2>
<ul>
<li>API key dari akun lain.</li>
<li>Domain staging belum di-whitelist.</li>
<li>Hanya terlihat saat login admin WordPress — lihat <a href="/docs/wordpress">panduan WordPress</a>.</li>
</ul>
<p><a href="/docs/troubleshooting">Troubleshooting</a></p>
HTML,
        'es' => <<<'HTML'
<p>Inserte el widget con un script antes de <code>&lt;/body&gt;</code>.</p>
<ol>
<li>Copie el código del panel.</li>
<li>Añada su dominio en orígenes permitidos.</li>
<li>Pruebe en incógnito.</li>
</ol>
<p><a href="/docs/wordpress">WordPress</a> · <a href="/docs/troubleshooting">Ayuda</a></p>
HTML,
        'fr' => <<<'HTML'
<p>Intégrez le widget avec un script avant <code>&lt;/body&gt;</code>.</p>
<ol>
<li>Copiez le code depuis le tableau de bord.</li>
<li>Ajoutez le domaine aux origines autorisées.</li>
<li>Testez en navigation privée.</li>
</ol>
<p><a href="/docs/wordpress">WordPress</a> · <a href="/docs/troubleshooting">Dépannage</a></p>
HTML,
        'pt' => <<<'HTML'
<p>Incorpore o widget com um script antes de <code>&lt;/body&gt;</code>.</p>
<ol>
<li>Copie o código do painel.</li>
<li>Adicione o domínio nas origens permitidas.</li>
<li>Teste em aba anônima.</li>
</ol>
<p><a href="/docs/wordpress">WordPress</a> · <a href="/docs/troubleshooting">Ajuda</a></p>
HTML,
        'ja' => <<<'HTML'
<p><code>&lt;/body&gt;</code> の直前にスクリプトを配置します。</p>
<ol>
<li>ダッシュボードからコードをコピー</li>
<li>許可ドメインにサイトを追加</li>
<li>シークレットウィンドウでテスト</li>
</ol>
<p><a href="/docs/wordpress">WordPress</a> · <a href="/docs/troubleshooting">トラブル</a></p>
HTML,
    ], $lang);
}

function docs_body_wordpress(string $lang): string
{
    return docs_pick([
        'en' => <<<'HTML'
<p>You do not need a WordPress plugin for ChatLM. The embed is the same script used on any HTML site.</p>
<h2>Method A — Theme footer (best)</h2>
<ol>
<li>Copy embed code from ChatLM dashboard.</li>
<li>WordPress → <strong>Appearance → Theme File Editor</strong> (use a child theme when possible).</li>
<li>Open <code>footer.php</code>, paste the script immediately before <code>&lt;/body&gt;</code>.</li>
<li>Save and clear cache (LiteSpeed, WP Rocket, etc.).</li>
</ol>
<h2>Method B — Header/footer plugin</h2>
<p>Install <strong>WPCode</strong> or <strong>Insert Headers and Footers</strong>. Paste the script in the <strong>Footer</strong> section site-wide.</p>
<h2>Widget only visible when logged in as admin?</h2>
<p>Some themes load different assets for administrators. Always test in incognito while logged out. Also check:</p>
<ul>
<li>Cache plugin serving an old page without the script</li>
<li>Allowed origins missing your public domain</li>
<li>Ad blockers hiding third-party scripts</li>
</ul>
<h2>Page builders (Elementor, Divi)</h2>
<p>Add an HTML widget block in the global footer template and paste the script there.</p>
<p><a href="/docs/embed-widget">Embed guide</a> · <a href="/blog/chatlm-wordpress-embed">Blog: WordPress tips</a></p>
HTML,
        'id' => <<<'HTML'
<p>ChatLM tidak memerlukan plugin WordPress khusus — script embed sama seperti situs HTML biasa.</p>
<h2>Metode A — Footer tema (terbaik)</h2>
<ol>
<li>Salin kode embed dari dashboard ChatLM.</li>
<li>WordPress → <strong>Tampilan → Editor Berkas Tema</strong> (gunakan child theme jika bisa).</li>
<li>Buka <code>footer.php</code>, tempel script sebelum <code>&lt;/body&gt;</code>.</li>
<li>Simpan dan kosongkan cache (LiteSpeed, WP Rocket, dll.).</li>
</ol>
<h2>Metode B — Plugin header/footer</h2>
<p>Pasang <strong>WPCode</strong> atau <strong>Insert Headers and Footers</strong>. Tempel script di bagian <strong>Footer</strong> untuk seluruh situs.</p>
<h2>Widget hanya muncul saat login admin?</h2>
<p>Uji selalu di incognito saat logout. Periksa juga:</p>
<ul>
<li>Plugin cache menyajikan halaman lama tanpa script</li>
<li>Allowed origins belum memuat domain publik</li>
<li>Ad blocker memblokir script</li>
</ul>
<h2>Page builder (Elementor, Divi)</h2>
<p>Tambahkan blok HTML di template footer global dan tempel script.</p>
<p><a href="/docs/embed-widget">Panduan embed</a></p>
HTML,
        'es' => <<<'HTML'
<p>Sin plugin: pegue el script en el footer del tema o con WPCode. Pruebe en incógnito. Limpie caché.</p>
<p><a href="/docs/embed-widget">Insertar widget</a></p>
HTML,
        'fr' => <<<'HTML'
<p>Sans plugin : collez le script dans le pied de page ou via WPCode. Testez en navigation privée.</p>
<p><a href="/docs/embed-widget">Intégration</a></p>
HTML,
        'pt' => <<<'HTML'
<p>Sem plugin: cole o script no rodapé do tema ou com WPCode. Teste em anônimo e limpe o cache.</p>
<p><a href="/docs/embed-widget">Incorporar</a></p>
HTML,
        'ja' => <<<'HTML'
<p>専用プラグイン不要。テーマフッターまたはWPCodeにスクリプトを貼り付け。シークレットで確認しキャッシュを削除。</p>
<p><a href="/docs/embed-widget">埋め込み</a></p>
HTML,
    ], $lang);
}

function docs_body_troubleshooting(string $lang): string
{
    return docs_pick([
        'en' => <<<'HTML'
<h2>Widget does not appear</h2>
<ol>
<li>Confirm the script is before <code>&lt;/body&gt;</code> on the live HTML (View Source).</li>
<li>Check <strong>Allowed origins</strong> includes your exact domain with <code>https://</code>.</li>
<li>Test incognito — not as WordPress admin only.</li>
<li>Disable cache plugins temporarily.</li>
<li>Open browser DevTools → Console for red errors.</li>
</ol>
<h2>Chat sends but no AI reply</h2>
<ul>
<li>Invalid or expired API key in dashboard.</li>
<li>Model ID typo — copy from provider docs.</li>
<li>Provider account out of credits.</li>
</ul>
<h2>CORS / blocked request</h2>
<p>Your domain must be listed in allowed origins. Wildcard <code>*</code> works for testing only.</p>
<h2>Site shows blank white page (500)</h2>
<p>Usually a server PHP error unrelated to the widget. Check hosting error logs. ChatLM marketing pages should return HTTP 200.</p>
<h2>Telegram not working</h2>
<p>Bot token belongs in server <code>config.local.php</code> (<code>TELEGRAM_BOT_TOKEN</code>). Chat ID is numeric only — not the bot token. See <a href="/docs/telegram">Telegram guide</a>.</p>
<p>Still stuck? Email support from your registered account email with your domain and screenshot of DevTools console.</p>
HTML,
        'id' => <<<'HTML'
<h2>Widget tidak muncul</h2>
<ol>
<li>Pastikan script ada sebelum <code>&lt;/body&gt;</code> di HTML live (Lihat Sumber).</li>
<li><strong>Allowed origins</strong> harus memuat domain persis dengan <code>https://</code>.</li>
<li>Uji incognito — bukan hanya sebagai admin WordPress.</li>
<li>Nonaktifkan plugin cache sementara.</li>
<li>DevTools → Console untuk error merah.</li>
</ol>
<h2>Chat terkirim tapi AI tidak menjawab</h2>
<ul>
<li>API key salah atau kedaluwarsa.</li>
<li>Salah ketik model ID.</li>
<li>Saldo provider habis.</li>
</ul>
<h2>CORS / request diblokir</h2>
<p>Domain harus ada di allowed origins. <code>*</code> hanya untuk testing.</p>
<h2>Halaman putih (500)</h2>
<p>Biasanya error PHP di server, bukan widget. Cek error log hosting.</p>
<h2>Telegram tidak jalan</h2>
<p>Token bot di <code>config.local.php</code>, Chat ID numerik di dashboard. Lihat <a href="/docs/telegram">panduan Telegram</a>.</p>
HTML,
        'es' => <<<'HTML'
<h2>El widget no aparece</h2>
<p>Revise el script, orígenes permitidos, caché y la consola del navegador.</p>
<h2>Sin respuesta de la IA</h2>
<p>Clave API, modelo y créditos del proveedor.</p>
<p><a href="/docs/telegram">Telegram</a></p>
HTML,
        'fr' => <<<'HTML'
<h2>Widget invisible</h2>
<p>Vérifiez le script, les origines autorisées et le cache.</p>
<h2>Pas de réponse IA</h2>
<p>Clé API, modèle et crédits fournisseur.</p>
HTML,
        'pt' => <<<'HTML'
<h2>Widget não aparece</h2>
<p>Verifique script, origens permitidas e cache.</p>
<h2>Sem resposta da IA</h2>
<p>Chave API, modelo e créditos.</p>
HTML,
        'ja' => <<<'HTML'
<h2>ウィジェットが表示されない</h2>
<p>スクリプト、許可ドメイン、キャッシュ、コンソールを確認。</p>
<h2>AIが返信しない</h2>
<p>APIキー、モデル名、残高を確認。</p>
HTML,
    ], $lang);
}
