/**
 * ChatLM — shared UI helpers (clipboard, password visibility).
 * Load with: <script src="/js/ui.js" defer></script>
 */
(function () {
  'use strict';

  function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(String(text));
    }
    return new Promise(function (resolve, reject) {
      var ta = document.createElement('textarea');
      ta.value = String(text);
      ta.setAttribute('readonly', '');
      ta.style.cssText = 'position:fixed;left:-9999px;top:0;opacity:0';
      document.body.appendChild(ta);
      ta.focus();
      ta.select();
      try {
        var ok = document.execCommand('copy');
        document.body.removeChild(ta);
        ok ? resolve() : reject(new Error('execCommand'));
      } catch (e) {
        try { document.body.removeChild(ta); } catch (_) {}
        reject(e);
      }
    });
  }

  function bindPwToggles(root) {
    root = root || document;
    root.querySelectorAll('[data-pw-target]').forEach(function (btn) {
      if (btn.dataset.cpPwBound) return;
      btn.dataset.cpPwBound = '1';
      /* Pastikan state awal benar: show visible, hide tersembunyi */
      var initA = btn.querySelector('.pw-ico-show');
      var initB = btn.querySelector('.pw-ico-hide');
      if (initA && initB) {
        initA.classList.remove('pw-hidden');
        initB.classList.add('pw-hidden');
      }
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var id = btn.getAttribute('data-pw-target');
        var input = id ? document.getElementById(id) : null;
        if (!input) return;
        var showPlain = input.type === 'password';
        input.type = showPlain ? 'text' : 'password';
        btn.setAttribute('aria-pressed', showPlain ? 'true' : 'false');
        var labels = window.CP_DASH_TEXT || {};
        btn.setAttribute('aria-label', showPlain ? (labels.hideApiKey || 'Hide password') : (labels.showApiKey || 'Show password'));
        var a = btn.querySelector('.pw-ico-show');
        var b = btn.querySelector('.pw-ico-hide');
        if (a && b) {
          /* Use CSS class so display:grid on .nav-ico-* can't override it */
          a.classList.toggle('pw-hidden', !!showPlain);
          b.classList.toggle('pw-hidden', !showPlain);
        }
      });
    });
  }

  function bindCopyButtons(root) {
    root = root || document;
    root.querySelectorAll('[data-copy-text], [data-copy]').forEach(function (btn) {
      if (btn.dataset.cpCopyBound) return;
      btn.dataset.cpCopyBound = '1';
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var text =
          btn.getAttribute('data-copy-text') ||
          btn.getAttribute('data-copy') ||
          '';
        var orig = btn.innerHTML;
        copyToClipboard(text)
          .then(function () {
            var labels = window.CP_DASH_TEXT || {};
            btn.innerHTML =
              '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:-2px;margin-right:4px"><polyline points="20 6 9 17 4 12"/></svg> ' + (labels.copied || 'Copied');
            btn.style.background = 'rgba(0,229,154,.22)';
            setTimeout(function () {
              btn.innerHTML = orig;
              btn.style.background = '';
            }, 1800);
          })
          .catch(function () {
            var labels = window.CP_DASH_TEXT || {};
            btn.innerHTML = labels.copyFailed || 'Copy failed';
            setTimeout(function () {
              btn.innerHTML = orig;
            }, 2000);
          });
      });
    });
  }

  function bindDashboardAccordion(root) {
    root = root || document;

    function setSection(sec, open) {
      var btn = sec ? sec.querySelector('.sec-head') : null;
      var body = sec ? sec.querySelector('.sec-body') : null;
      if (!sec || !btn || !body) return;

      sec.classList.toggle('open', !!open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) body.removeAttribute('hidden');
      else body.setAttribute('hidden', '');
    }

    window.toggleDashboardSection = function (btn) {
      var sec = btn && btn.closest ? btn.closest('.sec') : null;
      if (!sec) return false;
      setSection(sec, !sec.classList.contains('open'));
      return false;
    };

    root.querySelectorAll('.sec').forEach(function (sec) {
      var btn = sec.querySelector('.sec-head');
      setSection(sec, sec.classList.contains('open'));
      if (!btn || btn.dataset.cpSecBound) return;
      btn.dataset.cpSecBound = '1';
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        window.toggleDashboardSection(btn);
      });
    });
  }

  function bindPublicHeaderMenu() {
    var burger = document.getElementById('pubHdBurger');
    var drawer = document.getElementById('pubHdDrawer');
    var backdrop = document.getElementById('pubHdBackdrop');
    if (!burger || !drawer || !backdrop || burger.dataset.cpPubHd) return;
    burger.dataset.cpPubHd = '1';

    var openLabel = burger.getAttribute('aria-label') || 'Open menu';
    var closeLabel = burger.getAttribute('data-close-label') || 'Close menu';

    function setOpen(open) {
      drawer.classList.toggle('is-open', open);
      backdrop.classList.toggle('is-open', open);
      backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      burger.setAttribute('aria-label', open ? closeLabel : openLabel);
      document.body.style.overflow = open ? 'hidden' : '';
    }

    var touchHandled = false;
    function toggleMenu(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      setOpen(!drawer.classList.contains('is-open'));
    }

    burger.addEventListener(
      'touchend',
      function (e) {
        touchHandled = true;
        toggleMenu(e);
        setTimeout(function () {
          touchHandled = false;
        }, 450);
      },
      { passive: false }
    );
    burger.addEventListener('click', function (e) {
      if (touchHandled) {
        e.preventDefault();
        return;
      }
      toggleMenu(e);
    });
    backdrop.addEventListener('click', function () { setOpen(false); });
    drawer.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { setOpen(false); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') setOpen(false);
    });
  }

  function bindCompactLanguageDropdown(root) {
    root = root || document;
    root.querySelectorAll('[data-lang-compact]').forEach(function (wrap) {
      var btn = wrap.querySelector('.lang-btn');
      var drop = wrap.querySelector('.lang-drop');
      if (!btn || !drop || btn.dataset.cpLangCompact) return;
      btn.dataset.cpLangCompact = '1';

      function positionDrop() {
        var rect = btn.getBoundingClientRect();
        drop.style.top = (rect.bottom + 6) + 'px';
        var dropWidth = drop.offsetWidth || 160;
        var left = rect.right - dropWidth;
        if (left < 8) left = 8;
        drop.style.left = left + 'px';
        drop.style.right = 'auto';
      }

      function closeDrop() {
        wrap.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
      }

      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (wrap.classList.contains('open')) {
          closeDrop();
        } else {
          positionDrop();
          wrap.classList.add('open');
          btn.setAttribute('aria-expanded', 'true');
        }
      });

      document.addEventListener('click', function (e) {
        if (!wrap.contains(e.target)) closeDrop();
      });

      window.addEventListener('resize', function () {
        if (wrap.classList.contains('open')) positionDrop();
      }, { passive: true });
    });
  }

  function bindDashboardLanguageDropdown(root) {
    root = root || document;
    var wrap = root.getElementById ? root.getElementById('dashLangWrap') : document.getElementById('dashLangWrap');
    var btn = root.getElementById ? root.getElementById('dashLangBtn') : document.getElementById('dashLangBtn');
    var drop = root.getElementById ? root.getElementById('dashLangDrop') : document.getElementById('dashLangDrop');
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

    if (!btn.dataset.cpLangBound) {
      btn.dataset.cpLangBound = '1';
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (wrap.classList.contains('open')) closeDrop();
        else openDrop();
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

  window.CPUI = {
    copyToClipboard: copyToClipboard,
    bindPwToggles: bindPwToggles,
    bindCopyButtons: bindCopyButtons,
    bindDashboardAccordion: bindDashboardAccordion,
    bindDashboardLanguageDropdown: bindDashboardLanguageDropdown,
    bindCompactLanguageDropdown: bindCompactLanguageDropdown,
    bindPublicHeaderMenu: bindPublicHeaderMenu,
    init: function (root) {
      bindPwToggles(root);
      bindCopyButtons(root);
      bindDashboardAccordion(root);
      bindDashboardLanguageDropdown(root);
      bindCompactLanguageDropdown(root);
      bindPublicHeaderMenu();
    },
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      CPUI.init(document);
    });
  } else {
    CPUI.init(document);
  }
})();
