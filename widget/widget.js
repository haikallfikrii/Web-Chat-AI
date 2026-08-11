/**
 * widget.js
 * Script chat pop-up yang di-inject ke website klien.
 * Menggunakan Shadow DOM agar CSS terisolasi sepenuhnya.
 *
 * Cara embed di website klien:
 * <script
 *   src="https://yourdomain.com/widget/widget.js"
 *   data-api-key="YOUR_64_CHAR_API_KEY"
 *   data-base-url="https://yourdomain.com"
 *   async
 * ></script>
 */

(function () {
  "use strict";

  // ── Konfigurasi dari atribut script tag (async-safe) ───────
  function findWidgetScript() {
    if (document.currentScript) {
      return document.currentScript;
    }
    const scripts = document.getElementsByTagName("script");
    for (let i = scripts.length - 1; i >= 0; i--) {
      const src = scripts[i].getAttribute("src") || "";
      if (/\/widget\/widget\.js(\?|$)/i.test(src)) {
        return scripts[i];
      }
    }
    return null;
  }

  const currentScript = findWidgetScript();
  if (!currentScript) {
    console.warn("[ChatLM] Tag script widget tidak ditemukan.");
    return;
  }

  const API_KEY  = (currentScript.getAttribute("data-api-key")  || "").trim();
  const BASE_URL = (currentScript.getAttribute("data-base-url") || "").replace(/\/$/, "").trim();

  if (!API_KEY || !BASE_URL) {
    console.warn("[ChatLM] data-api-key dan data-base-url wajib diisi pada tag widget.");
    return;
  }

  // ── State widget ───────────────────────────────────────────
  const STATE = {
    isOpen:    false,
    isLoading: false,
    sessionId: "",
    settings:  null,
  };

  // Sesi pengunjung: localStorage, tetap 3 hari (refresh tab / tutup browser singkat)
  const SESSION_TTL_MS = 3 * 24 * 60 * 60 * 1000;

  // Jeda sebelum sapaan pertama muncul, supaya tidak menabrak load halaman.
  const GREETING_DELAY_MS = 1500;

  function sessionStorageKey() {
    return "cw_session_" + API_KEY.substring(0, 8);
  }

  function storageGet(key) {
    try {
      return localStorage.getItem(key);
    } catch (_) {
      try {
        return sessionStorage.getItem(key);
      } catch (_e) {
        return null;
      }
    }
  }

  function storageSet(key, value) {
    try {
      localStorage.setItem(key, value);
    } catch (_) {
      try {
        sessionStorage.setItem(key, value);
      } catch (_e) {}
    }
  }

  function persistSessionId(sessionId) {
    const key = sessionStorageKey();
    storageSet(key, sessionId);
    storageSet(key + "_at", String(Date.now()));
  }

  function getOrCreateSessionId() {
    const key = sessionStorageKey();
    const tsKey = key + "_at";
    const now = Date.now();
    let sessionId = storageGet(key);
    const ts = parseInt(storageGet(tsKey) || "0", 10);

    if (sessionId && isValidUUID(sessionId) && ts > 0 && now - ts < SESSION_TTL_MS) {
      persistSessionId(sessionId);
      return sessionId;
    }

    sessionId = generateUUID();
    persistSessionId(sessionId);
    return sessionId;
  }

  function isValidUUID(str) {
    return /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(str);
  }

  function generateUUID() {
    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (c) {
      const r = (Math.random() * 16) | 0;
      const v = c === "x" ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
  }

  // ── CSS Widget ─────────────────────────────────────────────
  function buildCSS(primaryColor) {
    return `
      :host {
        all: initial;
        display: block;
        position: fixed;
        left: 0;
        top: 0;
        width: 0;
        height: 0;
        overflow: visible;
        pointer-events: none;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 16px;
        line-height: 1.5;
        color: #111827;
      }

      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

      /* ─ Tombol Buka/Tutup ─ */
      #cw-toggle {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: ${primaryColor};
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(0,0,0,.25);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2147483647;
        pointer-events: auto;
        touch-action: manipulation;
        transition: transform .2s ease, box-shadow .2s ease;
        outline: none;
      }
      #cw-toggle:hover   { transform: scale(1.08); box-shadow: 0 6px 20px rgba(0,0,0,.3); }
      #cw-toggle:focus-visible { outline: 3px solid ${primaryColor}88; outline-offset: 3px; }
      #cw-toggle svg     { width: 26px; height: 26px; fill: #fff; transition: opacity .2s; }
      #cw-toggle .icon-close { display: none; }

      #cw-toggle.open .icon-chat  { display: none; }
      #cw-toggle.open .icon-close { display: block; }

      /* Notifikasi unread */
      #cw-badge {
        position: absolute;
        top: -4px; right: -4px;
        min-width: 20px; height: 20px;
        padding: 0 5px;
        border-radius: 999px;
        background: #EF4444;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        display: none;
      }
      #cw-badge.show {
        display: flex;
        animation: cwBadgePop .35s cubic-bezier(.18,.89,.32,1.28);
      }
      @keyframes cwBadgePop {
        0%   { transform: scale(0); opacity: 0; }
        70%  { transform: scale(1.25); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
      }

      /* Teaser sapaan pertama di samping tombol */
      #cw-teaser {
        position: fixed;
        bottom: 92px;
        right: 24px;
        max-width: 260px;
        display: none;
        gap: 8px;
        align-items: flex-start;
        padding: 12px 32px 12px 14px;
        background: #fff;
        color: #111827;
        border-radius: 14px;
        border-bottom-right-radius: 4px;
        box-shadow: 0 8px 28px rgba(0,0,0,.16);
        cursor: pointer;
        z-index: 2147483646;
        pointer-events: auto;
        text-align: left;
        border: none;
        font-family: inherit;
      }
      #cw-teaser.show {
        display: flex;
        animation: cwTeaserIn .35s ease;
      }
      @keyframes cwTeaserIn {
        from { opacity: 0; transform: translateY(10px) scale(.96); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
      }
      #cw-teaser-text { font-size: 13px; line-height: 1.5; }
      #cw-teaser-name { font-weight: 700; font-size: 12px; margin-bottom: 2px; color: ${primaryColor}; }
      #cw-teaser-close {
        position: absolute;
        top: 6px; right: 6px;
        width: 20px; height: 20px;
        border: none;
        border-radius: 50%;
        background: transparent;
        color: #9CA3AF;
        cursor: pointer;
        font-size: 15px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
      }
      #cw-teaser-close:hover { background: #F3F4F6; color: #4B5563; }

      @media (max-width: 420px) {
        #cw-teaser { max-width: calc(100vw - 100px); }
      }

      /* ─ Jendela Chat ─ */
      #cw-window {
        position: fixed;
        bottom: 92px;
        right: 24px;
        width: 380px;
        max-width: calc(100vw - 32px);
        height: 520px;
        max-height: calc(100vh - 120px);
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 40px rgba(0,0,0,.18);
        display: flex;
        flex-direction: column;
        z-index: 2147483646;
        overflow: hidden;
        transform: scale(.9) translateY(16px);
        opacity: 0;
        pointer-events: none;
        transition: transform .25s ease, opacity .25s ease;
      }
      #cw-window.open {
        transform: scale(1) translateY(0);
        opacity: 1;
        pointer-events: auto;
      }

      /* ─ Header ─ */
      #cw-header {
        background: ${primaryColor};
        color: #fff;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
      }
      #cw-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,.25);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
      }
      #cw-avatar img  { width: 100%; height: 100%; object-fit: cover; }
      #cw-avatar svg  { width: 22px; height: 22px; fill: #fff; }
      #cw-header-info { flex: 1; min-width: 0; }
      #cw-bot-name    { font-weight: 700; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
      #cw-status      { font-size: 12px; opacity: .85; }
      #cw-status-dot  { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #4ADE80; margin-right: 4px; }

      /* Tombol suara on/off */
      #cw-sound {
        width: 32px; height: 32px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.15);
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background .15s;
        outline: none;
      }
      #cw-sound:hover { background: rgba(255,255,255,.28); }
      #cw-sound:focus-visible { outline: 2px solid #fff; outline-offset: 2px; }
      #cw-sound svg { width: 17px; height: 17px; fill: #fff; }
      #cw-sound .icon-sound-off { display: none; }
      #cw-sound.muted .icon-sound-on  { display: none; }
      #cw-sound.muted .icon-sound-off { display: block; }
      #cw-sound.muted { opacity: .7; }

      /* ─ Area Pesan ─ */
      #cw-messages {
        flex: 1;
        overflow-y: auto;
        padding: 16px 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        scroll-behavior: smooth;
      }
      /* Scrollbar transparan dengan radius */
      #cw-messages{scrollbar-width:thin;scrollbar-color:rgba(0,0,0,.12) transparent}
      #cw-messages::-webkit-scrollbar{width:5px}
      #cw-messages::-webkit-scrollbar-track{background:transparent;margin:8px 0}
      #cw-messages::-webkit-scrollbar-thumb{background:rgba(0,0,0,.15);border-radius:999px}
      #cw-messages::-webkit-scrollbar-thumb:hover{background:rgba(0,0,0,.25)}

      /* Bubble */
      .cw-bubble {
        max-width: 80%;
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.55;
        word-break: break-word;
        white-space: pre-wrap;
        animation: cwFadeIn .2s ease;
      }
      @keyframes cwFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
      }
      .cw-bubble.user {
        align-self: flex-end;
        background: ${primaryColor};
        color: #fff;
        border-bottom-right-radius: 4px;
      }
      .cw-bubble.bot {
        align-self: flex-start;
        background: #F3F4F6;
        color: #111827;
        border-bottom-left-radius: 4px;
      }
      .cw-bubble.error {
        align-self: flex-start;
        background: #FEE2E2;
        color: #991B1B;
        border-bottom-left-radius: 4px;
      }

      /* Konten hasil render markdown */
      .cw-bubble.cw-md { white-space: normal; }
      .cw-bubble.cw-md > *:first-child { margin-top: 0; }
      .cw-bubble.cw-md > *:last-child  { margin-bottom: 0; }
      .cw-bubble.cw-md p       { margin: 0 0 8px; }
      .cw-bubble.cw-md strong  { font-weight: 700; }
      .cw-bubble.cw-md em      { font-style: italic; }
      .cw-bubble.cw-md u       { text-decoration: underline; }
      .cw-bubble.cw-md s       { text-decoration: line-through; opacity: .7; }
      .cw-bubble.cw-md h4      { font-size: 14px; font-weight: 700; margin: 10px 0 4px; }
      .cw-bubble.cw-md a {
        color: ${primaryColor};
        text-decoration: underline;
        word-break: break-word;
      }
      .cw-bubble.cw-md ul,
      .cw-bubble.cw-md ol { margin: 6px 0 8px; padding-left: 20px; }
      .cw-bubble.cw-md li { margin: 2px 0; }
      .cw-bubble.cw-md code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 12.5px;
        background: rgba(0,0,0,.07);
        padding: 1px 5px;
        border-radius: 5px;
      }
      .cw-bubble.cw-md pre {
        background: #1E293B;
        color: #E2E8F0;
        padding: 10px 12px;
        border-radius: 10px;
        overflow-x: auto;
        margin: 6px 0 8px;
      }
      .cw-bubble.cw-md pre code {
        background: none;
        padding: 0;
        color: inherit;
        font-size: 12.5px;
      }
      .cw-bubble.cw-md blockquote {
        border-left: 3px solid rgba(0,0,0,.15);
        padding-left: 10px;
        margin: 6px 0 8px;
        opacity: .85;
      }

      /* Typing indicator */
      .cw-typing {
        align-self: flex-start;
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 12px 16px;
        background: #F3F4F6;
        border-radius: 16px;
        border-bottom-left-radius: 4px;
      }
      .cw-typing span {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #9CA3AF;
        animation: cwTyping 1.2s infinite;
        display: block;
      }
      .cw-typing span:nth-child(2) { animation-delay: .2s; }
      .cw-typing span:nth-child(3) { animation-delay: .4s; }
      @keyframes cwTyping {
        0%, 60%, 100% { transform: translateY(0); opacity: .5; }
        30%            { transform: translateY(-5px); opacity: 1; }
      }

      /* Timestamp */
      .cw-time {
        font-size: 10px;
        color: #9CA3AF;
        margin-top: 2px;
        text-align: right;
      }
      .cw-bubble.bot ~ .cw-time,
      .cw-bubble.error ~ .cw-time { text-align: left; }

      /* ─ Input Area ─ */
      #cw-input-area {
        padding: 10px 14px 12px;
        border-top: 1px solid #F3F4F6;
        display: flex;
        gap: 8px;
        align-items: flex-end;
        flex-shrink: 0;
        background: #fff;
      }
      #cw-input {
        flex: 1;
        border: 1.5px solid #E5E7EB;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 14px;
        font-family: inherit;
        resize: none;
        outline: none;
        max-height: 120px;
        overflow-y: auto;
        line-height: 1.5;
        transition: border-color .15s;
        color: #111827;
        background: #F9FAFB;
        /* Scrollbar halus, transparan, radius — tetap di dalam textarea */
        scrollbar-width:thin;
        scrollbar-color:rgba(0,0,0,.12) transparent;
      }
      #cw-input::-webkit-scrollbar{width:4px}
      #cw-input::-webkit-scrollbar-track{background:transparent;border-radius:999px;margin:4px 0}
      #cw-input::-webkit-scrollbar-thumb{background:rgba(0,0,0,.15);border-radius:999px}
      #cw-input::-webkit-scrollbar-thumb:hover{background:rgba(0,0,0,.22)}
      #cw-input:focus { border-color: ${primaryColor}; background: #fff; }
      #cw-input::placeholder { color: #9CA3AF; }

      #cw-send {
        width: 40px; height: 40px;
        flex-shrink: 0;
        background: ${primaryColor};
        border: none;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity .15s, transform .1s;
        outline: none;
      }
      #cw-send:hover:not(:disabled)   { opacity: .88; }
      #cw-send:active:not(:disabled)  { transform: scale(.94); }
      #cw-send:disabled               { opacity: .45; cursor: not-allowed; }
      #cw-send svg { width: 18px; height: 18px; fill: #fff; }

      /* ─ Footer ─ */
      #cw-footer {
        text-align: center;
        padding: 6px 0 10px;
        font-size: 11px;
        color: #9CA3AF;
        flex-shrink: 0;
      }
      #cw-footer a.cw-wm-link {
        display: inline-flex; align-items: center; gap: 6px;
        color: #9CA3AF; text-decoration: none; font-size: 11px;
      }
      #cw-footer a.cw-wm-link:hover { color: #0D9488; }
      #cw-footer a.cw-wm-link strong { font-weight: 600; color: #6B7280; }
      #cw-footer a.cw-wm-link:hover strong { color: #0D9488; }
      .cw-wm-logo { width: 18px; height: 18px; object-fit: contain; flex-shrink: 0; }

      /* ─ Loading skeleton (saat fetch settings) ─ */
      #cw-loading {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9CA3AF;
        font-size: 14px;
      }
    `;
  }

  // ── Ikon SVG ───────────────────────────────────────────────
  const ICON_CHAT = `
    <svg class="icon-chat" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/>
    </svg>`;

  const ICON_CLOSE = `
    <svg class="icon-close" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
    </svg>`;

  const ICON_BOT = `
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7H3a7 7 0 0 1 7-7h1V5.73A2 2 0 0 1 10 4a2 2 0 0 1 2-2M7.5 14a1.5 1.5 0 0 0-1.5 1.5A1.5 1.5 0 0 0 7.5 17 1.5 1.5 0 0 0 9 15.5 1.5 1.5 0 0 0 7.5 14m9 0a1.5 1.5 0 0 0-1.5 1.5 1.5 1.5 0 0 0 1.5 1.5 1.5 1.5 0 0 0 1.5-1.5A1.5 1.5 0 0 0 16.5 14M3 21l2.5-2.5A7 7 0 0 0 10 21h4a7 7 0 0 0 4.5-1.5L21 21v-3h-1v1.83A5.91 5.91 0 0 1 14 21h-4a5.91 5.91 0 0 1-6-2.17V18H3z"/>
    </svg>`;

  const ICON_SEND = `
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
    </svg>`;

  const ICON_SOUND_ON = `
    <svg class="icon-sound-on" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3a4.5 4.5 0 0 0-2.5-4.03v8.05A4.47 4.47 0 0 0 16.5 12zM14 3.23v2.06a7 7 0 0 1 0 13.42v2.06a9 9 0 0 0 0-17.54z"/>
    </svg>`;

  const ICON_SOUND_OFF = `
    <svg class="icon-sound-off" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M16.5 12A4.5 4.5 0 0 0 14 7.97v2.21l2.45 2.45c.03-.2.05-.41.05-.63zM19 12c0 .94-.2 1.82-.54 2.64l1.51 1.51A8.9 8.9 0 0 0 21 12a9 9 0 0 0-7-8.77v2.06A7 7 0 0 1 19 12zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06a8.99 8.99 0 0 0 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>
    </svg>`;

  // ── Bangun HTML widget ─────────────────────────────────────
  function buildWatermarkFooter(settings) {
    // Default: tampilkan watermark jika API tidak mengirim field (trial / free)
    if (settings.show_watermark === false || settings.show_watermark === 0) {
      return "";
    }
    const brand = escapeHtml(settings.watermark_brand || "ChatLM");
    const url = escapeAttr(settings.watermark_url || BASE_URL);
    const logoUrl = (settings.watermark_logo_url || "").trim();
    const logoImg = logoUrl
      ? `<img src="${escapeAttr(logoUrl)}" alt="" class="cw-wm-logo" width="18" height="18" loading="lazy">`
      : "";
    return `
        <div id="cw-footer">
          <a href="${url}" target="_blank" rel="noopener noreferrer" class="cw-wm-link">
            ${logoImg}<span>Powered by <strong>${brand}</strong></span>
          </a>
        </div>`;
  }

  function buildHTML(settings) {
    const avatarContent = settings.bot_avatar_url
      ? `<img src="${escapeAttr(settings.bot_avatar_url)}" alt="avatar" loading="lazy">`
      : ICON_BOT;

    const footerHtml = buildWatermarkFooter(settings);

    const teaserHtml = settings.welcome_message
      ? `
      <div id="cw-teaser" role="button" tabindex="0" aria-label="Buka chat">
        <div id="cw-teaser-text">
          <div id="cw-teaser-name">${escapeHtml(settings.bot_name)}</div>
          <div id="cw-teaser-body">${escapeHtml(previewText(settings.welcome_message))}</div>
        </div>
        <button id="cw-teaser-close" type="button" aria-label="Tutup pesan">&times;</button>
      </div>`
      : "";

    return `
      <button id="cw-toggle" aria-label="Buka chat" aria-expanded="false">
        ${ICON_CHAT}
        ${ICON_CLOSE}
        <span id="cw-badge" aria-hidden="true"></span>
      </button>
      ${teaserHtml}

      <div id="cw-window" role="dialog" aria-label="Chat dengan ${escapeHtml(settings.bot_name)}" aria-modal="true">
        <div id="cw-header">
          <div id="cw-avatar">${avatarContent}</div>
          <div id="cw-header-info">
            <div id="cw-bot-name">${escapeHtml(settings.bot_name)}</div>
            <div id="cw-status"><span id="cw-status-dot"></span>Online</div>
          </div>
          <button id="cw-sound" type="button" aria-label="Matikan suara notifikasi" aria-pressed="false">
            ${ICON_SOUND_ON}
            ${ICON_SOUND_OFF}
          </button>
        </div>

        <div id="cw-messages" role="log" aria-live="polite" aria-atomic="false"></div>

        <div id="cw-input-area">
          <textarea
            id="cw-input"
            placeholder="Tulis pesan..."
            rows="1"
            maxlength="4000"
            aria-label="Tulis pesan"
          ></textarea>
          <button id="cw-send" aria-label="Kirim pesan" disabled>
            ${ICON_SEND}
          </button>
        </div>
        ${footerHtml}
      </div>
    `;
  }

  // ── Escape helper ──────────────────────────────────────────
  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function escapeAttr(str) {
    return String(str).replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }

  // ── Format waktu singkat ───────────────────────────────────
  function formatTime(date) {
    return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
  }

  // ── Render markdown ────────────────────────────────────────
  // Teks di-escape lebih dulu, baru token markdown diubah menjadi HTML.
  // Urutan ini yang membuat HTML mentah dari jawaban AI tidak pernah aktif.
  const MD_MARK = "\u0000cwmd";

  function isSafeUrl(url) {
    return /^(https?:\/\/|mailto:)/i.test(String(url).trim());
  }

  function renderMarkdown(raw) {
    let text = escapeHtml(String(raw == null ? "" : raw))
      .replace(/\u0000/g, "")
      .replace(/\r\n?/g, "\n");

    // Potongan yang sudah jadi HTML disimpan dulu agar tidak diproses ulang.
    const stashed = [];
    const stashedIsBlock = [];
    function stash(html, isBlock) {
      stashed.push(html);
      stashedIsBlock.push(Boolean(isBlock));
      return MD_MARK + (stashed.length - 1) + "\u0000";
    }

    function link(url, label) {
      if (!isSafeUrl(url)) return label;
      return stash(
        '<a href="' + escapeAttr(url) + '" target="_blank" rel="noopener noreferrer nofollow">' + label + "</a>"
      );
    }

    text = text.replace(/```[a-z0-9+#-]*\n?([\s\S]*?)```/gi, function (_m, code) {
      return stash("<pre><code>" + code.replace(/\n+$/, "") + "</code></pre>", true);
    });
    text = text.replace(/`([^`\n]+)`/g, function (_m, code) {
      return stash("<code>" + code + "</code>");
    });

    text = text.replace(/\[([^\]\n]+)\]\(([^)\s]+)\)/g, function (_m, label, url) {
      return link(url, label);
    });
    text = text.replace(/(^|[\s(])(https?:\/\/[^\s<)]+)/g, function (_m, before, url) {
      return before + link(url, url);
    });

    // Token terpanjang diproses lebih dulu supaya *** dan ** tidak saling rebut.
    text = text.replace(/\*\*\*([^\n*]+?)\*\*\*/g, "<strong><em>$1</em></strong>");
    text = text.replace(/\*\*([^\n*]+?)\*\*/g, "<strong>$1</strong>");
    text = text.replace(/__([^\n_]+?)__/g, "<u>$1</u>");
    text = text.replace(/~~([^\n~]+?)~~/g, "<s>$1</s>");
    text = text.replace(/(^|[^\w*])\*([^\n*]+?)\*/g, "$1<em>$2</em>");
    text = text.replace(/(^|[^\w_])_([^\n_]+?)_(?![\w_])/g, "$1<em>$2</em>");

    let html = "";
    let listTag = null;
    let paragraph = [];

    function flushParagraph() {
      if (paragraph.length) {
        html += "<p>" + paragraph.join("<br>") + "</p>";
        paragraph = [];
      }
    }
    function closeList() {
      if (listTag) {
        html += "</" + listTag + ">";
        listTag = null;
      }
    }
    function openList(tag) {
      if (listTag !== tag) {
        closeList();
        html += "<" + tag + ">";
        listTag = tag;
      }
    }

    const lines = text.split("\n");
    for (let i = 0; i < lines.length; i++) {
      const line = lines[i].trim();

      if (line === "") {
        flushParagraph();
        closeList();
        continue;
      }

      // Blok utuh (misal ```kode```) tidak boleh dibungkus <p> — HTML-nya invalid.
      const soloBlock = line.match(/^\u0000cwmd(\d+)\u0000$/);
      if (soloBlock && stashedIsBlock[Number(soloBlock[1])]) {
        flushParagraph();
        closeList();
        html += line;
        continue;
      }

      const heading = line.match(/^#{1,6}\s+(.+)$/);
      if (heading) {
        flushParagraph();
        closeList();
        html += "<h4>" + heading[1] + "</h4>";
        continue;
      }

      const bullet = line.match(/^[-*•]\s+(.+)$/);
      if (bullet) {
        flushParagraph();
        openList("ul");
        html += "<li>" + bullet[1] + "</li>";
        continue;
      }

      const numbered = line.match(/^\d{1,3}[.)]\s+(.+)$/);
      if (numbered) {
        flushParagraph();
        openList("ol");
        html += "<li>" + numbered[1] + "</li>";
        continue;
      }

      const quote = line.match(/^&gt;\s?(.*)$/);
      if (quote) {
        flushParagraph();
        closeList();
        html += "<blockquote>" + quote[1] + "</blockquote>";
        continue;
      }

      closeList();
      paragraph.push(line);
    }
    flushParagraph();
    closeList();

    return html.replace(new RegExp(MD_MARK + "(\\d+)\\u0000", "g"), function (_m, idx) {
      return stashed[Number(idx)] || "";
    });
  }

  // Versi teks polos untuk teaser: markdown dibuang, lalu dipotong.
  function previewText(raw, maxLen) {
    const limit = maxLen || 110;
    const plain = String(raw == null ? "" : raw)
      .replace(/```[\s\S]*?```/g, " ")
      .replace(/\[([^\]]+)\]\([^)]*\)/g, "$1")
      .replace(/[*_~`#>]/g, "")
      .replace(/\s+/g, " ")
      .trim();

    return plain.length > limit ? plain.slice(0, limit - 1).replace(/\s+\S*$/, "") + "…" : plain;
  }

  // ── Suara notifikasi (Web Audio, tanpa file eksternal) ─────
  const SOUND_KEY = "cw_sound_" + API_KEY.substring(0, 8);

  let soundMuted = storageGet(SOUND_KEY) === "off";
  let audioCtx = null;
  let chimePending = false;

  function isSoundMuted() {
    return soundMuted;
  }

  function setSoundMuted(muted) {
    soundMuted = muted;
    storageSet(SOUND_KEY, muted ? "off" : "on");
  }

  function getAudioContext() {
    const Ctx = window.AudioContext || window.webkitAudioContext;
    if (!Ctx) return null;
    if (!audioCtx) {
      try {
        audioCtx = new Ctx();
      } catch (_) {
        return null;
      }
    }
    return audioCtx;
  }

  function emitChime(ctx) {
    const start = ctx.currentTime;
    const gain = ctx.createGain();
    gain.connect(ctx.destination);
    gain.gain.setValueAtTime(0.0001, start);
    gain.gain.exponentialRampToValueAtTime(0.16, start + 0.02);
    gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.5);

    [880, 1174.66].forEach(function (freq, i) {
      const osc = ctx.createOscillator();
      const at = start + i * 0.1;
      osc.type = "sine";
      osc.frequency.setValueAtTime(freq, at);
      osc.connect(gain);
      osc.start(at);
      osc.stop(at + 0.4);
    });
  }

  /** @returns {boolean} true jika suara benar-benar dibunyikan. */
  function playChime() {
    if (soundMuted) return false;
    const ctx = getAudioContext();
    if (!ctx) return false;

    // Browser memblokir audio sebelum ada interaksi; tandai agar dicoba lagi nanti.
    if (ctx.state === "suspended") {
      ctx.resume().catch(function () {});
      return false;
    }

    try {
      emitChime(ctx);
      return true;
    } catch (_) {
      return false;
    }
  }

  // Begitu pengunjung berinteraksi, buka kunci audio dan bunyikan sapaan tertunda.
  function unlockAudioOnFirstGesture() {
    const events = ["pointerdown", "keydown", "touchstart", "scroll"];

    function unlock() {
      events.forEach(function (ev) {
        document.removeEventListener(ev, unlock, true);
      });

      const ctx = getAudioContext();
      if (!ctx) return;

      function flush() {
        if (chimePending) {
          chimePending = false;
          playChime();
        }
      }

      if (ctx.state === "suspended") {
        ctx.resume().then(flush).catch(function () {});
      } else {
        flush();
      }
    }

    events.forEach(function (ev) {
      document.addEventListener(ev, unlock, true);
    });
  }

  // ── Tambah bubble pesan ────────────────────────────────────
  function appendBubble(messagesEl, role, text, useMarkdown) {
    const wrapper = document.createElement("div");

    const bubble = document.createElement("div");
    bubble.className = "cw-bubble " + role;

    if (useMarkdown) {
      bubble.classList.add("cw-md");
      bubble.innerHTML = renderMarkdown(text);
    } else {
      bubble.textContent = text;
    }

    const time = document.createElement("div");
    time.className = "cw-time";
    time.textContent = formatTime(new Date());

    wrapper.appendChild(bubble);
    wrapper.appendChild(time);
    messagesEl.appendChild(wrapper);
    scrollToBottom(messagesEl);

    return wrapper;
  }

  // ── Tampilkan/hilangkan typing indicator ──────────────────
  function showTyping(messagesEl) {
    const el = document.createElement("div");
    el.className = "cw-typing";
    el.id = "cw-typing-indicator";
    el.setAttribute("aria-label", "Bot sedang mengetik");
    el.innerHTML = "<span></span><span></span><span></span>";
    messagesEl.appendChild(el);
    scrollToBottom(messagesEl);
    return el;
  }

  function hideTyping(messagesEl) {
    const el = messagesEl.querySelector("#cw-typing-indicator");
    if (el) el.remove();
  }

  function scrollToBottom(el) {
    el.scrollTop = el.scrollHeight;
  }

  // ── Fetch pengaturan dari backend ──────────────────────────
  async function fetchSettings() {
    const res = await fetch(`${BASE_URL}/api/get-settings.php`, {
      method: "GET",
      mode: "cors",
      credentials: "omit",
      headers: { "X-Api-Key": API_KEY },
    });

    let data = null;
    try {
      data = await res.json();
    } catch (_) {
      data = null;
    }

    if (!res.ok) {
      const msg =
        (data && data.error) ||
        `HTTP ${res.status} — cek Allowed Origins di dashboard ChatLM`;
      throw new Error(msg);
    }

    return data;
  }

  // ── Kirim pesan ke backend ─────────────────────────────────
  async function sendMessage(message) {
    const res = await fetch(`${BASE_URL}/api/chat.php`, {
      method:  "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Api-Key":    API_KEY,
      },
      body: JSON.stringify({
        session_id: STATE.sessionId,
        message:    message,
      }),
    });

    const data = await res.json();

    if (!res.ok) {
      throw new Error(data.error || `HTTP ${res.status}`);
    }

    // Update session_id jika server memberikan yang baru
    if (data.session_id && isValidUUID(data.session_id)) {
      STATE.sessionId = data.session_id;
      persistSessionId(data.session_id);
    }

    return data.reply;
  }

  // ── Auto-resize textarea ───────────────────────────────────
  function autoResize(textarea) {
    textarea.style.height = "auto";
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + "px";
  }

  function showInitError(shadow, message) {
    shadow.innerHTML = "";
    const styleEl = document.createElement("style");
    styleEl.textContent = `
      :host { all: initial; font-family: system-ui, sans-serif; }
      #cw-error {
        position: fixed; bottom: 24px; right: 24px; z-index: 2147483647; pointer-events: auto;
        max-width: 280px; padding: 12px 14px; border-radius: 12px;
        background: #1e293b; color: #f8fafc; font-size: 12px; line-height: 1.45;
        border: 1px solid #ef4444; box-shadow: 0 8px 24px rgba(0,0,0,.35);
      }
      #cw-error strong { display: block; color: #fca5a5; margin-bottom: 4px; font-size: 11px; }
    `;
    shadow.appendChild(styleEl);
    const box = document.createElement("div");
    box.id = "cw-error";
    box.innerHTML = "<strong>ChatLM</strong>" + escapeHtml(String(message));
    shadow.appendChild(box);
  }

  function showLoadingToggle(shadow) {
    shadow.innerHTML = "";
    const styleEl = document.createElement("style");
    styleEl.textContent = `
      :host { all: initial; }
      #cw-toggle-loading {
        position: fixed; bottom: 24px; right: 24px;
        width: 56px; height: 56px; border-radius: 50%;
        background: #14B8A6; border: none; cursor: wait;
        z-index: 2147483647; pointer-events: auto;
        box-shadow: 0 4px 16px rgba(0,0,0,.25);
        display: grid; place-items: center;
        animation: cw-pulse 1.2s ease-in-out infinite;
      }
      @keyframes cw-pulse { 0%,100%{opacity:.7} 50%{opacity:1} }
    `;
    shadow.appendChild(styleEl);
    const btn = document.createElement("button");
    btn.id = "cw-toggle-loading";
    btn.type = "button";
    btn.setAttribute("aria-label", "Loading chat");
    btn.innerHTML = '<svg width="26" height="26" viewBox="0 0 24 24" fill="#fff"><path d="M12 3C7 3 3 7 3 12s4 9 9 9 9-4 9-9-4-9-9-9zm0 16c-3.9 0-7-3.1-7-7s3.1-7 7-7 7 3.1 7 7-3.1 7-7 7z"/></svg>';
    shadow.appendChild(btn);
  }

  // ── Inisialisasi widget utama ──────────────────────────────
  async function init() {
    STATE.sessionId = getOrCreateSessionId();

    const host = document.createElement("div");
    host.id = "chat-widget-host";
    host.setAttribute("aria-hidden", "true");
    host.style.cssText =
      "position:fixed;left:0;top:0;width:0;height:0;overflow:visible;" +
      "pointer-events:none;z-index:2147483000;border:0;padding:0;margin:0;";
    document.body.appendChild(host);

    const shadow = host.attachShadow({ mode: "closed" });
    showLoadingToggle(shadow);

    // Ambil settings dari server
    let settings;
    try {
      settings = await fetchSettings();
    } catch (err) {
      console.error("[ChatLM] Gagal memuat settings:", err.message);
      showInitError(shadow, err.message || "Widget tidak dapat dimuat");
      return;
    }

    STATE.settings = settings;

    // Reset shadow DOM dan bangun widget lengkap
    shadow.innerHTML = "";

    const styleEl = document.createElement("style");
    styleEl.textContent = buildCSS(settings.primary_color || "#4F46E5");
    shadow.appendChild(styleEl);

    const container = document.createElement("div");
    container.innerHTML = buildHTML(settings);
    shadow.appendChild(container);

    // ── Referensi elemen ───────────────────────────────────
    const toggleBtn   = shadow.getElementById("cw-toggle");
    const chatWindow  = shadow.getElementById("cw-window");
    const messagesEl  = shadow.getElementById("cw-messages");
    const inputEl     = shadow.getElementById("cw-input");
    const sendBtn     = shadow.getElementById("cw-send");
    const badge       = shadow.getElementById("cw-badge");
    const soundBtn    = shadow.getElementById("cw-sound");
    const teaserEl    = shadow.getElementById("cw-teaser");
    const teaserClose = shadow.getElementById("cw-teaser-close");

    let unreadCount = 0;

    // ── Badge jumlah pesan belum dibaca ────────────────────
    function bumpUnread() {
      unreadCount++;
      badge.textContent = unreadCount > 9 ? "9+" : String(unreadCount);
      badge.classList.add("show");
    }

    function clearUnread() {
      unreadCount = 0;
      badge.textContent = "";
      badge.classList.remove("show");
    }

    // ── Teaser sapaan di samping tombol ────────────────────
    function showTeaser() {
      if (teaserEl && !STATE.isOpen) teaserEl.classList.add("show");
    }

    function hideTeaser() {
      if (teaserEl) teaserEl.classList.remove("show");
    }

    // ── Tombol suara ───────────────────────────────────────
    function syncSoundBtn() {
      if (!soundBtn) return;
      const muted = isSoundMuted();
      soundBtn.classList.toggle("muted", muted);
      soundBtn.setAttribute("aria-pressed", muted ? "true" : "false");
      soundBtn.setAttribute(
        "aria-label",
        muted ? "Nyalakan suara notifikasi" : "Matikan suara notifikasi"
      );
    }

    syncSoundBtn();

    if (soundBtn) {
      soundBtn.addEventListener("click", () => {
        setSoundMuted(!isSoundMuted());
        syncSoundBtn();
        // Bunyikan sekali sebagai konfirmasi saat suara dinyalakan kembali.
        if (!isSoundMuted()) playChime();
      });
    }

    // ── Tampilkan welcome message ──────────────────────────
    if (settings.welcome_message) {
      appendBubble(messagesEl, "bot", settings.welcome_message, true);
    }

    // ── Toggle buka/tutup jendela chat ────────────────────
    function openWidget() {
      STATE.isOpen = true;
      chatWindow.classList.add("open");
      toggleBtn.classList.add("open");
      toggleBtn.setAttribute("aria-expanded", "true");
      clearUnread();
      hideTeaser();
      setTimeout(() => inputEl.focus(), 250);
    }

    function closeWidget() {
      STATE.isOpen = false;
      chatWindow.classList.remove("open");
      toggleBtn.classList.remove("open");
      toggleBtn.setAttribute("aria-expanded", "false");
    }

    toggleBtn.addEventListener("click", () => {
      hideTeaser();
      STATE.isOpen ? closeWidget() : openWidget();
    });

    if (teaserEl) {
      teaserEl.addEventListener("click", (e) => {
        if (teaserClose && teaserClose.contains(e.target)) return;
        hideTeaser();
        openWidget();
      });
      teaserEl.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          hideTeaser();
          openWidget();
        }
      });
    }

    if (teaserClose) {
      teaserClose.addEventListener("click", (e) => {
        e.stopPropagation();
        hideTeaser();
      });
    }

    // ── Sapaan pertama: badge + teaser + suara ─────────────
    // Ditandai per sesi pengunjung agar tidak berulang di tiap halaman.
    const greetKey = "cw_greeted_" + API_KEY.substring(0, 8);

    if (settings.welcome_message && storageGet(greetKey) !== STATE.sessionId) {
      unlockAudioOnFirstGesture();

      setTimeout(() => {
        if (STATE.isOpen) return;
        storageSet(greetKey, STATE.sessionId);
        bumpUnread();
        showTeaser();
        if (!playChime()) chimePending = true;
      }, GREETING_DELAY_MS);
    }

    // Tutup jika klik di luar jendela
    document.addEventListener("click", (e) => {
      if (STATE.isOpen && !host.contains(e.target)) {
        closeWidget();
      }
    });

    // Tutup dengan tombol Escape
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && STATE.isOpen) {
        closeWidget();
        toggleBtn.focus();
      }
    });

    // ── Validasi input & aktifkan tombol kirim ─────────────
    inputEl.addEventListener("input", () => {
      autoResize(inputEl);
      sendBtn.disabled = inputEl.value.trim() === "" || STATE.isLoading;
    });

    // Kirim dengan Enter; stop semua keydown agar tidak scroll halaman host
    inputEl.addEventListener("keydown", (e) => {
      // Selalu hentikan propagasi ke host page (cegah space scroll, dll.)
      e.stopPropagation();
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        if (!sendBtn.disabled) handleSend();
      }
    });

    sendBtn.addEventListener("click", handleSend);

    // ── Handler kirim pesan ────────────────────────────────
    async function handleSend() {
      const message = inputEl.value.trim();
      if (!message || STATE.isLoading) return;

      STATE.isLoading = true;
      sendBtn.disabled = true;
      inputEl.value = "";
      autoResize(inputEl);

      appendBubble(messagesEl, "user", message);

      const typingEl = showTyping(messagesEl);

      try {
        const reply = await sendMessage(message);
        hideTyping(messagesEl);
        appendBubble(messagesEl, "bot", reply, true);

        playChime();
        if (!STATE.isOpen) bumpUnread();
      } catch (err) {
        hideTyping(messagesEl);
        const raw = err.message || "";
        const isBrowserNetworkErr = raw === "" || raw.startsWith("Failed to fetch") || raw.startsWith("NetworkError") || raw.startsWith("Load failed");
        const displayMsg = (!isBrowserNetworkErr && raw.length > 0 && raw.length < 300)
          ? raw
          : "Maaf, tidak bisa terhubung ke server. Periksa koneksi dan coba lagi.";
        appendBubble(messagesEl, "error", displayMsg);
        console.error("[ChatLM] Error:", raw);
      } finally {
        STATE.isLoading = false;
        sendBtn.disabled = inputEl.value.trim() === "";
        inputEl.focus();
      }
    }
  }

  // ── Mulai setelah DOM siap ─────────────────────────────────
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
