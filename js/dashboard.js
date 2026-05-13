/* dashboard.js — UI helpers (no external deps) */

/* Sync hex text input ↔ color picker */
(function () {
  var colorText = document.getElementById('primary_color');
  var colorPick = document.getElementById('primary_color_pick');
  if (!colorText || !colorPick) return;

  colorPick.value = colorText.value;

  colorPick.addEventListener('input', function () {
    colorText.value = this.value;
    updatePreview(this.value);
  });
  colorText.addEventListener('input', function () {
    if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
      colorPick.value = this.value;
      updatePreview(this.value);
    }
  });

  function updatePreview(hex) {
    var fab = document.getElementById('color-preview-fab');
    if (fab) fab.style.background = hex;
  }
})();

/* Copy embed code to clipboard */
(function () {
  var btn = document.getElementById('copy-embed-btn');
  var box = document.getElementById('embed-code-box');
  if (!btn || !box) return;

  btn.addEventListener('click', function () {
    var text = box.textContent || box.innerText;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(showCopied);
    } else {
      /* fallback for older browsers */
      var ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.focus();
      ta.select();
      try { document.execCommand('copy'); showCopied(); } catch (e) {}
      document.body.removeChild(ta);
    }
  });

  function showCopied() {
    var orig = btn.textContent;
    btn.textContent = '✓ Tersalin!';
    btn.style.background = '#16A34A';
    setTimeout(function () {
      btn.textContent = orig;
      btn.style.background = '';
    }, 2000);
  }
})();

/* Toggle show/hide AI API key */
(function () {
  var btn   = document.getElementById('toggle-key-btn');
  var input = document.getElementById('ai_api_key');
  if (!btn || !input) return;

  btn.addEventListener('click', function () {
    if (input.type === 'password') {
      input.type = 'text';
      btn.textContent = '🙈 Sembunyikan';
    } else {
      input.type = 'password';
      btn.textContent = '👁 Tampilkan';
    }
  });
})();

/* Accordion sections */
(function () {
  document.querySelectorAll('.section-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var body = document.getElementById(btn.dataset.target);
      if (!body) return;
      var open = body.style.display !== 'none';
      body.style.display = open ? 'none' : 'block';
      btn.querySelector('.chevron').textContent = open ? '▶' : '▼';
    });
  });
})();
