/* =========================================================
   FZM Production v2 — admin.js (dipakai semua halaman admin)
   - Penjaga login (prototipe: sessionStorage/localStorage)
   - Sidebar: lipat/buka (diingat), indikator aktif meluncur,
     laci di ponsel
   - Topbar: bayangan saat scroll, dropdown, pintasan "/"
   - Toast, angka count-up, sorotan cursor
   Ekspor: window.FZM = { toast, countUp, rp, $, $$ }
   ========================================================= */
(function () {
  'use strict';

  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var rp = function (n) { return 'Rp' + Math.round(n).toLocaleString('id-ID'); };
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var store = {
    get: function (k) { try { return localStorage.getItem(k) || sessionStorage.getItem(k); } catch (e) { return null; } },
    set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} },
    clear: function (k) { try { localStorage.removeItem(k); sessionStorage.removeItem(k); } catch (e) {} }
  };

  /* ---------- Penjaga login (Django: @login_required) ---------- */
  var session = store.get('fzm_admin');
  var valid = false;
  try { var parsed = JSON.parse(session || 'null'); valid = !!(parsed && parsed.exp > Date.now()); } catch (e) {}
  if (!valid) { store.clear('fzm_admin'); location.replace('login.html'); return; }
  try {
    var who = JSON.parse(session).email || 'admin';
    $$('[data-admin-email]').forEach(function (el) { el.textContent = who; });
  } catch (e) {}
  $$('[data-logout]').forEach(function (a) {
    a.addEventListener('click', function (e) { e.preventDefault(); store.clear('fzm_admin'); location.href = 'login.html'; });
  });

  /* ---------- Sidebar ---------- */
  var app = $('.app');
  if (store.get('fzm_side') === 'collapsed' && window.innerWidth > 960) app.classList.add('is-collapsed');
  var ind = $('.side-ind');
  function placeIndicator() {
    var cur = $('.side-nav a[aria-current="page"]');
    if (!ind || !cur) return;
    ind.style.transform = 'translateY(' + cur.offsetTop + 'px)';
    ind.style.height = cur.offsetHeight + 'px';
  }
  placeIndicator();
  window.addEventListener('resize', placeIndicator);
  $$('.side-nav a').forEach(function (a) {
    a.addEventListener('mouseenter', function () { if (ind) ind.style.transform = 'translateY(' + a.offsetTop + 'px)'; });
  });
  var nav = $('.side-nav');
  if (nav) nav.addEventListener('mouseleave', placeIndicator);

  var collapse = $('.side-collapse');
  if (collapse) collapse.addEventListener('click', function () {
    var c = app.classList.toggle('is-collapsed');
    collapse.setAttribute('aria-label', c ? 'Bentangkan sidebar' : 'Lipat sidebar');
    store.set('fzm_side', c ? 'collapsed' : 'open');
    setTimeout(placeIndicator, 650);
  });
  var opener = $('.topbar-menu');
  if (opener) opener.addEventListener('click', function () { app.classList.add('is-open'); });
  var scrim = $('.scrim');
  if (scrim) scrim.addEventListener('click', function () { app.classList.remove('is-open'); });

  /* ---------- Topbar ---------- */
  var topbar = $('.topbar');
  window.addEventListener('scroll', function () { if (topbar) topbar.classList.toggle('is-scrolled', window.scrollY > 8); }, { passive: true });

  $$('.dd').forEach(function (dd) {
    var btn = $('[data-dd]', dd);
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = !dd.classList.contains('is-open');
      $$('.dd.is-open').forEach(function (o) { o.classList.remove('is-open'); $('[data-dd]', o).setAttribute('aria-expanded', 'false'); });
      dd.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', String(open));
    });
  });
  document.addEventListener('click', function (e) {
    $$('.dd.is-open').forEach(function (o) { if (!o.contains(e.target)) { o.classList.remove('is-open'); $('[data-dd]', o).setAttribute('aria-expanded', 'false'); } });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      $$('.dd.is-open').forEach(function (o) { o.classList.remove('is-open'); });
      app.classList.remove('is-open');
    }
    var tag = (document.activeElement && document.activeElement.tagName) || '';
    if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(tag)) {
      var s = $('.search input'); if (s) { e.preventDefault(); s.focus(); }
    }
  });

  /* ---------- Toast ---------- */
  var box = $('.toasts');
  if (!box) { box = document.createElement('div'); box.className = 'toasts'; box.setAttribute('aria-live', 'polite'); document.body.appendChild(box); }
  function toast(msg, err) {
    var t = document.createElement('div');
    t.className = 'toast' + (err ? ' is-err' : '');
    t.setAttribute('role', err ? 'alert' : 'status');
    t.innerHTML = '<span class="ico" aria-hidden="true">' + (err
      ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"><path d="M12 7v6M12 17h.01"/></svg>'
      : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>') +
      '</span><span></span>';
    t.lastChild.textContent = msg;
    box.appendChild(t);
    setTimeout(function () { t.classList.add('is-out'); setTimeout(function () { t.remove(); }, 450); }, 3600);
  }

  /* ---------- Count-up ---------- */
  function countUp(el, to, fmt, dur) {
    fmt = fmt || rp;
    if (reduce) { el.textContent = fmt(to); return; }
    var from = +(el.dataset.v || 0), t0 = performance.now();
    dur = dur || 1200;
    (function step(now) {
      var k = Math.min(1, (now - t0) / dur), e = 1 - Math.pow(1 - k, 4);
      el.textContent = fmt(from + (to - from) * e);
      if (k < 1) requestAnimationFrame(step); else el.dataset.v = to;
    })(t0);
  }

  /* ---------- Muncul saat scroll ---------- */
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (en) {
      en.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -5% 0px', threshold: 0.08 });
    $$('[data-reveal]').forEach(function (el) { io.observe(el); });
  } else $$('[data-reveal]').forEach(function (el) { el.classList.add('is-in'); });

  /* ---------- Sorotan cursor ---------- */
  $$('.stat-glow, .hero-card').forEach(function (c) {
    c.addEventListener('pointermove', function (e) {
      var r = c.getBoundingClientRect();
      c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      c.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  });

  window.FZM = { toast: toast, countUp: countUp, rp: rp, $: $, $$: $$, reduce: reduce };
})();
