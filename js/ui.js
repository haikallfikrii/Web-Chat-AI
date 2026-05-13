/**
 * ChatPopup.AI — shared UI helpers (clipboard, password visibility).
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
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var id = btn.getAttribute('data-pw-target');
        var input = id ? document.getElementById(id) : null;
        if (!input) return;
        var showPlain = input.type === 'password';
        input.type = showPlain ? 'text' : 'password';
        btn.setAttribute('aria-pressed', showPlain ? 'true' : 'false');
        btn.setAttribute('aria-label', showPlain ? 'Sembunyikan password' : 'Tampilkan password');
        var a = btn.querySelector('.pw-ico-show');
        var b = btn.querySelector('.pw-ico-hide');
        if (a && b) {
          a.hidden = !!showPlain;
          b.hidden = !showPlain;
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
            btn.innerHTML =
              '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:-2px;margin-right:4px"><polyline points="20 6 9 17 4 12"/></svg> Tersalin';
            btn.style.background = 'rgba(0,229,154,.22)';
            setTimeout(function () {
              btn.innerHTML = orig;
              btn.style.background = '';
            }, 1800);
          })
          .catch(function () {
            btn.innerHTML = 'Gagal salin';
            setTimeout(function () {
              btn.innerHTML = orig;
            }, 2000);
          });
      });
    });
  }

  window.CPUI = {
    copyToClipboard: copyToClipboard,
    bindPwToggles: bindPwToggles,
    bindCopyButtons: bindCopyButtons,
    init: function (root) {
      bindPwToggles(root);
      bindCopyButtons(root);
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
