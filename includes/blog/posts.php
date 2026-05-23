<?php
declare(strict_types=1);

/**
 * @return list<array{slug:string,title:string,description:string,published:string,updated:string,keywords:string,body:string}>
 */
function blog_all_posts(): array
{
    static $posts = null;
    if ($posts !== null) {
        return $posts;
    }

    $posts = [
        [
            'slug'        => 'add-ai-chat-widget-5-minutes',
            'title'       => 'How to Add an AI Chat Widget to Your Website in 5 Minutes',
            'description' => 'Step-by-step guide to embed ChatLM on any site with one script tag. No plugin required. Works on WordPress, Shopify, and custom HTML.',
            'published'   => '2026-05-20',
            'updated'     => '2026-05-23',
            'keywords'    => 'AI chat widget, embed chat, website chatbot, ChatLM',
            'body'        => blog_body_add_widget(),
        ],
        [
            'slug'        => 'chatlm-vs-intercom',
            'title'       => 'ChatLM vs Intercom: Which AI Chat Widget Fits Small Websites?',
            'description' => 'Compare pricing, setup time, and AI flexibility. Why founders choose a lightweight embed over full Intercom suites.',
            'published'   => '2026-05-21',
            'updated'     => '2026-05-23',
            'keywords'    => 'Intercom alternative, AI chat widget pricing, small business chatbot',
            'body'        => blog_body_vs_intercom(),
        ],
        [
            'slug'        => 'chatlm-wordpress-embed',
            'title'       => 'Install ChatLM on WordPress Without a Plugin',
            'description' => 'Paste one script before </body> or use a header/footer plugin. Fix cache issues when the widget only shows for logged-in admins.',
            'published'   => '2026-05-22',
            'updated'     => '2026-05-23',
            'keywords'    => 'WordPress AI chat, embed chat WordPress, ChatLM widget',
            'body'        => blog_body_wordpress(),
        ],
    ];

    return $posts;
}

function blog_post_url(string $slug): string
{
    return app_url('/blog/' . $slug);
}

function blog_post_by_slug(string $slug): ?array
{
    foreach (blog_all_posts() as $post) {
        if ($post['slug'] === $slug) {
            return $post;
        }
    }

    return null;
}

function blog_body_add_widget(): string
{
    return <<<'HTML'
<p>Visitors expect instant answers. An <strong>AI chat widget for your website</strong> handles FAQs, qualifies leads, and reduces support load — without hiring a 24/7 team. With ChatLM, you can go live in about five minutes using a single embed script.</p>

<h2>What you need before you start</h2>
<ul>
<li>A ChatLM account (free trial available)</li>
<li>An API key from OpenAI, OpenRouter, Gemini, or DeepSeek</li>
<li>Access to edit your site HTML or WordPress theme footer</li>
</ul>

<h2>Step 1 — Configure your bot in the dashboard</h2>
<p>After signing up, open the dashboard and set your bot name, welcome message, brand color, and AI provider. Paste your provider API key and choose a model (for example <code>openai/gpt-4o-mini</code> on OpenRouter for a low-cost start).</p>
<p>Under <strong>Allowed Origins</strong>, add your live domain with <code>https://</code>, for example <code>https://yourdomain.com</code>. Include both <code>www</code> and non-<code>www</code> if you use both. For testing you can use <code>*</code>, then tighten before launch.</p>

<h2>Step 2 — Copy the embed code</h2>
<p>The dashboard shows a script tag like this:</p>
<pre><code>&lt;script src="https://chatlm.tech/widget/widget.js"
  data-api-key="YOUR_KEY"
  data-base-url="https://chatlm.tech"
  defer&gt;&lt;/script&gt;</code></pre>
<p>Place it just before <code>&lt;/body&gt;</code> on every page where you want the chat bubble.</p>

<h2>Step 3 — Publish and test</h2>
<p>Open your site in a private/incognito window. You should see the chat button bottom-right. Send a test message and confirm the AI replies. Enable Telegram notifications in settings if you want a ping on every new visitor message.</p>

<h2>Why ChatLM instead of a heavy live-chat suite?</h2>
<p>Many tools charge per seat and per conversation. ChatLM is built for site owners who want a focused <strong>embed AI chat</strong> experience: one script, your API keys, your branding on paid plans.</p>

<p><a href="/register.php">Create your free account</a> or <a href="/pricing.php">compare plans</a> to remove the watermark on Starter and Pro.</p>
HTML;
}

function blog_body_vs_intercom(): string
{
    return <<<'HTML'
<p>Intercom is powerful — but for a marketing site, portfolio, or small store, it can be more than you need. <strong>ChatLM</strong> is an alternative focused on one job: an AI chat widget you embed anywhere.</p>

<h2>Setup time</h2>
<p>Intercom requires account setup, messenger configuration, and often JavaScript installation guides. ChatLM gives you a copy-paste script and a dashboard for prompts and colors. Most users are live in under ten minutes.</p>

<h2>Cost structure</h2>
<p>Intercom pricing scales with seats and advanced features. ChatLM offers a free tier (with a small watermark), plus Starter and Pro plans in USD with transparent monthly or yearly billing. You bring your own AI provider keys, so model costs stay under your control.</p>

<h2>AI flexibility</h2>
<p>ChatLM connects to OpenAI, Google Gemini, DeepSeek, and OpenRouter from a dropdown. Switch models without redeploying your site. System prompts and bot persona live in your dashboard.</p>

<h2>When Intercom still wins</h2>
<p>If you need full product tours, email campaigns, and a large support inbox in one suite, Intercom remains strong. If you want a lightweight <strong>website chatbot with no code</strong> and AI-first replies, ChatLM is the better fit.</p>

<p>Ready to try it? <a href="/register.php">Start free</a> or read <a href="/blog/post.php?slug=add-ai-chat-widget-5-minutes">how to embed in 5 minutes</a>.</p>
HTML;
}

function blog_body_wordpress(): string
{
    return <<<'HTML'
<p>WordPress powers millions of sites — and you do not need a dedicated plugin to add an <strong>AI chat widget</strong>. ChatLM loads via one script tag, the same way you would add Google Analytics.</p>

<h2>Method A — Theme footer (recommended)</h2>
<ol>
<li>Copy your embed code from the ChatLM dashboard.</li>
<li>In WordPress go to <strong>Appearance → Theme File Editor</strong> (or use a child theme).</li>
<li>Open <code>footer.php</code> and paste the script before <code>&lt;/body&gt;</code>.</li>
<li>Save and clear any cache plugin.</li>
</ol>

<h2>Method B — Insert Headers and Footers plugin</h2>
<p>If you prefer not to edit theme files, install “WPCode” or “Insert Headers and Footers”, paste the script in the footer section, and save.</p>

<h2>Widget only visible when logged in as admin?</h2>
<p>This is almost always <strong>page cache</strong>. Logged-in users bypass the cache and see the new script; guests see an old HTML snapshot without it. After adding the embed:</p>
<ul>
<li>Purge LiteSpeed / WP Rocket / Hostinger cache</li>
<li>Purge CDN if you use Cloudflare</li>
<li>Test in incognito, not only in the admin bar preview</li>
</ul>
<p>Also add your public site URL (not <code>/wp-admin</code>) to <strong>Allowed Origins</strong> in ChatLM.</p>

<h2>Allowed Origins checklist</h2>
<pre><code>https://yourblog.com, https://www.yourblog.com</code></pre>

<h2>Next steps</h2>
<p>Configure your AI key, welcome message, and optional Telegram alerts. Then run a test message as a guest user.</p>
<p><a href="/register.php">Get started free</a> · <a href="/pricing.php">See pricing</a></p>
HTML;
}
