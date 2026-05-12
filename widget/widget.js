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

  // ── Konfigurasi dari atribut script tag ────────────────────
  const currentScript =
    document.currentScript ||
    (function () {
      const scripts = document.getElementsByTagName("script");
      return scripts[scripts.length - 1];
    })();

  const API_KEY  = (currentScript.getAttribute("data-api-key")  || "").trim();
  const BASE_URL = (currentScript.getAttribute("data-base-url") || "").replace(/\/$/, "").trim();

  if (!API_KEY || !BASE_URL) {
    console.warn("[ChatWidget] data-api-key dan data-base-url wajib diisi.");
    return;
  }

  // ── State widget ───────────────────────────────────────────
  const STATE = {
    isOpen:    false,
    isLoading: false,
    sessionId: getOrCreateSessionId(),
    settings:  null,
  };

  // ── Ambil/buat session_id di sessionStorage ────────────────
  function getOrCreateSessionId() {
    const key       = "cw_session_" + API_KEY.substring(0, 8);
    let   sessionId = sessionStorage.getItem(key);

    if (!sessionId || !isValidUUID(sessionId)) {
      sessionId = generateUUID();
      sessionStorage.setItem(key, sessionId);
    }
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
        width: 20px; height: 20px;
        border-radius: 50%;
        background: #EF4444;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        display: none;
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
      #cw-messages::-webkit-scrollbar       { width: 4px; }
      #cw-messages::-webkit-scrollbar-track { background: transparent; }
      #cw-messages::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 4px; }

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
        padding: 12px 14px;
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
      }
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
      #cw-footer a { color: #9CA3AF; text-decoration: none; }
      #cw-footer a:hover { text-decoration: underline; }

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

  // ── Bangun HTML widget ─────────────────────────────────────
  function buildHTML(settings) {
    const avatarContent = settings.bot_avatar_url
      ? `<img src="${escapeAttr(settings.bot_avatar_url)}" alt="avatar" loading="lazy">`
      : ICON_BOT;

    return `
      <button id="cw-toggle" aria-label="Buka chat" aria-expanded="false">
        ${ICON_CHAT}
        ${ICON_CLOSE}
        <span id="cw-badge" aria-hidden="true"></span>
      </button>

      <div id="cw-window" role="dialog" aria-label="Chat dengan ${escapeHtml(settings.bot_name)}" aria-modal="true">
        <div id="cw-header">
          <div id="cw-avatar">${avatarContent}</div>
          <div id="cw-header-info">
            <div id="cw-bot-name">${escapeHtml(settings.bot_name)}</div>
            <div id="cw-status"><span id="cw-status-dot"></span>Online</div>
          </div>
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

        <div id="cw-footer">
          Powered by <a href="${BASE_URL}" target="_blank" rel="noopener">ChatWidget</a>
        </div>
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

  // ── Tambah bubble pesan ────────────────────────────────────
  function appendBubble(messagesEl, role, text) {
    const wrapper = document.createElement("div");

    const bubble = document.createElement("div");
    bubble.className = "cw-bubble " + role;
    bubble.textContent = text;

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
      headers: { "X-Api-Key": API_KEY },
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }

    return res.json();
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
      sessionStorage.setItem("cw_session_" + API_KEY.substring(0, 8), data.session_id);
    }

    return data.reply;
  }

  // ── Auto-resize textarea ───────────────────────────────────
  function autoResize(textarea) {
    textarea.style.height = "auto";
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + "px";
  }

  // ── Inisialisasi widget utama ──────────────────────────────
  async function init() {
    // Buat container host (elemen khusus agar Shadow DOM bisa attach)
    const host = document.createElement("div");
    host.id = "chat-widget-host";
    document.body.appendChild(host);

    // Attach Shadow DOM (closed = tidak bisa diakses dari luar)
    const shadow = host.attachShadow({ mode: "closed" });

    // Tampilkan tombol toggle dulu sebelum settings dimuat
    const tempStyle = document.createElement("style");
    tempStyle.textContent = `
      :host { all: initial; }
      #cw-toggle-placeholder {
        position: fixed; bottom: 24px; right: 24px;
        width: 56px; height: 56px;
        border-radius: 50%;
        background: #6B7280;
        z-index: 2147483647;
        box-shadow: 0 4px 16px rgba(0,0,0,.25);
      }
    `;
    shadow.appendChild(tempStyle);

    // Ambil settings dari server
    let settings;
    try {
      settings = await fetchSettings();
    } catch (err) {
      console.error("[ChatWidget] Gagal memuat settings:", err.message);
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
    const toggleBtn  = shadow.getElementById("cw-toggle");
    const chatWindow = shadow.getElementById("cw-window");
    const messagesEl = shadow.getElementById("cw-messages");
    const inputEl    = shadow.getElementById("cw-input");
    const sendBtn    = shadow.getElementById("cw-send");
    const badge      = shadow.getElementById("cw-badge");

    let unreadCount = 0;

    // ── Tampilkan welcome message ──────────────────────────
    if (settings.welcome_message) {
      appendBubble(messagesEl, "bot", settings.welcome_message);
    }

    // ── Toggle buka/tutup jendela chat ────────────────────
    function openWidget() {
      STATE.isOpen = true;
      chatWindow.classList.add("open");
      toggleBtn.classList.add("open");
      toggleBtn.setAttribute("aria-expanded", "true");
      badge.style.display = "none";
      unreadCount = 0;
      setTimeout(() => inputEl.focus(), 250);
    }

    function closeWidget() {
      STATE.isOpen = false;
      chatWindow.classList.remove("open");
      toggleBtn.classList.remove("open");
      toggleBtn.setAttribute("aria-expanded", "false");
    }

    toggleBtn.addEventListener("click", () => {
      STATE.isOpen ? closeWidget() : openWidget();
    });

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

    // Kirim dengan Enter (Shift+Enter = newline)
    inputEl.addEventListener("keydown", (e) => {
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
        appendBubble(messagesEl, "bot", reply);

        if (!STATE.isOpen) {
          unreadCount++;
          badge.textContent = unreadCount;
          badge.style.display = "flex";
        }
      } catch (err) {
        hideTyping(messagesEl);
        appendBubble(messagesEl, "error", "Maaf, terjadi kesalahan. Silakan coba lagi.");
        console.error("[ChatWidget] Error:", err.message);
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
