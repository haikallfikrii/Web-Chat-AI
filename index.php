<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

if (current_user() !== null) {
    header('Location: /dashboard.php');
    exit;
}
$base = dashboard_base_url();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Chat PopUp AI — Widget AI untuk Website Anda</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --blue:#2563EB;--blue-dark:#1D4ED8;--purple:#7C3AED;
  --green:#16A34A;--slate:#0F172A;--muted:#64748B;
  --border:#E2E8F0;--bg:#F8FAFC;--white:#FFFFFF;
}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--slate);line-height:1.6}
a{color:inherit;text-decoration:none}
/* NAV */
nav{background:var(--white);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100}
.nav-inner{max-width:1160px;margin:0 auto;padding:0 24px;height:64px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.logo{font-size:20px;font-weight:800;color:var(--blue);letter-spacing:-.5px}
.logo span{color:var(--purple)}
.nav-links{display:flex;align-items:center;gap:8px}
.btn-ghost{padding:8px 16px;border-radius:10px;font-weight:600;font-size:14px;color:var(--muted);transition:background .15s}
.btn-ghost:hover{background:#F1F5F9;color:var(--slate)}
.btn-primary{padding:9px 20px;border-radius:10px;font-weight:700;font-size:14px;background:var(--blue);color:#fff;transition:background .15s}
.btn-primary:hover{background:var(--blue-dark)}
/* HERO */
.hero{max-width:1160px;margin:0 auto;padding:80px 24px 60px;text-align:center}
.hero-badge{display:inline-flex;align-items:center;gap:6px;background:#EFF6FF;color:var(--blue);border:1px solid #BFDBFE;border-radius:999px;padding:6px 14px;font-size:13px;font-weight:600;margin-bottom:24px}
.hero h1{font-size:clamp(36px,5vw,60px);font-weight:900;line-height:1.1;letter-spacing:-1.5px;margin-bottom:20px}
.hero h1 .grad{background:linear-gradient(135deg,var(--blue),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{font-size:18px;color:var(--muted);max-width:580px;margin:0 auto 36px}
.hero-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.btn-lg{padding:14px 28px;border-radius:14px;font-size:16px;font-weight:700}
.btn-outline{padding:14px 28px;border-radius:14px;font-size:16px;font-weight:700;border:2px solid var(--border);color:var(--slate);background:var(--white)}
.btn-outline:hover{border-color:var(--blue);color:var(--blue)}
/* SOCIAL PROOF */
.proof{text-align:center;padding:16px 24px 40px;color:var(--muted);font-size:14px}
.proof-chips{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:12px}
.chip{background:var(--white);border:1px solid var(--border);border-radius:999px;padding:6px 14px;font-size:13px;font-weight:600;color:var(--slate)}
/* PREVIEW MOCK */
.preview-wrap{max-width:900px;margin:0 auto 72px;padding:0 24px}
.browser{background:var(--white);border-radius:18px;border:1px solid var(--border);box-shadow:0 20px 60px rgba(15,23,42,.10);overflow:hidden}
.browser-bar{background:#F1F5F9;padding:12px 16px;display:flex;align-items:center;gap:8px}
.dot{width:12px;height:12px;border-radius:50%}
.dot.r{background:#FF5F57}.dot.y{background:#FFBD2E}.dot.g{background:#28C840}
.browser-url{background:var(--white);border-radius:6px;padding:4px 12px;font-size:12px;color:var(--muted);margin-left:8px}
.browser-body{padding:32px 28px;background:#F8FAFC;min-height:180px;position:relative}
.mock-content{display:flex;flex-direction:column;gap:8px;opacity:.7}
.mock-line{height:12px;border-radius:6px;background:#E2E8F0}
.mock-line.w80{width:80%}.mock-line.w60{width:60%}.mock-line.w90{width:90%}.mock-line.w40{width:40%}
.mock-widget{position:absolute;bottom:20px;right:20px;display:flex;flex-direction:column;align-items:flex-end;gap:8px}
.mock-chat{background:var(--white);border-radius:14px 14px 4px 14px;box-shadow:0 8px 24px rgba(15,23,42,.12);padding:12px 14px;max-width:220px;font-size:13px;color:var(--slate)}
.mock-chat-user{background:var(--blue);color:#fff;border-radius:14px 14px 14px 4px;padding:10px 14px;font-size:13px;max-width:180px}
.mock-fab{width:48px;height:48px;border-radius:50%;background:var(--blue);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(37,99,235,.4)}
.mock-fab svg{fill:#fff;width:22px;height:22px}
/* FEATURES */
.section{max-width:1160px;margin:0 auto;padding:0 24px 80px}
.section-label{text-align:center;font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--blue);margin-bottom:12px}
.section-title{text-align:center;font-size:clamp(28px,3.5vw,40px);font-weight:800;letter-spacing:-.5px;margin-bottom:12px}
.section-sub{text-align:center;color:var(--muted);font-size:16px;max-width:540px;margin:0 auto 44px}
.grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.feat-card{background:var(--white);border-radius:18px;padding:28px;border:1px solid var(--border)}
.feat-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:16px}
.feat-card h3{font-size:18px;font-weight:700;margin-bottom:8px}
.feat-card p{color:var(--muted);font-size:14px;line-height:1.6}
/* STEPS */
.steps-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;position:relative}
.step-card{background:var(--white);border-radius:18px;padding:28px;border:1px solid var(--border);text-align:center}
.step-num{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--purple));color:#fff;font-size:18px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
.step-card h3{font-size:17px;font-weight:700;margin-bottom:8px}
.step-card p{color:var(--muted);font-size:14px}
/* PROVIDERS */
.providers{max-width:1160px;margin:0 auto;padding:0 24px 80px;text-align:center}
.provider-chips{display:flex;gap:12px;flex-wrap:wrap;justify-content:center;margin-top:24px}
.provider-chip{background:var(--white);border:1px solid var(--border);border-radius:14px;padding:14px 24px;font-weight:700;font-size:15px;display:flex;align-items:center;gap:8px}
/* CTA SECTION */
.cta-section{background:linear-gradient(135deg,#1E3A8A,var(--purple));margin:0 24px 80px;border-radius:24px;padding:60px 40px;text-align:center;color:#fff}
.cta-section h2{font-size:clamp(28px,4vw,42px);font-weight:800;margin-bottom:12px;letter-spacing:-.5px}
.cta-section p{font-size:16px;opacity:.85;margin-bottom:32px;max-width:480px;margin-left:auto;margin-right:auto}
.btn-white{background:#fff;color:var(--blue);padding:14px 32px;border-radius:14px;font-size:16px;font-weight:700;display:inline-block}
.btn-white:hover{background:#EFF6FF}
/* FOOTER */
footer{border-top:1px solid var(--border);padding:24px;text-align:center;color:var(--muted);font-size:13px}
@media(max-width:768px){
  .grid3,.steps-grid{grid-template-columns:1fr}
  .cta-section{padding:40px 24px}
  .hero{padding:50px 24px 40px}
}
</style>
</head>
<body>

<nav>
  <div class="nav-inner">
    <a class="logo" href="/">Chat<span>Popup</span>.AI</a>
    <div class="nav-links">
      <a class="btn-ghost" href="/login.php">Masuk</a>
      <a class="btn-primary" href="/register.php">Daftar Gratis</a>
    </div>
  </div>
</nav>

<div class="hero">
  <div class="hero-badge">✨ Widget AI siap pakai untuk website Anda</div>
  <h1>Pasang <span class="grad">Asisten AI</span><br>di Website Dalam 5 Menit</h1>
  <p>Satu baris kode. Semua model AI. Bisa diatur sendiri dari dashboard — tanpa sentuh database.</p>
  <div class="hero-btns">
    <a class="btn-primary btn-lg" href="/register.php">Mulai Gratis Sekarang →</a>
    <a class="btn-outline btn-lg" href="/login.php">Sudah punya akun</a>
  </div>
</div>

<div class="proof">
  Didukung provider AI terpopuler:
  <div class="proof-chips">
    <span class="chip">🤖 OpenRouter</span>
    <span class="chip">⚡ OpenAI</span>
    <span class="chip">💎 Google Gemini</span>
    <span class="chip">🌊 DeepSeek</span>
  </div>
</div>

<div class="preview-wrap">
  <div class="browser">
    <div class="browser-bar">
      <div class="dot r"></div><div class="dot y"></div><div class="dot g"></div>
      <div class="browser-url">https://website-anda.com</div>
    </div>
    <div class="browser-body">
      <div class="mock-content">
        <div class="mock-line w80"></div>
        <div class="mock-line w60"></div>
        <div class="mock-line w90"></div>
        <div class="mock-line w40"></div>
        <div style="height:12px"></div>
        <div class="mock-line w70"></div>
        <div class="mock-line w55"></div>
      </div>
      <div class="mock-widget">
        <div class="mock-chat">Halo! Ada yang bisa saya bantu hari ini? 😊</div>
        <div class="mock-chat-user">Saya mau tanya soal produk</div>
        <div class="mock-fab">
          <svg viewBox="0 0 24 24"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/></svg>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="section">
  <div class="section-label">Fitur Utama</div>
  <div class="section-title">Semua yang Anda butuhkan</div>
  <div class="section-sub">Dari setup hingga percakapan AI, semuanya bisa diatur dari satu dashboard.</div>
  <div class="grid3">
    <div class="feat-card">
      <div class="feat-icon" style="background:#EFF6FF">🚀</div>
      <h3>Pasang dalam 5 Menit</h3>
      <p>Copy satu baris script embed dari dashboard, tempel ke website. Widget langsung muncul di semua halaman.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon" style="background:#F5F3FF">🤖</div>
      <h3>Multi-Provider AI</h3>
      <p>Support OpenRouter, OpenAI, Google Gemini, dan DeepSeek. Ganti provider kapan saja tanpa ubah kode widget.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon" style="background:#ECFDF5">🎨</div>
      <h3>Fully Customizable</h3>
      <p>Atur warna primary, nama bot, welcome message, dan system prompt. Widget menyesuaikan brand website Anda.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon" style="background:#FFF7ED">🧠</div>
      <h3>Memori Percakapan</h3>
      <p>AI mengingat konteks dalam satu sesi. Pengguna bisa melanjutkan percakapan tanpa mengulang dari awal.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon" style="background:#FDF2F8">📱</div>
      <h3>Notifikasi Telegram</h3>
      <p>Aktifkan notifikasi Telegram Bot agar Anda tahu setiap kali ada pesan baru dari pengunjung website.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon" style="background:#F0FDF4">🔒</div>
      <h3>Aman & Terisolasi</h3>
      <p>Shadow DOM memastikan widget tidak merusak tampilan website Anda. API key AI dienkripsi AES-256 di database.</p>
    </div>
  </div>
</div>

<div class="section">
  <div class="section-label">Cara Kerja</div>
  <div class="section-title">Tiga langkah, selesai</div>
  <div class="section-sub">Tidak perlu keahlian teknis khusus. Kalau bisa copy-paste, Anda bisa pakai ini.</div>
  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">1</div>
      <h3>Daftar & Atur</h3>
      <p>Buat akun, pilih provider AI, masukkan API key, tulis system prompt sesuai bisnis Anda.</p>
    </div>
    <div class="step-card">
      <div class="step-num">2</div>
      <h3>Copy Kode Embed</h3>
      <p>Dashboard otomatis menghasilkan satu baris script yang unik untuk website Anda.</p>
    </div>
    <div class="step-card">
      <div class="step-num">3</div>
      <h3>Tempel ke Website</h3>
      <p>Paste sebelum tag &lt;/body&gt; di WordPress, landing page, atau toko online. Selesai!</p>
    </div>
  </div>
</div>

<div class="providers">
  <div class="section-label">Didukung Oleh</div>
  <div class="section-title">Pilih provider AI terbaik untuk Anda</div>
  <div class="provider-chips">
    <div class="provider-chip">🌐 OpenRouter <span style="color:var(--muted);font-size:12px;font-weight:400">(Rekomendasi — semua model)</span></div>
    <div class="provider-chip">⚡ OpenAI GPT-4o / mini</div>
    <div class="provider-chip">💎 Google Gemini 1.5</div>
    <div class="provider-chip">🌊 DeepSeek Chat / Coder</div>
  </div>
</div>

<div class="cta-section">
  <h2>Siap memberi website Anda otak AI?</h2>
  <p>Daftar gratis sekarang. Tidak perlu kartu kredit. Siap pakai dalam hitungan menit.</p>
  <a class="btn-white" href="/register.php">Buat Akun Gratis →</a>
</div>

<footer>
  &copy; <?= date('Y') ?> ChatPopup.AI · Dibangun di atas Hostinger &amp; PHP 8 ·
  <a href="/login.php" style="color:var(--blue)">Login</a>
</footer>

</body>
</html>
