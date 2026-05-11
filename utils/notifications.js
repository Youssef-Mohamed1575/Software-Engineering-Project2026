/**
 * HomeSense Notification System
 * ─────────────────────────────
 * Usage:
 *   window.Notifications.add({ title, message, type })
 *   window.Notifications.clear()
 *
 * Types: 'info' | 'warning' | 'alert' | 'success'
 *
 * Call Notifications.add() from anywhere — PHP responses,
 * usage threshold checks, device events, etc.
 */

(function () {
  const STORAGE_KEY = 'homesense_notifications';

  // ── Persist to sessionStorage so notifications survive page nav ──
  function load() {
    try {
      return JSON.parse(sessionStorage.getItem(STORAGE_KEY)) || [];
    } catch {
      return [];
    }
  }

  function save(items) {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  }

  // ── Build the notification panel DOM (injected once per page) ────
  function injectPanel() {
    if (document.getElementById('notif-panel')) return;

    const style = document.createElement('style');
    style.textContent = `
      #notif-badge {
        position: absolute;
        top: -3px; right: -3px;
        width: 10px; height: 10px;
        background: #ef4444;
        border-radius: 50%;
        border: 2px solid white;
        display: none;
        animation: notif-pulse 2s ease-in-out infinite;
      }
      @keyframes notif-pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50%       { transform: scale(1.25); opacity: 0.75; }
      }

      #notif-panel {
        position: fixed;
        top: calc(8% + 8px);
        right: 24px;
        width: 340px;
        max-height: 480px;
        background: #1e293b;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.45), 0 0 0 1px rgba(255,255,255,0.04);
        z-index: 9999;
        display: none;
        flex-direction: column;
        overflow: hidden;
        transform: translateY(-8px);
        opacity: 0;
        transition: transform 0.22s cubic-bezier(0.34,1.56,0.64,1),
                    opacity   0.18s ease;
      }
      #notif-panel.open {
        display: flex;
        transform: translateY(0);
        opacity: 1;
      }
      #notif-panel.animating {
        display: flex;
      }

      #notif-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px 12px;
        border-bottom: 1px solid rgba(255,255,255,0.07);
      }
      #notif-header span {
        font-family: Inter, sans-serif;
        font-size: 13px;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 0.08em;
        text-transform: uppercase;
      }
      #notif-clear-btn {
        font-family: Inter, sans-serif;
        font-size: 11px;
        color: #64748b;
        background: none;
        border: none;
        cursor: pointer;
        padding: 3px 6px;
        border-radius: 6px;
        transition: color 0.15s, background 0.15s;
      }
      #notif-clear-btn:hover {
        color: #f87171;
        background: rgba(239,68,68,0.12);
      }

      #notif-list {
        overflow-y: auto;
        flex: 1;
        padding: 8px 0;
        scrollbar-width: thin;
        scrollbar-color: #334155 transparent;
      }

      .notif-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 18px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        animation: notif-slide-in 0.25s ease;
        cursor: default;
        transition: background 0.12s;
      }
      .notif-item:hover { background: rgba(255,255,255,0.03); }
      .notif-item:last-child { border-bottom: none; }

      @keyframes notif-slide-in {
        from { opacity: 0; transform: translateX(10px); }
        to   { opacity: 1; transform: translateX(0); }
      }

      .notif-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        margin-top: 5px;
        flex-shrink: 0;
      }
      .notif-dot.info    { background: #38bdf8; }
      .notif-dot.warning { background: #fbbf24; }
      .notif-dot.alert   { background: #f87171; }
      .notif-dot.success { background: #34d399; }

      .notif-body { flex: 1; min-width: 0; }
      .notif-title {
        font-family: Inter, sans-serif;
        font-size: 13px;
        font-weight: 600;
        color: #e2e8f0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      .notif-message {
        font-family: Inter, sans-serif;
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
        line-height: 1.45;
      }
      .notif-time {
        font-family: Inter, sans-serif;
        font-size: 10px;
        color: #475569;
        margin-top: 4px;
      }

      .notif-dismiss {
        background: none;
        border: none;
        color: #475569;
        font-size: 14px;
        cursor: pointer;
        padding: 0 2px;
        line-height: 1;
        border-radius: 4px;
        transition: color 0.12s;
        flex-shrink: 0;
      }
      .notif-dismiss:hover { color: #94a3b8; }

      #notif-empty {
        padding: 32px 18px;
        text-align: center;
        font-family: Inter, sans-serif;
        font-size: 13px;
        color: #475569;
      }
      #notif-empty svg {
        display: block;
        margin: 0 auto 10px;
        opacity: 0.35;
      }
    `;
    document.head.appendChild(style);

    const panel = document.createElement('div');
    panel.id = 'notif-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Notifications');
    panel.innerHTML = `
      <div id="notif-header">
        <span>Notifications</span>
        <button id="notif-clear-btn" title="Clear all">Clear all</button>
      </div>
      <div id="notif-list"></div>
    `;
    document.body.appendChild(panel);

    document.getElementById('notif-clear-btn')
      .addEventListener('click', () => window.Notifications.clear());

    // Close on outside click
    document.addEventListener('click', (e) => {
      const bellBtn = document.getElementById('notif-bell-btn');
      const panel   = document.getElementById('notif-panel');
      if (!panel || !bellBtn) return;
      if (!panel.contains(e.target) && !bellBtn.contains(e.target)) {
        closePanel();
      }
    });
  }

  // ── Wrap the bell button so we control it ───────────────────────
  function hookBellButton() {
    // Find the bell <button> by its img src
    const bells = document.querySelectorAll('button img[src*="bell"]');
    if (!bells.length) return;

    const bellImg = bells[0];
    const bellBtn = bellImg.parentElement;
    bellBtn.id    = 'notif-bell-btn';

    // Make the button relatively positioned for the badge
    bellBtn.style.position = 'relative';

    // Inject badge dot
    const badge = document.createElement('div');
    badge.id = 'notif-badge';
    bellBtn.appendChild(badge);

    bellBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      togglePanel();
    });
  }

  function togglePanel() {
    const panel = document.getElementById('notif-panel');
    if (!panel) return;
    panel.classList.contains('open') ? closePanel() : openPanel();
  }

  function openPanel() {
    const panel = document.getElementById('notif-panel');
    if (!panel) return;
    renderList();
    panel.style.display = 'flex';
    requestAnimationFrame(() => panel.classList.add('open'));
  }

  function closePanel() {
    const panel = document.getElementById('notif-panel');
    if (!panel) return;
    panel.classList.remove('open');
    panel.classList.add('animating');
    setTimeout(() => {
      panel.style.display = 'none';
      panel.classList.remove('animating');
    }, 220);
  }

  // ── Render ───────────────────────────────────────────────────────
  function renderList() {
    const list  = document.getElementById('notif-list');
    const items = load();
    list.innerHTML = '';

    if (!items.length) {
      list.innerHTML = `
        <div id="notif-empty">
          <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
          No notifications yet
        </div>`;
      return;
    }

    items.forEach((n, idx) => {
      const el = document.createElement('div');
      el.className = 'notif-item';
      el.innerHTML = `
        <div class="notif-dot ${n.type || 'info'}"></div>
        <div class="notif-body">
          <div class="notif-title">${escapeHtml(n.title)}</div>
          ${n.message ? `<div class="notif-message">${escapeHtml(n.message)}</div>` : ''}
          <div class="notif-time">${formatTime(n.ts)}</div>
        </div>
        <button class="notif-dismiss" title="Dismiss" data-idx="${idx}">✕</button>
      `;
      list.appendChild(el);
    });

    list.querySelectorAll('.notif-dismiss').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        dismissOne(parseInt(btn.dataset.idx));
      });
    });
  }

  function updateBadge() {
    const badge = document.getElementById('notif-badge');
    if (!badge) return;
    const count = load().length;
    badge.style.display = count > 0 ? 'block' : 'none';
  }

  function dismissOne(idx) {
    const items = load();
    items.splice(idx, 1);
    save(items);
    renderList();
    updateBadge();
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function formatTime(ts) {
    if (!ts) return '';
    const diff = Math.floor((Date.now() - ts) / 1000);
    if (diff < 60)  return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return new Date(ts).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  }

  // ── Public API ───────────────────────────────────────────────────
  const Notifications = {
    /**
     * Add a notification.
     * @param {{ title: string, message?: string, type?: 'info'|'warning'|'alert'|'success' }} notif
     */
    add(notif) {
      const items = load();
      items.unshift({
        title:   notif.title   || 'Notification',
        message: notif.message || '',
        type:    notif.type    || 'info',
        ts:      Date.now()
      });
      // Cap at 50 notifications
      if (items.length > 50) items.length = 50;
      save(items);
      updateBadge();
      // Briefly animate badge
      const badge = document.getElementById('notif-badge');
      if (badge) {
        badge.style.transform = 'scale(1.5)';
        setTimeout(() => badge.style.transform = '', 200);
      }
    },

    /** Remove all notifications */
    clear() {
      save([]);
      renderList();
      updateBadge();
    },

    /** Get all stored notifications */
    getAll() {
      return load();
    }
  };

  window.Notifications = Notifications;

  // ── Init on DOM ready ────────────────────────────────────────────
  function init() {
    injectPanel();
    hookBellButton();
    updateBadge();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
