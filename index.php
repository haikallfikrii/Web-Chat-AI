<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
if (current_user() !== null) { header('Location: /dashboard.php'); exit; }
$base = dashboard_base_url();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ChatPopup.AI — Widget AI untuk Website Anda</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#030712;--bg2:#0D1117;--bg3:#161B22;
  --green:#00D68F;--green-dark:#00B077;--green-dim:rgba(0,214,143,.15);
  --blue:#3B82F6;--purple:#8B5CF6;
  --text:#E6EDF3;--muted:#7D8590;
  --border:rgba(255,255,255,.08);--card:rgba(22,27,34,.8);
  --r:18px;
}
html{scroll-behavior:smooth}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif;
     background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}
a{color:inherit;text-decoration:none}
/* ── ANIMATED BG ── */
body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:linear-gradient(rgba(0,214,143,.025) 1px,transparent 1px),
    linear-gradient(90deg,rgba(0,214,143,.025) 1px,transparent 1px);
  background-size:64px 64px}
.orb{position:fixed;border-radius:50%;filter:blur(100px);pointer-events:none;z-index:0}
.orb1{width:700px;height:700px;top:-200px;right:-150px;
  background:radial-gradient(circle,rgba(0,214,143,.12),transparent 70%);
  animation:orbFloat1 16s ease-in-out infinite}
.orb2{width:600px;height:600px;bottom:-200px;left:-150px;
  background:radial-gradient(circle,rgba(59,130,246,.1),transparent 70%);
  animation:orbFloat2 20s ease-in-out infinite}
.orb3{width:400px;height:400px;top:50%;left:40%;
  background:radial-gradient(circle,rgba(139,92,246,.07),transparent 70%);
  animation:orbFloat1 24s ease-in-out infinite reverse}
@keyframes orbFloat1{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(-40px,30px) scale(1.1)}66%{transform:translate(20px,-20px) scale(.9)}}
@keyframes orbFloat2{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(40px,-30px) scale(1.05)}}
/* ── NAV ── */
nav{position:sticky;top:0;z-index:100;
  background:rgba(3,7,18,.8);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border)}
.nav-in{max-width:1160px;margin:0 auto;padding:0 24px;height:64px;
  display:flex;align-items:center;gap:16px}
.logo{font-size:20px;font-weight:900;letter-spacing:-.5px;margin-right:auto;
  background:linear-gradient(135deg,var(--green),var(--blue));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.nav-links{display:flex;align-items:center;gap:8px}
.btn-ghost{padding:8px 16px;border-radius:10px;font-size:14px;font-weight:600;
  color:var(--muted);transition:all .2s}
.btn-ghost:hover{color:var(--text);background:rgba(255,255,255,.06)}
.btn-nav{padding:9px 20px;border-radius:10px;font-size:14px;font-weight:700;
  background:var(--green);color:#030712;transition:all .2s}
.btn-nav:hover{background:var(--green-dark);box-shadow:0 0 20px rgba(0,214,143,.3)}
/* ── HERO ── */
.hero{position:relative;z-index:1;
  max-width:1160px;margin:0 auto;padding:100px 24px 80px;
  display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
.hero-left{animation:heroIn .9s cubic-bezier(.22,1,.36,1) both}
@keyframes heroIn{from{opacity:0;transform:translateY(40px)}to{opacity:1;transform:translateY(0)}}
.hero-badge{display:inline-flex;align-items:center;gap:6px;
  background:var(--green-dim);color:var(--green);border:1px solid rgba(0,214,143,.3);
  border-radius:999px;padding:6px 14px;font-size:13px;font-weight:700;margin-bottom:20px}
.hero-badge .dot{width:6px;height:6px;border-radius:50%;background:var(--green);
  animation:pulse-dot 2s ease infinite}
@keyframes pulse-dot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.8)}}
h1{font-size:clamp(38px,5vw,62px);font-weight:900;line-height:1.08;letter-spacing:-2px;margin-bottom:20px}
.grad{background:linear-gradient(135deg,var(--green) 0%,var(--blue) 60%,var(--purple) 100%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{font-size:18px;color:var(--muted);max-width:500px;margin-bottom:36px;line-height:1.7}
.hero-btns{display:flex;gap:12px;flex-wrap:wrap}
.btn-primary{display:inline-flex;align-items:center;gap:8px;
  padding:14px 28px;border-radius:14px;font-size:16px;font-weight:800;
  background:linear-gradient(135deg,var(--green),var(--green-dark));color:#030712;
  position:relative;overflow:hidden;transition:all .25s}
.btn-primary::after{content:'';position:absolute;inset:0;
  background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,.35) 50%,transparent 60%);
  background-size:200% 100%;animation:shimmer 3s infinite}
@keyframes shimmer{0%{background-position:-200% center}100%{background-position:200% center}}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(0,214,143,.35)}
.btn-outline{display:inline-flex;align-items:center;gap:8px;
  padding:14px 28px;border-radius:14px;font-size:16px;font-weight:700;
  border:1.5px solid var(--border);color:var(--text);background:transparent;transition:all .25s}
.btn-outline:hover{border-color:rgba(0,214,143,.4);color:var(--green);background:var(--green-dim)}
/* ── HERO MOCK ── */
.hero-right{position:relative;z-index:1;animation:heroIn .9s .2s cubic-bezier(.22,1,.36,1) both}
.mock-browser{background:var(--bg3);border-radius:var(--r);
  border:1px solid rgba(0,214,143,.15);
  box-shadow:0 24px 80px rgba(0,0,0,.6),0 0 0 1px rgba(0,214,143,.1);
  animation:float 7s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0) rotate(-.5deg)}50%{transform:translateY(-14px) rotate(.5deg)}}
.mock-bar{padding:12px 16px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:8px}
.dot-r{width:12px;height:12px;border-radius:50%;background:#FF5F57}
.dot-y{width:12px;height:12px;border-radius:50%;background:#FFBD2E}
.dot-g{width:12px;height:12px;border-radius:50%;background:#28C840}
.mock-url{background:rgba(255,255,255,.05);border-radius:6px;
  padding:4px 12px;font-size:12px;color:var(--muted);margin-left:8px}
.mock-body{padding:24px 20px;min-height:180px;position:relative}
.mock-lines{display:flex;flex-direction:column;gap:8px;opacity:.4}
.ml{height:10px;border-radius:5px;background:rgba(255,255,255,.15)}
.ml.w80{width:80%}.ml.w60{width:60%}.ml.w40{width:40%}.ml.w90{width:90%}
.mock-chat-wrap{position:absolute;bottom:16px;right:16px;
  display:flex;flex-direction:column;align-items:flex-end;gap:8px}
.mock-bot-msg{background:var(--bg2);border:1px solid var(--border);
  border-radius:14px 14px 4px 14px;padding:10px 14px;font-size:13px;color:var(--text);
  max-width:200px;box-shadow:0 4px 16px rgba(0,0,0,.3)}
.mock-user-msg{background:linear-gradient(135deg,var(--green),var(--green-dark));
  color:#030712;border-radius:14px 14px 14px 4px;padding:10px 14px;font-size:13px;font-weight:600;
  max-width:160px}
.mock-fab{width:48px;height:48px;border-radius:50%;
  background:linear-gradient(135deg,var(--green),var(--green-dark));
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 4px 20px rgba(0,214,143,.5),0 0 0 4px rgba(0,214,143,.1);
  animation:pulse-fab 3s ease infinite}
@keyframes pulse-fab{0%,100%{box-shadow:0 4px 20px rgba(0,214,143,.5),0 0 0 4px rgba(0,214,143,.1)}50%{box-shadow:0 4px 30px rgba(0,214,143,.7),0 0 0 8px rgba(0,214,143,.06)}}
.mock-fab svg{fill:#030712;width:22px;height:22px}
/* ── STATS STRIP ── */
.stats-strip{position:relative;z-index:1;
  max-width:1160px;margin:0 auto;padding:0 24px 72px}
.stats-inner{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;
  background:var(--border);border-radius:var(--r);overflow:hidden;
  border:1px solid var(--border)}
.stat-item{background:var(--bg2);padding:28px;text-align:center}
.stat-num{font-size:36px;font-weight:900;
  background:linear-gradient(135deg,var(--green),var(--blue));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.stat-label{font-size:14px;color:var(--muted);margin-top:4px}
/* ── SECTION ── */
.section{position:relative;z-index:1;max-width:1160px;margin:0 auto;padding:0 24px 80px}
.sec-tag{text-align:center;font-size:12px;font-weight:800;letter-spacing:.1em;
  text-transform:uppercase;color:var(--green);margin-bottom:10px}
.sec-title{text-align:center;font-size:clamp(28px,3.5vw,44px);font-weight:900;
  letter-spacing:-1px;margin-bottom:12px}
.sec-sub{text-align:center;color:var(--muted);font-size:16px;
  max-width:520px;margin:0 auto 48px;line-height:1.7}
/* ── GLASS CARD ── */
.glass{background:var(--card);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border:1px solid var(--border);border-radius:var(--r);transition:all .3s}
.glass:hover{border-color:rgba(0,214,143,.25);
  box-shadow:0 8px 32px rgba(0,0,0,.4),0 0 0 1px rgba(0,214,143,.08)}
/* ── FEATURES ── */
.grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.feat-card{padding:28px;border-radius:var(--r)}
.feat-icon{width:48px;height:48px;border-radius:14px;
  display:flex;align-items:center;justify-content:center;font-size:22px;
  margin-bottom:16px;background:rgba(0,214,143,.1);border:1px solid rgba(0,214,143,.2)}
.feat-card h3{font-size:17px;font-weight:700;margin-bottom:8px}
.feat-card p{color:var(--muted);font-size:14px;line-height:1.6}
/* ── HOW IT WORKS ── */
.steps-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;position:relative}
.steps-grid::before{content:'';position:absolute;top:44px;left:calc(16.67% + 22px);
  right:calc(16.67% + 22px);height:1px;
  background:linear-gradient(90deg,transparent,rgba(0,214,143,.3),rgba(0,214,143,.3),transparent)}
.step-card{padding:28px;border-radius:var(--r);text-align:center}
.step-num{width:44px;height:44px;border-radius:50%;margin:0 auto 16px;
  display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:900;
  background:linear-gradient(135deg,var(--green),var(--green-dark));color:#030712}
.step-card h3{font-size:17px;font-weight:700;margin-bottom:8px}
.step-card p{color:var(--muted);font-size:14px;line-height:1.6}
/* ── PROVIDERS ── */
.provider-row{display:flex;gap:12px;flex-wrap:wrap;justify-content:center;margin-top:28px}
.p-card{background:var(--card);border:1px solid var(--border);border-radius:14px;
  padding:16px 24px;font-weight:700;font-size:15px;
  transition:all .3s;cursor:default}
.p-card:hover{border-color:rgba(0,214,143,.35);color:var(--green);
  background:var(--green-dim)}
.p-card.rec{border-color:rgba(0,214,143,.3);color:var(--green);background:var(--green-dim)}
.p-card .tag{font-size:11px;color:var(--muted);font-weight:400;margin-left:4px}
/* ── CTA ── */
.cta-wrap{position:relative;z-index:1;max-width:1160px;margin:0 auto;padding:0 24px 100px}
.cta-card{border-radius:24px;padding:72px 48px;text-align:center;position:relative;overflow:hidden;
  background:linear-gradient(135deg,rgba(0,214,143,.05),rgba(59,130,246,.05));
  border:1px solid rgba(0,214,143,.2)}
.cta-card::before{content:'';position:absolute;inset:-1px;border-radius:25px;
  background:linear-gradient(135deg,rgba(0,214,143,.3),transparent 50%,rgba(59,130,246,.2));
  mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
  mask-composite:exclude;padding:1px;pointer-events:none}
.cta-card h2{font-size:clamp(32px,5vw,52px);font-weight:900;letter-spacing:-1.5px;margin-bottom:12px}
.cta-card p{font-size:18px;color:var(--muted);margin-bottom:36px}
/* ── REVEAL ANIMATION ── */
.reveal{opacity:0;transform:translateY(30px);
  transition:all .8s cubic-bezier(.22,1,.36,1)}
.reveal.visible{opacity:1;transform:translateY(0)}
.d1{transition-delay:.1s}.d2{transition-delay:.2s}.d3{transition-delay:.3s}
.d4{transition-delay:.4s}.d5{transition-delay:.5s}.d6{transition-delay:.6s}
/* ── FOOTER ── */
footer{position:relative;z-index:1;border-top:1px solid var(--border);
  padding:28px 24px;text-align:center;color:var(--muted);font-size:13px}
footer a{color:var(--green)}footer a:hover{text-decoration:underline}
@media(max-width:900px){
  .hero{grid-template-columns:1fr;padding:60px 20px 50px}
  .hero-right{order:-1}.mock-browser{max-width:360px;margin:0 auto}
  .grid3,.steps-grid,.stats-inner{grid-template-columns:1fr}
  .steps-grid::before{display:none}
  .cta-card{padding:48px 24px}
}
</style>
</head>
<body>

<div class="orb orb1"></div>
<div class="orb orb2"></div>
<div class="orb orb3"></div>

<nav>
  <div class="nav-in">
    <a class="logo" href="/">ChatPopup.AI</a>
    <div class="nav-links">
      <a class="btn-ghost" href="#features">Fitur</a>
      <a class="btn-ghost" href="#how">Cara Kerja</a>
      <a class="btn-ghost" href="/login.php">Masuk</a>
      <a class="btn-nav" href="/register.php">Daftar Gratis</a>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="hero-left">
    <div class="hero-badge"><span class="dot"></span> Sekarang dalam tahap Trial Gratis</div>
    <h1>Pasang <span class="grad">Asisten AI</span><br>di Website<br>dalam 5 Menit</h1>
    <p>Satu baris kode. Semua model AI. Warna dan persona bot sesuai brand Anda — diatur dari dashboard tanpa sentuh database.</p>
    <div class="hero-btns">
      <a class="btn-primary" href="/register.php">Mulai Gratis →</a>
      <a class="btn-outline" href="/login.php">Sudah punya akun</a>
    </div>
  </div>
  <div class="hero-right">
    <div class="mock-browser">
      <div class="mock-bar">
        <div class="dot-r"></div><div class="dot-y"></div><div class="dot-g"></div>
        <div class="mock-url">https://website-anda.com</div>
      </div>
      <div class="mock-body">
        <div class="mock-lines">
          <div class="ml w80"></div><div class="ml w60"></div>
          <div class="ml w90"></div><div class="ml w40"></div>
          <div style="height:8px"></div>
          <div class="ml w70"></div><div class="ml w55"></div>
        </div>
        <div class="mock-chat-wrap">
          <div class="mock-bot-msg">Halo! Ada yang bisa saya bantu? 😊</div>
          <div class="mock-user-msg">Saya mau tanya produk ini</div>
          <div class="mock-fab">
            <svg viewBox="0 0 24 24"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/></svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="stats-strip">
  <div class="stats-inner">
    <div class="stat-item">
      <div class="stat-num" data-count="5" data-suffix=" menit">5 menit</div>
      <div class="stat-label">Waktu setup rata-rata</div>
    </div>
    <div class="stat-item">
      <div class="stat-num" data-count="4" data-suffix=" provider">4 provider</div>
      <div class="stat-label">AI didukung penuh</div>
    </div>
    <div class="stat-item">
      <div class="stat-num" data-count="1" data-suffix=" baris kode">1 baris</div>
      <div class="stat-label">Untuk pasang widget</div>
    </div>
  </div>
</div>

<section class="section" id="features">
  <div class="sec-tag reveal">✦ Fitur Lengkap</div>
  <h2 class="sec-title reveal d1">Semua yang Anda butuhkan,<br>siap dari hari pertama</h2>
  <p class="sec-sub reveal d2">Dari setup hingga percakapan AI berkonteks, semuanya bisa diatur sendiri dari dashboard.</p>
  <div class="grid3">
    <div class="glass feat-card reveal d1">
      <div class="feat-icon">🚀</div>
      <h3>Pasang dalam 5 Menit</h3>
      <p>Copy satu baris script dari dashboard, tempel ke website. Widget langsung muncul di semua halaman.</p>
    </div>
    <div class="glass feat-card reveal d2">
      <div class="feat-icon">🤖</div>
      <h3>Multi-Provider AI</h3>
      <p>OpenRouter, OpenAI, Gemini, DeepSeek. Ganti provider kapan saja cukup dari dropdown dashboard.</p>
    </div>
    <div class="glass feat-card reveal d3">
      <div class="feat-icon">🎨</div>
      <h3>Fully Customizable</h3>
      <p>Warna primary, nama bot, welcome message, system prompt — semua ikuti brand Anda.</p>
    </div>
    <div class="glass feat-card reveal d4">
      <div class="feat-icon">🧠</div>
      <h3>Memori Percakapan</h3>
      <p>AI mengingat konteks satu sesi. Pengunjung bisa lanjut chat tanpa mengulang dari awal.</p>
    </div>
    <div class="glass feat-card reveal d5">
      <div class="feat-icon">📱</div>
      <h3>Notifikasi Telegram</h3>
      <p>Tahu langsung setiap ada pesan masuk lewat bot Telegram — tanpa perlu buka dashboard.</p>
    </div>
    <div class="glass feat-card reveal d6">
      <div class="feat-icon">🔒</div>
      <h3>Aman &amp; Terisolasi</h3>
      <p>Shadow DOM melindungi CSS widget. API key AI dienkripsi AES-256-GCM di database.</p>
    </div>
  </div>
</section>

<section class="section" id="how">
  <div class="sec-tag reveal">✦ Cara Kerja</div>
  <h2 class="sec-title reveal d1">Tiga langkah, widget AI aktif</h2>
  <p class="sec-sub reveal d2">Tidak perlu keahlian teknis. Kalau bisa copy-paste, Anda bisa pakai ini.</p>
  <div class="steps-grid">
    <div class="glass step-card reveal d1">
      <div class="step-num">1</div>
      <h3>Daftar &amp; Atur</h3>
      <p>Pilih provider AI, masukkan API key, tulis system prompt sesuai bisnis Anda dari dashboard.</p>
    </div>
    <div class="glass step-card reveal d2">
      <div class="step-num">2</div>
      <h3>Copy Kode Embed</h3>
      <p>Dashboard otomatis menghasilkan satu baris script unik. Satu klik, tersalin ke clipboard.</p>
    </div>
    <div class="glass step-card reveal d3">
      <div class="step-num">3</div>
      <h3>Tempel ke Website</h3>
      <p>Paste sebelum <code style="background:rgba(0,214,143,.1);color:var(--green);padding:2px 6px;border-radius:4px">&lt;/body&gt;</code> di WordPress atau landing page. Selesai!</p>
    </div>
  </div>
</section>

<section class="section">
  <div class="sec-tag reveal">✦ Provider AI</div>
  <h2 class="sec-title reveal d1">Pilih otak AI terbaik untuk Anda</h2>
  <div class="provider-row">
    <div class="p-card rec reveal d1">🌐 OpenRouter <span class="tag">Rekomendasi — akses semua model</span></div>
    <div class="p-card reveal d2">⚡ OpenAI <span class="tag">GPT-4o, GPT-4o-mini</span></div>
    <div class="p-card reveal d3">💎 Google Gemini <span class="tag">1.5 Flash, 1.5 Pro</span></div>
    <div class="p-card reveal d4">🌊 DeepSeek <span class="tag">DeepSeek-Chat, Coder</span></div>
  </div>
</section>

<div class="cta-wrap">
  <div class="cta-card reveal">
    <h2>Siap memberi website Anda<br>otak <span class="grad">AI</span>?</h2>
    <p>Daftar gratis. Tidak perlu kartu kredit. Siap dalam hitungan menit.</p>
    <a class="btn-primary" href="/register.php" style="font-size:18px;padding:16px 36px">Buat Akun Gratis →</a>
  </div>
</div>

<footer>
  <p>&copy; <?= date('Y') ?> ChatPopup.AI · Dibangun di atas Hostinger PHP 8 ·
  <a href="/login.php">Login</a> · <a href="/register.php">Daftar</a></p>
</footer>

<script src="/js/animations.js"></script>
</body>
</html>
