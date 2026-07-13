/**
 * ChatLM — Savings calculator (homepage)
 * Separate file for CSP compliance and mobile touch reliability.
 */
(function () {
  'use strict';

  function initCalc() {
    var slider = document.getElementById('calcConvos');
    if (!slider) return;

    var convosVal = document.getElementById('calcConvosVal');
    var humanEl = document.getElementById('calcHuman');
    var chatlmEl = document.getElementById('calcChatlm');
    var savingsEl = document.getElementById('calcSavings');
    var HUMAN_COST_PER_CONVO = 0.5;
    var CHATLM_FLAT_COST = 19;
    var touchFrame = 0;

    function fmt(n) {
      return '$' + n.toLocaleString('en-US', { maximumFractionDigits: 0 });
    }

    function update() {
      var convos = parseInt(slider.value, 10) || 0;
      var min = parseInt(slider.min, 10) || 0;
      var max = parseInt(slider.max, 10) || 100;
      var pct = max > min ? ((convos - min) / (max - min)) * 100 : 0;
      slider.style.setProperty('--fill', pct + '%');

      var humanCost = convos * HUMAN_COST_PER_CONVO;
      var savings = Math.max(0, humanCost - CHATLM_FLAT_COST);

      if (convosVal) convosVal.textContent = convos.toLocaleString('en-US');
      if (humanEl) humanEl.textContent = fmt(humanCost);
      if (chatlmEl) chatlmEl.textContent = fmt(CHATLM_FLAT_COST);
      if (savingsEl) savingsEl.textContent = fmt(savings);
    }

    function bindUpdate(eventName) {
      slider.addEventListener(eventName, update, { passive: true });
    }

    bindUpdate('input');
    bindUpdate('change');
    bindUpdate('pointerup');
    bindUpdate('touchend');

    slider.addEventListener(
      'touchstart',
      function () {
        function tick() {
          update();
          if (slider.matches(':active')) {
            touchFrame = requestAnimationFrame(tick);
          }
        }
        if (touchFrame) cancelAnimationFrame(touchFrame);
        touchFrame = requestAnimationFrame(tick);
      },
      { passive: true }
    );

    update();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCalc);
  } else {
    initCalc();
  }
})();
