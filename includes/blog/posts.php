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
        [
            'slug'        => 'byok-vs-managed-ai',
            'title'       => 'BYOK vs Managed AI: Which ChatLM Plan Fits Your Business?',
            'description' => 'Bring your own API key or let ChatLM host the model for you? Compare cost, setup effort, and control to pick the right track.',
            'published'   => '2026-07-13',
            'updated'     => '2026-07-13',
            'keywords'    => 'BYOK vs managed AI, AI chat widget pricing, ChatLM plans',
            'body'        => blog_body_byok_vs_managed(),
        ],
        [
            'slug'        => 'ai-chatbot-cost-2026',
            'title'       => 'How Much Does an AI Chatbot Cost in 2026? Full Price Breakdown',
            'description' => 'From $0 watermarked widgets to $200+/mo enterprise suites — see what you actually pay for an AI chat widget and where the hidden costs are.',
            'published'   => '2026-07-13',
            'updated'     => '2026-07-13',
            'keywords'    => 'AI chatbot cost, chat widget pricing, live chat software price',
            'body'        => blog_body_chatbot_cost(),
        ],
        [
            'slug'        => 'chat-widget-conversion-tips',
            'title'       => '7 Chat Widget Best Practices That Turn Visitors Into Leads',
            'description' => 'Placement, timing, welcome message, and persona tweaks that measurably increase chat engagement and lead capture.',
            'published'   => '2026-07-13',
            'updated'     => '2026-07-13',
            'keywords'    => 'chat widget conversion, increase website leads, AI chatbot best practices',
            'body'        => blog_body_conversion_tips(),
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

function blog_body_byok_vs_managed(): string
{
    return <<<'HTML'
<p>When you set up an AI chat widget, the first real decision isn't the color of the bubble — it's <strong>who pays for the AI, and who manages the key</strong>. ChatLM offers two tracks: <strong>BYOK</strong> (Bring Your Own Key) and <strong>Managed AI</strong>. Both power the same widget; they just move the API cost and setup step to a different place.</p>

<h2>BYOK: you control the model and the bill</h2>
<p>With BYOK, you paste your own API key from OpenAI, Google Gemini, DeepSeek, or OpenRouter into the dashboard. ChatLM only charges a flat platform fee — the AI usage itself is billed to you directly by the provider, at their raw rate.</p>
<ul>
<li><strong>Best for:</strong> technical founders, agencies with existing provider accounts, high-volume sites where token costs matter</li>
<li><strong>You get:</strong> full model choice (GPT-4o, GPT-4o-mini, Gemini 1.5, DeepSeek Chat, or anything on OpenRouter), zero markup on tokens</li>
<li><strong>Setup effort:</strong> ~2 minutes — create a provider account, generate a key, paste it in</li>
</ul>

<h2>Managed AI: zero setup, flat monthly quota</h2>
<p>Managed AI plans skip the API key step entirely. ChatLM hosts the model (via OpenRouter, GPT-4o-mini class) and includes a fixed number of messages per month in the subscription price. You pick a plan, configure your bot persona, and you're done.</p>
<ul>
<li><strong>Best for:</strong> non-technical owners, agencies onboarding clients quickly, anyone who wants one predictable invoice</li>
<li><strong>You get:</strong> no provider account needed, predictable monthly cost, faster onboarding for clients</li>
<li><strong>Setup effort:</strong> under 60 seconds — no key, no provider dashboard</li>
</ul>

<h2>Side-by-side</h2>
<table>
<tr><th>Question</th><th>BYOK</th><th>Managed AI</th></tr>
<tr><td>Who pays the AI provider?</td><td>You, directly</td><td>Included in plan</td></tr>
<tr><td>Message limits?</td><td>None from ChatLM (provider limits apply)</td><td>Fixed monthly quota per plan</td></tr>
<tr><td>Setup time</td><td>~2 minutes</td><td>&lt;1 minute</td></tr>
<tr><td>Model choice</td><td>Any supported provider/model</td><td>Curated, cost-optimized model</td></tr>
</table>

<h2>How to choose</h2>
<p>If you already have (or don't mind creating) an OpenAI/OpenRouter account and want the lowest possible per-message cost at scale, go <strong>BYOK</strong>. If you want to be live in under a minute with one flat bill and no provider account to manage, go <strong>Managed AI</strong>. Both remove the watermark on paid tiers and include the same customization: colors, persona, welcome message, and Telegram alerts.</p>

<p>Compare exact prices on the <a href="/pricing.php">pricing page</a>, or <a href="/register.php">start free</a> and switch tracks later — nothing is locked in.</p>
HTML;
}

function blog_body_chatbot_cost(): string
{
    return <<<'HTML'
<p>"How much does an AI chatbot cost?" has a wide answer — anywhere from <strong>$0 to $300+ per month</strong> — because the price depends on who hosts the model, how many conversations you run, and how much branding control you need. Here's a realistic breakdown for 2026.</p>

<h2>Free tier: $0/month</h2>
<p>Most AI chat widgets, including ChatLM, offer a free tier. Expect a small "Powered by" watermark and, on some platforms, a message cap. This is enough to validate that an AI widget actually helps your site before paying anything.</p>

<h2>Small business plans: $15–$40/month</h2>
<p>This tier typically removes the watermark and adds custom branding, a system prompt, and basic analytics. ChatLM's Starter plan sits at <strong>$19/month</strong> (BYOK, or from $29/month Managed AI) — enough for most single-site owners, freelancers, and small stores.</p>

<h2>Growing teams: $40–$90/month</h2>
<p>At this level you usually get multiple websites, higher message quotas, priority support, and sometimes a customer-facing knowledge base. This is where ChatLM's Pro plan lives.</p>

<h2>Agencies & multi-client: $90–$300+/month</h2>
<p>Agencies managing chat widgets for several client sites need multi-website support, white-label branding, and centralized billing. Enterprise live-chat suites (Intercom, Drift-style tools) often start here even before adding AI add-ons — see our <a href="/blog/chatlm-vs-intercom">ChatLM vs Intercom comparison</a> for a direct cost breakdown.</p>

<h2>The hidden cost most people miss: token usage</h2>
<p>If a platform is "BYOK" (bring your own key), the platform fee is only half the bill — you also pay the AI provider per token. A GPT-4o-mini-class model costs a fraction of a cent per short conversation, but high-volume sites should estimate this separately. Our <a href="/blog/byok-vs-managed-ai">BYOK vs Managed AI guide</a> explains how to avoid surprise token bills, or you can pick a Managed AI plan with a flat included quota instead.</p>

<h2>Quick math: is it worth it?</h2>
<p>A human support agent costs roughly $0.30–$1.00 in time per handled conversation, once you include salary, tools, and overhead. Use the savings calculator on our <a href="/#calculator">homepage</a> to estimate your break-even point based on your actual monthly conversation volume.</p>

<p>Ready to see real numbers for your site? <a href="/pricing.php">Compare ChatLM plans</a> or <a href="/register.php">start free</a> — no credit card required.</p>
HTML;
}

function blog_body_conversion_tips(): string
{
    return <<<'HTML'
<p>Adding a chat widget is easy. Getting visitors to actually <strong>use</strong> it — and turning that conversation into a lead — takes a few deliberate choices. Here are seven changes that reliably move the needle.</p>

<h2>1. Write a welcome message that asks a question</h2>
<p>"Hi! How can I help?" is passive. Try "Looking for pricing, or have a quick question about setup?" — a specific opening invites a specific reply instead of silence.</p>

<h2>2. Match your bot name and avatar to your brand</h2>
<p>A generic "Support Bot" feels like a cost center. A named assistant ("Maya from Acme") feels like part of the team. This is a two-minute change in the ChatLM dashboard and measurably increases first-message rates.</p>

<h2>3. Give the AI a real system prompt, not a generic one</h2>
<p>The default "You are a helpful assistant" wastes the AI's potential. Tell it your product, your tone, your top 5 FAQs, and what to do when it doesn't know an answer (e.g. "offer to connect them via the contact form"). This single change is usually the biggest lever for lead quality.</p>

<h2>4. Don't hide the widget on your highest-intent pages</h2>
<p>Pricing pages, checkout pages, and product pages are where visitors have the most questions and the highest buying intent. Make sure your <strong>Allowed Origins</strong> and embed script cover every page, not just the homepage.</p>

<h2>5. Turn on Telegram notifications</h2>
<p>The fastest way to lose a warm lead is not knowing they're chatting. ChatLM's Telegram alerts ping you the moment a new message arrives, so you (or a human teammate) can jump in on high-value conversations within minutes.</p>

<h2>6. Keep response scope honest</h2>
<p>An AI that confidently invents pricing or policies erodes trust fast. Instruct it to say "let me connect you with the team" for anything outside its system prompt, rather than guessing.</p>

<h2>7. Review real conversations monthly</h2>
<p>Patterns emerge fast — the same three questions, a confusing product name, a missing FAQ. Revisit your system prompt every few weeks based on what visitors actually ask, not what you assumed they'd ask.</p>

<h2>Put it into practice</h2>
<p>Most of these changes take under ten minutes combined in the ChatLM dashboard. If you haven't installed the widget yet, see <a href="/blog/add-ai-chat-widget-5-minutes">how to add it in 5 minutes</a>, or <a href="/register.php">start free</a> to test these tips on your own site today.</p>
HTML;
}
