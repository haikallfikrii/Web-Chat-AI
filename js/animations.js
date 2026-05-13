/* animations.js — scroll reveal + micro-interactions for landing page */
(function () {
  'use strict';

  /* ── Intersection Observer: reveal on scroll ── */
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

    document.querySelectorAll('.reveal').forEach(function (el) {
      io.observe(el);
    });
  } else {
    /* Fallback: just show everything */
    document.querySelectorAll('.reveal').forEach(function (el) {
      el.classList.add('visible');
    });
  }

  /* ── Smooth active nav link highlight ── */
  var sections = document.querySelectorAll('section[id]');
  if (sections.length) {
    var navLinks = document.querySelectorAll('nav a[href^="#"]');
    var sio = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          navLinks.forEach(function (a) { a.classList.remove('active'); });
          var active = document.querySelector('nav a[href="#' + entry.target.id + '"]');
          if (active) active.classList.add('active');
        }
      });
    }, { threshold: 0.5 });
    sections.forEach(function (s) { sio.observe(s); });
  }

  /* ── Typewriter for hero tagline ── */
  var tw = document.getElementById('typewriter');
  if (tw) {
    var words = tw.dataset.words ? tw.dataset.words.split('|') : [];
    var wi = 0, ci = 0, deleting = false;
    function tick() {
      var word = words[wi] || '';
      tw.textContent = deleting ? word.slice(0, ci--) : word.slice(0, ci++);
      var speed = deleting ? 40 : 80;
      if (!deleting && ci > word.length) { speed = 1800; deleting = true; }
      else if (deleting && ci < 0) { deleting = false; wi = (wi + 1) % words.length; ci = 0; speed = 300; }
      setTimeout(tick, speed);
    }
    if (words.length) setTimeout(tick, 800);
  }

  /* ── Counter animation ── */
  document.querySelectorAll('[data-count]').forEach(function (el) {
    var target = parseInt(el.dataset.count, 10);
    var suffix = el.dataset.suffix || '';
    var started = false;
    new IntersectionObserver(function (entries) {
      if (entries[0].isIntersecting && !started) {
        started = true;
        var start = 0;
        var duration = 1600;
        var step = target / (duration / 16);
        var t = setInterval(function () {
          start = Math.min(start + step, target);
          el.textContent = Math.floor(start).toLocaleString() + suffix;
          if (start >= target) clearInterval(t);
        }, 16);
      }
    }, { threshold: 0.5 }).observe(el);
  });
})();
