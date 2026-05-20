/**
 * ChatPopup.AI — Landing Page Interactions
 * Scroll progress · Particles canvas · 3D tilt · Magnetic buttons
 * Typewriter · Counter animations · Scroll reveal · Mock chat demo
 */
(function () {
  'use strict';

  /* ═══════════════════════════════════════════
     SCROLL PROGRESS BAR
  ═══════════════════════════════════════════ */
  var scrollBar = document.getElementById('scroll-bar');
  function updateScrollBar() {
    if (!scrollBar) return;
    var doc = document.documentElement;
    var scrolled = (doc.scrollTop || document.body.scrollTop);
    var total = doc.scrollHeight - doc.clientHeight;
    var pct = total > 0 ? (scrolled / total) * 100 : 0;
    scrollBar.style.width = pct.toFixed(2) + '%';
  }
  window.addEventListener('scroll', updateScrollBar, { passive: true });

  /* ═══════════════════════════════════════════
     PARTICLES CANVAS
  ═══════════════════════════════════════════ */
  var canvas = document.getElementById('particles-canvas');
  if (canvas) {
    var ctx = canvas.getContext('2d');
    var particles = [];
    var W, H;

    function resize() {
      W = canvas.width  = window.innerWidth;
      H = canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize, { passive: true });

    /* Generate particles */
    function mkParticle() {
      return {
        x: Math.random() * W,
        y: Math.random() * H,
        r: Math.random() * 1.8 + 0.4,
        vx: (Math.random() - 0.5) * 0.35,
        vy: (Math.random() - 0.5) * 0.35,
        alpha: Math.random() * 0.5 + 0.15,
        hue: Math.random() > 0.7 ? '34,211,238' : '0,229,154', /* cyan or green */
      };
    }
    for (var i = 0; i < 90; i++) particles.push(mkParticle());

    /* Mouse repulsion */
    var mx = -9999, my = -9999;
    window.addEventListener('mousemove', function (e) { mx = e.clientX; my = e.clientY; }, { passive: true });

    function drawParticles() {
      ctx.clearRect(0, 0, W, H);
      for (var p = 0; p < particles.length; p++) {
        var pt = particles[p];
        /* Move */
        pt.x += pt.vx; pt.y += pt.vy;
        /* Wrap */
        if (pt.x < -10) pt.x = W + 10;
        if (pt.x > W + 10) pt.x = -10;
        if (pt.y < -10) pt.y = H + 10;
        if (pt.y > H + 10) pt.y = -10;
        /* Repel from mouse */
        var dx = pt.x - mx, dy = pt.y - my;
        var dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 120) {
          var force = (120 - dist) / 120;
          pt.x += (dx / dist) * force * 2.2;
          pt.y += (dy / dist) * force * 2.2;
        }
        /* Draw */
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, pt.r, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(' + pt.hue + ',' + pt.alpha + ')';
        ctx.fill();
      }
      /* Connections */
      ctx.lineWidth = 0.5;
      for (var a = 0; a < particles.length; a++) {
        for (var b = a + 1; b < particles.length; b++) {
          var ddx = particles[a].x - particles[b].x;
          var ddy = particles[a].y - particles[b].y;
          var d = Math.sqrt(ddx * ddx + ddy * ddy);
          if (d < 100) {
            var alpha2 = (1 - d / 100) * 0.18;
            ctx.strokeStyle = 'rgba(0,229,154,' + alpha2 + ')';
            ctx.beginPath();
            ctx.moveTo(particles[a].x, particles[a].y);
            ctx.lineTo(particles[b].x, particles[b].y);
            ctx.stroke();
          }
        }
      }
    }

    var animRunning = true;
    function loop() { if (animRunning) { drawParticles(); requestAnimationFrame(loop); } }
    loop();

    /* Pause when tab hidden */
    document.addEventListener('visibilitychange', function () {
      animRunning = !document.hidden;
      if (animRunning) loop();
    });
  }

  /* ═══════════════════════════════════════════
     3D TILT ON MOCK BROWSER
  ═══════════════════════════════════════════ */
  var tiltEl = document.getElementById('mockTilt');
  if (tiltEl && window.matchMedia('(hover:hover)').matches) {
    var mockWrap = tiltEl.closest('.mock-wrap');
    if (mockWrap) {
      mockWrap.addEventListener('mousemove', function (e) {
        var rect = mockWrap.getBoundingClientRect();
        var xPct = (e.clientX - rect.left) / rect.width  - 0.5; /* -0.5 … +0.5 */
        var yPct = (e.clientY - rect.top)  / rect.height - 0.5;
        var rotY =  xPct * 12; /* max 12° */
        var rotX = -yPct * 8;  /* max 8° */
        tiltEl.style.transform = 'rotateX(' + rotX + 'deg) rotateY(' + rotY + 'deg) scale3d(1.02,1.02,1.02)';
      });
      mockWrap.addEventListener('mouseleave', function () {
        tiltEl.style.transform = '';
      });
    }
  }

  /* ═══════════════════════════════════════════
     TYPEWRITER
  ═══════════════════════════════════════════ */
  var tw = document.getElementById('typewriter');
  if (tw) {
    var words = (tw.dataset.words || 'in 5 minutes|without coding|for anyone').split('|');
    var wIdx = 0, cIdx = 0, deleting = false, pauseTimer = null;

    function type() {
      var word = words[wIdx];
      if (!deleting) {
        tw.textContent = word.slice(0, ++cIdx);
        if (cIdx >= word.length) {
          deleting = true;
          pauseTimer = setTimeout(type, 1800);
          return;
        }
      } else {
        tw.textContent = word.slice(0, --cIdx);
        if (cIdx === 0) {
          deleting = false;
          wIdx = (wIdx + 1) % words.length;
        }
      }
      setTimeout(type, deleting ? 55 : 90);
    }
    setTimeout(type, 600);
  }

  /* ═══════════════════════════════════════════
     SCROLL REVEAL (IntersectionObserver)
  ═══════════════════════════════════════════ */
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.sr').forEach(function (el) { io.observe(el); });
  } else {
    /* Fallback: show all */
    document.querySelectorAll('.sr').forEach(function (el) { el.classList.add('in'); });
  }

  /* ═══════════════════════════════════════════
     COUNTER ANIMATION
  ═══════════════════════════════════════════ */
  var counters = document.querySelectorAll('[data-count]');
  if ('IntersectionObserver' in window && counters.length) {
    var cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        var target = parseFloat(el.dataset.count);
        var suffix = el.dataset.suffix || '';
        var isFloat = target % 1 !== 0;
        var start = 0, duration = 1400, startTime = null;
        function animate(ts) {
          if (!startTime) startTime = ts;
          var progress = Math.min((ts - startTime) / duration, 1);
          var eased = 1 - Math.pow(1 - progress, 3); /* ease-out-cubic */
          var val = eased * target;
          el.textContent = (isFloat ? val.toFixed(1) : Math.round(val)) + suffix;
          if (progress < 1) requestAnimationFrame(animate);
        }
        requestAnimationFrame(animate);
        cio.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach(function (c) { cio.observe(c); });
  }

  /* ═══════════════════════════════════════════
     MAGNETIC BUTTONS
  ═══════════════════════════════════════════ */
  document.querySelectorAll('.btn-magnetic').forEach(function (btn) {
    if (!window.matchMedia('(hover:hover)').matches) return;
    btn.addEventListener('mousemove', function (e) {
      var rect = btn.getBoundingClientRect();
      var dx = (e.clientX - rect.left - rect.width  / 2) * 0.30;
      var dy = (e.clientY - rect.top  - rect.height / 2) * 0.30;
      btn.style.transform = 'translate(' + dx + 'px,' + dy + 'px) scale(1.04)';
    });
    btn.addEventListener('mouseleave', function () {
      btn.style.transform = '';
    });
  });

  /* ═══════════════════════════════════════════
     MOCK CHAT — animated user bubble reveal
  ═══════════════════════════════════════════ */
  var userBubble = document.getElementById('mockUserMsg');
  if (userBubble) {
    setTimeout(function () {
      userBubble.style.transition = 'opacity .5s ease, transform .5s cubic-bezier(.22,1,.36,1)';
      userBubble.style.opacity    = '1';
      userBubble.style.transform  = 'translateY(0)';
    }, 2000);
  }

  /* ═══════════════════════════════════════════
     NAVBAR ACTIVE LINK on scroll
  ═══════════════════════════════════════════ */
  var sections = document.querySelectorAll('section[id], div[id]');
  var navLinks = document.querySelectorAll('.nav-link[href^="#"]');
  if (navLinks.length) {
    var linkMap = {};
    navLinks.forEach(function (l) { linkMap[l.getAttribute('href').slice(1)] = l; });

    window.addEventListener('scroll', function () {
      var scrollY = window.scrollY + 100;
      sections.forEach(function (sec) {
        if (!sec.id || !linkMap[sec.id]) return;
        var top = sec.offsetTop, h = sec.offsetHeight;
        if (scrollY >= top && scrollY < top + h) {
          navLinks.forEach(function (l) { l.classList.remove('active'); });
          linkMap[sec.id].classList.add('active');
        }
      });
    }, { passive: true });
  }

  /* ═══════════════════════════════════════════
     SMOOTH CARD HOVER GLOW (cursor-tracking)
  ═══════════════════════════════════════════ */
  if (window.matchMedia('(hover:hover)').matches) {
    document.querySelectorAll('.feat-card, .testi-card, .step-card').forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var rect = card.getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;
        card.style.setProperty('--gx', x + 'px');
        card.style.setProperty('--gy', y + 'px');
        card.style.background =
          'radial-gradient(circle 180px at ' + x + 'px ' + y + 'px, rgba(0,229,154,.06), transparent 70%),' +
          'rgba(10,16,28,.85)';
      });
      card.addEventListener('mouseleave', function () {
        card.style.background = '';
      });
    });
  }

})();
