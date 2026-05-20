<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/lang.php';

if (current_user() !== null) { header('Location: ' . app_url('/dashboard.php')); exit; }

$lang    = get_lang();
$t       = lang_strings($lang);
$meta    = lang_meta();

function esc(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="<?= esc($t['html_lang']) ?>" dir="<?= esc($t['dir']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<title><?= esc($t['page_title']) ?></title>
<meta name="description" content="<?= esc($t['page_desc']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<style>
/* ══════════════════════════════════════════════════════════
   LANDING v3  ·  i18n + rich scroll animations
══════════════════════════════════════════════════════════ */

/* ── Scroll progress ── */
#sp{position:fixed;top:0;left:0;z-index:9999;height:3px;width:0%;
  background:linear-gradient(90deg,var(--green),var(--cyan),#818cf8);
  pointer-events:none;transition:width .08s linear;}

/* ── Language switcher ── */
/* lang-wrap sits directly in .nav-in, NOT inside .nav-links,
   so it is never clipped by the mobile drawer's overflow-y:auto  */
.lang-wrap{
  position:relative;
  flex-shrink:0;
  /* On mobile: hide the globe-dropdown; show .nav-langs-mobile in drawer instead */
}
.lang-btn{
  display:flex;align-items:center;gap:5px;
  padding:6px 11px;border-radius:9px;
  border:1px solid var(--border-2);background:transparent;
  color:var(--text-2);font-size:13px;font-weight:600;
  cursor:pointer;font-family:inherit;transition:all .2s;white-space:nowrap;
}
.lang-btn:hover{border-color:var(--green-line);color:var(--green);background:var(--green-dim)}
.lang-btn .chv{width:13px;height:13px;transition:transform .22s;flex-shrink:0}
.lang-wrap.open .lang-btn .chv{transform:rotate(180deg)}
.lang-flag{font-size:14px;line-height:1}
/* dropdown — position:fixed so it's never clipped by any ancestor overflow */
.lang-drop{
  position:fixed;
  /* JS will set top/right after button position is measured */
  min-width:160px;
  background:var(--bg-3);border:1px solid var(--border-2);
  border-radius:13px;padding:5px;
  box-shadow:0 20px 52px rgba(0,0,0,.6);
  opacity:0;visibility:hidden;transform:translateY(-6px) scale(.97);
  transition:opacity .2s cubic-bezier(.22,1,.36,1),
             transform .2s cubic-bezier(.22,1,.36,1),
             visibility .2s;
  z-index:9990;pointer-events:none;
}
.lang-wrap.open .lang-drop{
  opacity:1;visibility:visible;transform:none;pointer-events:auto;
}
.lang-opt{
  display:flex;align-items:center;gap:9px;
  padding:9px 12px;border-radius:8px;
  font-size:13px;font-weight:500;color:var(--text-2);
  cursor:pointer;transition:all .15s;text-decoration:none;
}
.lang-opt:hover{background:rgba(255,255,255,.06);color:var(--text)}
.lang-opt.cur{color:var(--green);background:var(--green-dim)}
.lang-opt-flag{font-size:15px;line-height:1;width:22px;text-align:center}

/* Mobile-only inline lang picker inside the drawer */
.nav-langs-mobile{
  display:none; /* hidden on desktop */
  flex-wrap:wrap;gap:6px;padding:10px 14px 4px;
  border-top:1px solid var(--border);margin-top:4px;
}
.nlm-opt{
  display:flex;align-items:center;gap:5px;
  padding:6px 10px;border-radius:8px;border:1px solid var(--border-2);
  font-size:12.5px;font-weight:500;color:var(--text-2);
  text-decoration:none;transition:all .15s;
}
.nlm-opt:hover{border-color:var(--green-line);color:var(--green);background:var(--green-dim)}
.nlm-opt.cur{border-color:var(--green-line);color:var(--green);background:var(--green-dim)}
.nlm-lbl{font-size:12px}

@media(max-width:780px){
  .lang-wrap{display:none} /* hide globe button on mobile */
  .nav-langs-mobile{display:flex} /* show inline flags in drawer */
  .lang-label-text{display:none} /* not needed but just in case */
}

/* ── Hero ── */
.hero{
  position:relative;z-index:1;
  max-width:1180px;margin:0 auto;padding:96px 24px 72px;
  display:grid;grid-template-columns:1.1fr .9fr;gap:52px;align-items:center;
}
.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 15px;border-radius:999px;
  background:var(--green-dim);color:var(--green);
  border:1px solid var(--green-line);
  font-size:12.5px;font-weight:700;letter-spacing:.15px;margin-bottom:22px;
  animation:fadeUp .7s cubic-bezier(.22,1,.36,1) both;
}
.badge-dot{width:6px;height:6px;border-radius:50%;background:var(--green);animation:pulseDot 2s infinite}
.hero h1{
  font-size:clamp(38px,5.5vw,70px);font-weight:900;line-height:1.05;letter-spacing:-2.5px;
  margin-bottom:22px;animation:fadeUp .85s .07s cubic-bezier(.22,1,.36,1) both;
}
.hero h1 .grad{
  background:linear-gradient(120deg,var(--green),var(--cyan) 55%,#818cf8);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.typed-wrap{display:inline-block;min-height:1em;color:var(--green)}
.typed-wrap::after{content:'|';animation:caret 1s steps(1) infinite;margin-left:1px}
@keyframes caret{50%{opacity:0}}
.hero p{font-size:17.5px;color:var(--text-2);max-width:510px;margin-bottom:34px;line-height:1.75;
  animation:fadeUp .95s .14s cubic-bezier(.22,1,.36,1) both;}
.hero-cta{display:flex;gap:12px;flex-wrap:wrap;animation:fadeUp 1.1s .2s cubic-bezier(.22,1,.36,1) both}
.hero-trust{
  display:flex;align-items:center;gap:18px;margin-top:32px;
  color:var(--muted);font-size:13px;flex-wrap:wrap;
  animation:fadeUp 1.2s .28s cubic-bezier(.22,1,.36,1) both;
}
.hero-trust > span{display:flex;align-items:center;gap:5px}
.hero-trust svg{color:var(--green);width:14px;height:14px}
@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}

/* ── Mock browser ── */
.mock-wrap{position:relative;animation:fadeUp 1s .28s cubic-bezier(.22,1,.36,1) both;overflow:visible!important}
.mock-tilt{transform-style:preserve-3d;transition:transform .14s ease-out;will-change:transform}
.mock{background:var(--bg-3);border-radius:var(--r-lg);overflow:hidden;
  border:1px solid var(--border-2);
  box-shadow:0 32px 80px rgba(0,0,0,.65),0 0 80px rgba(0,229,154,.08);
  animation:floatY 7s ease-in-out infinite;}
@keyframes floatY{0%,100%{transform:translateY(0) rotate(-.35deg)}50%{transform:translateY(-14px) rotate(.35deg)}}
.mock-bar{padding:11px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px}
.mock-bar .d{width:11px;height:11px;border-radius:50%}
.mock-bar .d.r{background:#FF5F57}.mock-bar .d.y{background:#FFBD2E}.mock-bar .d.g{background:#28C840}
.mock-bar .url{margin-left:8px;background:rgba(255,255,255,.05);border-radius:6px;padding:4px 12px;
  font-size:11px;color:var(--muted);font-family:'SF Mono',monospace;}
.mock-body{position:relative;padding:22px 20px;min-height:240px}
.mock-skel{display:flex;flex-direction:column;gap:9px;opacity:.28}
.mock-skel .ln{height:9px;border-radius:5px;background:rgba(255,255,255,.18)}
.ln-80{width:80%}.ln-55{width:55%}.ln-70{width:70%}.ln-40{width:40%}.ln-90{width:90%}
.mock-chat{position:absolute;bottom:16px;right:16px;display:flex;flex-direction:column;align-items:flex-end;gap:7px;z-index:10}
.mb-bot{background:var(--bg-2);border:1px solid var(--border-2);border-radius:14px 14px 4px 14px;
  padding:9px 13px;font-size:12.5px;color:var(--text);max-width:200px;box-shadow:0 4px 18px rgba(0,0,0,.4);}
.mb-user{background:linear-gradient(135deg,var(--green),var(--green-2));color:#031018;
  border-radius:14px 14px 14px 4px;padding:9px 13px;font-size:12.5px;font-weight:600;max-width:165px;}
.mock-fab{width:46px;height:46px;border-radius:50%;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--green),var(--green-2));
  box-shadow:0 4px 20px rgba(0,229,154,.55),0 0 0 4px rgba(0,229,154,.1);
  animation:pulseRing 3s ease infinite;}
@keyframes pulseRing{0%,100%{box-shadow:0 4px 20px rgba(0,229,154,.55),0 0 0 4px rgba(0,229,154,.1)}
  50%{box-shadow:0 4px 28px rgba(0,229,154,.7),0 0 0 9px rgba(0,229,154,.05)}}
.mock-fab svg{color:#031018;width:22px;height:22px;stroke-width:2.5}
.float-card{
  position:absolute;backdrop-filter:blur(20px) saturate(140%);-webkit-backdrop-filter:blur(20px) saturate(140%);
  background:var(--glass-2);border:1px solid var(--border-2);border-radius:12px;
  padding:9px 14px;box-shadow:0 12px 28px rgba(0,0,0,.5);
  font-size:12px;font-weight:600;color:var(--text);display:flex;align-items:center;gap:7px;
  z-index:20;white-space:nowrap;
}
.float-card svg{width:15px;height:15px;color:var(--green)}
.fc1{top:-18px;left:-18px;animation:floatA 5s ease-in-out infinite}
.fc2{bottom:-18px;right:-18px;animation:floatB 6s ease-in-out infinite}
@keyframes floatA{0%,100%{transform:translateY(0)}50%{transform:translateY(-9px)}}
@keyframes floatB{0%,100%{transform:translateY(0)}50%{transform:translateY(9px)}}

/* ── Ticker ── */
.ticker-outer{overflow:hidden;border-top:1px solid var(--border);border-bottom:1px solid var(--border);
  padding:13px 0;position:relative;z-index:1;background:rgba(3,7,18,.55);}
.ticker-inner{display:flex;gap:44px;width:max-content;animation:tickRun 30s linear infinite;}
.ticker-inner:hover{animation-play-state:paused}
@keyframes tickRun{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.tick-item{display:flex;align-items:center;gap:9px;font-size:12.5px;font-weight:600;color:var(--muted);white-space:nowrap}
.tick-item svg{width:13px;height:13px;color:var(--green);flex-shrink:0}

/* ── Stats ── */
.stats-wrap{position:relative;z-index:1;max-width:1180px;margin:0 auto;padding:60px 24px 80px}
.stats-grid{
  display:grid;grid-template-columns:repeat(4,1fr);
  border:1px solid var(--border-2);border-radius:var(--r-xl);overflow:hidden;
  background:var(--glass);backdrop-filter:blur(20px);
}
.stat-cell{padding:28px 20px;text-align:center;border-right:1px solid var(--border-2);
  transition:background .3s;}
.stat-cell:hover{background:rgba(0,229,154,.05)}
.stat-cell:last-child{border-right:none}
.stat-val{font-size:38px;font-weight:900;letter-spacing:-1px;line-height:1}
.stat-val span{background:linear-gradient(135deg,var(--green),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.stat-lbl{font-size:13px;color:var(--text-2);margin-top:7px;font-weight:500}

/* ── Section wrappers ── */
.lp-sec{position:relative;z-index:1;max-width:1180px;margin:0 auto;padding:0 24px 96px}
.sec-kicker{display:flex;justify-content:center;align-items:center;gap:6px;
  font-size:11px;font-weight:800;letter-spacing:.22em;text-transform:uppercase;
  color:var(--green);margin-bottom:12px;}
.sec-kicker svg{width:13px;height:13px}
.sec-h2{text-align:center;font-size:clamp(26px,4vw,46px);font-weight:900;letter-spacing:-1.5px;
  margin-bottom:14px;line-height:1.1;white-space:pre-line;}
.sec-sub{text-align:center;color:var(--text-2);font-size:16px;
  max-width:560px;margin:0 auto 56px;line-height:1.75;}

/* ── Features bento ── */
.feat-bento{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.feat-card{
  padding:28px 24px;border-radius:20px;
  background:rgba(10,16,28,.85);border:1px solid var(--border-2);
  position:relative;overflow:hidden;cursor:default;
  transition:border-color .3s,box-shadow .4s,transform .4s cubic-bezier(.22,1,.36,1);
}
/* Cursor-tracking glow — set via JS */
.feat-card::before{
  content:'';position:absolute;inset:0;border-radius:inherit;
  background:radial-gradient(circle 200px at var(--gx,50%) var(--gy,50%),rgba(0,229,154,.07),transparent 70%);
  opacity:0;transition:opacity .35s;pointer-events:none;
}
.feat-card:hover{
  border-color:rgba(0,229,154,.3);
  transform:translateY(-6px) scale(1.012);
  box-shadow:0 22px 56px rgba(0,0,0,.4),0 0 0 1px rgba(0,229,154,.12);
}
.feat-card:hover::before{opacity:1}
/* Left border accent line */
.feat-card::after{
  content:'';position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:3px 0 0 3px;
  background:linear-gradient(180deg,var(--green),var(--cyan));
  transform:scaleY(0);transform-origin:top;transition:transform .4s cubic-bezier(.22,1,.36,1);
}
.feat-card:hover::after{transform:scaleY(1)}
.feat-card.span2{grid-column:span 2}
.feat-icon{width:50px;height:50px;border-radius:14px;display:grid;place-items:center;
  background:var(--green-dim);border:1px solid var(--green-line);color:var(--green);
  margin-bottom:18px;transition:transform .3s;}
.feat-card:hover .feat-icon{transform:scale(1.12) rotate(-5deg)}
.feat-icon svg{width:22px;height:22px}
.feat-card h3{font-size:17px;font-weight:700;margin-bottom:9px;color:var(--text)}
.feat-card p{color:var(--text-2);font-size:13.5px;line-height:1.7}

/* ── Steps ── */
.steps-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;position:relative}
.steps-grid::before{content:'';position:absolute;top:50px;left:calc(16.67% + 32px);right:calc(16.67% + 32px);
  height:1px;background:linear-gradient(90deg,transparent,var(--green-line),var(--green-line),transparent);}
.step-card{
  padding:30px 24px;border-radius:20px;text-align:center;position:relative;z-index:1;
  background:rgba(10,16,28,.85);border:1px solid var(--border-2);
  transition:border-color .3s,transform .35s cubic-bezier(.22,1,.36,1),box-shadow .35s;
}
.step-card:hover{border-color:var(--green-line);transform:translateY(-8px);
  box-shadow:0 28px 64px rgba(0,0,0,.4),0 0 28px rgba(0,229,154,.08);}
.step-num{width:50px;height:50px;border-radius:50%;margin:0 auto 18px;display:grid;place-items:center;
  font-size:20px;font-weight:900;color:#031018;
  background:linear-gradient(135deg,var(--green),var(--green-2));
  box-shadow:0 6px 20px rgba(0,229,154,.4);transition:transform .3s;}
.step-card:hover .step-num{transform:scale(1.1) rotate(8deg)}
.step-card h3{font-size:16.5px;font-weight:700;margin-bottom:9px;color:var(--text)}
.step-card p{color:var(--text-2);font-size:13.5px;line-height:1.7}

/* ── Providers grid ── */
.prov-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.prov-card{
  padding:28px 18px;border-radius:18px;
  background:rgba(10,16,28,.85);border:1px solid var(--border-2);
  display:flex;flex-direction:column;align-items:center;gap:13px;
  text-align:center;font-weight:700;font-size:15px;color:var(--text);
  transition:all .35s cubic-bezier(.22,1,.36,1);cursor:default;
}
.prov-card:hover{border-color:var(--border-hover);transform:translateY(-6px);
  box-shadow:0 24px 56px rgba(0,0,0,.4),0 0 24px rgba(0,229,154,.08);}
.prov-icon{width:48px;height:48px;border-radius:50%;display:grid;place-items:center;
  transition:transform .3s;}
.prov-card:hover .prov-icon{transform:scale(1.14)}
.prov-sub{font-size:12px;color:var(--muted);font-weight:500}
.prov-card.rec{border-color:var(--green-line);background:rgba(0,229,154,.05)}
.prov-card.rec .prov-sub{color:var(--green);opacity:.85}

/* ── Testimonials ── */
.testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.testi-card{
  padding:26px;border-radius:20px;
  background:rgba(10,16,28,.85);border:1px solid var(--border-2);
  transition:border-color .3s,transform .35s cubic-bezier(.22,1,.36,1),box-shadow .35s;
}
.testi-card:hover{border-color:var(--green-line);transform:translateY(-5px);
  box-shadow:0 22px 52px rgba(0,0,0,.4);}
.testi-stars{color:#FBBF24;font-size:13px;letter-spacing:2px;margin-bottom:14px}
.testi-body{font-size:14px;color:var(--text-2);line-height:1.75;margin-bottom:18px;font-style:italic}
.testi-foot{display:flex;align-items:center;gap:11px}
.testi-av{width:38px;height:38px;border-radius:50%;flex-shrink:0;
  background:var(--green-dim);border:1.5px solid var(--green-line);
  display:grid;place-items:center;font-size:15px;font-weight:800;color:var(--green);}
.testi-name{font-size:13.5px;font-weight:700;color:var(--text)}
.testi-role{font-size:11.5px;color:var(--muted);margin-top:2px}

/* ── CTA box ── */
.cta-wrap{position:relative;z-index:1;max-width:1180px;margin:0 auto;padding:0 24px 100px}
.cta-box{
  border-radius:26px;padding:76px 52px;text-align:center;position:relative;overflow:hidden;
  background:radial-gradient(ellipse at 30% 0%,rgba(0,229,154,.09),transparent 60%),
             radial-gradient(ellipse at 80% 100%,rgba(59,130,246,.07),transparent 60%),
             rgba(10,16,28,.95);
  border:1px solid var(--green-line);
}
.cta-box::before{
  content:'';position:absolute;inset:-1px;border-radius:inherit;pointer-events:none;
  background:linear-gradient(135deg,rgba(0,229,154,.3),transparent 50%,rgba(59,130,246,.2));
  -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
  -webkit-mask-composite:xor;mask-composite:exclude;padding:1px;
}
.cta-box h2{font-size:clamp(30px,5vw,56px);font-weight:900;letter-spacing:-2px;margin-bottom:14px;
  line-height:1.08;white-space:pre-line;}
.cta-box p{font-size:17px;color:var(--text-2);margin-bottom:36px;max-width:500px;
  margin-left:auto;margin-right:auto;line-height:1.7;}
/* Magnetic button js-handled */
.btn-mag{transition:transform .2s cubic-bezier(.22,1,.36,1),box-shadow .2s}

/* ── Footer ── */
footer{
  position:relative;z-index:1;border-top:1px solid var(--border);
  padding:32px 24px;display:flex;flex-wrap:wrap;align-items:center;
  justify-content:space-between;gap:12px;color:var(--muted);font-size:13px;
}
footer a{color:var(--green)}
footer a:hover{opacity:.75}
.footer-links{display:flex;gap:18px}

/* ── Scroll-reveal ── */
.sr{opacity:0;transform:translateY(34px);
  transition:opacity .75s cubic-bezier(.22,1,.36,1),transform .75s cubic-bezier(.22,1,.36,1);}
.sr.sl{transform:translateX(-34px)}
.sr.sr2{transform:translateX(34px)}
.sr.sc{transform:scale(.92);opacity:0;}
.sr.in{opacity:1!important;transform:none!important}
.d1{transition-delay:.06s}.d2{transition-delay:.12s}.d3{transition-delay:.18s}
.d4{transition-delay:.24s}.d5{transition-delay:.30s}.d6{transition-delay:.36s}

/* ── Responsive ── */
@media(max-width:1060px){
  .feat-bento{grid-template-columns:repeat(2,1fr)}
  .feat-card.span2{grid-column:span 1}
  .prov-grid{grid-template-columns:repeat(2,1fr)}
  .testi-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:860px){
  .hero{grid-template-columns:1fr;padding:68px 20px 52px;gap:38px}
  .mock-wrap{order:-1;max-width:420px;margin:0 auto}
  .steps-grid{grid-template-columns:1fr}
  .steps-grid::before{display:none}
  .stats-grid{grid-template-columns:repeat(2,1fr)}
  .stat-cell:nth-child(2){border-right:none}
  .stat-cell:nth-child(1),.stat-cell:nth-child(2){border-bottom:1px solid var(--border-2)}
}
@media(max-width:600px){
  .feat-bento{grid-template-columns:1fr}
  .testi-grid{grid-template-columns:1fr}
  .prov-grid{grid-template-columns:repeat(2,1fr)}
  .hero h1{font-size:38px;letter-spacing:-1.5px}
  .hero p{font-size:15.5px}
  .float-card{display:none}
  .cta-box{padding:44px 20px}
  .stats-grid{grid-template-columns:1fr}
  .stat-cell{border-right:none!important;border-bottom:1px solid var(--border-2)!important}
  .stat-cell:last-child{border-bottom:none!important}
}
@media(max-width:400px){
  .hero{padding:48px 14px 40px}
  .hero-cta{flex-direction:column;width:100%}
  .hero-cta .btn{width:100%;justify-content:center}
  .lp-sec{padding-bottom:68px}
  .cta-wrap{padding-bottom:72px}
}
</style>
</head>
<body>

<div id="sp"></div>
<div class="bg-grid"></div>
<div class="bg-noise"></div>
<canvas id="pcv" style="position:fixed;inset:0;z-index:0;pointer-events:none;opacity:.5"></canvas>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<!-- ═══ NAV ═══ -->
<header class="nav">
  <div class="nav-in">
    <a href="<?= esc(app_url('/')) ?>" class="brand">
      <span class="brand-mark"><?= icon('sparkles', 18) ?></span>
      <span class="brand-text">ChatPopup.AI</span>
    </a>

    <!-- Language Switcher — lives OUTSIDE nav-links to avoid overflow clipping -->
    <div class="lang-wrap" id="langWrap">
      <button class="lang-btn" id="langBtn" type="button"
              aria-haspopup="true" aria-expanded="false">
        <span class="lang-flag"><?= $meta[$lang]['flag'] ?></span>
        <span class="lang-label-text"><?= esc($meta[$lang]['label']) ?></span>
        <svg class="chv" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </button>
      <div class="lang-drop" id="langDrop" role="menu">
        <?php foreach ($meta as $code => $info): ?>
        <a class="lang-opt <?= $code === $lang ? 'cur' : '' ?>"
           href="<?= esc(lang_switch_url($code)) ?>" role="menuitem">
          <span class="lang-opt-flag"><?= $info['flag'] ?></span>
          <?= esc($info['label']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <button class="nav-burger" id="navBurger" type="button" onclick="return toggleLandingMenu()"
            aria-label="Open menu" aria-expanded="false" aria-controls="navLinks">
      <span class="nav-ico-menu"><?= icon('menu', 20) ?></span>
      <span class="nav-ico-close" style="display:none"><?= icon('x', 20) ?></span>
    </button>

    <nav class="nav-links" id="navLinks" aria-label="Main navigation">
      <a class="nav-link" href="#features"><?= esc($t['nav_features']) ?></a>
      <a class="nav-link" href="#how"><?= esc($t['nav_how']) ?></a>
      <a class="nav-link" href="#providers"><?= esc($t['nav_providers']) ?></a>
      <a class="nav-link" href="<?= esc(app_url('/login.php')) ?>"><?= esc($t['nav_login']) ?></a>

      <!-- Mobile-only: inline lang flags inside the drawer -->
      <div class="nav-langs-mobile">
        <?php foreach ($meta as $code => $info): ?>
        <a class="nlm-opt <?= $code === $lang ? 'cur' : '' ?>"
           href="<?= esc(lang_switch_url($code)) ?>">
          <span><?= $info['flag'] ?></span>
          <span class="nlm-lbl"><?= esc($info['label']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>

      <a class="nav-link btn btn-primary" href="<?= esc(app_url('/register.php')) ?>">
        <?= esc($t['nav_register']) ?> <?= icon('arrow-right', 14) ?>
      </a>
    </nav>
  </div>
</header>
<div class="nav-backdrop" id="navBackdrop" onclick="closeLandingMenu()"></div>

<!-- ═══ HERO ═══ -->
<section class="hero" id="home">
  <div>
    <div class="hero-badge">
      <span class="badge-dot"></span>
      <?= esc($t['hero_eyebrow']) ?>
    </div>
    <h1>
      <?= esc($t['hero_h1_a']) ?> <span class="grad">AI</span><br>
      <?= esc($t['hero_h1_b']) ?><br>
      <span class="typed-wrap" id="typewriter"
            data-words="<?= esc($t['typewords']) ?>"></span>
    </h1>
    <p><?= esc($t['hero_p']) ?></p>
    <div class="hero-cta">
      <a class="btn btn-primary btn-lg btn-mag" href="<?= esc(app_url('/register.php')) ?>">
        <?= esc($t['cta_start']) ?> <?= icon('arrow-right', 18) ?>
      </a>
      <a class="btn btn-outline btn-lg btn-mag" href="<?= esc(app_url('/login.php')) ?>">
        <?= esc($t['cta_login']) ?>
      </a>
    </div>
    <div class="hero-trust">
      <span><?= icon('check', 14) ?> <?= esc($t['trust_1']) ?></span>
      <span><?= icon('check', 14) ?> <?= esc($t['trust_2']) ?></span>
      <span><?= icon('check', 14) ?> <?= esc($t['trust_3']) ?></span>
    </div>
  </div>

  <div class="mock-wrap">
    <div class="float-card fc1"><?= icon('zap', 15) ?> <?= esc($t['float_1']) ?></div>
    <div class="float-card fc2"><?= icon('shield', 15) ?> <?= esc($t['float_2']) ?></div>
    <div class="mock-tilt" id="mockTilt">
      <div class="mock">
        <div class="mock-bar">
          <div class="d r"></div><div class="d y"></div><div class="d g"></div>
          <div class="url">https://your-site.com</div>
        </div>
        <div class="mock-body">
          <div class="mock-skel">
            <div class="ln ln-80"></div><div class="ln ln-55"></div>
            <div class="ln ln-90"></div><div class="ln ln-40"></div>
            <div style="height:8px"></div>
            <div class="ln ln-70"></div><div class="ln ln-55"></div>
          </div>
          <div class="mock-chat">
            <div class="mb-bot" id="mockBotMsg"><?= esc($t['mock_bot']) ?></div>
            <div class="mb-user" id="mockUserMsg"
                 style="opacity:0;transform:translateY(8px);transition:opacity .5s ease,transform .5s cubic-bezier(.22,1,.36,1)">
              <?= esc($t['mock_user']) ?>
            </div>
            <div class="mock-fab"><?= icon('message', 20) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ TICKER ═══ -->
<div class="ticker-outer" aria-hidden="true">
  <div class="ticker-inner" id="tickerInner">
    <?php
    $tickIcos = ['zap','brain','bot','sparkles','shield','rocket','check','globe','message','phone'];
    $tickItems = explode('|', $t['tick_items']);
    // Duplicate for seamless loop
    for ($r = 0; $r < 2; $r++):
      foreach ($tickItems as $i => $lbl):
        $ico = $tickIcos[$i % count($tickIcos)];
    ?>
      <span class="tick-item"><?= icon($ico, 13) ?> <?= esc($lbl) ?></span>
    <?php endforeach; endfor; ?>
  </div>
</div>

<!-- ═══ STATS ═══ -->
<div class="stats-wrap">
  <div class="stats-grid">
    <?php
    $stats = [
      [$t['stat_1_v'], $t['stat_1_suf'], $t['stat_1_lbl']],
      [$t['stat_2_v'], $t['stat_2_suf'], $t['stat_2_lbl']],
      [$t['stat_3_v'], $t['stat_3_suf'], $t['stat_3_lbl']],
      [$t['stat_4_v'], $t['stat_4_suf'], $t['stat_4_lbl']],
    ];
    foreach ($stats as $i => [$v, $suf, $lbl]):
    ?>
    <div class="stat-cell sr d<?= $i+1 ?>">
      <div class="stat-val">
        <span data-count="<?= esc($v) ?>" data-suf="<?= esc($suf) ?>"><?= esc($v.$suf) ?></span>
      </div>
      <div class="stat-lbl"><?= esc($lbl) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ═══ FEATURES ═══ -->
<section class="lp-sec" id="features">
  <div class="sec-kicker sr"><?= icon('sparkles', 13) ?> <?= esc($t['feat_tag']) ?></div>
  <h2 class="sec-h2 sr d1"><?= nl2br(esc($t['feat_h2'])) ?></h2>
  <p class="sec-sub sr d2"><?= esc($t['feat_sub']) ?></p>
  <div class="feat-bento">
    <div class="feat-card sr d1">
      <div class="feat-icon"><?= icon('rocket', 22) ?></div>
      <h3><?= esc($t['feat_1_h']) ?></h3>
      <p><?= esc($t['feat_1_p']) ?></p>
    </div>
    <div class="feat-card sr d2">
      <div class="feat-icon"><?= icon('bot', 22) ?></div>
      <h3><?= esc($t['feat_2_h']) ?></h3>
      <p><?= esc($t['feat_2_p']) ?></p>
    </div>
    <div class="feat-card span2 sr d3">
      <div class="feat-icon"><?= icon('palette', 22) ?></div>
      <h3><?= esc($t['feat_3_h']) ?></h3>
      <p><?= esc($t['feat_3_p']) ?></p>
    </div>
    <div class="feat-card span2 sr d4">
      <div class="feat-icon"><?= icon('brain', 22) ?></div>
      <h3><?= esc($t['feat_4_h']) ?></h3>
      <p><?= esc($t['feat_4_p']) ?></p>
    </div>
    <div class="feat-card sr d5">
      <div class="feat-icon"><?= icon('phone', 22) ?></div>
      <h3><?= esc($t['feat_5_h']) ?></h3>
      <p><?= esc($t['feat_5_p']) ?></p>
    </div>
    <div class="feat-card sr d6">
      <div class="feat-icon"><?= icon('shield', 22) ?></div>
      <h3><?= esc($t['feat_6_h']) ?></h3>
      <p><?= esc($t['feat_6_p']) ?></p>
    </div>
  </div>
</section>

<!-- ═══ HOW IT WORKS ═══ -->
<section class="lp-sec" id="how">
  <div class="sec-kicker sr"><?= icon('zap', 13) ?> <?= esc($t['how_tag']) ?></div>
  <h2 class="sec-h2 sr d1"><?= esc($t['how_h2']) ?></h2>
  <p class="sec-sub sr d2"><?= esc($t['how_sub']) ?></p>
  <div class="steps-grid">
    <div class="step-card sr d1">
      <div class="step-num">1</div>
      <h3><?= esc($t['step_1_h']) ?></h3>
      <p><?= $t['step_1_p'] /* contains escaped HTML entity */ ?></p>
    </div>
    <div class="step-card sr d2">
      <div class="step-num">2</div>
      <h3><?= esc($t['step_2_h']) ?></h3>
      <p><?= esc($t['step_2_p']) ?></p>
    </div>
    <div class="step-card sr d3">
      <div class="step-num">3</div>
      <h3><?= esc($t['step_3_h']) ?></h3>
      <p><?= $t['step_3_p'] /* contains &lt;/body&gt; */ ?></p>
    </div>
  </div>
</section>

<!-- ═══ PROVIDERS ═══ -->
<section class="lp-sec" id="providers">
  <div class="sec-kicker sr"><?= icon('brain', 13) ?> <?= esc($t['prov_tag']) ?></div>
  <h2 class="sec-h2 sr d1"><?= esc($t['prov_h2']) ?></h2>
  <p class="sec-sub sr d2"><?= esc($t['prov_sub']) ?></p>
  <div class="prov-grid">
    <div class="prov-card rec sr d1">
      <div class="prov-icon" style="background:rgba(0,229,154,.12);color:var(--green);box-shadow:0 0 22px rgba(0,229,154,.3)">
        <?= icon('sparkles', 22) ?>
      </div>
      <span><?= esc($t['prov_1']) ?></span>
      <span class="prov-sub"><?= esc($t['prov_1_sub']) ?></span>
    </div>
    <div class="prov-card sr d2">
      <div class="prov-icon" style="background:#10A37F1A;color:#10A37F"><?= icon('bot', 22) ?></div>
      <span><?= esc($t['prov_2']) ?></span>
      <span class="prov-sub"><?= esc($t['prov_2_sub']) ?></span>
    </div>
    <div class="prov-card sr d3">
      <div class="prov-icon" style="background:#4285F41A;color:#4285F4"><?= icon('brain', 22) ?></div>
      <span><?= esc($t['prov_3']) ?></span>
      <span class="prov-sub"><?= esc($t['prov_3_sub']) ?></span>
    </div>
    <div class="prov-card sr d4">
      <div class="prov-icon" style="background:#1F6FEB1A;color:#1F6FEB"><?= icon('rocket', 22) ?></div>
      <span><?= esc($t['prov_4']) ?></span>
      <span class="prov-sub"><?= esc($t['prov_4_sub']) ?></span>
    </div>
  </div>
</section>

<!-- ═══ TESTIMONIALS ═══ -->
<section class="lp-sec" id="testimonials">
  <div class="sec-kicker sr"><?= icon('check-circle', 13) ?> <?= esc($t['testi_tag']) ?></div>
  <h2 class="sec-h2 sr d1"><?= esc($t['testi_h2']) ?></h2>
  <p class="sec-sub sr d2"><?= esc($t['testi_sub']) ?></p>
  <div class="testi-grid">
    <?php
    $testis = [
      [$t['testi_1_q'], $t['testi_1_name'], $t['testi_1_role']],
      [$t['testi_2_q'], $t['testi_2_name'], $t['testi_2_role']],
      [$t['testi_3_q'], $t['testi_3_name'], $t['testi_3_role']],
    ];
    $delays = ['d1','d2','d3'];
    foreach ($testis as $i => [$q, $name, $role]):
    ?>
    <div class="testi-card sr <?= $delays[$i] ?>">
      <div class="testi-stars">★★★★★</div>
      <p class="testi-body"><?= esc($q) ?></p>
      <div class="testi-foot">
        <div class="testi-av"><?= esc(strtoupper(mb_substr($name, 0, 1))) ?></div>
        <div>
          <div class="testi-name"><?= esc($name) ?></div>
          <div class="testi-role"><?= esc($role) ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ═══ CTA ═══ -->
<div class="cta-wrap">
  <div class="cta-box sr sc">
    <h2 class="h-grad-soft"><?= nl2br(esc($t['cta_h2'])) ?></h2>
    <p><?= esc($t['cta_p']) ?></p>
    <a class="btn btn-primary btn-lg btn-mag" href="<?= esc(app_url('/register.php')) ?>">
      <?= esc($t['cta_btn']) ?> <?= icon('arrow-right', 18) ?>
    </a>
  </div>
</div>

<!-- ═══ FOOTER ═══ -->
<footer>
  <span>&copy; <?= date('Y') ?> ChatPopup.AI &nbsp;·&nbsp; <?= esc($t['footer_built']) ?></span>
  <div class="footer-links">
    <a href="<?= esc(app_url('/login.php')) ?>"><?= esc($t['footer_login']) ?></a>
    <a href="<?= esc(app_url('/register.php')) ?>"><?= esc($t['footer_reg']) ?></a>
  </div>
</footer>

<script>
/* ── Mobile nav: isolated from other scripts ── */
(function(){
  function els(){
    return {
      bg: document.getElementById('navBurger'),
      dr: document.getElementById('navLinks'),
      bd: document.getElementById('navBackdrop')
    };
  }

  function setMenu(open){
    var ref = els();
    if(!ref.bg || !ref.dr) return false;
    var menuIcon = ref.bg.querySelector('.nav-ico-menu');
    var closeIcon = ref.bg.querySelector('.nav-ico-close');

    ref.dr.classList.toggle('is-open', !!open);
    if(ref.bd) ref.bd.classList.toggle('is-open', !!open);
    ref.bg.setAttribute('aria-expanded', open ? 'true' : 'false');
    if(menuIcon) menuIcon.style.display = open ? 'none' : 'grid';
    if(closeIcon) closeIcon.style.display = open ? 'grid' : 'none';
    document.body.style.overflow = open ? 'hidden' : '';
    return false;
  }

  window.toggleLandingMenu = function(){
    var ref = els();
    if(!ref.bg || !ref.dr) return false;
    return setMenu(!ref.dr.classList.contains('is-open'));
  };

  window.closeLandingMenu = function(){
    return setMenu(false);
  };

  document.addEventListener('DOMContentLoaded', function(){
    var ref = els();
    if(!ref.bg || !ref.dr) return;
    setMenu(false);
    ref.dr.querySelectorAll('a').forEach(function(a){
      a.addEventListener('click', function(){ closeLandingMenu(); });
    });
  });

  window.addEventListener('resize',function(){if(window.innerWidth>780) closeLandingMenu();});
})();
</script>

<script>
/* ── Language dropdown (position:fixed to avoid any overflow clip) ── */
(function(){
  var w=document.getElementById('langWrap');
  var b=document.getElementById('langBtn');
  var d=document.getElementById('langDrop');
  if(!w||!b||!d) return;

  function positionDrop(){
    var r=b.getBoundingClientRect();
    d.style.top=(r.bottom+6)+'px';
    // Align right edge of dropdown to right edge of button, clamped within viewport
    var dropW=d.offsetWidth||160;
    var left=r.right-dropW;
    if(left<8) left=8;
    d.style.left=left+'px';
    d.style.right='auto';
  }

  function openDrop(){
    positionDrop();
    w.classList.add('open');
    b.setAttribute('aria-expanded','true');
  }
  function closeDrop(){
    w.classList.remove('open');
    b.setAttribute('aria-expanded','false');
  }

  b.addEventListener('click',function(e){
    e.stopPropagation();
    w.classList.contains('open') ? closeDrop() : openDrop();
  });

  // Close on outside click
  document.addEventListener('click',function(e){
    if(!w.contains(e.target)) closeDrop();
  });

  // Reposition on scroll/resize
  window.addEventListener('scroll',function(){if(w.classList.contains('open')) positionDrop();},{passive:true});
  window.addEventListener('resize',function(){if(w.classList.contains('open')) positionDrop();},{passive:true});
})();
</script>
<script src="/js/landing.js"></script>
</body>
</html>
