/**
 * ChatLM — Landing Page JS
 * Scroll-progress · Particle canvas · 3D tilt · Typewriter
 * Scroll-reveal · Counter · Magnetic buttons · Card cursor-glow
 * Nav active link · Mock chat reveal
 */
(function () {
  'use strict';

  function initLandingMenu() {
    var burger = document.getElementById('navBurger');
    var drawer = document.getElementById('navLinks');
    var backdrop = document.getElementById('navBackdrop');
    if (!burger || !drawer) return;

    var menuIcon = burger.querySelector('.nav-ico-menu');
    var closeIcon = burger.querySelector('.nav-ico-close');

    function setMenu(open) {
      drawer.classList.toggle('is-open', !!open);
      if (backdrop) backdrop.classList.toggle('is-open', !!open);
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (menuIcon) menuIcon.style.display = open ? 'none' : 'grid';
      if (closeIcon) closeIcon.style.display = open ? 'grid' : 'none';
      document.body.style.overflow = open ? 'hidden' : '';
    }

    window.toggleLandingMenu = function () {
      setMenu(!drawer.classList.contains('is-open'));
      return false;
    };

    window.closeLandingMenu = function () {
      setMenu(false);
      return false;
    };

    if (!burger.dataset.cpBound) {
      burger.dataset.cpBound = '1';
      burger.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        window.toggleLandingMenu();
      });
    }

    if (backdrop && !backdrop.dataset.cpBound) {
      backdrop.dataset.cpBound = '1';
      backdrop.addEventListener('click', function () {
        window.closeLandingMenu();
      });
    }

    drawer.querySelectorAll('a').forEach(function (link) {
      if (link.dataset.cpBound) return;
      link.dataset.cpBound = '1';
      link.addEventListener('click', function () {
        window.closeLandingMenu();
      });
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 780) window.closeLandingMenu();
    }, { passive: true });

    setMenu(false);
  }

  function initLandingLanguageDropdown() {
    var wrap = document.getElementById('langWrap');
    var btn = document.getElementById('langBtn');
    var drop = document.getElementById('langDrop');
    if (!wrap || !btn || !drop) return;

    function positionDrop() {
      var rect = btn.getBoundingClientRect();
      drop.style.top = (rect.bottom + 6) + 'px';
      var dropWidth = drop.offsetWidth || 160;
      var left = rect.right - dropWidth;
      if (left < 8) left = 8;
      drop.style.left = left + 'px';
      drop.style.right = 'auto';
    }

    function openDrop() {
      positionDrop();
      wrap.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
    }

    function closeDrop() {
      wrap.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    }

    window.toggleLandingLanguageDropdown = function () {
      if (wrap.classList.contains('open')) closeDrop();
      else openDrop();
      return false;
    };

    if (!btn.dataset.cpBound) {
      btn.dataset.cpBound = '1';
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        window.toggleLandingLanguageDropdown();
      });
    }

    document.addEventListener('click', function (e) {
      if (!wrap.contains(e.target)) closeDrop();
    });

    window.addEventListener('scroll', function () {
      if (wrap.classList.contains('open')) positionDrop();
    }, { passive: true });

    window.addEventListener('resize', function () {
      if (wrap.classList.contains('open')) positionDrop();
    }, { passive: true });
  }

  initLandingMenu();
  initLandingLanguageDropdown();

  /* ─────────────────────────────────────────────
     1. SCROLL PROGRESS BAR
  ───────────────────────────────────────────── */
  var sp = document.getElementById('sp');
  function updateSP() {
    if (!sp) return;
    var d = document.documentElement;
    var pct = d.scrollHeight - d.clientHeight;
    sp.style.width = (pct > 0 ? (d.scrollTop || document.body.scrollTop) / pct * 100 : 0).toFixed(2) + '%';
  }
  window.addEventListener('scroll', updateSP, { passive: true });

  /* ─────────────────────────────────────────────
     2. CONSTELLATION FIELD
        Glowing dots + brand nodes (platforms ChatLM
        installs on, and the AI models it can run).
        Nodes drift, link up, magnetise toward the
        cursor, reveal their name up close, and burst
        outward on click.
  ───────────────────────────────────────────── */
  var cv = document.getElementById('pcv');
  if (cv) {
    var cx = cv.getContext('2d');
    var TAU = Math.PI * 2;
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Monogram + brand colour. `glyph` draws a custom mark instead of letters. */
    var BRANDS = [
      { m: 'WP',   l: 'WordPress',  c: '#4C86FF' },
      { m: 'S',    l: 'Shopify',    c: '#95BF47', glyph: 'bag' },
      { m: '',     l: 'React',      c: '#61DAFB', glyph: 'atom' },
      { m: 'WF',   l: 'Webflow',    c: '#4A7DFF' },
      { m: 'Wix',  l: 'Wix',        c: '#F5D74B' },
      { m: 'N',    l: 'Next.js',    c: '#E8EDF5' },
      { m: 'SQ',   l: 'Squarespace',c: '#B8C4D4' },
      { m: '</>',  l: 'Any HTML',   c: '#F0803C' },
      { m: 'GPT',  l: 'OpenAI',     c: '#10A37F' },
      { m: '',     l: 'Gemini',     c: '#7BA7FF', glyph: 'spark' },
      { m: 'DS',   l: 'DeepSeek',   c: '#6C8CFF' },
      { m: 'OR',   l: 'OpenRouter', c: '#9B8CFF' },
      { m: 'OL',   l: 'Ollama',     c: '#E2E8F0' },
      { m: 'CL',   l: 'Claude',     c: '#E0885F' },
      { m: 'MS',   l: 'Mistral',    c: '#FF8A3D' },
      { m: 'LM',   l: 'Llama',      c: '#4C9BFF' }
    ];

    var W = 0, H = 0, DPR = 1, dim = 1;
    var nodes = [], chips = [], ripples = [];
    var glowCache = {};
    var mx = -9999, my = -9999;

    function rnd(min, max) { return min + Math.random() * (max - min); }

    function rgba(hex, a) {
      var n = parseInt(hex.slice(1), 16);
      return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + a + ')';
    }

    function resizeCV() {
      DPR = Math.min(window.devicePixelRatio || 1, 2);
      W = window.innerWidth;
      H = window.innerHeight;
      cv.width = Math.round(W * DPR);
      cv.height = Math.round(H * DPR);
      cv.style.width = W + 'px';
      cv.style.height = H + 'px';
      cx.setTransform(DPR, 0, 0, DPR, 0, 0);
      glowCache = {};
    }

    /* Pre-rendered halo — far cheaper than a gradient per dot per frame. */
    function glowSprite(color, rad) {
      var key = color + '|' + rad;
      if (glowCache[key]) return glowCache[key];
      var px = Math.max(4, Math.ceil(rad * 2 * DPR));
      var oc = document.createElement('canvas');
      oc.width = oc.height = px;
      var octx = oc.getContext('2d');
      var grd = octx.createRadialGradient(px / 2, px / 2, 0, px / 2, px / 2, px / 2);
      grd.addColorStop(0, rgba(color, .55));
      grd.addColorStop(1, rgba(color, 0));
      octx.fillStyle = grd;
      octx.fillRect(0, 0, px, px);
      glowCache[key] = oc;
      return oc;
    }

    function build() {
      var small = W < 760;
      dim = small ? .62 : 1;
      var dotCount = small ? 26 : 58;
      var chipCount = small ? 6 : BRANDS.length;

      nodes = [];
      chips = [];

      for (var i = 0; i < dotCount; i++) {
        var dvx = rnd(-.22, .22), dvy = rnd(-.22, .22);
        nodes.push({
          kind: 'dot',
          x: Math.random() * W, y: Math.random() * H,
          vx: dvx, vy: dvy, bvx: dvx, bvy: dvy,
          r: rnd(1.1, 2.6),
          a: rnd(.35, .8),
          c: Math.random() > .62 ? '#22D3EE' : '#14F0A8',
          tw: Math.random() * TAU
        });
      }

      for (var j = 0; j < chipCount; j++) {
        var cvx = rnd(-.16, .16), cvy = rnd(-.16, .16);
        var chip = {
          kind: 'chip',
          brand: BRANDS[j % BRANDS.length],
          x: Math.random() * W, y: Math.random() * H,
          vx: cvx, vy: cvy, bvx: cvx, bvy: cvy,
          r: small ? rnd(12, 15) : rnd(15, 20),
          s: 1, glow: 0, spin: Math.random() * TAU
        };
        chips.push(chip);
        nodes.push(chip);
      }
    }

    function roundRect(x, y, w, h, r) {
      if (cx.roundRect) { cx.beginPath(); cx.roundRect(x, y, w, h, r); return; }
      cx.beginPath();
      cx.moveTo(x + r, y);
      cx.arcTo(x + w, y, x + w, y + h, r);
      cx.arcTo(x + w, y + h, x, y + h, r);
      cx.arcTo(x, y + h, x, y, r);
      cx.arcTo(x, y, x + w, y, r);
      cx.closePath();
    }

    /* React-style atom: three tilted orbits around a nucleus. */
    function drawAtom(r, color, spin) {
      cx.save();
      cx.rotate(spin);
      cx.strokeStyle = color;
      cx.lineWidth = Math.max(.9, r * .075);
      for (var i = 0; i < 3; i++) {
        cx.save();
        cx.rotate((Math.PI / 3) * i);
        cx.beginPath();
        cx.ellipse(0, 0, r * .62, r * .24, 0, 0, TAU);
        cx.stroke();
        cx.restore();
      }
      cx.restore();
      cx.beginPath();
      cx.arc(0, 0, r * .15, 0, TAU);
      cx.fillStyle = color;
      cx.fill();
    }

    /* Gemini-style four-point spark. */
    function drawSpark(r, color) {
      var o = r * .62, w = r * .2;
      cx.beginPath();
      cx.moveTo(0, -o);
      cx.quadraticCurveTo(w, -w, o, 0);
      cx.quadraticCurveTo(w, w, 0, o);
      cx.quadraticCurveTo(-w, w, -o, 0);
      cx.quadraticCurveTo(-w, -w, 0, -o);
      cx.closePath();
      cx.fillStyle = color;
      cx.fill();
    }

    /* Shopify-style shopping bag. */
    function drawBag(r, color) {
      var w = r * .82, h = r * .78;
      cx.lineWidth = Math.max(1, r * .1);
      cx.strokeStyle = color;
      roundRect(-w / 2, -h / 2 + r * .12, w, h, r * .18);
      cx.stroke();
      cx.beginPath();
      cx.arc(0, -h / 2 + r * .14, r * .24, Math.PI, 0);
      cx.stroke();
    }

    function drawChip(n) {
      var r = n.r * n.s;
      var b = n.brand;

      cx.save();
      cx.translate(n.x, n.y);

      if (n.glow > .01) {
        cx.beginPath();
        cx.arc(0, 0, r * 2.1, 0, TAU);
        cx.fillStyle = rgba(b.c, .13 * n.glow);
        cx.fill();
      }

      cx.beginPath();
      cx.arc(0, 0, r, 0, TAU);
      cx.fillStyle = 'rgba(7,12,22,' + (.7 + .22 * n.glow) + ')';
      cx.fill();
      cx.lineWidth = 1.2 + n.glow * 1.1;
      cx.strokeStyle = rgba(b.c, (.42 + .5 * n.glow) * dim);
      cx.stroke();

      var ink = rgba(b.c, (.78 + .22 * n.glow) * dim);
      if (b.glyph === 'atom') drawAtom(r, ink, n.spin);
      else if (b.glyph === 'spark') drawSpark(r, ink);
      else if (b.glyph === 'bag') drawBag(r, ink);
      else {
        cx.font = '800 ' + (r * (b.m.length > 2 ? .58 : .72)).toFixed(1) + 'px Inter,system-ui,sans-serif';
        cx.textAlign = 'center';
        cx.textBaseline = 'middle';
        cx.fillStyle = ink;
        cx.fillText(b.m, 0, r * .04);
      }
      cx.restore();

      /* Name tag once the cursor gets close */
      if (n.glow > .3) {
        var la = Math.min(1, (n.glow - .3) / .35);
        cx.save();
        cx.font = '700 11px Inter,system-ui,sans-serif';
        cx.textAlign = 'center';
        cx.textBaseline = 'middle';
        var tw = cx.measureText(b.l).width;
        var ly = n.y + r + 14;
        roundRect(n.x - tw / 2 - 8, ly - 9.5, tw + 16, 19, 9.5);
        cx.fillStyle = 'rgba(3,7,18,' + (.88 * la) + ')';
        cx.fill();
        cx.lineWidth = 1;
        cx.strokeStyle = rgba(b.c, .4 * la);
        cx.stroke();
        cx.fillStyle = rgba(b.c, .95 * la);
        cx.fillText(b.l, n.x, ly);
        cx.restore();
      }
    }

    function step() {
      var i, n;

      for (i = 0; i < ripples.length; i++) {
        ripples[i].r += 9;
        ripples[i].a -= .022;
      }
      ripples = ripples.filter(function (rp) { return rp.a > 0; });

      for (i = 0; i < nodes.length; i++) {
        n = nodes[i];
        n.x += n.vx;
        n.y += n.vy;

        var pad = n.kind === 'chip' ? n.r + 26 : 10;
        if (n.x < -pad) n.x = W + pad; else if (n.x > W + pad) n.x = -pad;
        if (n.y < -pad) n.y = H + pad; else if (n.y > H + pad) n.y = -pad;

        var dx = n.x - mx, dy = n.y - my;
        var d = Math.sqrt(dx * dx + dy * dy) || 1;

        if (n.kind === 'dot') {
          /* dots scatter away from the pointer */
          if (d < 130) {
            var push = (130 - d) / 130 * 2.6;
            n.x += dx / d * push;
            n.y += dy / d * push;
          }
          n.tw += .028;
        } else {
          /* brand chips are drawn in and light up instead */
          var near = d < 190 ? 1 - d / 190 : 0;
          n.glow += (near - n.glow) * .12;
          n.s += ((1 + near * .55) - n.s) * .12;
          n.spin += .006 + near * .02;
          if (near > 0 && d > 46) {
            var pull = near * .55;
            n.x -= dx / d * pull;
            n.y -= dy / d * pull;
          }
        }
      }

      /* keep gathered chips from stacking on top of each other */
      for (i = 0; i < chips.length; i++) {
        for (var j = i + 1; j < chips.length; j++) {
          var ca = chips[i], cb = chips[j];
          var sx = ca.x - cb.x, sy = ca.y - cb.y;
          var sd = Math.sqrt(sx * sx + sy * sy) || 1;
          var minD = (ca.r * ca.s + cb.r * cb.s) + 14;
          if (sd >= minD) continue;
          var sep = (minD - sd) / minD * .9;
          ca.x += sx / sd * sep;
          ca.y += sy / sd * sep;
          cb.x -= sx / sd * sep;
          cb.y -= sy / sd * sep;
        }
      }
    }

    function render() {
      cx.clearRect(0, 0, W, H);

      /* links */
      for (var a = 0; a < nodes.length; a++) {
        var na = nodes[a];
        for (var b = a + 1; b < nodes.length; b++) {
          var nb = nodes[b];
          var dx = na.x - nb.x, dy = na.y - nb.y;
          var d2 = dx * dx + dy * dy;
          var reach = (na.kind === 'chip' || nb.kind === 'chip') ? 150 : 108;
          if (d2 >= reach * reach) continue;

          var strength = 1 - Math.sqrt(d2) / reach;
          var boost = Math.max(na.glow || 0, nb.glow || 0);
          var tint = na.kind === 'chip' ? na.brand.c : (nb.kind === 'chip' ? nb.brand.c : '#14F0A8');
          cx.strokeStyle = rgba(tint, strength * (.16 + boost * .5) * dim);
          cx.lineWidth = .5 + boost * 1.1;
          cx.beginPath();
          cx.moveTo(na.x, na.y);
          cx.lineTo(nb.x, nb.y);
          cx.stroke();
        }
      }

      /* click ripples */
      for (var k = 0; k < ripples.length; k++) {
        var rp = ripples[k];
        cx.beginPath();
        cx.arc(rp.x, rp.y, rp.r, 0, TAU);
        cx.strokeStyle = rgba('#14F0A8', rp.a * .5);
        cx.lineWidth = 2;
        cx.stroke();
      }

      /* dots — cached halo sprite + crisp core */
      for (var i = 0; i < nodes.length; i++) {
        var n = nodes[i];
        if (n.kind !== 'dot') continue;
        var pulse = .78 + Math.sin(n.tw) * .22;
        var rr = n.r * pulse;
        var halo = Math.round(rr * 4);
        cx.globalAlpha = Math.min(1, n.a * pulse) * dim;
        cx.drawImage(glowSprite(n.c, halo), n.x - halo, n.y - halo, halo * 2, halo * 2);
        cx.globalAlpha = 1;
        cx.beginPath();
        cx.arc(n.x, n.y, rr, 0, TAU);
        cx.fillStyle = rgba(n.c, Math.min(1, n.a * pulse + .12) * dim);
        cx.fill();
      }

      /* brand chips on top */
      for (var c = 0; c < chips.length; c++) drawChip(chips[c]);
    }

    resizeCV();
    build();

    window.addEventListener('resize', function () {
      var wasSmall = W < 760;
      resizeCV();
      if ((W < 760) !== wasSmall) build();
    }, { passive: true });

    window.addEventListener('mousemove', function (e) {
      mx = e.clientX; my = e.clientY;
    }, { passive: true });

    window.addEventListener('mouseout', function (e) {
      if (!e.relatedTarget) { mx = -9999; my = -9999; }
    }, { passive: true });

    if (!reduceMotion) {
      window.addEventListener('pointerdown', function (e) {
        ripples.push({ x: e.clientX, y: e.clientY, r: 4, a: 1 });
        for (var i = 0; i < nodes.length; i++) {
          var n = nodes[i];
          var dx = n.x - e.clientX, dy = n.y - e.clientY;
          var d = Math.sqrt(dx * dx + dy * dy) || 1;
          if (d > 260) continue;
          var f = (260 - d) / 260 * (n.kind === 'chip' ? 3.4 : 6.5);
          n.vx += dx / d * f;
          n.vy += dy / d * f;
        }
      }, { passive: true });
    }

    if (reduceMotion) {
      render();
    } else {
      var running = false;
      var frame = function () {
        if (document.hidden) { running = false; return; }
        step();
        /* ease the click impulse back to each node's natural drift */
        for (var i = 0; i < nodes.length; i++) {
          var n = nodes[i];
          n.vx += (n.bvx - n.vx) * .035;
          n.vy += (n.bvy - n.vy) * .035;
        }
        render();
        requestAnimationFrame(frame);
      };
      var start = function () {
        if (running || document.hidden) return;
        running = true;
        requestAnimationFrame(frame);
      };
      start();
      document.addEventListener('visibilitychange', start);
    }
  }

  /* 3D tilt disabled: CSS transform on the mock ancestor
     prevents backdrop-filter frost from sampling the page background. */

  /* ─────────────────────────────────────────────
     4. TYPEWRITER
  ───────────────────────────────────────────── */
  var tw = document.getElementById('typewriter');
  if (tw) {
    var words = (tw.dataset.words || '').split('|').filter(Boolean);
    var wi = 0, ci = 0, del = false;
    function type() {
      var w = words[wi] || '';
      if (!del) { tw.textContent = w.slice(0, ++ci); if (ci >= w.length) { del = true; setTimeout(type, 1800); return; } }
      else       { tw.textContent = w.slice(0, --ci); if (ci === 0) { del = false; wi = (wi + 1) % words.length; } }
      setTimeout(type, del ? 52 : 88);
    }
    setTimeout(type, 700);
  }

  /* ─────────────────────────────────────────────
     5. SCROLL REVEAL
  ───────────────────────────────────────────── */
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
      });
    }, { threshold: .12, rootMargin: '0px 0px -36px 0px' });
    document.querySelectorAll('.sr').forEach(function (el) { io.observe(el); });
  } else {
    document.querySelectorAll('.sr').forEach(function (el) { el.classList.add('in'); });
  }

  /* ─────────────────────────────────────────────
     6. COUNTER ANIMATION
  ───────────────────────────────────────────── */
  var cnts = document.querySelectorAll('[data-count]');
  if ('IntersectionObserver' in window && cnts.length) {
    var cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        var el = e.target;
        var target = parseFloat(el.dataset.count);
        var suf = el.dataset.suf || '';
        var isF = target % 1 !== 0, t0 = null, dur = 1350;
        function anim(ts) {
          if (!t0) t0 = ts;
          var prog = Math.min((ts - t0) / dur, 1);
          var ease = 1 - Math.pow(1 - prog, 3);
          el.textContent = (isF ? (ease * target).toFixed(1) : Math.round(ease * target)) + suf;
          if (prog < 1) requestAnimationFrame(anim);
        }
        requestAnimationFrame(anim);
        cio.unobserve(el);
      });
    }, { threshold: .5 });
    cnts.forEach(function (c) { cio.observe(c); });
  }

  /* ─────────────────────────────────────────────
     7. MAGNETIC BUTTONS
  ───────────────────────────────────────────── */
  if (window.matchMedia('(hover:hover)').matches) {
    document.querySelectorAll('.btn-mag').forEach(function (btn) {
      btn.addEventListener('mousemove', function (e) {
        var r = btn.getBoundingClientRect();
        var dx = (e.clientX - r.left - r.width  / 2) * .28;
        var dy = (e.clientY - r.top  - r.height / 2) * .28;
        btn.style.transform = 'translate(' + dx + 'px,' + dy + 'px) scale(1.04)';
      });
      btn.addEventListener('mouseleave', function () { btn.style.transform = ''; });
    });
  }

  /* ─────────────────────────────────────────────
     8. CARD CURSOR GLOW (CSS custom property)
  ───────────────────────────────────────────── */
  if (window.matchMedia('(hover:hover)').matches) {
    document.querySelectorAll('.feat-card, .testi-card, .step-card, .prov-card').forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var r = card.getBoundingClientRect();
        card.style.setProperty('--gx', (e.clientX - r.left) + 'px');
        card.style.setProperty('--gy', (e.clientY - r.top) + 'px');
      });
    });
  }

  /* ─────────────────────────────────────────────
     9. MOCK CHAT — interactive live demo
        FAB toggles the panel; quick-reply chips play
        a scripted conversation with typing dots.
  ───────────────────────────────────────────── */
  (function initMockDemo() {
    var root = document.getElementById('mockChat');
    if (!root) return;

    var panel = document.getElementById('mockPanel');
    var msgs  = document.getElementById('mockMsgs');
    var chips = document.getElementById('mockChips');
    var fab   = document.getElementById('mockFab');
    var badge = document.getElementById('mockBadge');
    var closeBtn = document.getElementById('mockClose');
    if (!panel || !msgs || !chips || !fab) return;

    var data;
    try { data = JSON.parse(root.dataset.demo || '{}'); } catch (e) { data = {}; }
    var chipData = (data.chips || []).filter(function (c) { return c && c.q && c.a; });
    var started = false, busy = false, unseen = false;

    var icoChat  = fab.querySelector('.fab-ico-chat');
    var icoClose = fab.querySelector('.fab-ico-close');

    function scrollBottom() { msgs.scrollTop = msgs.scrollHeight; }

    function addMsg(cls, text) {
      var el = document.createElement('div');
      el.className = cls;
      el.textContent = text;
      msgs.appendChild(el);
      scrollBottom();
      if (!root.classList.contains('open')) { unseen = true; syncBadge(); }
      return el;
    }

    function showTyping() {
      var el = document.createElement('div');
      el.className = 'mb-typing';
      el.innerHTML = '<i></i><i></i><i></i>';
      msgs.appendChild(el);
      scrollBottom();
      return el;
    }

    function botReply(text, delay, done) {
      var typing = showTyping();
      setTimeout(function () {
        typing.remove();
        addMsg('mb-bot', text);
        if (done) done();
      }, delay);
    }

    function renderChips() {
      chips.innerHTML = '';
      chipData.forEach(function (c, i) {
        if (c.used) return;
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'mock-chip';
        b.style.animationDelay = (i * 70) + 'ms';
        b.textContent = c.q;
        b.addEventListener('click', function () {
          if (busy) return;
          busy = true;
          c.used = true;
          chips.innerHTML = '';
          addMsg('mb-user', c.q);
          botReply(c.a, 850 + Math.random() * 500, function () {
            busy = false;
            renderChips();
          });
        });
        chips.appendChild(b);
      });
    }

    function syncBadge() {
      if (!badge) return;
      badge.style.display = (unseen && !root.classList.contains('open')) ? 'grid' : 'none';
    }

    function playIntro() {
      if (started) return;
      started = true;
      botReply(data.bot || 'Hi!', 900, function () {
        setTimeout(renderChips, 350);
      });
    }

    function setOpen(open) {
      root.classList.toggle('open', open);
      var mockEl = root.closest('.mock');
      if (mockEl) mockEl.classList.toggle('chat-open', open);
      fab.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (icoChat)  icoChat.style.display  = open ? 'none' : '';
      if (icoClose) icoClose.style.display = open ? '' : 'none';
      if (open) { unseen = false; playIntro(); }
      syncBadge();
      if (open) setTimeout(scrollBottom, 60);
    }

    fab.addEventListener('click', function () {
      setOpen(!root.classList.contains('open'));
    });
    if (closeBtn) closeBtn.addEventListener('click', function () { setOpen(false); });

    /* Auto-open once the hero settles, so visitors see it's alive */
    setTimeout(function () {
      if (!root.classList.contains('open')) setOpen(true);
    }, 1400);
  })();

  /* ─────────────────────────────────────────────
     9b. PARALLAX — [data-plx] layers drift at
         different speeds (uses the CSS `translate`
         property so keyframe transforms still run).
  ───────────────────────────────────────────── */
  (function initParallax() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var els = Array.prototype.slice.call(document.querySelectorAll('[data-plx]'));
    if (!els.length || !('translate' in document.documentElement.style)) return;

    var items = els.map(function (el) {
      return {
        el: el,
        speed: parseFloat(el.dataset.plx) || 0,
        fixed: getComputedStyle(el).position === 'fixed',
        y: 0
      };
    });

    var ticking = false;
    function apply() {
      ticking = false;
      var vh = window.innerHeight;
      var sy = window.scrollY || document.documentElement.scrollTop;
      items.forEach(function (it) {
        var y;
        if (it.fixed) {
          y = sy * it.speed;
        } else {
          var r = it.el.getBoundingClientRect();
          var center = r.top - it.y + r.height / 2; /* compensate current shift */
          y = (center - vh / 2) * it.speed;
        }
        if (Math.abs(y - it.y) < 0.1) return;
        it.y = y;
        it.el.style.translate = '0 ' + y.toFixed(1) + 'px';
      });
    }
    function onScroll() {
      if (!ticking) { ticking = true; requestAnimationFrame(apply); }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    apply();
  })();

  /* ─────────────────────────────────────────────
     10. NAV ACTIVE LINK on scroll
  ───────────────────────────────────────────── */
  var secEls = document.querySelectorAll('section[id]');
  var navAs  = document.querySelectorAll('.nav-link[href^="#"]');
  if (navAs.length) {
    var lmap = {};
    navAs.forEach(function (a) { lmap[a.getAttribute('href').slice(1)] = a; });
    window.addEventListener('scroll', function () {
      var sy = window.scrollY + 90;
      secEls.forEach(function (s) {
        if (!lmap[s.id]) return;
        if (sy >= s.offsetTop && sy < s.offsetTop + s.offsetHeight) {
          navAs.forEach(function (a) { a.classList.remove('active'); });
          lmap[s.id].classList.add('active');
        }
      });
    }, { passive: true });
  }

})();
