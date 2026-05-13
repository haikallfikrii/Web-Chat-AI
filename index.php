<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';

if (current_user() !== null) { header('Location: /dashboard.php'); exit; }
$base = dashboard_base_url();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<title>ChatPopup.AI — Widget Asisten AI untuk Website Anda</title>
<meta name="description" content="Pasang widget chat AI di website dalam 5 menit. Multi-provider (OpenAI, Gemini, DeepSeek, OpenRouter), custom branding, memori percakapan.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<style>
/* ── HERO ─────────────────────────────────────────────────── */
.hero{
  position:relative;z-index:1;
  max-width:1180px;margin:0 auto;
  padding:88px 24px 72px;
  display:grid;grid-template-columns:1.05fr .95fr;gap:48px;align-items:center;
}
.hero-eyebrow{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 14px;border-radius:999px;
  background:var(--green-dim);color:var(--green);
  border:1px solid var(--green-line);
  font-size:12.5px;font-weight:700;letter-spacing:.2px;
  margin-bottom:22px;
  animation:fadeUp .8s cubic-bezier(.22,1,.36,1);
}
.hero-eyebrow .dot{
  width:6px;height:6px;border-radius:50%;background:var(--green);
  animation:pulseDot 2s infinite;
}
.hero h1{
  font-size:clamp(38px,5.5vw,68px);font-weight:900;line-height:1.06;letter-spacing:-2px;
  margin-bottom:22px;animation:fadeUp .9s .1s cubic-bezier(.22,1,.36,1) both;
}
.hero h1 .typed-wrap{display:inline-block;min-height:1em;color:var(--green)}
.hero h1 .typed-wrap::after{content:'|';color:var(--green);animation:caret 1s steps(1) infinite;margin-left:2px}
@keyframes caret{50%{opacity:0}}
.hero p{
  font-size:18px;color:var(--text-2);max-width:520px;margin-bottom:34px;line-height:1.7;
  animation:fadeUp 1s .2s cubic-bezier(.22,1,.36,1) both;
}
.hero-cta{display:flex;gap:12px;flex-wrap:wrap;animation:fadeUp 1.1s .3s cubic-bezier(.22,1,.36,1) both}
.hero-trust{
  display:flex;align-items:center;gap:18px;margin-top:34px;
  color:var(--muted);font-size:13px;flex-wrap:wrap;
  animation:fadeUp 1.2s .4s cubic-bezier(.22,1,.36,1) both;
}
.hero-trust > div{display:flex;align-items:center;gap:6px}
.hero-trust svg{color:var(--green);width:16px;height:16px}
@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}

/* ── HERO MOCK ─────────────────────────────────────────────── */
.mock-wrap{position:relative;animation:fadeUp 1s .3s cubic-bezier(.22,1,.36,1) both}
.mock{
  background:var(--bg-3);border-radius:var(--r-lg);overflow:hidden;
  border:1px solid var(--border-2);
  box-shadow:0 30px 80px rgba(0,0,0,.6),0 0 80px rgba(0,229,154,.08);
  animation:floatY 7s ease-in-out infinite;
}
@keyframes floatY{0%,100%{transform:translateY(0) rotate(-.4deg)}50%{transform:translateY(-14px) rotate(.4deg)}}
.mock-bar{padding:11px 14px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:7px}
.mock-bar .dot{width:11px;height:11px;border-radius:50%}
.mock-bar .dot.r{background:#FF5F57}
.mock-bar .dot.y{background:#FFBD2E}
.mock-bar .dot.g{background:#28C840}
.mock-bar .url{
  margin-left:8px;background:rgba(255,255,255,.05);border-radius:6px;
  padding:4px 12px;font-size:11.5px;color:var(--muted);font-family:'SF Mono',monospace;
}
.mock-body{position:relative;padding:24px 22px;min-height:240px}
.mock-skel{display:flex;flex-direction:column;gap:9px;opacity:.35}
.mock-skel .ln{height:9px;border-radius:5px;background:rgba(255,255,255,.18)}
.mock-skel .ln.w80{width:80%}.mock-skel .ln.w55{width:55%}
.mock-skel .ln.w70{width:70%}.mock-skel .ln.w40{width:40%}
.mock-skel .ln.w90{width:90%}
.mock-chat{position:absolute;bottom:18px;right:18px;
  display:flex;flex-direction:column;align-items:flex-end;gap:8px;z-index:10}
.mock-bubble.bot{
  background:var(--bg-2);border:1px solid var(--border-2);
  border-radius:14px 14px 4px 14px;padding:9px 13px;font-size:12.5px;color:var(--text);
  max-width:210px;box-shadow:0 4px 18px rgba(0,0,0,.4);
}
.mock-bubble.user{
  background:linear-gradient(135deg,var(--green),var(--green-2));
  color:#031018;border-radius:14px 14px 14px 4px;padding:9px 13px;font-size:12.5px;font-weight:600;
  max-width:170px;
}
.mock-fab{
  width:48px;height:48px;border-radius:50%;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--green),var(--green-2));
  box-shadow:0 4px 20px rgba(0,229,154,.55),0 0 0 4px rgba(0,229,154,.1);
  animation:pulseRing 3s ease infinite;
}
@keyframes pulseRing{0%,100%{box-shadow:0 4px 20px rgba(0,229,154,.55),0 0 0 4px rgba(0,229,154,.1)}50%{box-shadow:0 4px 28px rgba(0,229,154,.7),0 0 0 10px rgba(0,229,154,.05)}}
.mock-fab svg{color:#031018;width:22px;height:22px;stroke-width:2.5}

/* Floating accent cards on mock */
.float-card{
  position:absolute;backdrop-filter:blur(20px) saturate(140%);
  -webkit-backdrop-filter:blur(20px) saturate(140%);
  background:var(--glass-2);border:1px solid var(--border-2);
  border-radius:12px;padding:10px 14px;
  box-shadow:0 12px 32px rgba(0,0,0,.5);
  font-size:12px;font-weight:600;color:var(--text);
  display:flex;align-items:center;gap:8px;
  z-index:20;white-space:nowrap;
}
.float-card svg{width:16px;height:16px;color:var(--green)}
/* Geser ke kiri lebih jauh agar tidak tutupi chat bubbles di kanan */
.float-1{top:-18px;left:-18px;animation:floatA 5s ease-in-out infinite}
.float-2{bottom:-18px;right:-18px;animation:floatB 6s ease-in-out infinite}
/* Mock-wrap memerlukan overflow visible agar float-card tidak terpotong */
.mock-wrap{overflow:visible!important}
@keyframes floatA{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
@keyframes floatB{0%,100%{transform:translateY(0)}50%{transform:translateY(10px)}}

/* ── STATS ─────────────────────────────────────────────────── */
.stats{
  position:relative;z-index:1;max-width:1180px;margin:0 auto;padding:0 24px 80px;
}
.stats-inner{
  display:grid;grid-template-columns:repeat(3,1fr);
  border:1px solid var(--border-2);border-radius:var(--r-lg);overflow:hidden;
  background:var(--glass);backdrop-filter:blur(16px);
}
.stat{padding:28px;text-align:center;border-right:1px solid var(--border-2)}
.stat:last-child{border-right:none}
.stat-num{font-size:38px;font-weight:900;letter-spacing:-1px}
.stat-num span{
  background:linear-gradient(135deg,var(--green),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.stat-label{font-size:13.5px;color:var(--text-2);margin-top:6px;font-weight:500}

/* ── SECTION ───────────────────────────────────────────────── */
.section{position:relative;z-index:1;max-width:1180px;margin:0 auto;padding:0 24px 90px}
.sec-tag{
  display:flex;justify-content:center;align-items:center;gap:6px;
  font-size:11.5px;font-weight:800;letter-spacing:.18em;text-transform:uppercase;
  color:var(--green);margin-bottom:12px;
}
.sec-tag svg{width:14px;height:14px}
.sec-title{
  text-align:center;font-size:clamp(28px,3.8vw,46px);font-weight:900;
  letter-spacing:-1.2px;margin-bottom:14px;line-height:1.12;
}
.sec-sub{
  text-align:center;color:var(--text-2);font-size:16.5px;
  max-width:560px;margin:0 auto 52px;line-height:1.7;
}

/* ── FEATURES ──────────────────────────────────────────────── */
.feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.feat{padding:28px;border-radius:var(--r-lg)}
.feat-icon{
  width:48px;height:48px;border-radius:13px;display:grid;place-items:center;
  background:var(--green-dim);border:1px solid var(--green-line);
  color:var(--green);margin-bottom:18px;
}
.feat-icon svg{width:22px;height:22px}
.feat h3{font-size:17px;font-weight:700;margin-bottom:8px;color:var(--text)}
.feat p{color:var(--text-2);font-size:14px;line-height:1.65}

/* ── STEPS ─────────────────────────────────────────────────── */
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;position:relative}
.steps::before{
  content:'';position:absolute;top:46px;left:calc(16.67% + 30px);right:calc(16.67% + 30px);
  height:1px;background:linear-gradient(90deg,transparent,var(--green-line),var(--green-line),transparent);
}
.step{padding:28px;border-radius:var(--r-lg);text-align:center;position:relative;z-index:1;background:var(--glass-2)}
.step-num{
  width:46px;height:46px;border-radius:50%;margin:0 auto 18px;display:grid;place-items:center;
  font-size:18px;font-weight:900;color:#031018;
  background:linear-gradient(135deg,var(--green),var(--green-2));
  box-shadow:0 6px 18px rgba(0,229,154,.4);
}
.step h3{font-size:17px;font-weight:700;margin-bottom:8px;color:var(--text)}
.step p{color:var(--text-2);font-size:14px;line-height:1.65}

/* ── PROVIDERS ─────────────────────────────────────────────── */
.providers{display:flex;flex-wrap:wrap;gap:12px;justify-content:center}
.prov{
  padding:14px 22px;border-radius:13px;font-weight:700;font-size:14.5px;
  background:var(--glass);border:1px solid var(--border-2);color:var(--text);
  transition:all .25s;display:flex;align-items:center;gap:10px;
}
.prov:hover{border-color:var(--green-line);color:var(--green);background:var(--green-dim);transform:translateY(-2px)}
.prov-dot{width:8px;height:8px;border-radius:50%}
.prov.rec{border-color:var(--green-line);color:var(--green);background:var(--green-dim)}
.prov-tag{font-size:11.5px;color:var(--muted);font-weight:500}
.prov.rec .prov-tag{color:var(--green);opacity:.8}

/* ── CTA ───────────────────────────────────────────────────── */
.cta-wrap{position:relative;z-index:1;max-width:1180px;margin:0 auto;padding:0 24px 100px}
.cta{
  border-radius:24px;padding:72px 48px;text-align:center;position:relative;overflow:hidden;
  background:
    radial-gradient(ellipse at top,rgba(0,229,154,.08),transparent 60%),
    radial-gradient(ellipse at bottom right,rgba(59,130,246,.06),transparent 60%),
    var(--glass-2);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border:1px solid var(--green-line);
}
.cta::before{
  content:'';position:absolute;inset:-1px;border-radius:inherit;pointer-events:none;
  background:linear-gradient(135deg,rgba(0,229,154,.4),transparent 50%,rgba(59,130,246,.25));
  -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
  -webkit-mask-composite:xor;mask-composite:exclude;padding:1px;
}
.cta h2{font-size:clamp(32px,5vw,54px);font-weight:900;letter-spacing:-1.5px;margin-bottom:14px;line-height:1.1}
.cta p{font-size:18px;color:var(--text-2);margin-bottom:34px;max-width:520px;margin-left:auto;margin-right:auto}

/* ── FOOTER ────────────────────────────────────────────────── */
footer{
  position:relative;z-index:1;border-top:1px solid var(--border);
  padding:30px 24px;text-align:center;color:var(--muted);font-size:13px;
}
footer a{color:var(--green)}

/* ── RESPONSIVE ────────────────────────────────────────────── */
@media (max-width:900px){
  .hero{grid-template-columns:1fr;padding:60px 20px 50px;gap:36px}
  .mock-wrap{order:-1;max-width:420px;margin:0 auto}
  .feat-grid,.steps,.stats-inner{grid-template-columns:1fr!important}
  .stat{border-right:none;border-bottom:1px solid var(--border-2)}
  .stat:last-child{border-bottom:none}
  .steps::before{display:none}
  .cta{padding:48px 24px}
}
@media (max-width:520px){
  .hero h1{font-size:38px;letter-spacing:-1.5px}
  .hero p{font-size:16px}
  .float-card{display:none}
}
@media (max-width:400px){
  .hero{padding:48px 12px 40px}
  .hero-cta{flex-direction:column;width:100%;gap:10px}
  .hero-cta .btn{width:100%;justify-content:center}
  .stats{padding:0 12px 56px}
  .section{padding:0 12px 64px}
  .cta-wrap{padding:0 12px 72px}
}
</style>
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-noise"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<header class="nav">
  <div class="nav-in">
    <a href="/" class="brand">
      <span class="brand-mark"><?= icon('sparkles', 18) ?></span>
      <span class="brand-text">ChatPopup.AI</span>
    </a>
    <button class="nav-burger" id="navBurger" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="navLinks">
      <span class="nav-ico-menu"><?= icon('menu', 20) ?></span>
      <span class="nav-ico-close pw-hidden"><?= icon('x', 20) ?></span>
    </button>
    <nav class="nav-links" id="navLinks" aria-label="Navigasi utama">
      <a class="nav-link" href="#features">Fitur</a>
      <a class="nav-link" href="#how">Cara Kerja</a>
      <a class="nav-link" href="#providers">Provider</a>
      <a class="nav-link" href="/login.php">Masuk</a>
      <a class="nav-link btn btn-primary" href="/register.php">Daftar Gratis <?= icon('arrow-right', 14) ?></a>
    </nav>
  </div>
</header>
<div class="nav-backdrop" id="navBackdrop"></div>

<section class="hero">
  <div>
    <div class="hero-eyebrow">
      <span class="dot"></span> Trial Gratis · Tanpa Kartu Kredit
    </div>
    <h1>
      Asisten <span class="h-grad">AI</span><br>
      di website Anda<br>
      <span class="typed-wrap" id="typewriter" data-words="dalam 5 menit|tanpa coding|untuk siapa saja"></span>
    </h1>
    <p>
      Satu baris kode. Semua provider AI didukung.<br>
      Warna, persona, dan instruksi bot sesuai brand Anda — diatur dari dashboard, tanpa sentuh database.
    </p>
    <div class="hero-cta">
      <a class="btn btn-primary btn-lg" href="/register.php">
        Mulai Gratis <?= icon('arrow-right', 18) ?>
      </a>
      <a class="btn btn-outline btn-lg" href="/login.php">
        Sudah punya akun
      </a>
    </div>
    <div class="hero-trust">
      <div><?= icon('check', 16) ?> Setup 5 menit</div>
      <div><?= icon('check', 16) ?> Tanpa coding</div>
      <div><?= icon('check', 16) ?> Custom brand</div>
    </div>
  </div>

  <div class="mock-wrap">
    <div class="float-card float-1"><?= icon('zap', 16) ?> Pesan dijawab</div>
    <div class="float-card float-2"><?= icon('shield', 16) ?> Aman &amp; Encrypted</div>
    <div class="mock">
      <div class="mock-bar">
        <div class="dot r"></div><div class="dot y"></div><div class="dot g"></div>
        <div class="url">https://website-anda.com</div>
      </div>
      <div class="mock-body">
        <div class="mock-skel">
          <div class="ln w80"></div><div class="ln w55"></div>
          <div class="ln w90"></div><div class="ln w40"></div>
          <div style="height:8px"></div>
          <div class="ln w70"></div><div class="ln w55"></div>
          <div class="ln w90"></div>
        </div>
        <div class="mock-chat">
          <div class="mock-bubble bot">Halo! Ada yang bisa saya bantu hari ini?</div>
          <div class="mock-bubble user">Saya mau tanya produk ini</div>
          <div class="mock-fab"><?= icon('message', 20) ?></div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="stats">
  <div class="stats-inner">
    <div class="stat">
      <div class="stat-num"><span>5 menit</span></div>
      <div class="stat-label">Waktu setup rata-rata</div>
    </div>
    <div class="stat">
      <div class="stat-num"><span>4 provider</span></div>
      <div class="stat-label">AI didukung penuh</div>
    </div>
    <div class="stat">
      <div class="stat-num"><span>1 baris</span></div>
      <div class="stat-label">Untuk pasang widget</div>
    </div>
  </div>
</div>

<section class="section" id="features">
  <div class="sec-tag reveal"><?= icon('sparkles', 14) ?> Fitur Unggulan</div>
  <h2 class="sec-title reveal d1">Semua yang Anda butuhkan,<br>siap dari hari pertama</h2>
  <p class="sec-sub reveal d2">Dari setup hingga percakapan AI berkonteks, semuanya bisa diatur sendiri dari dashboard.</p>
  <div class="feat-grid">
    <div class="glass glass-hover feat reveal d1">
      <div class="feat-icon"><?= icon('rocket', 22) ?></div>
      <h3>Pasang dalam 5 Menit</h3>
      <p>Copy satu baris script dari dashboard, tempel ke website. Widget langsung muncul di semua halaman.</p>
    </div>
    <div class="glass glass-hover feat reveal d2">
      <div class="feat-icon"><?= icon('bot', 22) ?></div>
      <h3>Multi-Provider AI</h3>
      <p>OpenRouter, OpenAI, Gemini, DeepSeek. Ganti provider kapan saja cukup dari dropdown dashboard.</p>
    </div>
    <div class="glass glass-hover feat reveal d3">
      <div class="feat-icon"><?= icon('palette', 22) ?></div>
      <h3>Fully Customizable</h3>
      <p>Warna primary, nama bot, welcome message, system prompt — semua ikuti brand Anda.</p>
    </div>
    <div class="glass glass-hover feat reveal d4">
      <div class="feat-icon"><?= icon('brain', 22) ?></div>
      <h3>Memori Percakapan</h3>
      <p>AI mengingat konteks dalam satu sesi. Pengunjung tidak perlu mengulang dari awal.</p>
    </div>
    <div class="glass glass-hover feat reveal d5">
      <div class="feat-icon"><?= icon('phone', 22) ?></div>
      <h3>Notifikasi Telegram</h3>
      <p>Tahu langsung setiap ada pesan masuk lewat bot Telegram — tanpa perlu buka dashboard.</p>
    </div>
    <div class="glass glass-hover feat reveal d6">
      <div class="feat-icon"><?= icon('shield', 22) ?></div>
      <h3>Aman &amp; Terisolasi</h3>
      <p>Shadow DOM melindungi CSS widget. API key AI dienkripsi AES-256-GCM di database.</p>
    </div>
  </div>
</section>

<section class="section" id="how">
  <div class="sec-tag reveal"><?= icon('zap', 14) ?> Cara Kerja</div>
  <h2 class="sec-title reveal d1">Tiga langkah, widget AI aktif</h2>
  <p class="sec-sub reveal d2">Tidak perlu keahlian teknis. Kalau bisa copy-paste, Anda bisa pakai ini.</p>
  <div class="steps">
    <div class="glass step reveal d1">
      <div class="step-num">1</div>
      <h3>Daftar &amp; Atur</h3>
      <p>Pilih provider AI, masukkan API key, dan tulis system prompt sesuai bisnis Anda dari dashboard.</p>
    </div>
    <div class="glass step reveal d2">
      <div class="step-num">2</div>
      <h3>Copy Kode Embed</h3>
      <p>Dashboard otomatis menghasilkan satu baris script unik. Satu klik, tersalin ke clipboard.</p>
    </div>
    <div class="glass step reveal d3">
      <div class="step-num">3</div>
      <h3>Tempel ke Website</h3>
      <p>Paste sebelum <code style="background:var(--green-dim);color:var(--green);padding:2px 7px;border-radius:5px;font-size:.85em">&lt;/body&gt;</code> di WordPress atau landing page mana saja.</p>
    </div>
  </div>
</section>

<section class="section" id="providers">
  <div class="sec-tag reveal"><?= icon('brain', 14) ?> Provider AI</div>
  <h2 class="sec-title reveal d1">Pilih otak AI terbaik untuk Anda</h2>
  <p class="sec-sub reveal d2">Ganti provider kapan saja tanpa coding. Cocok dengan budget &amp; kebutuhan Anda.</p>
  <div class="providers">
    <div class="prov rec reveal d1">
      <span class="prov-dot" style="background:var(--green)"></span>
      OpenRouter <span class="prov-tag">Rekomendasi · akses semua model</span>
    </div>
    <div class="prov reveal d2">
      <span class="prov-dot" style="background:#10A37F"></span>
      OpenAI <span class="prov-tag">GPT-4o · GPT-4o-mini</span>
    </div>
    <div class="prov reveal d3">
      <span class="prov-dot" style="background:#4285F4"></span>
      Google Gemini <span class="prov-tag">1.5 Flash · 1.5 Pro</span>
    </div>
    <div class="prov reveal d4">
      <span class="prov-dot" style="background:#1F6FEB"></span>
      DeepSeek <span class="prov-tag">Chat · Coder</span>
    </div>
  </div>
</section>

<div class="cta-wrap">
  <div class="cta reveal">
    <h2>Saatnya beri website Anda<br>otak <span class="h-grad">AI</span></h2>
    <p>Daftar gratis. Tidak perlu kartu kredit. Siap dalam hitungan menit.</p>
    <a class="btn btn-primary btn-lg" href="/register.php">
      Buat Akun Gratis <?= icon('arrow-right', 18) ?>
    </a>
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> ChatPopup.AI · Dibangun di atas PHP 8 Hostinger ·
  <a href="/login.php">Login</a> · <a href="/register.php">Daftar</a>
</footer>

<script src="/js/animations.js"></script>
<script>
(function () {
  var btn = document.getElementById('navBurger');
  var menu = document.getElementById('navLinks');
  var bd = document.getElementById('navBackdrop');
  var icoM = btn && btn.querySelector('.nav-ico-menu');
  var icoX = btn && btn.querySelector('.nav-ico-close');
  if (!btn || !menu) return;

  function setOpen(open) {
    menu.classList.toggle('open', open);
    if (bd) {
      bd.classList.toggle('is-open', open);
    }
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    document.body.style.overflow = open ? 'hidden' : '';
    if (icoM && icoX) {
      /* Gunakan class bukan hidden attr, karena display:grid di CSS bisa override */
      icoM.classList.toggle('pw-hidden', !!open);
      icoX.classList.toggle('pw-hidden', !open);
    }
  }

  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    setOpen(!menu.classList.contains('open'));
  });
  if (bd) {
    bd.addEventListener('click', function () { setOpen(false); });
  }
  menu.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', function () { setOpen(false); });
  });
  window.addEventListener('resize', function () {
    if (window.innerWidth > 780) setOpen(false);
  });
})();
</script>
</body>
</html>
