/**
 * ShokherSrity — Floating WhatsApp Chat-Head Widget
 * ─────────────────────────────────────────────────────
 * Self-contained: injects its own CSS + HTML.
 * • Draggable like a Messenger chat-head
 * • Snaps to the nearest screen corner on release
 * • Shows a frosted-glass tooltip after 3 s delay
 * • Apple-style frosted glass / liquid glass aesthetic
 * • Works on every page — just add one <script> tag
 */
;(function () {
  'use strict';

  /* ─── Configuration ──────────────────────────────── */
  const WA_NUMBER  = '8801799334656';
  const WA_MESSAGE = 'Hello! I would like to book a wedding photography session. Please share more details.';
  const TOOLTIP_DELAY_MS = 3000;
  const TOOLTIP_TEXT = '📸 Book your shoot now!';
  const EDGE_MARGIN = 16;
  const SNAP_DURATION_MS = 320;
  const FAB_SIZE = 58;
  const FAB_SIZE_MOBILE = 52;

  /* ─── Inject Styles ──────────────────────────────── */
  const css = `
/* ====== WhatsApp Chat-Head Widget ====== */

/* ── Tooltip bubble ── */
#wa-tooltip{
  pointer-events:none;
  position:fixed;
  z-index:2147483641;
  padding:10px 18px;
  border-radius:14px;
  font-family:'Inter',system-ui,sans-serif;
  font-size:13px;
  font-weight:500;
  letter-spacing:.01em;
  color:rgba(255,255,255,.92);
  white-space:nowrap;
  cursor:pointer;

  /* Frosted glass */
  background:rgba(30,30,30,.55);
  -webkit-backdrop-filter:blur(24px) saturate(180%);
  backdrop-filter:blur(24px) saturate(180%);
  border:1px solid rgba(255,255,255,.14);
  box-shadow:
    0 8px 32px rgba(0,0,0,.28),
    inset 0 1px 0 rgba(255,255,255,.12);

  /* start hidden off-screen */
  left:-9999px;
  top:-9999px;

  /* Entry animation */
  opacity:0;
  transform:translateY(8px) scale(.92);
  transition:opacity .4s cubic-bezier(.22,1,.36,1),
             transform .4s cubic-bezier(.22,1,.36,1);
}
#wa-tooltip.show{
  opacity:1;
  transform:translateY(0) scale(1);
  pointer-events:auto;
}
/* small arrow */
#wa-tooltip::after{
  content:'';
  position:absolute;
  bottom:-6px;
  right:22px;
  width:12px;height:12px;
  background:rgba(30,30,30,.55);
  -webkit-backdrop-filter:blur(24px) saturate(180%);
  backdrop-filter:blur(24px) saturate(180%);
  border-right:1px solid rgba(255,255,255,.14);
  border-bottom:1px solid rgba(255,255,255,.14);
  transform:rotate(45deg);
}
#wa-tooltip.left-side::after{
  right:auto;
  left:22px;
}
#wa-tooltip .wa-tt-close{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  margin-left:10px;
  width:18px;height:18px;
  border-radius:50%;
  background:rgba(255,255,255,.12);
  border:none;
  cursor:pointer;
  color:rgba(255,255,255,.7);
  font-size:11px;
  line-height:1;
  transition:background .2s;
  vertical-align:middle;
}
#wa-tooltip .wa-tt-close:hover{
  background:rgba(255,255,255,.25);
}

/* ── FAB (floating action button) ── */
#wa-fab{
  pointer-events:auto;
  position:fixed;
  z-index:2147483640;
  width:${FAB_SIZE}px;height:${FAB_SIZE}px;
  border-radius:50%;
  border:none;
  cursor:grab;
  display:flex;
  align-items:center;
  justify-content:center;
  touch-action:none;
  -webkit-tap-highlight-color:transparent;

  /* Start off-screen, JS places it immediately */
  left:-9999px;
  top:-9999px;

  /* Frosted glass */
  background:rgba(37,211,102,.82);
  -webkit-backdrop-filter:blur(20px) saturate(160%);
  backdrop-filter:blur(20px) saturate(160%);
  border:1.5px solid rgba(255,255,255,.22);
  box-shadow:
    0 6px 24px rgba(37,211,102,.35),
    0 2px 8px rgba(0,0,0,.18),
    inset 0 1px 0 rgba(255,255,255,.18);

  /* Entry */
  animation:wa-fab-entrance .6s cubic-bezier(.22,1,.36,1) both;
  transition:box-shadow .25s, transform .25s;
}
#wa-fab:active{
  cursor:grabbing;
}
#wa-fab:hover{
  box-shadow:
    0 8px 32px rgba(37,211,102,.45),
    0 4px 12px rgba(0,0,0,.22),
    inset 0 1px 0 rgba(255,255,255,.22);
  transform:scale(1.06);
}
#wa-fab svg{
  width:28px;height:28px;
  fill:#fff;
  filter:drop-shadow(0 1px 2px rgba(0,0,0,.18));
  pointer-events:none;
}

/* Pulse ring */
#wa-fab::before{
  content:'';
  position:absolute;
  inset:-6px;
  border-radius:50%;
  border:2px solid rgba(37,211,102,.45);
  animation:wa-pulse 2.4s ease-out infinite;
}
@keyframes wa-pulse{
  0%{transform:scale(1);opacity:.7}
  70%{transform:scale(1.35);opacity:0}
  100%{transform:scale(1.35);opacity:0}
}
@keyframes wa-fab-entrance{
  0%{opacity:0;transform:scale(.4) translateY(30px)}
  100%{opacity:1;transform:scale(1) translateY(0)}
}

/* ── Drag state ── */
#wa-fab.dragging{
  transform:scale(1.12)!important;
  box-shadow:
    0 12px 40px rgba(37,211,102,.5),
    0 4px 14px rgba(0,0,0,.25),
    inset 0 1px 0 rgba(255,255,255,.25);
  cursor:grabbing;
}
#wa-fab.dragging::before{
  animation:none;
  opacity:0;
}

/* ── Snap transition ── */
#wa-fab.snapping{
  transition:left ${SNAP_DURATION_MS}ms cubic-bezier(.22,1,.36,1),
             top  ${SNAP_DURATION_MS}ms cubic-bezier(.22,1,.36,1);
}

/* ── Mobile refinements ── */
@media(max-width:480px){
  #wa-fab{width:${FAB_SIZE_MOBILE}px;height:${FAB_SIZE_MOBILE}px}
  #wa-fab svg{width:24px;height:24px}
  #wa-tooltip{font-size:12px;padding:8px 14px;border-radius:12px}
}
`;

  const styleEl = document.createElement('style');
  styleEl.textContent = css;
  document.head.appendChild(styleEl);

  /* ─── Inject HTML ────────────────────────────────── */
  const tooltip = document.createElement('div');
  tooltip.id = 'wa-tooltip';
  tooltip.innerHTML = TOOLTIP_TEXT + '<button class="wa-tt-close" aria-label="Dismiss">✕</button>';
  document.body.appendChild(tooltip);

  const fab = document.createElement('button');
  fab.id = 'wa-fab';
  fab.setAttribute('aria-label', 'Chat on WhatsApp');
  fab.innerHTML = '<svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
  document.body.appendChild(fab);

  const closeBtn = tooltip.querySelector('.wa-tt-close');

  /* ─── Helper: get current FAB size ───────────────── */
  function getFabSize () {
    return window.innerWidth <= 480 ? FAB_SIZE_MOBILE : FAB_SIZE;
  }

  /* ─── FAB position state (left, top in px) ───────── */
  var fabX, fabY;

  /* Place FAB at bottom-right immediately */
  function placeAtBottomRight () {
    var s  = getFabSize();
    var vw = window.innerWidth;
    var vh = window.innerHeight;
    fabX = vw - s - EDGE_MARGIN;
    fabY = vh - s - EDGE_MARGIN - 12;
    fab.style.left = fabX + 'px';
    fab.style.top  = fabY + 'px';
  }

  // Place now, synchronously
  placeAtBottomRight();

  /* ─── Position tooltip above the FAB ─────────────── */
  function positionTooltip () {
    var s = getFabSize();
    var vw = window.innerWidth;

    // Tooltip dimensions (use fallback if not rendered yet)
    var ttW = tooltip.offsetWidth || 200;
    var ttH = tooltip.offsetHeight || 40;

    var fabCx = fabX + s / 2;
    var isLeftSide = fabCx < vw / 2;

    // Place tooltip above FAB with a clear gap
    var gap = 14;
    var ttTop = fabY - ttH - gap;

    // If it would go above viewport, place it below the FAB instead
    if (ttTop < 8) {
      ttTop = fabY + s + gap;
    }

    var ttLeft;
    if (isLeftSide) {
      ttLeft = fabX;
      tooltip.classList.add('left-side');
    } else {
      ttLeft = fabX + s - ttW;
      tooltip.classList.remove('left-side');
    }

    // Clamp horizontally within viewport
    ttLeft = Math.max(8, Math.min(vw - ttW - 8, ttLeft));

    tooltip.style.left = ttLeft + 'px';
    tooltip.style.top  = ttTop + 'px';
  }

  /* ─── Tooltip logic ──────────────────────────────── */
  var tooltipDismissed = false;

  function showTooltip () {
    if (tooltipDismissed) return;
    positionTooltip();
    tooltip.classList.add('show');
  }
  function hideTooltip () {
    tooltip.classList.remove('show');
  }

  setTimeout(showTooltip, TOOLTIP_DELAY_MS);

  closeBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    tooltipDismissed = true;
    hideTooltip();
  });

  tooltip.addEventListener('click', function (e) {
    if (e.target === closeBtn) return;
    openWhatsApp();
  });

  /* ─── WhatsApp open ──────────────────────────────── */
  function openWhatsApp () {
    window.open(
      'https://wa.me/' + WA_NUMBER + '?text=' + encodeURIComponent(WA_MESSAGE),
      '_blank'
    );
  }

  /* ─── Apply FAB position to DOM ──────────────────── */
  function applyFabPosition (animate) {
    if (animate) fab.classList.add('snapping');
    fab.style.left = fabX + 'px';
    fab.style.top  = fabY + 'px';
    if (animate) {
      setTimeout(function () { fab.classList.remove('snapping'); }, SNAP_DURATION_MS + 20);
    }
    // Keep tooltip in sync
    if (tooltip.classList.contains('show')) positionTooltip();
  }

  /* ─── Drag & Snap engine ─────────────────────────── */
  var isDragging = false;
  var wasDragged = false;
  var startX, startY, dragOrigX, dragOrigY;

  function pointerDown (e) {
    if (e.target.closest('.wa-tt-close')) return;
    isDragging = true;
    wasDragged = false;
    fab.classList.add('dragging');
    fab.classList.remove('snapping');
    hideTooltip();

    var point = e.touches ? e.touches[0] : e;
    startX = point.clientX;
    startY = point.clientY;
    dragOrigX = fabX;
    dragOrigY = fabY;

    e.preventDefault();
  }

  function pointerMove (e) {
    if (!isDragging) return;
    var point = e.touches ? e.touches[0] : e;
    var dx = point.clientX - startX;
    var dy = point.clientY - startY;

    if (Math.abs(dx) > 4 || Math.abs(dy) > 4) wasDragged = true;

    var s  = getFabSize();
    var vw = window.innerWidth;
    var vh = window.innerHeight;

    fabX = Math.max(EDGE_MARGIN, Math.min(vw - s - EDGE_MARGIN, dragOrigX + dx));
    fabY = Math.max(EDGE_MARGIN, Math.min(vh - s - EDGE_MARGIN, dragOrigY + dy));

    fab.style.left = fabX + 'px';
    fab.style.top  = fabY + 'px';
    e.preventDefault();
  }

  function pointerUp () {
    if (!isDragging) return;
    isDragging = false;
    fab.classList.remove('dragging');

    if (!wasDragged) {
      snapToCorner();
      openWhatsApp();
      return;
    }
    snapToCorner();

    // Re-show tooltip after snap
    if (!tooltipDismissed) {
      setTimeout(function () {
        showTooltip();
      }, SNAP_DURATION_MS + 80);
    }
  }

  function snapToCorner () {
    var s  = getFabSize();
    var vw = window.innerWidth;
    var vh = window.innerHeight;
    var cx = fabX + s / 2;
    var cy = fabY + s / 2;

    var corners = [
      { x: EDGE_MARGIN,            y: EDGE_MARGIN            },
      { x: vw - s - EDGE_MARGIN,   y: EDGE_MARGIN            },
      { x: EDGE_MARGIN,            y: vh - s - EDGE_MARGIN   },
      { x: vw - s - EDGE_MARGIN,   y: vh - s - EDGE_MARGIN   },
    ];

    var best = corners[3];
    var bestDist = Infinity;
    corners.forEach(function (c) {
      var d = Math.hypot(cx - (c.x + s / 2), cy - (c.y + s / 2));
      if (d < bestDist) { bestDist = d; best = c; }
    });

    fabX = best.x;
    fabY = best.y;
    applyFabPosition(true);
  }

  // Pointer events
  fab.addEventListener('mousedown',  pointerDown);
  fab.addEventListener('touchstart', pointerDown, { passive: false });
  document.addEventListener('mousemove',  pointerMove);
  document.addEventListener('touchmove',  pointerMove, { passive: false });
  document.addEventListener('mouseup',    pointerUp);
  document.addEventListener('touchend',   pointerUp);

  // Re-clamp on resize
  var resizeTimer;
  window.addEventListener('resize', function () {
    if (isDragging) return;
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      var s  = getFabSize();
      var vw = window.innerWidth;
      var vh = window.innerHeight;
      fabX = Math.max(EDGE_MARGIN, Math.min(vw - s - EDGE_MARGIN, fabX));
      fabY = Math.max(EDGE_MARGIN, Math.min(vh - s - EDGE_MARGIN, fabY));
      applyFabPosition(true);
    }, 150);
  });

})();
