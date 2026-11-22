/* =========================================================
   FZM Production v2 — admin.js (dipakai semua halaman admin)
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

  /* ---------- Keluar: harus POST dengan token CSRF ---------- */
  $$('[data-logout]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var f = $('#logoutForm'); if (f) f.submit();
    });
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

  /* ---------- Angka: langsung tampil, tanpa animasi menghitung naik ---------- */
  function countUp(el, to, fmt) {
    el.textContent = (fmt || rp)(to);
    el.dataset.v = to;
  }

  /* ---------- Isi halaman (dijalankan ulang setiap pindah menu) ---------- */
  var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (en) {
    en.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
  }, { rootMargin: '0px 0px -5% 0px', threshold: 0.08 }) : null;
  var uploadLimit = +document.body.getAttribute('data-upload-limit') || 0;

  /* Unggahan: nginx Herd menolak di atas batas sebelum PHP berjalan */
  function guardUpload(f) {
    f.addEventListener('submit', function (e) {
      if (!uploadLimit) return;
      var total = 0;
      $$('input[type="file"]', f).forEach(function (input) {
        Array.prototype.forEach.call(input.files, function (file) { total += file.size; });
      });
      if (total > uploadLimit) {
        e.preventDefault();
        e.stopImmediatePropagation();
        toast('Berkas terlalu besar (' + (total / 1048576).toFixed(1) + ' MB). Batas server ' + (uploadLimit / 1048576).toFixed(1) + ' MB per sekali simpan.', true);
      }
    });
  }

  function initContent(root) {
    /* Muncul saat scroll */
    $$('[data-reveal]', root).forEach(function (el) { if (io) io.observe(el); else el.classList.add('is-in'); });

    /* Baris tabel yang bisa diklik */
    $$('tr[data-href]', root).forEach(function (tr) {
      tr.addEventListener('click', function (e) { if (!e.target.closest('a, button, form')) location.href = tr.getAttribute('data-href'); });
      tr.addEventListener('keydown', function (e) { if (e.key === 'Enter') location.href = tr.getAttribute('data-href'); });
    });

    $$('form[enctype="multipart/form-data"]', root).forEach(guardUpload);
  }

  /* Pesan hasil aksi dari server */
  function showFlash(el) {
    if (!el) return;
    try {
      var msg = JSON.parse(el.textContent);
      if (msg.ok) toast(msg.ok);
      if (msg.error) toast(msg.error, true);
    } catch (e) {}
  }

  window.FZM = { toast: toast, countUp: countUp, rp: rp, $: $, $$: $$, reduce: reduce };
  initContent(document);
  showFlash($('#flashData'));

  /* =========================================================
     Pindah menu tanpa muat ulang halaman.
     Sidebar & topbar tetap; hanya isi .content dan skrip
     halaman yang ditukar. Jika gagal, jatuh ke muat biasa.
     ========================================================= */
  var content = $('.content'), main = $('.main');
  var canSwap = !!(app && content && main && window.fetch && window.DOMParser && history.pushState);
  var cache = {}, ticket = 0, FRESH = 8000;

  function load(url) {
    var hit = cache[url];
    if (hit && Date.now() - hit.t < FRESH) { delete cache[url]; return hit.p; }
    return fetch(url, { headers: { Accept: 'text/html' }, credentials: 'same-origin' }).then(function (r) {
      if (!r.ok || r.redirected) throw new Error('muat biasa');
      return r.text();
    });
  }
  /* Ambil lebih dulu saat kursor mendekat, agar klik terasa instan. */
  function prefetch(url) {
    if (url === location.href || (cache[url] && Date.now() - cache[url].t < FRESH)) return;
    var p = load(url);
    p.catch(function () { delete cache[url]; });
    cache[url] = { t: Date.now(), p: p };
  }

  /* Jalankan ulang skrip halaman berurutan: skrip ber-src ditunggu sebelum yang berikutnya. */
  function runScripts(from) {
    // Dicari saat dipakai: wadah ini baru ada setelah admin.js berjalan.
    var pageScripts = document.getElementById('pageScripts');
    if (!pageScripts) { pageScripts = document.createElement('div'); pageScripts.id = 'pageScripts'; pageScripts.hidden = true; document.body.appendChild(pageScripts); }
    pageScripts.textContent = '';
    var list = $$('script', from);
    (function next(i) {
      if (i >= list.length) return;
      var src = list[i], el = document.createElement('script');
      Array.prototype.forEach.call(src.attributes, function (a) { el.setAttribute(a.name, a.value); });
      if (src.hasAttribute('src')) {
        el.onload = el.onerror = function () { next(i + 1); };
        pageScripts.appendChild(el);
      } else {
        el.textContent = src.textContent;
        pageScripts.appendChild(el);
        next(i + 1);
      }
    })(0);
  }

  function swap(html) {
    var doc = new DOMParser().parseFromString(html, 'text/html');
    var next = doc.querySelector('.content');
    if (!next || !doc.querySelector('.side-nav')) throw new Error('muat biasa');
    document.title = doc.title;

    /* Menu aktif & jumlah booking menunggu */
    $$('.side-nav a').forEach(function (a) {
      var twin = doc.querySelector('.side-nav a[data-nav="' + a.getAttribute('data-nav') + '"]');
      if (!twin) return;
      if (twin.hasAttribute('aria-current')) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current');
      var c = $('.side-count', a), nc = twin.querySelector('.side-count');
      if (nc && c) c.textContent = nc.textContent;
      else if (nc) a.insertBefore(nc.cloneNode(true), $('.tip', a));
      else if (c) a.removeChild(c);
    });
    placeIndicator();
    var search = $('#globalSearch'), ns = doc.getElementById('globalSearch');
    if (search && ns) search.value = ns.value;

    content.innerHTML = next.innerHTML;
    content.classList.remove('is-swapping');
    if (!reduce) {
      content.classList.add('is-entering');
      setTimeout(function () { content.classList.remove('is-entering'); }, 500);
    }
    window.scrollTo(0, 0);
    initContent(content);
    runScripts(doc.getElementById('pageScripts') || doc.createElement('div'));
    showFlash(doc.getElementById('flashData'));
    var h = $('h1', content);
    if (h) { h.setAttribute('tabindex', '-1'); h.focus({ preventScroll: true }); }
  }

  function go(url, push) {
    var mine = ++ticket;
    main.classList.add('is-loading');
    content.classList.add('is-swapping');
    app.classList.remove('is-open');
    load(url).then(function (html) {
      if (mine !== ticket) return;
      if (push) history.pushState({ fzm: 1 }, '', url);
      swap(html);
      main.classList.remove('is-loading');
    }).catch(function () {
      location.href = url;
    });
  }

  if (canSwap) {
    history.replaceState({ fzm: 1 }, '', location.href);
    $$('.side-nav a, .side-top .brand').forEach(function (a) {
      a.addEventListener('mouseenter', function () { prefetch(a.href); });
      a.addEventListener('focus', function () { prefetch(a.href); });
      a.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        e.preventDefault();
        if (a.href === location.href) { app.classList.remove('is-open'); return; }
        go(a.href, true);
      });
    });
    window.addEventListener('popstate', function (e) {
      if (e.state && e.state.fzm) go(location.href, false);
    });
  }
})();
