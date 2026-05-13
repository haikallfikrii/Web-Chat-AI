<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (current_user() !== null) {
    header('Location: /dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat PopUp AI</title>
    <style>
        :root { color-scheme: light; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 32px 20px 64px; }
        .hero { display: grid; grid-template-columns: 1.2fr .8fr; gap: 24px; align-items: center; }
        .card, .panel { background: #fff; border-radius: 18px; padding: 24px; box-shadow: 0 10px 30px rgba(15,23,42,.08); }
        h1, h2, h3, p { margin-top: 0; }
        h1 { font-size: 42px; line-height: 1.1; margin-bottom: 14px; }
        .lead { font-size: 18px; color: #475569; margin-bottom: 24px; }
        .cta { display: inline-block; padding: 14px 18px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 12px; font-weight: 700; }
        .muted { color: #64748b; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 28px; }
        .steps { display: grid; gap: 14px; }
        .step { padding: 14px 16px; border-radius: 14px; background: #eff6ff; }
        code, pre { font-family: Consolas, Monaco, monospace; }
        pre { white-space: pre-wrap; overflow-wrap: anywhere; padding: 14px; background: #0f172a; color: #e2e8f0; border-radius: 12px; }
        @media (max-width: 820px) {
            .hero, .grid { grid-template-columns: 1fr; }
            h1 { font-size: 32px; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="hero">
            <div class="card">
                <p class="muted">SaaS Chat Widget untuk WordPress, landing page, dan toko online</p>
                <h1>Kelola widget AI Anda dari satu dashboard sederhana.</h1>
                <p class="lead">Atur nama bot, warna widget, system prompt, model AI, domain website, dan ambil kode embed tanpa perlu buka phpMyAdmin setiap saat.</p>
                <a class="cta" href="/login.php">Login Dashboard</a>
                <p class="muted" style="margin-top:16px;">Cocok untuk shared hosting dan setup cepat di Hostinger.</p>
            </div>
            <div class="panel">
                <h2>Alur MVP saat ini</h2>
                <div class="steps">
                    <div class="step"><strong>1.</strong> Anda input client dan user dashboard.</div>
                    <div class="step"><strong>2.</strong> Client login ke dashboard.</div>
                    <div class="step"><strong>3.</strong> Client atur bot, provider AI, prompt, dan domain.</div>
                    <div class="step"><strong>4.</strong> Client copy script embed ke website mereka.</div>
                </div>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <h3>Yang Bisa Diatur</h3>
                <p class="muted">Bot name, warna, welcome message, allowed origins, provider AI, model, system prompt, Telegram alert, dan fallback webhook n8n.</p>
            </div>
            <div class="card">
                <h3>Provider Didukung</h3>
                <p class="muted">OpenAI, Google Gemini, DeepSeek, dan OpenRouter. Ganti provider cukup dari dashboard tanpa ubah kode widget.</p>
            </div>
            <div class="card">
                <h3>Instalasi Widget</h3>
                <pre>&lt;script src="<?= e(dashboard_base_url()) ?>/widget/widget.js"
  data-api-key="CLIENT_API_KEY"
  data-base-url="<?= e(dashboard_base_url()) ?>"
  async&gt;&lt;/script&gt;</pre>
            </div>
        </div>
    </div>
</body>
</html>
