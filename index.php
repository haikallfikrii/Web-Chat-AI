<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/lang.php';

if (current_user() !== null) { header('Location: /dashboard.php'); exit; }

$lang = get_lang();
$t    = lang_strings($lang);

/* Language meta */
$langNames = [
    'en' => ['flag'=>'🇺🇸','label'=>'English'],
    'id' => ['flag'=>'🇮🇩','label'=>'Indonesia'],
    'es' => ['flag'=>'🇪🇸','label'=>'Español'],
    'fr' => ['flag'=>'🇫🇷','label'=>'Français'],
    'pt' => ['flag'=>'🇧🇷','label'=>'Português'],
    'ja' => ['flag'=>'🇯🇵','label'=>'日本語'],
];
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="<?= e($t['html_lang']) ?>" dir="<?= e($t['dir']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<title><?= e($t['page_title']) ?></title>
<meta name="description" content="<?= e($t['page_desc']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<style>
/* ════════════════════════════════════════════════════════════
   LANDING PAGE — v3  (i18n + rich scroll animations)
════════════════════════════════════════════════════════════ */

/* ── SCROLL PROGRESS BAR ── */
#scroll-bar{
  position:fixed;top:0;left:0;z-index:999;
  height:3px;width:0%;
  background:linear-gradient(90deg,var(--green),var(--cyan),var(--blue));
  transition:width .1s linear;
  pointer-events:none;
}

/* ── LANGUAGE SWITCHER ── */
.lang-wrap{position:relative}
.lang-btn{
  display:flex;align-items:center;gap:6px;
  padding:7px 12px;border-radius:9px;border:1px solid var(--border-2);
  background:transparent;color:var(--text-2);font-size:13px;font-weight:600;
  cursor:pointer;font-family:inherit;transition:all .2s;
}
.lang-btn:hover{border-color:var(--green-line);color:var(--text);background:var(--green-dim)}
.lang-btn svg{width:14px;height:14px;transition:transform .25s}
.lang-wrap.open .lang-btn svg{transform:rotate(180deg)}
.lang-flag{font-size:15px;line-height:1}
.lang-drop{
  position:absolute;top:calc(100% + 6px);right:0;
  min-width:160px;
  background:var(--bg-3);border:1px solid var(--border-2);border-radius:12px;
  padding:6px;
  box-shadow:0 16px 48px rgba(0,0,0,.5);
  opacity:0;visibility:hidden;transform:translateY(-6px);
  transition:all .2s cubic-bezier(.22,1,.36,1);
  z-index:200;
}
.lang-wrap.open .lang-drop{opacity:1;visibility:visible;transform:translateY(0)}
.lang-opt{
  display:flex;align-items:center;gap:9px;
  padding:9px 12px;border-radius:8px;font-size:13.5px;font-weight:500;
  color:var(--text-2);cursor:pointer;transition:all .15s;text-decoration:none;
}
.lang-opt:hover{background:rgba(255,255,255,.06);color:var(--text)}
.lang-opt.active{color:var(--green);background:var(--green-dim)}
.lang-opt-flag{font-size:16px;line-height:1;width:20px;text-align:center}

/* ── HERO ── */
.hero{
  position:relative;z-index:1;
  max-width:1200px;margin:0 auto;
  padding:100px 28px 80px;
  display:grid;grid-template-columns:1.1fr 0.9fr;gap:56px;align-items:center;
}
.hero-eyebrow{
  display:inline-flex;align-items:center;gap:8px;
  padding:7px 16px;border-radius:999px;
  background:var(--green-dim);color:var(--green);
  border:1px solid var(--green-line);
  font-size:12.5px;font-weight:700;letter-spacing:.2px;
  margin-bottom:24px;
  animation:fadeUp .7s cubic-bezier(.22,1,.36,1) both;
}
.hero-eyebrow .dot{width:6px;height:6px;border-radius:50%;background:var(--green);animation:pulseDot 2s infinite}
.hero h1{
  font-size:clamp(40px,5.5vw,72px);font-weight:900;line-height:1.04;letter-spacing:-2.5px;
  margin-bottom:24px;animation:fadeUp .85s .08s cubic-bezier(.22,1,.36,1) both;
}
.hero h1 .grad{
  background:linear-gradient(120deg,var(--green) 0%,var(--cyan) 55%,#818cf8 100%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.typed-wrap{display:inline-block;min-height:1.04em;color:var(--green)}
.typed-wrap::after{content:'|';color:var(--green);animation:caret 1s steps(1) infinite;margin-left:2px}
@keyframes caret{50%{opacity:0}}
.hero p{
  font-size:18px;color:var(--text-2);max-width:510px;margin-bottom:36px;line-height:1.75;
  animation:fadeUp 1s .16s cubic-bezier(.22,1,.36,1) both;
}
.hero-cta{display:flex;gap:12px;flex-wrap:wrap;animation:fadeUp 1.1s .24s cubic-bezier(.22,1,.36,1) both}
.hero-trust{
  display:flex;align-items:center;gap:20px;margin-top:36px;
  color:var(--muted);font-size:13px;flex-wrap:wrap;
  animation:fadeUp 1.2s .32s cubic-bezier(.22,1,.36,1) both;
}
.hero-trust > div{display:flex;align-items:center;gap:6px}
.hero-trust svg{color:var(--green);width:15px;height:15px}
@keyframes fadeUp{from{opacity:0;transform:translateY(32px)}to{opacity:1;transform:translateY(0)}}

/* ── HERO MOCK ── */
.mock-wrap{
  position:relative;
  animation:fadeUp 1s .3s cubic-bezier(.22,1,.36,1) both;
  perspective:1000px;
}
.mock-tilt{
  transform-style:preserve-3d;
  transition:transform .15s ease-out;
  will-change:transform;
}
.mock{
  background:var(--bg-3);border-radius:var(--r-lg);overflow:hidden;
  border:1px solid var(--border-2);
  box-shadow:0 32px 80px rgba(0,0,0,.65),0 0 80px rgba(0,229,154,.08);
  animation:floatY 7s ease-in-out infinite;
}
@keyframes floatY{0%,100%{transform:translateY(0) rotate(-.3deg)}50%{transform:translateY(-14px) rotate(.3deg)}}
.mock-bar{padding:11px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:7px}
.mock-bar .dot{width:11px;height:11px;border-radius:50%}
.mock-bar .dot.r{background:#FF5F57}.mock-bar .dot.y{background:#FFBD2E}.mock-bar .dot.g{background:#28C840}
.mock-bar .url{margin-left:8px;background:rgba(255,255,255,.05);border-radius:6px;padding:4px 12px;font-size:11.5px;color:var(--muted);font-family:'SF Mono',monospace}
.mock-body{position:relative;padding:24px 22px;min-height:250px}
.mock-skel{display:flex;flex-direction:column;gap:9px;opacity:.3}
.mock-skel .ln{height:9px;border-radius:5px;background:rgba(255,255,255,.18)}
.mock-skel .ln.w80{width:80%}.mock-skel .ln.w55{width:55%}.mock-skel .ln.w70{width:70%}.mock-skel .ln.w40{width:40%}.mock-skel .ln.w90{width:90%}
.mock-chat{position:absolute;bottom:18px;right:18px;display:flex;flex-direction:column;align-items:flex-end;gap:8px;z-index:10}
.mock-bubble.bot{background:var(--bg-2);border:1px solid var(--border-2);border-radius:14px 14px 4px 14px;padding:9px 13px;font-size:12.5px;color:var(--text);max-width:210px;box-shadow:0 4px 18px rgba(0,0,0,.4)}
.mock-bubble.user{background:linear-gradient(135deg,var(--green),var(--green-2));color:#031018;border-radius:14px 14px 14px 4px;padding:9px 13px;font-size:12.5px;font-weight:600;max-width:170px}
.mock-fab{width:48px;height:48px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,var(--green),var(--green-2));box-shadow:0 4px 20px rgba(0,229,154,.55),0 0 0 4px rgba(0,229,154,.1);animation:pulseRing 3s ease infinite}
@keyframes pulseRing{0%,100%{box-shadow:0 4px 20px rgba(0,229,154,.55),0 0 0 4px rgba(0,229,154,.1)}50%{box-shadow:0 4px 28px rgba(0,229,154,.7),0 0 0 10px rgba(0,229,154,.05)}}
.mock-fab svg{color:#031018;width:22px;height:22px;stroke-width:2.5}
.float-card{
  position:absolute;backdrop-filter:blur(20px) saturate(140%);-webkit-backdrop-filter:blur(20px) saturate(140%);
  background:var(--glass-2);border:1px solid var(--border-2);border-radius:12px;
  padding:10px 14px;box-shadow:0 12px 32px rgba(0,0,0,.5);
  font-size:12px;font-weight:600;color:var(--text);
  display:flex;align-items:center;gap:8px;z-index:20;white-space:nowrap;
}
.float-card svg{width:16px;height:16px;color:var(--green)}
.float-1{top:-18px;left:-18px;animation:floatA 5s ease-in-out infinite}
.float-2{bottom:-18px;right:-18px;animation:floatB 6s ease-in-out infinite}
.mock-wrap{overflow:visible!important}
@keyframes floatA{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
@keyframes floatB{0%,100%{transform:translateY(0)}50%{transform:translateY(10px)}}

/* ── TICKER ── */
.ticker-wrap{
  overflow:hidden;border-top:1px solid var(--border);border-bottom:1px solid var(--border);
  padding:14px 0;margin:0 0 0;position:relative;z-index:1;background:rgba(3,7,18,.6);
}
.ticker-inner{
  display:flex;gap:48px;width:max-content;
  animation:tickerMove 28s linear infinite;
}
.ticker-inner:hover{animation-play-state:paused}
@keyframes tickerMove{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.ticker-item{
  display:flex;align-items:center;gap:10px;
  font-size:13px;font-weight:600;color:var(--muted);white-space:nowrap;
}
.ticker-item svg{width:14px;height:14px;color:var(--green);flex-shrink:0}

/* ── STATS ── */
.stats{position:relative;z-index:1;max-width:1200px;margin:0 auto;padding:60px 28px 80px}
.stats-inner{
  display:grid;grid-template-columns:repeat(4,1fr);
  border:1px solid var(--border-2);border-radius:var(--r-xl);overflow:hidden;
  background:var(--glass);backdrop-filter:blur(20px);
}
.stat{padding:30px 24px;text-align:center;border-right:1px solid var(--border-2);transition:background .3s}
.stat:hover{background:rgba(0,229,154,.04)}
.stat:last-child{border-right:none}
.stat-num{font-size:40px;font-weight:900;letter-spacing:-1px;line-height:1}
.stat-num span{
  background:linear-gradient(135deg,var(--green),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.stat-label{font-size:13px;color:var(--text-2);margin-top:8px;font-weight:500}

/* ── SECTIONS SHARED ── */
.lp-section{position:relative;z-index:1;max-width:1200px;margin:0 auto;padding:0 28px 100px}
.sec-tag{display:flex;justify-content:center;align-items:center;gap:7px;font-size:11.5px;font-weight:800;letter-spacing:.2em;text-transform:uppercase;color:var(--green);margin-bottom:14px}
.sec-tag svg{width:14px;height:14px}
.sec-h2{text-align:center;font-size:clamp(28px,4vw,48px);font-weight:900;letter-spacing:-1.5px;margin-bottom:16px;line-height:1.1;white-space:pre-line}
.sec-sub{text-align:center;color:var(--text-2);font-size:16.5px;max-width:560px;margin:0 auto 60px;line-height:1.75}

/* ── FEATURES (bento grid) ── */
.feat-bento{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  grid-template-rows:auto;
  gap:16px;
}
.feat-card{
  padding:30px 26px;border-radius:20px;
  background:rgba(10,16,28,.8);
  border:1px solid var(--border-2);
  transition:border-color .3s,transform .4s cubic-bezier(.22,1,.36,1),box-shadow .4s;
  cursor:default;
  position:relative;overflow:hidden;
}
.feat-card::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(circle at 70% 20%,rgba(0,229,154,.06),transparent 60%);
  opacity:0;transition:opacity .4s;
}
.feat-card:hover{
  border-color:rgba(0,229,154,.35);
  transform:translateY(-6px) scale(1.015);
  box-shadow:0 24px 60px rgba(0,0,0,.4),0 0 0 1px rgba(0,229,154,.15);
}
.feat-card:hover::before{opacity:1}
.feat-card.wide{grid-column:span 2}
.feat-icon{
  width:52px;height:52px;border-radius:14px;display:grid;place-items:center;
  background:var(--green-dim);border:1px solid var(--green-line);
  color:var(--green);margin-bottom:20px;transition:transform .3s;
}
.feat-card:hover .feat-icon{transform:scale(1.12) rotate(-4deg)}
.feat-icon svg{width:24px;height:24px}
.feat-card h3{font-size:17.5px;font-weight:700;margin-bottom:10px;color:var(--text)}
.feat-card p{color:var(--text-2);font-size:14px;line-height:1.7}
/* Animated line accent on left */
.feat-card::after{
  content:'';position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:3px 0 0 3px;
  background:linear-gradient(180deg,var(--green),var(--cyan));
  transform:scaleY(0);transform-origin:top;transition:transform .4s cubic-bezier(.22,1,.36,1);
}
.feat-card:hover::after{transform:scaleY(1)}

/* ── STEPS ── */
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;position:relative}
.steps::before{content:'';position:absolute;top:52px;left:calc(16.67% + 36px);right:calc(16.67% + 36px);height:1px;background:linear-gradient(90deg,transparent,var(--green-line),var(--green-line),transparent)}
.step-card{
  padding:32px 26px;border-radius:20px;text-align:center;position:relative;z-index:1;
  background:rgba(10,16,28,.85);border:1px solid var(--border-2);
  transition:border-color .3s,transform .35s cubic-bezier(.22,1,.36,1),box-shadow .35s;
}
.step-card:hover{border-color:var(--green-line);transform:translateY(-8px);box-shadow:0 28px 64px rgba(0,0,0,.4),0 0 30px rgba(0,229,154,.08)}
.step-num{width:52px;height:52px;border-radius:50%;margin:0 auto 20px;display:grid;place-items:center;font-size:20px;font-weight:900;color:#031018;background:linear-gradient(135deg,var(--green),var(--green-2));box-shadow:0 6px 20px rgba(0,229,154,.4);transition:transform .3s}
.step-card:hover .step-num{transform:scale(1.1) rotate(8deg)}
.step-card h3{font-size:17px;font-weight:700;margin-bottom:10px;color:var(--text)}
.step-card p{color:var(--text-2);font-size:14px;line-height:1.7}

/* ── PROVIDERS GRID ── */
.prov-logos{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:16px;
}
.prov-logo{
  padding:28px 20px;border-radius:18px;
  background:rgba(10,16,28,.8);border:1px solid var(--border-2);
  display:flex;flex-direction:column;align-items:center;gap:14px;
  font-weight:700;font-size:15px;color:var(--text);
  transition:all .35s cubic-bezier(.22,1,.36,1);
  cursor:default;
}
.prov-logo:hover{border-color:var(--border-hover);transform:translateY(-6px);box-shadow:0 24px 56px rgba(0,0,0,.4),0 0 24px rgba(0,229,154,.08)}
.prov-logo-dot{width:44px;height:44px;border-radius:50%;display:grid;place-items:center;transition:transform .3s}
.prov-logo:hover .prov-logo-dot{transform:scale(1.15)}
.prov-logo-tag{font-size:12px;color:var(--muted);font-weight:500;text-align:center}
.prov-logo.rec{border-color:var(--green-line);background:rgba(0,229,154,.06)}
.prov-logo.rec .prov-logo-tag{color:var(--green);opacity:.9}

/* ── TESTIMONIALS ── */
.testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.testi-card{
  padding:28px;border-radius:20px;
  background:rgba(10,16,28,.85);border:1px solid var(--border-2);
  transition:border-color .3s,transform .35s cubic-bezier(.22,1,.36,1),box-shadow .35s;
}
.testi-card:hover{border-color:var(--green-line);transform:translateY(-6px);box-shadow:0 24px 56px rgba(0,0,0,.4)}
.testi-stars{color:var(--green);font-size:14px;letter-spacing:2px;margin-bottom:16px}
.testi-body{font-size:14.5px;color:var(--text-2);line-height:1.75;margin-bottom:20px;font-style:italic}
.testi-author{display:flex;align-items:center;gap:12px}
.testi-avatar{
  width:40px;height:40px;border-radius:50%;flex-shrink:0;
  background:var(--green-dim);border:1.5px solid var(--green-line);
  display:grid;place-items:center;font-size:16px;font-weight:800;color:var(--green);
}
.testi-name{font-size:14px;font-weight:700;color:var(--text)}
.testi-role{font-size:12px;color:var(--muted);margin-top:1px}

/* ── CTA ── */
.cta-section{position:relative;z-index:1;max-width:1200px;margin:0 auto;padding:0 28px 100px}
.cta-box{
  border-radius:28px;padding:80px 56px;text-align:center;position:relative;overflow:hidden;
  background:radial-gradient(ellipse at 30% 0%,rgba(0,229,154,.10),transparent 60%),
             radial-gradient(ellipse at 80% 100%,rgba(59,130,246,.08),transparent 60%),
             rgba(10,16,28,.9);
  border:1px solid var(--green-line);
}
.cta-box::before{
  content:'';position:absolute;inset:-1px;border-radius:inherit;pointer-events:none;
  background:linear-gradient(135deg,rgba(0,229,154,.35),transparent 50%,rgba(59,130,246,.2));
  -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
  -webkit-mask-composite:xor;mask-composite:exclude;padding:1px;
}
.cta-box h2{font-size:clamp(32px,5vw,58px);font-weight:900;letter-spacing:-2px;margin-bottom:16px;line-height:1.08;white-space:pre-line}
.cta-box p{font-size:18px;color:var(--text-2);margin-bottom:38px;max-width:520px;margin-left:auto;margin-right:auto;line-height:1.7}
.btn-magnetic{position:relative;overflow:hidden;transition:transform .2s cubic-bezier(.22,1,.36,1)}

/* ── FOOTER ── */
footer{
  position:relative;z-index:1;border-top:1px solid var(--border);
  padding:36px 28px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;
  color:var(--muted);font-size:13px;max-width:100%;
}
footer a{color:var(--green);transition:opacity .2s}
footer a:hover{opacity:.75}
.footer-links{display:flex;gap:20px}
.footer-copy{opacity:.6}

/* ── SCROLL REVEAL ── */
.sr{opacity:0;transform:translateY(36px);transition:opacity .75s cubic-bezier(.22,1,.36,1),transform .75s cubic-bezier(.22,1,.36,1)}
.sr.sr-left{transform:translateX(-36px)}
.sr.sr-right{transform:translateX(36px)}
.sr.sr-scale{transform:scale(.9);opacity:0}
.sr.in{opacity:1!important;transform:none!important}
.sr-d1{transition-delay:.06s}.sr-d2{transition-delay:.12s}.sr-d3{transition-delay:.18s}
.sr-d4{transition-delay:.24s}.sr-d5{transition-delay:.30s}.sr-d6{transition-delay:.36s}

/* ── RESPONSIVE ── */
@media (max-width:1080px){
  .feat-bento{grid-template-columns:repeat(2,1fr)}
  .feat-card.wide{grid-column:span 1}
  .prov-logos{grid-template-columns:repeat(2,1fr)}
  .testi-grid{grid-template-columns:repeat(2,1fr)}
}
@media (max-width:880px){
  .hero{grid-template-columns:1fr;padding:72px 20px 56px;gap:40px}
  .mock-wrap{max-width:440px;margin:0 auto;order:-1}
  .steps{grid-template-columns:1fr}
  .steps::before{display:none}
  .stats-inner{grid-template-columns:repeat(2,1fr)}
  .stat:nth-child(2){border-right:none}
  .stat:nth-child(1),.stat:nth-child(2){border-bottom:1px solid var(--border-2)}
}
@media (max-width:620px){
  .feat-bento{grid-template-columns:1fr}
  .testi-grid{grid-template-columns:1fr}
  .prov-logos{grid-template-columns:repeat(2,1fr)}
  .hero h1{font-size:40px;letter-spacing:-1.5px}
  .hero p{font-size:16.5px}
  .float-card{display:none}
  .cta-box{padding:48px 24px}
  .stats-inner{grid-template-columns:1fr}
  .stat{border-right:none!important;border-bottom:1px solid var(--border-2)}
  .stat:last-child{border-bottom:none}
}
@media (max-width:400px){
  .hero{padding:52px 16px 40px}
  .hero-cta{flex-direction:column;width:100%}
  .hero-cta .btn{width:100%;justify-content:center}
  .lp-section{padding-bottom:72px}
}
</style>
</head>
<body>

<!-- Scroll progress bar -->
<div id="scroll-bar"></div>

<!-- Animated bg -->
<div class="bg-grid"></div>
<div class="bg-noise"></div>
<canvas id="particles-canvas" style="position:fixed;inset:0;z-index:0;pointer-events:none;opacity:.55"></canvas>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<!-- ── NAV ── -->
<header class="nav">
  <div class="nav-in">
    <a href="/" class="brand">
      <span class="brand-mark"><?= icon('sparkles', 18) ?></span>
      <span class="brand-text">ChatPopup.AI</span>
    </a>

    <!-- Mobile burger -->
    <button class="nav-burger" id="navBurger" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="navLinks">
      <span class="nav-ico-menu"><?= icon('menu', 20) ?></span>
      <span class="nav-ico-close" style="display:none"><?= icon('x', 20) ?></span>
    </button>

    <nav class="nav-links" id="navLinks" aria-label="Main navigation">
      <a class="nav-link" href="#features"><?= e($t['nav_features']) ?></a>
      <a class="nav-link" href="#how"><?= e($t['nav_how']) ?></a>
      <a class="nav-link" href="#providers"><?= e($t['nav_providers']) ?></a>
      <a class="nav-link" href="/login.php"><?= e($t['nav_login']) ?></a>

      <!-- Language switcher -->
      <div class="lang-wrap" id="langWrap">
        <button class="lang-btn" id="langBtn" type="button" aria-haspopup="true" aria-expanded="false">
          <span class="lang-flag"><?= $langNames[$lang]['flag'] ?></span>
          <span><?= e($langNames[$lang]['label']) ?></span>
          <?= icon('chevron-down', 14) ?>
        </button>
        <div class="lang-drop" id="langDrop" role="menu">
          <?php foreach ($langNames as $code => $info): ?>
          <a class="lang-opt <?= $code === $lang ? 'active' : '' ?>"
             href="?lang=<?= e($code) ?>" role="menuitem">
            <span class="lang-opt-flag"><?= $info['flag'] ?></span>
            <?= e($info['label']) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <a class="nav-link btn btn-primary" href="/register.php"><?= e($t['nav_register']) ?> <?= icon('arrow-right', 14) ?></a>
    </nav>
  </div>
</header>
<div class="nav-backdrop" id="navBackdrop"></div>

<!-- ── HERO ── -->
<section class="hero">
  <div>
    <div class="hero-eyebrow">
      <span class="dot"></span> <?= e($t['hero_eyebrow']) ?>
    </div>
    <h1>
      <?= e($t['hero_h1_1']) ?> <span class="grad">AI</span><br>
      <?= e($t['hero_h1_2']) ?><br>
      <span class="typed-wrap" id="typewriter"
            data-words="<?= e($t['typewriter_words']) ?>"></span>
    </h1>
    <p><?= e($t['hero_p']) ?></p>
    <div class="hero-cta">
      <a class="btn btn-primary btn-lg btn-magnetic" href="/register.php">
        <?= e($t['hero_cta1']) ?> <?= icon('arrow-right', 18) ?>
      </a>
      <a class="btn btn-outline btn-lg btn-magnetic" href="/login.php">
        <?= e($t['hero_cta2']) ?>
      </a>
    </div>
    <div class="hero-trust">
      <div><?= icon('check', 15) ?> <?= e($t['trust_1']) ?></div>
      <div><?= icon('check', 15) ?> <?= e($t['trust_2']) ?></div>
      <div><?= icon('check', 15) ?> <?= e($t['trust_3']) ?></div>
    </div>
  </div>

  <div class="mock-wrap">
    <div class="float-card float-1"><?= icon('zap', 16) ?> <?= e($t['float_1']) ?></div>
    <div class="float-card float-2"><?= icon('shield', 16) ?> <?= e($t['float_2']) ?></div>
    <div class="mock-tilt" id="mockTilt">
      <div class="mock">
        <div class="mock-bar">
          <div class="dot r"></div><div class="dot y"></div><div class="dot g"></div>
          <div class="url">https://your-site.com</div>
        </div>
        <div class="mock-body">
          <div class="mock-skel">
            <div class="ln w80"></div><div class="ln w55"></div>
            <div class="ln w90"></div><div class="ln w40"></div>
            <div style="height:8px"></div>
            <div class="ln w70"></div><div class="ln w55"></div><div class="ln w90"></div>
          </div>
          <div class="mock-chat">
            <div class="mock-bubble bot" id="mockBotMsg"><?= e($t['mock_chat_bot']) ?></div>
            <div class="mock-bubble user" id="mockUserMsg" style="opacity:0;transform:translateY(8px)"><?= e($t['mock_chat_user']) ?></div>
            <div class="mock-fab"><?= icon('message', 20) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── TICKER ── -->
<div class="ticker-wrap" aria-hidden="true">
  <div class="ticker-inner" id="tickerInner">
    <?php
    $ticks = [
      ['zap',      'OpenAI GPT-4o'],
      ['brain',    'Google Gemini'],
      ['bot',      'DeepSeek Chat'],
      ['sparkles', 'OpenRouter'],
      ['shield',   'AES-256-GCM'],
      ['rocket',   '5 min setup'],
      ['check',    'No coding needed'],
      ['globe',    'Multi-language'],
      ['message',  'Chat memory'],
      ['phone',    'Telegram alerts'],
    ];
    for ($r = 0; $r < 2; $r++) foreach ($ticks as [$ico,$lbl]):
    ?>
      <span class="ticker-item"><?= icon($ico, 14) ?> <?= e($lbl) ?></span>
    <?php endforeach; ?>
  </div>
</div>

<!-- ── STATS ── -->
<div class="stats">
  <div class="stats-inner">
    <div class="stat sr">
      <div class="stat-num"><span data-count="5" data-suffix=" min">5 min</span></div>
      <div class="stat-label"><?= e($t['stat_1_label']) ?></div>
    </div>
    <div class="stat sr sr-d1">
      <div class="stat-num"><span data-count="4" data-suffix="">4</span></div>
      <div class="stat-label"><?= e($t['stat_2_label']) ?></div>
    </div>
    <div class="stat sr sr-d2">
      <div class="stat-num"><span data-count="1" data-suffix=" line">1 line</span></div>
      <div class="stat-label"><?= e($t['stat_3_label']) ?></div>
    </div>
    <div class="stat sr sr-d3">
      <div class="stat-num"><span data-count="99.9" data-suffix="%">99.9%</span></div>
      <div class="stat-label"><?= e($t['stat_4_label']) ?></div>
    </div>
  </div>
</div>

<!-- ── FEATURES ── -->
<section class="lp-section" id="features">
  <div class="sec-tag sr"><?= icon('sparkles', 14) ?> <?= e($t['feat_tag']) ?></div>
  <h2 class="sec-h2 sr sr-d1"><?= nl2br(e($t['feat_h2'])) ?></h2>
  <p class="sec-sub sr sr-d2"><?= e($t['feat_sub']) ?></p>
  <div class="feat-bento">
    <div class="feat-card sr sr-d1">
      <div class="feat-icon"><?= icon('rocket', 24) ?></div>
      <h3><?= e($t['feat_1_h']) ?></h3>
      <p><?= e($t['feat_1_p']) ?></p>
    </div>
    <div class="feat-card sr sr-d2">
      <div class="feat-icon"><?= icon('bot', 24) ?></div>
      <h3><?= e($t['feat_2_h']) ?></h3>
      <p><?= e($t['feat_2_p']) ?></p>
    </div>
    <div class="feat-card wide sr sr-d3">
      <div class="feat-icon"><?= icon('palette', 24) ?></div>
      <h3><?= e($t['feat_3_h']) ?></h3>
      <p><?= e($t['feat_3_p']) ?></p>
    </div>
    <div class="feat-card wide sr sr-d4">
      <div class="feat-icon"><?= icon('brain', 24) ?></div>
      <h3><?= e($t['feat_4_h']) ?></h3>
      <p><?= e($t['feat_4_p']) ?></p>
    </div>
    <div class="feat-card sr sr-d5">
      <div class="feat-icon"><?= icon('phone', 24) ?></div>
      <h3><?= e($t['feat_5_h']) ?></h3>
      <p><?= e($t['feat_5_p']) ?></p>
    </div>
    <div class="feat-card sr sr-d6">
      <div class="feat-icon"><?= icon('shield', 24) ?></div>
      <h3><?= e($t['feat_6_h']) ?></h3>
      <p><?= e($t['feat_6_p']) ?></p>
    </div>
  </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section class="lp-section" id="how">
  <div class="sec-tag sr"><?= icon('zap', 14) ?> <?= e($t['how_tag']) ?></div>
  <h2 class="sec-h2 sr sr-d1"><?= e($t['how_h2']) ?></h2>
  <p class="sec-sub sr sr-d2"><?= e($t['how_sub']) ?></p>
  <div class="steps">
    <div class="step-card sr sr-d1">
      <div class="step-num">1</div>
      <h3><?= e($t['step_1_h']) ?></h3>
      <p><?= e($t['step_1_p']) ?></p>
    </div>
    <div class="step-card sr sr-d2">
      <div class="step-num">2</div>
      <h3><?= e($t['step_2_h']) ?></h3>
      <p><?= e($t['step_2_p']) ?></p>
    </div>
    <div class="step-card sr sr-d3">
      <div class="step-num">3</div>
      <h3><?= e($t['step_3_h']) ?></h3>
      <p><?= e($t['step_3_p']) ?> <code style="background:var(--green-dim);color:var(--green);padding:2px 6px;border-radius:5px;font-size:.85em">&lt;/body&gt;</code></p>
    </div>
  </div>
</section>

<!-- ── PROVIDERS ── -->
<section class="lp-section" id="providers">
  <div class="sec-tag sr"><?= icon('brain', 14) ?> <?= e($t['prov_tag']) ?></div>
  <h2 class="sec-h2 sr sr-d1"><?= e($t['prov_h2']) ?></h2>
  <p class="sec-sub sr sr-d2"><?= e($t['prov_sub']) ?></p>
  <div class="prov-logos">
    <div class="prov-logo rec sr sr-d1">
      <div class="prov-logo-dot" style="background:radial-gradient(circle,rgba(0,229,154,.3),rgba(0,229,154,.06));box-shadow:0 0 24px rgba(0,229,154,.35)">
        <?= icon('sparkles', 22) ?>
      </div>
      <span><?= e($t['prov_1']) ?></span>
      <span class="prov-logo-tag"><?= e($t['prov_1_tag']) ?></span>
    </div>
    <div class="prov-logo sr sr-d2">
      <div class="prov-logo-dot" style="background:#10A37F22;color:#10A37F;border-radius:50%;display:grid;place-items:center"><?= icon('bot', 22) ?></div>
      <span><?= e($t['prov_2']) ?></span>
      <span class="prov-logo-tag"><?= e($t['prov_2_tag']) ?></span>
    </div>
    <div class="prov-logo sr sr-d3">
      <div class="prov-logo-dot" style="background:#4285F422;color:#4285F4;border-radius:50%;display:grid;place-items:center"><?= icon('brain', 22) ?></div>
      <span><?= e($t['prov_3']) ?></span>
      <span class="prov-logo-tag"><?= e($t['prov_3_tag']) ?></span>
    </div>
    <div class="prov-logo sr sr-d4">
      <div class="prov-logo-dot" style="background:#1F6FEB22;color:#1F6FEB;border-radius:50%;display:grid;place-items:center"><?= icon('rocket', 22) ?></div>
      <span><?= e($t['prov_4']) ?></span>
      <span class="prov-logo-tag"><?= e($t['prov_4_tag']) ?></span>
    </div>
  </div>
</section>

<!-- ── TESTIMONIALS ── -->
<section class="lp-section" id="testimonials">
  <div class="sec-tag sr"><?= icon('check-circle', 14) ?> <?= e($t['test_tag']) ?></div>
  <h2 class="sec-h2 sr sr-d1"><?= e($t['test_h2']) ?></h2>
  <p class="sec-sub sr sr-d2"><?= e($t['test_sub']) ?></p>
  <div class="testi-grid">
    <div class="testi-card sr sr-d1">
      <div class="testi-stars">★★★★★</div>
      <p class="testi-body"><?= e($t['test_1_body']) ?></p>
      <div class="testi-author">
        <div class="testi-avatar"><?= strtoupper(substr($t['test_1_name'],0,1)) ?></div>
        <div>
          <div class="testi-name"><?= e($t['test_1_name']) ?></div>
          <div class="testi-role"><?= e($t['test_1_role']) ?></div>
        </div>
      </div>
    </div>
    <div class="testi-card sr sr-d2">
      <div class="testi-stars">★★★★★</div>
      <p class="testi-body"><?= e($t['test_2_body']) ?></p>
      <div class="testi-author">
        <div class="testi-avatar"><?= strtoupper(substr($t['test_2_name'],0,1)) ?></div>
        <div>
          <div class="testi-name"><?= e($t['test_2_name']) ?></div>
          <div class="testi-role"><?= e($t['test_2_role']) ?></div>
        </div>
      </div>
    </div>
    <div class="testi-card sr sr-d3">
      <div class="testi-stars">★★★★★</div>
      <p class="testi-body"><?= e($t['test_3_body']) ?></p>
      <div class="testi-author">
        <div class="testi-avatar"><?= strtoupper(substr($t['test_3_name'],0,1)) ?></div>
        <div>
          <div class="testi-name"><?= e($t['test_3_name']) ?></div>
          <div class="testi-role"><?= e($t['test_3_role']) ?></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<div class="cta-section">
  <div class="cta-box sr sr-scale">
    <h2 class="h-grad-soft"><?= nl2br(e($t['cta_h2'])) ?></h2>
    <p><?= e($t['cta_p']) ?></p>
    <a class="btn btn-primary btn-lg btn-magnetic" href="/register.php">
      <?= e($t['cta_btn']) ?> <?= icon('arrow-right', 18) ?>
    </a>
  </div>
</div>

<!-- ── FOOTER ── -->
<footer>
  <span class="footer-copy">&copy; <?= date('Y') ?> ChatPopup.AI · <?= e($t['footer_built']) ?></span>
  <div class="footer-links">
    <a href="/login.php"><?= e($t['footer_login']) ?></a>
    <a href="/register.php"><?= e($t['footer_register']) ?></a>
  </div>
</footer>

<script src="/js/landing.js"></script>
<script>
/* ── Mobile nav ── */
(function () {
  var burger = document.getElementById('navBurger');
  var drawer = document.getElementById('navLinks');
  var backdrop = document.getElementById('navBackdrop');
  if (!burger || !drawer) return;
  var icoM = burger.querySelector('.nav-ico-menu');
  var icoX = burger.querySelector('.nav-ico-close');
  var isOpen = false;
  function setOpen(open) {
    isOpen = open;
    drawer.classList.toggle('is-open', open);
    if (backdrop) backdrop.classList.toggle('is-open', open);
    burger.setAttribute('aria-expanded', String(open));
    if (icoM) icoM.style.display = open ? 'none' : 'grid';
    if (icoX) icoX.style.display = open ? 'grid' : 'none';
    document.body.style.overflow = open ? 'hidden' : '';
  }
  if (icoM) icoM.style.display = 'grid';
  if (icoX) icoX.style.display = 'none';
  burger.addEventListener('click', function (e) { e.stopPropagation(); setOpen(!isOpen); });
  if (backdrop) backdrop.addEventListener('click', function () { setOpen(false); });
  drawer.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', function () { setOpen(false); });
  });
  window.addEventListener('resize', function () { if (window.innerWidth > 780) setOpen(false); });
})();

/* ── Language dropdown ── */
(function () {
  var wrap = document.getElementById('langWrap');
  var btn  = document.getElementById('langBtn');
  if (!wrap || !btn) return;
  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    var open = wrap.classList.toggle('open');
    btn.setAttribute('aria-expanded', String(open));
  });
  document.addEventListener('click', function () {
    wrap.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
  });
})();
</script>
</body>
</html>
