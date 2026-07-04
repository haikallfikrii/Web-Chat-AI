/**
 * Pricing page — BYOK/Managed track + monthly/yearly billing toggle.
 * Supports new layout (data-panel grids) and legacy planGridMonthly/Yearly.
 */
(function () {
  'use strict';

  function bindToggleButton(btn, handler) {
    if (!btn) {
      return;
    }
    var touchHandled = false;

    btn.addEventListener(
      'touchend',
      function (e) {
        touchHandled = true;
        e.preventDefault();
        handler();
        setTimeout(function () {
          touchHandled = false;
        }, 450);
      },
      { passive: false }
    );

    btn.addEventListener('click', function (e) {
      if (touchHandled) {
        e.preventDefault();
        return;
      }
      e.preventDefault();
      handler();
    });
  }

  function initLegacyPricing() {
    var monthlyGrid = document.getElementById('planGridMonthly');
    var yearlyGrid = document.getElementById('planGridYearly');
    var tabMonthly = document.getElementById('tabMonthly');
    var tabYearly = document.getElementById('tabYearly');

    if (!monthlyGrid || !yearlyGrid || !tabMonthly || !tabYearly) {
      return false;
    }

    var params = new URLSearchParams(window.location.search);
    var cycle =
      params.get('billing') === 'yearly' || params.get('cycle') === 'yearly' ? 'yearly' : 'monthly';

    function refresh() {
      var yearly = cycle === 'yearly';
      monthlyGrid.style.display = yearly ? 'none' : 'grid';
      yearlyGrid.style.display = yearly ? 'grid' : 'none';
      tabMonthly.classList.toggle('active', !yearly);
      tabYearly.classList.toggle('active', yearly);
    }

    bindToggleButton(tabMonthly, function () {
      cycle = 'monthly';
      refresh();
    });
    bindToggleButton(tabYearly, function () {
      cycle = 'yearly';
      refresh();
    });

    refresh();
    return true;
  }

  function initModernPricing() {
    var cycleToggle = document.querySelector('.billing-toggle');
    var panels = document.querySelectorAll('[data-panel]');

    if (!cycleToggle || !panels.length) {
      return false;
    }

    var params = new URLSearchParams(window.location.search);
    var track = params.get('track') === 'managed' ? 'managed' : 'byok';
    var cycle =
      params.get('billing') === 'yearly' || params.get('cycle') === 'yearly' ? 'yearly' : 'monthly';

    var tabByok = document.getElementById('tabByok');
    var tabManaged = document.getElementById('tabManaged');
    var tabMonthly = document.getElementById('tabMonthly');
    var tabYearly = document.getElementById('tabYearly');
    var descByok = document.getElementById('trackDescByok');
    var descManaged = document.getElementById('trackDescManaged');

    function panelKey() {
      return track + '-' + cycle;
    }

    function refresh() {
      var key = panelKey();
      var anyVisible = false;

      panels.forEach(function (panel) {
        var show = panel.getAttribute('data-panel') === key;
        panel.style.display = show ? 'grid' : 'none';
        if (show) {
          anyVisible = true;
        }
      });

      if (!anyVisible) {
        panels.forEach(function (panel) {
          if (panel.getAttribute('data-panel') === track + '-monthly') {
            panel.style.display = 'grid';
          }
        });
        cycle = 'monthly';
      }

      if (tabByok) {
        tabByok.classList.toggle('active', track === 'byok');
      }
      if (tabManaged) {
        tabManaged.classList.toggle('active', track === 'managed');
      }
      if (tabMonthly) {
        tabMonthly.classList.toggle('active', cycle === 'monthly');
      }
      if (tabYearly) {
        tabYearly.classList.toggle('active', cycle === 'yearly');
      }
      if (descByok) {
        descByok.style.display = track === 'byok' ? 'block' : 'none';
      }
      if (descManaged) {
        descManaged.style.display = track === 'managed' ? 'block' : 'none';
      }
    }

    bindToggleButton(tabByok, function () {
      track = 'byok';
      refresh();
    });
    bindToggleButton(tabManaged, function () {
      track = 'managed';
      refresh();
    });
    bindToggleButton(tabMonthly, function () {
      cycle = 'monthly';
      refresh();
    });
    bindToggleButton(tabYearly, function () {
      cycle = 'yearly';
      refresh();
    });

    refresh();
    return true;
  }

  function initPricingPage() {
    if (initModernPricing()) {
      return;
    }
    initLegacyPricing();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPricingPage);
  } else {
    initPricingPage();
  }
})();
