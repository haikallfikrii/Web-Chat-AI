<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/brand.php';
require_once __DIR__ . '/includes/seo.php';

if (current_user() !== null) { header('Location: ' . app_url('/dashboard.php')); exit; }

$lang    = get_lang();
$t       = lang_strings($lang);
$meta    = lang_meta();
$seoMeta = seo_landing_meta($lang);

function esc(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="<?= esc($t['html_lang']) ?>" dir="<?= esc($t['dir']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<?= brand_favicon_tags() ?>
<?php
seo_render_head([
    'title'       => $seoMeta['title'],
    'description' => $seoMeta['description'],
    'path'        => '/',
    'json_ld'     => [seo_landing_json_ld($lang)],
]);
?>
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
  display:none;
  flex-shrink:0;
  gap:6px;padding:10px 0 4px;
  border-top:1px solid var(--border);margin-top:6px;
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
  .lang-wrap{display:none}
  .nav-langs-mobile{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
  }
  .lang-label-text{display:none}
}

/* ── Hero ── */
.hero{
  position:relative;z-index:1;
  max-width:1180px;margin:0 auto;padding:96px 24px 72px;
  display:grid;grid-template-columns:1.1fr .9fr;gap:52px;align-items:center;
}
.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  padding:8px 18px;border-radius:999px;
  background:
    linear-gradient(135deg,rgba(0,229,154,.12),rgba(255,255,255,.03)),
    rgba(8,13,26,.08);
  backdrop-filter:blur(10px) saturate(140%);
  -webkit-backdrop-filter:blur(10px) saturate(140%);
  color:var(--green);
  border:1px solid rgba(0,229,154,.25);
  font-size:12.5px;font-weight:700;letter-spacing:.15px;margin-bottom:22px;
  animation:fadeUp .7s cubic-bezier(.22,1,.36,1) both,badgeGlow 4s ease-in-out infinite 1s;
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.15),
    0 8px 24px rgba(0,229,154,.15);
}
@keyframes badgeGlow{
  0%,100%{box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 8px 24px rgba(0,229,154,.15)}
  50%{box-shadow:inset 0 1px 0 rgba(255,255,255,.2),0 12px 32px rgba(0,229,154,.25),0 0 20px rgba(0,229,154,.1)}
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
.mock{
  border-radius:var(--r-lg);overflow:hidden;
  border:1px solid rgba(255,255,255,.16);
  background:
    linear-gradient(135deg,rgba(255,255,255,.05),rgba(255,255,255,.01) 45%,rgba(0,229,154,.03)),
    rgba(8,13,26,.08);
  backdrop-filter:blur(10px) saturate(140%);
  -webkit-backdrop-filter:blur(10px) saturate(140%);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.18),
    0 32px 80px rgba(0,0,0,.5),
    0 0 60px rgba(0,229,154,.06);
  animation:floatY 7s ease-in-out infinite;
}
@keyframes floatY{0%,100%{transform:translateY(0) rotate(-.35deg)}50%{transform:translateY(-14px) rotate(.35deg)}}
/* Freeze float while visitor interacts with the live demo */
.mock.chat-open{animation-play-state:paused}
.mock-bar{
  padding:11px 14px;
  border-bottom:1px solid rgba(255,255,255,.1);
  display:flex;align-items:center;gap:6px;
  background:rgba(255,255,255,.02);
}
.mock-bar .d{width:11px;height:11px;border-radius:50%}
.mock-bar .d.r{background:#FF5F57}.mock-bar .d.y{background:#FFBD2E}.mock-bar .d.g{background:#28C840}
.mock-bar .url{margin-left:8px;background:rgba(255,255,255,.05);border-radius:6px;padding:4px 12px;
  font-size:11px;color:var(--muted);font-family:'SF Mono',monospace;}
.mock-body{
  position:relative;padding:22px 20px;min-height:330px;
  background:
    linear-gradient(135deg,rgba(255,255,255,.05),rgba(255,255,255,.01) 45%,rgba(0,229,154,.03)),
    rgba(8,13,26,.08);
  backdrop-filter:blur(10px) saturate(140%);
  -webkit-backdrop-filter:blur(10px) saturate(140%);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.18);
}
.mock-skel{display:flex;flex-direction:column;gap:9px;opacity:.32}
.mock-skel .ln{height:9px;border-radius:5px;background:rgba(255,255,255,.15)}
.ln-80{width:80%}.ln-55{width:55%}.ln-70{width:70%}.ln-40{width:40%}.ln-90{width:90%}
.mock-chat{position:absolute;bottom:16px;right:16px;left:16px;display:flex;flex-direction:column;align-items:flex-end;gap:9px;z-index:10}

/* Interactive demo panel */
.mock-panel{
  width:100%;max-width:270px;
  background:
    linear-gradient(135deg,rgba(255,255,255,.05),rgba(255,255,255,.01) 45%,rgba(0,229,154,.03)),
    rgba(8,13,26,.12);
  backdrop-filter:blur(12px) saturate(150%);
  -webkit-backdrop-filter:blur(12px) saturate(150%);
  border:1px solid rgba(255,255,255,.16);border-radius:16px;
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.18),
    0 18px 48px rgba(0,0,0,.5),
    0 0 0 1px rgba(0,229,154,.08);
  overflow:hidden;display:flex;flex-direction:column;
  opacity:0;transform:translateY(14px) scale(.94);transform-origin:bottom right;
  transition:opacity .32s cubic-bezier(.22,1,.36,1),transform .32s cubic-bezier(.22,1,.36,1);
  pointer-events:none;
}
.mock-chat.open .mock-panel{opacity:1;transform:none;pointer-events:auto}
.mock-panel-head{
  display:flex;align-items:center;gap:8px;padding:9px 11px;
  background:linear-gradient(135deg,var(--green),var(--green-2));color:#031018;
}
.mp-avatar{width:24px;height:24px;border-radius:8px;display:grid;place-items:center;
  background:rgba(255,255,255,.28);flex-shrink:0}
.mp-avatar svg{width:14px;height:14px;stroke-width:2.4}
.mp-title{font-size:12px;font-weight:800;line-height:1.15;display:flex;flex-direction:column;min-width:0}
.mp-title em{font-style:normal;font-weight:600;font-size:9.5px;opacity:.75;display:flex;align-items:center;gap:4px}
.mp-title em::before{content:'';width:5px;height:5px;border-radius:50%;background:#065f46;box-shadow:0 0 0 2px rgba(6,95,70,.3);animation:pulseDot 2s infinite}
.mp-close{margin-left:auto;width:22px;height:22px;border-radius:7px;border:none;cursor:pointer;
  background:rgba(3,16,24,.14);color:#031018;display:grid;place-items:center;transition:background .2s}
.mp-close:hover{background:rgba(3,16,24,.28)}
.mp-close svg{width:13px;height:13px;stroke-width:2.6}
.mock-msgs{display:flex;flex-direction:column;gap:7px;padding:11px;min-height:80px;max-height:128px;overflow-y:auto;scrollbar-width:thin}
.mb-bot{align-self:flex-start;background:var(--bg-2);border:1px solid var(--border-2);border-radius:13px 13px 13px 4px;
  padding:8px 12px;font-size:12px;color:var(--text);max-width:88%;line-height:1.55;
  animation:msgIn .38s cubic-bezier(.22,1,.36,1) both;}
.mb-user{align-self:flex-end;background:linear-gradient(135deg,var(--green),var(--green-2));color:#031018;
  border-radius:13px 13px 4px 13px;padding:8px 12px;font-size:12px;font-weight:600;max-width:82%;line-height:1.5;
  animation:msgIn .38s cubic-bezier(.22,1,.36,1) both;}
@keyframes msgIn{from{opacity:0;transform:translateY(9px) scale(.96)}to{opacity:1;transform:none}}
.mb-typing{align-self:flex-start;display:flex;gap:4px;padding:10px 13px;background:var(--bg-2);
  border:1px solid var(--border-2);border-radius:13px 13px 13px 4px;animation:msgIn .3s both}
.mb-typing i{width:6px;height:6px;border-radius:50%;background:var(--text-2);animation:typDot 1.1s ease-in-out infinite}
.mb-typing i:nth-child(2){animation-delay:.15s}
.mb-typing i:nth-child(3){animation-delay:.3s}
@keyframes typDot{0%,60%,100%{transform:translateY(0);opacity:.4}30%{transform:translateY(-4px);opacity:1}}
.mock-chips{display:flex;flex-wrap:wrap;gap:6px;padding:0 11px 12px}
.mock-chip{
  border:1px solid var(--green-line);background:var(--green-dim);color:var(--green);
  font-size:11px;font-weight:700;padding:6px 11px;border-radius:999px;cursor:pointer;
  font-family:inherit;transition:all .2s;animation:msgIn .4s cubic-bezier(.22,1,.36,1) both;
}
.mock-chip:hover{background:rgba(0,229,154,.2);transform:translateY(-1px)}
.mock-chip:active{transform:scale(.96)}

/* FAB + hint */
.mock-fab-row{display:flex;align-items:center;gap:9px}
.mock-hint{
  font-size:11px;font-weight:700;color:var(--green);
  background:var(--green-dim);border:1px solid var(--green-line);
  padding:5px 11px;border-radius:999px;white-space:nowrap;
  animation:hintBob 2.4s ease-in-out infinite;
}
.mock-chat.open .mock-hint{display:none}
@keyframes hintBob{0%,100%{transform:translateX(0)}50%{transform:translateX(-5px)}}
.mock-fab{position:relative;width:46px;height:46px;border-radius:50%;display:grid;place-items:center;
  border:none;cursor:pointer;
  background:linear-gradient(135deg,var(--green),var(--green-2));
  box-shadow:0 4px 20px rgba(0,229,154,.55),0 0 0 4px rgba(0,229,154,.1);
  animation:pulseRing 3s ease infinite;transition:transform .2s;}
.mock-fab:hover{transform:scale(1.08)}
.mock-fab:active{transform:scale(.95)}
@keyframes pulseRing{0%,100%{box-shadow:0 4px 20px rgba(0,229,154,.55),0 0 0 4px rgba(0,229,154,.1)}
  50%{box-shadow:0 4px 28px rgba(0,229,154,.7),0 0 0 9px rgba(0,229,154,.05)}}
.mock-fab svg{color:#031018;width:22px;height:22px;stroke-width:2.5}
.fab-badge{position:absolute;top:-3px;right:-3px;min-width:17px;height:17px;border-radius:999px;
  background:#ef4444;color:#fff;font-size:10px;font-weight:800;display:grid;place-items:center;
  padding:0 4px;box-shadow:0 2px 8px rgba(239,68,68,.5);animation:msgIn .3s both}
/* ── Orbiting Floating Cards with Position Swap ── */
.float-card{
  position:absolute;backdrop-filter:blur(10px) saturate(140%);-webkit-backdrop-filter:blur(10px) saturate(140%);
  background:
    linear-gradient(135deg,rgba(255,255,255,.08),rgba(255,255,255,.02) 45%,rgba(0,229,154,.05)),
    rgba(8,13,26,.12);
  border:1px solid rgba(255,255,255,.18);border-radius:12px;
  padding:9px 14px;
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.2),
    0 12px 28px rgba(0,0,0,.4);
  font-size:12px;font-weight:600;color:var(--text);display:flex;align-items:center;gap:7px;
  white-space:nowrap;
  will-change:transform,z-index;
}
.float-card svg{width:15px;height:15px;color:var(--green)}

/* Orbit container */
.mock-wrap{
  position:relative;
  animation:fadeUp 1s .28s cubic-bezier(.22,1,.36,1) both;
  overflow:visible!important;
}

/* 
  Elliptical orbit tightly around the prototype screen
  Both cards orbit COUNTER-CLOCKWISE
  They are 180° apart - when one is in FRONT, the other is BEHIND
*/

/* fc1: starts at BOTTOM of the ellipse (front/closer) */
.fc1{
  top:50%;left:50%;
  margin-top:180px;margin-left:-60px;
  animation:ringOrbit 8s linear infinite;
}

/* fc2: starts at TOP of the ellipse (back/farther) - 180° offset */
.fc2{
  top:50%;left:50%;
  margin-top:-220px;margin-left:-60px;
  animation:ringOrbit 8s linear infinite;
  animation-delay:-4s;
}

/* 
  Tight elliptical orbit around the prototype screen
  Oval: ~240px horizontal radius, ~200px vertical radius
  Cards scale and z-index change for 3D depth effect
*/
@keyframes ringOrbit{
  0%{
    transform:translate(0,0) scale(1.08) rotate(2deg);
    z-index:35;
  }
  6.25%{
    transform:translate(100px,-25px) scale(1.06) rotate(3deg);
    z-index:32;
  }
  12.5%{
    transform:translate(180px,-70px) scale(1.02) rotate(4deg);
    z-index:26;
  }
  18.75%{
    transform:translate(220px,-120px) scale(.96) rotate(3deg);
    z-index:20;
  }
  25%{
    transform:translate(240px,-170px) scale(.9) rotate(2deg);
    z-index:14;
  }
  31.25%{
    transform:translate(220px,-220px) scale(.86) rotate(0deg);
    z-index:10;
  }
  37.5%{
    transform:translate(160px,-260px) scale(.84) rotate(-2deg);
    z-index:7;
  }
  43.75%{
    transform:translate(80px,-280px) scale(.82) rotate(-3deg);
    z-index:5;
  }
  50%{
    transform:translate(0,-290px) scale(.82) rotate(-2deg);
    z-index:4;
  }
  56.25%{
    transform:translate(-80px,-280px) scale(.83) rotate(-1deg);
    z-index:5;
  }
  62.5%{
    transform:translate(-160px,-260px) scale(.86) rotate(0deg);
    z-index:8;
  }
  68.75%{
    transform:translate(-220px,-220px) scale(.92) rotate(1deg);
    z-index:14;
  }
  75%{
    transform:translate(-240px,-170px) scale(.98) rotate(2deg);
    z-index:22;
  }
  81.25%{
    transform:translate(-220px,-120px) scale(1.02) rotate(3deg);
    z-index:28;
  }
  87.5%{
    transform:translate(-180px,-70px) scale(1.06) rotate(3deg);
    z-index:32;
  }
  93.75%{
    transform:translate(-100px,-25px) scale(1.08) rotate(2deg);
    z-index:35;
  }
  100%{
    transform:translate(0,0) scale(1.08) rotate(2deg);
    z-index:35;
  }
}

/* Add subtle glow pulse to floating cards */
.fc1::before,.fc2::before{
  content:'';position:absolute;inset:-2px;border-radius:inherit;
  background:linear-gradient(135deg,rgba(0,229,154,.15),transparent 60%);
  z-index:-1;opacity:0;
  animation:floatGlow 3s ease-in-out infinite;
}
.fc2::before{animation-delay:1.5s}
@keyframes floatGlow{
  0%,100%{opacity:0;transform:scale(.95)}
  50%{opacity:1;transform:scale(1.02)}
}

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
.stat-cell{position:relative;display:block;padding:28px 20px;text-align:center;border-right:1px solid var(--border-2);
  text-decoration:none;color:inherit;cursor:pointer;overflow:hidden;
  transition:background .3s,transform .3s cubic-bezier(.22,1,.36,1);}
.stat-cell:hover{background:rgba(0,229,154,.07);transform:translateY(-3px)}
.stat-cell:last-child{border-right:none}
.stat-val{font-size:38px;font-weight:900;letter-spacing:-1px;line-height:1;transition:transform .3s}
.stat-cell:hover .stat-val{transform:scale(1.06)}
.stat-val span{background:linear-gradient(135deg,var(--green),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.stat-lbl{font-size:13px;color:var(--text-2);margin-top:7px;font-weight:500;transition:color .3s}
.stat-cell:hover .stat-lbl{color:var(--green)}
.stat-arrow{
  position:absolute;top:10px;right:12px;color:var(--green);
  opacity:0;transform:translate(-6px,6px);transition:opacity .28s,transform .28s cubic-bezier(.22,1,.36,1);
}
.stat-arrow svg{width:14px;height:14px;transform:rotate(-45deg)}
.stat-cell:hover .stat-arrow{opacity:1;transform:none}

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

/* ── Features bento — 2 kolom; kartu tunggal lebar penuh ── */
.feat-bento{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
.feat-card{
  display:block;padding:28px 24px;border-radius:20px;
  background:rgba(10,16,28,.85);border:1px solid var(--border-2);
  position:relative;overflow:hidden;cursor:pointer;
  text-decoration:none;color:inherit;
  transition:border-color .3s,box-shadow .4s,transform .4s cubic-bezier(.22,1,.36,1);
}
/* Corner arrow for clickable cards */
.card-go{
  position:absolute;top:16px;right:16px;z-index:2;
  width:28px;height:28px;border-radius:9px;display:grid;place-items:center;
  background:var(--green-dim);border:1px solid var(--green-line);color:var(--green);
  opacity:0;transform:translate(-6px,6px) rotate(-45deg);
  transition:opacity .3s,transform .3s cubic-bezier(.22,1,.36,1);
}
.card-go svg{width:14px;height:14px}
.feat-card:hover .card-go,.step-card:hover .card-go{opacity:1;transform:rotate(-45deg)}
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
.feat-card.span-full{grid-column:1 / -1}
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
  display:block;padding:30px 24px;border-radius:20px;text-align:center;position:relative;z-index:1;
  background:rgba(10,16,28,.85);border:1px solid var(--border-2);
  text-decoration:none;color:inherit;cursor:pointer;
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
  text-decoration:none;cursor:pointer;
  transition:all .35s cubic-bezier(.22,1,.36,1);
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

/* ── Savings calculator (interactive card) ── */
.calc-card{
  max-width:640px;margin:0 auto;padding:40px clamp(22px,5vw,44px);border-radius:24px;
  background:linear-gradient(160deg,rgba(0,229,154,.06),rgba(10,16,28,.92) 55%);
  border:1px solid var(--green-line);position:relative;overflow:visible;
  box-shadow:0 30px 70px rgba(0,0,0,.45);
  transform:none;-webkit-transform:none;
}
.calc-card::before{
  content:'';position:absolute;top:-60px;right:-60px;width:220px;height:220px;border-radius:50%;
  background:radial-gradient(circle,rgba(0,229,154,.18),transparent 70%);pointer-events:none;
}
.calc-input-row{display:flex;align-items:baseline;justify-content:space-between;gap:10px;margin-bottom:10px;position:relative;z-index:1}
.calc-input-row label{font-size:13.5px;font-weight:700;color:var(--text-2)}
.calc-convos-val{font-size:22px;font-weight:900;color:var(--green);letter-spacing:-.5px}
.calc-slider{
  width:100%;-webkit-appearance:none;appearance:none;height:28px;
  background:transparent;outline:none;margin:0 0 22px;padding:0;
  position:relative;z-index:2;cursor:pointer;
  touch-action:manipulation;-webkit-tap-highlight-color:transparent;
}
.calc-slider::-webkit-slider-runnable-track{
  height:6px;border-radius:999px;
  background:linear-gradient(90deg,var(--green),var(--cyan) var(--fill,50%),rgba(255,255,255,.12) var(--fill,50%));
}
.calc-slider::-moz-range-track{
  height:6px;border-radius:999px;border:none;
  background:linear-gradient(90deg,var(--green),var(--cyan) var(--fill,50%),rgba(255,255,255,.12) var(--fill,50%));
}
.calc-slider::-webkit-slider-thumb{
  -webkit-appearance:none;width:20px;height:20px;border-radius:50%;margin-top:-7px;
  background:#fff;border:3px solid var(--green);box-shadow:0 2px 8px rgba(0,0,0,.4);cursor:pointer;
  transition:transform .15s;
}
.calc-slider::-webkit-slider-thumb:hover{transform:scale(1.12)}
.calc-slider::-moz-range-thumb{
  width:20px;height:20px;border-radius:50%;border:3px solid var(--green);
  background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.4);cursor:pointer;
}
.calc-results{
  display:grid;grid-template-columns:1fr auto 1fr;gap:14px;align-items:center;
  margin-bottom:22px;position:relative;z-index:1;
}
.calc-result-cell{text-align:center;padding:16px 10px;border-radius:16px;background:rgba(255,255,255,.03);border:1px solid var(--border-2)}
.calc-result-cell--vs{background:none;border:none;color:var(--muted);font-size:12px;font-weight:800;text-transform:uppercase;padding:0}
.calc-result-lbl{display:block;font-size:11.5px;color:var(--text-2);margin-bottom:8px;font-weight:600;line-height:1.3}
.calc-result-val{font-size:24px;font-weight:900;letter-spacing:-.5px}
.calc-human{color:#fb923c}
.calc-chatlm{color:var(--green)}
.calc-savings{
  text-align:center;padding:22px;border-radius:18px;margin-bottom:18px;position:relative;z-index:1;
  background:linear-gradient(135deg,rgba(0,229,154,.14),rgba(45,212,191,.08));
  border:1px solid var(--green-line);
}
.calc-savings-lbl{display:block;font-size:12.5px;font-weight:700;color:var(--text-2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
.calc-savings-val{font-size:40px;font-weight:900;letter-spacing:-1.5px;color:var(--green)}
.calc-per-month{font-size:15px;font-weight:700;color:var(--text-2);margin-left:4px}
.calc-note{font-size:12px;color:var(--muted);text-align:center;line-height:1.6;margin-bottom:22px;position:relative;z-index:1}
.calc-card .btn{position:relative;z-index:1}

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

/* ── Glassmorphism — translucent fill + frost blur ── */
.stats-grid,
.feat-card,
.step-card,
.prov-card,
.testi-card,
.calc-card,
.cta-box,
.mock,
.mock-panel,
.float-card,
.hero-badge,
.ticker-outer,
footer{
  background:
    linear-gradient(135deg,rgba(255,255,255,.05),rgba(255,255,255,.01) 45%,rgba(0,229,154,.03)),
    rgba(8,13,26,.08);
  backdrop-filter:blur(10px) saturate(140%);
  -webkit-backdrop-filter:blur(10px) saturate(140%);
  border-color:rgba(255,255,255,.16);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.18),
    0 12px 40px rgba(0,0,0,.22);
}

/* ── Hero Section Glass Elements ── */
.hero-trust{
  background:
    linear-gradient(135deg,rgba(255,255,255,.04),rgba(255,255,255,.01) 50%,rgba(0,229,154,.02)),
    rgba(8,13,26,.06);
  backdrop-filter:blur(8px) saturate(130%);
  -webkit-backdrop-filter:blur(8px) saturate(130%);
  border:1px solid rgba(255,255,255,.1);
  border-radius:16px;
  padding:14px 20px;
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.12),
    0 8px 24px rgba(0,0,0,.15);
}

/* Glass effect for hero CTA buttons */
.hero-cta .btn-outline{
  background:
    linear-gradient(135deg,rgba(255,255,255,.06),rgba(255,255,255,.02)),
    rgba(8,13,26,.1);
  backdrop-filter:blur(8px) saturate(130%);
  -webkit-backdrop-filter:blur(8px) saturate(130%);
  border-color:rgba(255,255,255,.18);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.15),
    0 6px 20px rgba(0,0,0,.18);
}
.hero-cta .btn-outline:hover{
  background:
    linear-gradient(135deg,rgba(255,255,255,.1),rgba(0,229,154,.05)),
    rgba(8,13,26,.15);
  border-color:var(--green-line);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.2),
    0 10px 30px rgba(0,229,154,.15);
}
.prov-card.rec{
  background:
    linear-gradient(135deg,rgba(0,229,154,.12),rgba(255,255,255,.03)),
    rgba(8,22,20,.10);
  border-color:rgba(0,229,154,.28);
}
.cta-box{
  background:
    radial-gradient(ellipse at 30% 0%,rgba(0,229,154,.12),transparent 58%),
    radial-gradient(ellipse at 80% 100%,rgba(59,130,246,.08),transparent 60%),
    rgba(8,13,26,.10);
}

/* ── Footer ── */
footer{
  position:relative;z-index:1;border-top:1px solid var(--border);
  padding:32px 24px;display:flex;flex-wrap:wrap;align-items:center;
  justify-content:space-between;gap:18px;color:var(--muted);font-size:13px;
}
footer a{color:var(--green)}
footer a:hover{opacity:.75}
.footer-links{display:flex;gap:18px}
.footer-meta{display:flex;flex-direction:column;gap:6px;line-height:1.45}
.footer-dev{display:flex;flex-wrap:wrap;align-items:center;gap:6px;color:var(--text-2)}
.footer-dev strong{color:var(--text);font-weight:700}
.footer-dev a{font-weight:600}
.footer-dot{color:var(--muted)}

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
@media(max-width:780px){
  .calc-slider{height:36px}
  .calc-slider::-webkit-slider-thumb{width:24px;height:24px;margin-top:-9px;border-width:3px}
  .calc-slider::-moz-range-thumb{width:24px;height:24px;border-width:3px}
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
<canvas id="pcv" style="position:fixed;inset:0;z-index:0;pointer-events:none;opacity:.92"></canvas>
<div class="orb orb-1" data-plx="0.1"></div>
<div class="orb orb-2" data-plx="-0.07"></div>
<div class="orb orb-3" data-plx="0.14"></div>

<!-- ═══ NAV ═══ -->
<header class="nav">
  <div class="nav-in">
    <a href="<?= esc(app_url('/')) ?>" class="brand">
      <?= brand_mark_html(36) ?>
      <span class="brand-text"><?= brand_name_html() ?></span>
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

    <button class="nav-burger" id="navBurger" type="button"
            aria-label="Open menu" aria-expanded="false" aria-controls="navLinks">
      <span class="nav-ico-menu"><?= icon('menu', 20) ?></span>
      <span class="nav-ico-close" style="display:none"><?= icon('x', 20) ?></span>
    </button>

    <nav class="nav-links" id="navLinks" aria-label="Main navigation">
      <div class="nav-drawer-main">
        <a class="nav-link" href="#features"><?= esc($t['nav_features']) ?></a>
        <a class="nav-link" href="#how"><?= esc($t['nav_how']) ?></a>
        <a class="nav-link" href="#providers"><?= esc($t['nav_providers']) ?></a>
        <a class="nav-link" href="<?= esc(app_url('/pricing.php')) ?>"><?= esc($t['nav_pricing']) ?></a>
        <a class="nav-link" href="<?= esc(app_url('/docs/')) ?>"><?= esc($t['nav_docs']) ?></a>
        <a class="nav-link" href="<?= esc(app_url('/blog/')) ?>"><?= esc($t['nav_blog']) ?></a>
        <a class="nav-link" href="<?= esc(app_url('/login.php')) ?>"><?= esc($t['nav_login']) ?></a>
      </div>

      <div class="nav-langs-mobile" aria-label="Language">
        <?php foreach ($meta as $code => $info): ?>
        <a class="nlm-opt <?= $code === $lang ? 'cur' : '' ?>"
           href="<?= esc(lang_switch_url($code)) ?>">
          <span><?= $info['flag'] ?></span>
          <span class="nlm-lbl"><?= esc($info['label']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>

      <a class="nav-link btn btn-primary nav-cta-register" href="<?= esc(app_url('/register.php')) ?>">
        <?= esc($t['nav_register']) ?> <?= icon('arrow-right', 14) ?>
      </a>
    </nav>
  </div>
</header>
<div class="nav-spacer" aria-hidden="true"></div>
<div class="nav-backdrop" id="navBackdrop"></div>

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

  <div class="mock-wrap" data-plx="-0.06">
    <div class="float-card fc1" data-plx="-0.12"><?= icon('zap', 15) ?> <?= esc($t['float_1']) ?></div>
    <div class="float-card fc2" data-plx="0.1"><?= icon('shield', 15) ?> <?= esc($t['float_2']) ?></div>
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

          <!-- Live interactive demo chat -->
          <div class="mock-chat" id="mockChat"
               data-demo='<?= esc(json_encode([
                   'bot'   => $t['mock_bot'],
                   'user'  => $t['mock_user'],
                   'chips' => [
                       ['q' => $t['demo_q1'] ?? 'How much does it cost?', 'a' => $t['demo_a1'] ?? ''],
                       ['q' => $t['demo_q2'] ?? 'How do I install it?',   'a' => $t['demo_a2'] ?? ''],
                       ['q' => $t['demo_q3'] ?? 'Is my data secure?',     'a' => $t['demo_a3'] ?? ''],
                   ],
               ], JSON_UNESCAPED_UNICODE)) ?>'>
            <div class="mock-panel" id="mockPanel">
              <div class="mock-panel-head">
                <span class="mp-avatar"><?= icon('bot', 14) ?></span>
                <span class="mp-title">ChatLM<em><?= esc($t['demo_status'] ?? 'Online') ?></em></span>
                <button type="button" class="mp-close" id="mockClose" aria-label="Close demo chat"><?= icon('x', 14) ?></button>
              </div>
              <div class="mock-msgs" id="mockMsgs" aria-live="polite"></div>
              <div class="mock-chips" id="mockChips"></div>
            </div>
            <div class="mock-fab-row">
              <span class="mock-hint" id="mockHint"><?= esc($t['demo_hint'] ?? 'Try me') ?></span>
              <button type="button" class="mock-fab" id="mockFab" aria-label="Open demo chat" aria-expanded="false">
                <span class="fab-ico-chat"><?= icon('message', 20) ?></span>
                <span class="fab-ico-close" style="display:none"><?= icon('x', 20) ?></span>
                <span class="fab-badge" id="mockBadge" style="display:none">1</span>
              </button>
            </div>
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

<!-- ═══ STATS (clickable) ═══ -->
<div class="stats-wrap">
  <div class="stats-grid">
    <?php
    $stats = [
      [$t['stat_1_v'], $t['stat_1_suf'], $t['stat_1_lbl'], '#how'],
      [$t['stat_2_v'], $t['stat_2_suf'], $t['stat_2_lbl'], '#providers'],
      [$t['stat_3_v'], $t['stat_3_suf'], $t['stat_3_lbl'], app_url('/docs/embed-widget')],
      [$t['stat_4_v'], $t['stat_4_suf'], $t['stat_4_lbl'], app_url('/docs/')],
    ];
    foreach ($stats as $i => [$v, $suf, $lbl, $href]):
    ?>
    <a class="stat-cell sr d<?= $i+1 ?>" href="<?= esc($href) ?>">
      <div class="stat-val">
        <span data-count="<?= esc($v) ?>" data-suf="<?= esc($suf) ?>"><?= esc($v.$suf) ?></span>
      </div>
      <div class="stat-lbl"><?= esc($lbl) ?></div>
      <span class="stat-arrow" aria-hidden="true"><?= icon('arrow-right', 14) ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- ═══ FEATURES ═══ -->
<section class="lp-sec" id="features">
  <div class="sec-kicker sr"><?= icon('sparkles', 13) ?> <?= esc($t['feat_tag']) ?></div>
  <h2 class="sec-h2 sr d1"><?= nl2br(esc($t['feat_h2'])) ?></h2>
  <p class="sec-sub sr d2"><?= esc($t['feat_sub']) ?></p>
  <div class="feat-bento">
    <?php
    $featCards = [
      ['rocket',  $t['feat_1_h'], $t['feat_1_p'], app_url('/docs/quick-start'),   '',          'd1'],
      ['bot',     $t['feat_2_h'], $t['feat_2_p'], app_url('/docs/ai-providers'),  '',          'd2'],
      ['palette', $t['feat_3_h'], $t['feat_3_p'], app_url('/docs/create-account'),'span-full', 'd3'],
      ['brain',   $t['feat_4_h'], $t['feat_4_p'], app_url('/docs/embed-widget'),  'span-full', 'd4'],
      ['shield',  $t['feat_6_h'], $t['feat_6_p'], app_url('/docs/allowed-domains'),'',         'd5'],
      ['phone',   $t['feat_5_h'], $t['feat_5_p'], app_url('/docs/telegram'),      '',          'd6'],
    ];
    foreach ($featCards as [$ico, $h, $p, $href, $span, $delay]):
    ?>
    <a class="feat-card <?= $span ?> sr <?= $delay ?>" href="<?= esc($href) ?>">
      <div class="feat-icon"><?= icon($ico, 22) ?></div>
      <h3><?= esc($h) ?></h3>
      <p><?= esc($p) ?></p>
      <span class="card-go" aria-hidden="true"><?= icon('arrow-right', 15) ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ═══ HOW IT WORKS ═══ -->
<section class="lp-sec" id="how">
  <div class="sec-kicker sr"><?= icon('zap', 13) ?> <?= esc($t['how_tag']) ?></div>
  <h2 class="sec-h2 sr d1"><?= esc($t['how_h2']) ?></h2>
  <p class="sec-sub sr d2"><?= esc($t['how_sub']) ?></p>
  <div class="steps-grid">
    <a class="step-card sr d1" href="<?= esc(app_url('/register.php')) ?>">
      <div class="step-num">1</div>
      <h3><?= esc($t['step_1_h']) ?></h3>
      <p><?= $t['step_1_p'] /* contains escaped HTML entity */ ?></p>
      <span class="card-go" aria-hidden="true"><?= icon('arrow-right', 15) ?></span>
    </a>
    <a class="step-card sr d2" href="<?= esc(app_url('/docs/create-account')) ?>">
      <div class="step-num">2</div>
      <h3><?= esc($t['step_2_h']) ?></h3>
      <p><?= esc($t['step_2_p']) ?></p>
      <span class="card-go" aria-hidden="true"><?= icon('arrow-right', 15) ?></span>
    </a>
    <a class="step-card sr d3" href="<?= esc(app_url('/docs/embed-widget')) ?>">
      <div class="step-num">3</div>
      <h3><?= esc($t['step_3_h']) ?></h3>
      <p><?= $t['step_3_p'] /* contains &lt;/body&gt; */ ?></p>
      <span class="card-go" aria-hidden="true"><?= icon('arrow-right', 15) ?></span>
    </a>
  </div>
</section>

<!-- ═══ PROVIDERS ═══ -->
<section class="lp-sec" id="providers">
  <div class="sec-kicker sr"><?= icon('brain', 13) ?> <?= esc($t['prov_tag']) ?></div>
  <h2 class="sec-h2 sr d1"><?= esc($t['prov_h2']) ?></h2>
  <p class="sec-sub sr d2"><?= esc($t['prov_sub']) ?></p>
  <div class="prov-grid">
    <a class="prov-card rec sr d1" href="<?= esc(app_url('/docs/ai-providers')) ?>">
      <div class="prov-icon" style="background:rgba(0,229,154,.12);color:var(--green);box-shadow:0 0 22px rgba(0,229,154,.3)">
        <?= icon('sparkles', 22) ?>
      </div>
      <span><?= esc($t['prov_1']) ?></span>
      <span class="prov-sub"><?= esc($t['prov_1_sub']) ?></span>
    </a>
    <a class="prov-card sr d2" href="<?= esc(app_url('/docs/ai-providers')) ?>">
      <div class="prov-icon" style="background:#10A37F1A;color:#10A37F"><?= icon('bot', 22) ?></div>
      <span><?= esc($t['prov_2']) ?></span>
      <span class="prov-sub"><?= esc($t['prov_2_sub']) ?></span>
    </a>
    <a class="prov-card sr d3" href="<?= esc(app_url('/docs/ai-providers')) ?>">
      <div class="prov-icon" style="background:#4285F41A;color:#4285F4"><?= icon('brain', 22) ?></div>
      <span><?= esc($t['prov_3']) ?></span>
      <span class="prov-sub"><?= esc($t['prov_3_sub']) ?></span>
    </a>
    <a class="prov-card sr d4" href="<?= esc(app_url('/docs/ai-providers')) ?>">
      <div class="prov-icon" style="background:#1F6FEB1A;color:#1F6FEB"><?= icon('rocket', 22) ?></div>
      <span><?= esc($t['prov_4']) ?></span>
      <span class="prov-sub"><?= esc($t['prov_4_sub']) ?></span>
    </a>
  </div>
</section>

<!-- ═══ SAVINGS CALCULATOR ═══ -->
<section class="lp-sec" id="calculator">
  <div class="sec-kicker sr"><?= icon('calculator', 13) ?> <?= esc($t['calc_tag']) ?></div>
  <h2 class="sec-h2 sr d1"><?= esc($t['calc_h2']) ?></h2>
  <p class="sec-sub sr d2"><?= esc($t['calc_sub']) ?></p>
  <div class="calc-card">
    <div class="calc-input-row">
      <label for="calcConvos"><?= esc($t['calc_label_convos']) ?></label>
      <span class="calc-convos-val" id="calcConvosVal">800</span>
    </div>
    <input type="range" id="calcConvos" class="calc-slider" min="100" max="5000" step="50" value="800"
           aria-label="<?= esc($t['calc_label_convos']) ?>">
    <div class="calc-results">
      <div class="calc-result-cell">
        <span class="calc-result-lbl"><?= esc($t['calc_label_human']) ?></span>
        <span class="calc-result-val calc-human" id="calcHuman">$400</span>
      </div>
      <div class="calc-result-cell calc-result-cell--vs">vs</div>
      <div class="calc-result-cell">
        <span class="calc-result-lbl"><?= esc($t['calc_label_chatlm']) ?></span>
        <span class="calc-result-val calc-chatlm" id="calcChatlm">$19</span>
      </div>
    </div>
    <div class="calc-savings">
      <span class="calc-savings-lbl"><?= esc($t['calc_label_savings']) ?></span>
      <span class="calc-savings-val"><span id="calcSavings">$381</span><span class="calc-per-month"><?= esc($t['calc_per_month']) ?></span></span>
    </div>
    <p class="calc-note"><?= esc($t['calc_note']) ?></p>
    <a class="btn btn-primary btn-lg btn-block btn-mag" href="<?= esc(app_url('/register.php')) ?>">
      <?= esc($t['calc_cta']) ?> <?= icon('arrow-right', 18) ?>
    </a>
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
  <div class="footer-meta">
    <span>&copy; <?= date('Y') ?> <?= brand_name_html() ?>. All rights reserved.</span>
    <span class="footer-dev">
      Developed by
      <a href="https://dev-khalfikri.pantheonsite.io/" target="_blank" rel="noopener noreferrer"><strong>KalFikri</strong></a>
      <span class="footer-dot">·</span>
      <a href="https://www.linkedin.com/in/muhamad-fikri-haikal-fullstack-web-developer/" target="_blank" rel="noopener noreferrer">LinkedIn</a>
      <span class="footer-dot">·</span>
      <a href="mailto:muhamadfikrih29@gmail.com">muhamadfikrih29@gmail.com</a>
    </span>
  </div>
  <div class="footer-links">
    <a href="<?= esc(app_url('/docs/')) ?>"><?= esc($t['footer_docs']) ?></a>
    <a href="<?= esc(app_url('/blog/')) ?>"><?= esc($t['footer_blog']) ?></a>
    <a href="<?= esc(app_url('/pricing.php')) ?>"><?= esc($t['footer_pricing']) ?></a>
    <a href="<?= esc(app_url('/login.php')) ?>"><?= esc($t['footer_login']) ?></a>
    <a href="<?= esc(app_url('/register.php')) ?>"><?= esc($t['footer_reg']) ?></a>
  </div>
</footer>

<?php
$calcJsVer = (int) (@filemtime(__DIR__ . '/js/calc.js') ?: time());
$landJsVer = (int) (@filemtime(__DIR__ . '/js/landing.js') ?: time());
?>
<script src="/js/ui.js" defer></script>
<script src="/js/calc.js?v=<?= $calcJsVer ?>" defer></script>
<script src="/js/landing.js?v=<?= $landJsVer ?>" defer></script>
<?php require __DIR__ . '/includes/partials/widget_embed.php'; ?>
</body>
</html>
