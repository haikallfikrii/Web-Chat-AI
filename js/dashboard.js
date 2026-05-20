/* dashboard.js — dashboard interactions */
(function () {
  'use strict';

  function runtimeData() {
    var el = document.getElementById('dashboardRuntimeData');
    if (!el) return {};
    try {
      return JSON.parse(el.textContent || '{}');
    } catch (_) {
      return {};
    }
  }

  var data = runtimeData();

  function bindColorPicker() {
    var hexEl = document.getElementById('color-hex');
    var pickEl = document.getElementById('color-picker');
    if (!hexEl || !pickEl) return;

    function isValidHex(value) {
      return /^#[0-9a-fA-F]{6}$/.test(String(value).trim());
    }

    function syncFromPicker() {
      hexEl.value = String(pickEl.value || '').toUpperCase();
    }

    pickEl.addEventListener('input', syncFromPicker);
    pickEl.addEventListener('change', syncFromPicker);

    hexEl.addEventListener('input', function () {
      if (isValidHex(hexEl.value)) {
        pickEl.value = hexEl.value;
      }
    });

    hexEl.addEventListener('blur', function () {
      if (!isValidHex(hexEl.value)) {
        hexEl.value = String(pickEl.value || '').toUpperCase();
      } else {
        hexEl.value = hexEl.value.toUpperCase();
      }
    });
  }

  function bindAvatarPreview() {
    var fileInput = document.getElementById('avatarFile');
    var wrap = document.getElementById('avatarPreviewWrap');
    if (!fileInput || !wrap) return;

    fileInput.addEventListener('change', function () {
      var file = fileInput.files && fileInput.files[0];
      if (!file) return;

      if (file.size > 2 * 1024 * 1024) {
        alert(data.photoMax || 'Photo size must be 2 MB or less');
        fileInput.value = '';
        return;
      }

      if (!/^image\/(png|jpeg|webp|gif)$/i.test(file.type)) {
        alert('Unsupported image format');
        fileInput.value = '';
        return;
      }

      var reader = new FileReader();
      reader.onload = function (event) {
        var img = document.createElement('img');
        img.id = 'avatarPreview';
        img.alt = 'avatar';
        img.src = String(event.target && event.target.result || '');
        wrap.innerHTML = '';
        wrap.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  }

  function bindProviderPills() {
    var hints = data.providerHints || {};
    var hintBox = document.getElementById('modelHint');

    function updateHint() {
      var checked = document.querySelector('input[name="ai_provider"]:checked');
      if (hintBox) hintBox.innerHTML = checked ? (hints[checked.value] || '') : '';
    }

    document.querySelectorAll('.prov-pill').forEach(function (pill) {
      if (pill.dataset.cpProvBound) return;
      pill.dataset.cpProvBound = '1';
      pill.addEventListener('click', function () {
        document.querySelectorAll('.prov-pill').forEach(function (item) {
          item.classList.remove('active');
        });
        pill.classList.add('active');
        var radio = pill.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
        updateHint();
      });
    });

    updateHint();
  }

  function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = String(text);
    ta.setAttribute('readonly', '');
    ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try { document.execCommand('copy'); } catch (_) {}
    document.body.removeChild(ta);
  }

  function bindCopyEmbed() {
    var btn = document.getElementById('btnCopyEmbed');
    var raw = document.getElementById('embedCodeRaw');
    if (!btn || !raw) return;

    window.cpCopyEmbed = function () {
      var text = raw.value || '';
      var orig = btn.innerHTML;
      var checkSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:-2px;margin-right:5px"><polyline points="20 6 9 17 4 12"/></svg>';

      function showCopied() {
        btn.innerHTML = checkSVG + ' ' + (data.copied || 'Copied');
        btn.style.background = 'linear-gradient(135deg,rgba(0,229,154,.55),rgba(0,229,154,.35))';
        btn.style.color = '#031018';
        btn.style.borderColor = 'var(--green)';
        setTimeout(function () {
          btn.innerHTML = orig;
          btn.style.background = '';
          btn.style.color = '';
          btn.style.borderColor = '';
        }, 2200);
      }

      function showFailed() {
        btn.textContent = data.copyFailed || 'Copy failed';
        setTimeout(function () {
          btn.innerHTML = orig;
        }, 2000);
      }

      if (window.CPUI && typeof window.CPUI.copyToClipboard === 'function') {
        window.CPUI.copyToClipboard(text).then(showCopied).catch(function () {
          try { fallbackCopy(text); showCopied(); } catch (_) { showFailed(); }
        });
        return false;
      }

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(showCopied).catch(function () {
          try { fallbackCopy(text); showCopied(); } catch (_) { showFailed(); }
        });
        return false;
      }

      try {
        fallbackCopy(text);
        showCopied();
      } catch (_) {
        showFailed();
      }
      return false;
    };

    if (!btn.dataset.cpCopyBound) {
      btn.dataset.cpCopyBound = '1';
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        window.cpCopyEmbed();
      });
    }
  }

  bindColorPicker();
  bindAvatarPreview();
  bindProviderPills();
  bindCopyEmbed();
})();
