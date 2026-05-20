/**
 * ChatPopup.AI — Landing Page JS
 * Scroll-progress · Particle canvas · 3D tilt · Typewriter
 * Scroll-reveal · Counter · Magnetic buttons · Card cursor-glow
 * Nav active link · Mock chat reveal
 */
(function () {
  'use strict';

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
     2. PARTICLE CANVAS
  ───────────────────────────────────────────── */
  var cv = document.getElementById('pcv');
  if (cv) {
    var cx = cv.getContext('2d');
    var W = 0, H = 0;
    var pts = [];
    var mx = -9999, my = -9999;

    function resizeCV() { W = cv.width = window.innerWidth; H = cv.height = window.innerHeight; }
    resizeCV();
    window.addEventListener('resize', resizeCV, { passive: true });
    window.addEventListener('mousemove', function (e) { mx = e.clientX; my = e.clientY; }, { passive: true });

    function mkPt() {
      return {
        x: Math.random() * W, y: Math.random() * H,
        r: Math.random() * 1.7 + 0.3,
        vx: (Math.random() - .5) * .32, vy: (Math.random() - .5) * .32,
        a: Math.random() * .45 + .12,
        g: Math.random() > .65 ? '34,211,238' : '0,229,154'
      };
    }
    for (var i = 0; i < 85; i++) pts.push(mkPt());

    var alive = true;
    function frame() {
      if (!alive) return;
      cx.clearRect(0, 0, W, H);
      for (var p = 0; p < pts.length; p++) {
        var pt = pts[p];
        pt.x += pt.vx; pt.y += pt.vy;
        if (pt.x < -8) pt.x = W + 8; else if (pt.x > W + 8) pt.x = -8;
        if (pt.y < -8) pt.y = H + 8; else if (pt.y > H + 8) pt.y = -8;
        // mouse repel
        var dx = pt.x - mx, dy = pt.y - my, d2 = dx * dx + dy * dy;
        if (d2 < 14400) { var f = (120 - Math.sqrt(d2)) / 120 * 2.4; pt.x += dx / Math.sqrt(d2) * f; pt.y += dy / Math.sqrt(d2) * f; }
        // draw dot
        cx.beginPath(); cx.arc(pt.x, pt.y, pt.r, 0, 6.2832);
        cx.fillStyle = 'rgba(' + pt.g + ',' + pt.a + ')'; cx.fill();
      }
      // draw connections
      cx.lineWidth = .45;
      for (var a = 0; a < pts.length; a++) {
        for (var b = a + 1; b < pts.length; b++) {
          var ddx = pts[a].x - pts[b].x, ddy = pts[a].y - pts[b].y;
          var dd = Math.sqrt(ddx * ddx + ddy * ddy);
          if (dd < 95) {
            cx.strokeStyle = 'rgba(0,229,154,' + (1 - dd / 95) * .16 + ')';
            cx.beginPath(); cx.moveTo(pts[a].x, pts[a].y); cx.lineTo(pts[b].x, pts[b].y); cx.stroke();
          }
        }
      }
      requestAnimationFrame(frame);
    }
    frame();
    document.addEventListener('visibilitychange', function () {
      alive = !document.hidden; if (alive) frame();
    });
  }

  /* ─────────────────────────────────────────────
     3. 3D TILT (mock browser)
  ───────────────────────────────────────────── */
  var tilt = document.getElementById('mockTilt');
  if (tilt && window.matchMedia('(hover:hover)').matches) {
    var wrap = tilt.parentElement;
    if (wrap) {
      wrap.addEventListener('mousemove', function (e) {
        var r = wrap.getBoundingClientRect();
        var rx = -((e.clientY - r.top)  / r.height - .5) * 9;
        var ry =  ((e.clientX - r.left) / r.width  - .5) * 13;
        tilt.style.transform = 'rotateX(' + rx + 'deg) rotateY(' + ry + 'deg) scale3d(1.02,1.02,1.02)';
      });
      wrap.addEventListener('mouseleave', function () { tilt.style.transform = ''; });
    }
  }

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
     9. MOCK CHAT — user bubble delayed reveal
  ───────────────────────────────────────────── */
  var ubbl = document.getElementById('mockUserMsg');
  if (ubbl) setTimeout(function () { ubbl.style.opacity = '1'; ubbl.style.transform = 'none'; }, 2200);

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
