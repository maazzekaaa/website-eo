/* =========================================================
   FZM Production — admin.js (dipakai semua halaman admin)
   - Navbar bawah: pindah menu tanpa muat ulang halaman
   - Baris atas: dropdown notifikasi/akun, pintasan "/"
   - Toast, format angka
   Ekspor: window.FZM = { toast, countUp, rp, $, $$ }
   ========================================================= */
(function () {
  'use strict';

  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var rp = function (n) { return 'Rp' + Math.round(n).toLocaleString('id-ID'); };
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Keluar: harus POST dengan token CSRF ---------- */
  $$('[data-logout]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var f = $('#logoutForm'); if (f) f.submit();
    });
  });

  var app = $('.app');

  /* ---------- Dropdown ---------- */
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

    /* Kontrol segmen: jempol putih di bawah tab aktif */
    $$('.tabs', root).forEach(function (tabs) {
      if ($('.tabs-thumb', tabs)) return;
      var t = document.createElement('span');
      t.className = 'tabs-thumb';
      t.setAttribute('aria-hidden', 'true');
      tabs.insertBefore(t, tabs.firstChild);
      tabs.classList.add('has-thumb');
      placeTabs(tabs, true);
    });
  }

  function activeTab(tabs) { return $('a[aria-current="page"], button[aria-selected="true"]', tabs); }
  /* Geser jempol ke tab aktif. from = posisi awal (dipakai saat isi ditukar agar gerakan bersambung). */
  function placeTabs(tabs, instant, from) {
    var t = $('.tabs-thumb', tabs), on = activeTab(tabs);
    if (!t || !on) return;
    if (from) {
      t.classList.add('is-instant');
      t.style.width = from.w; t.style.transform = from.tf;
      void t.offsetWidth;
      instant = false;
    }
    t.classList.toggle('is-instant', !!instant || reduce);
    t.style.width = on.offsetWidth + 'px';
    t.style.transform = 'translateX(' + on.offsetLeft + 'px)';
  }
  window.addEventListener('resize', function () { $$('.tabs').forEach(function (x) { placeTabs(x, true); }); });
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { $$('.tabs').forEach(function (x) { placeTabs(x, true); }); });
  // Tab berupa tombol (mis. di dashboard): skrip halaman mengganti aria-selected dulu, lalu jempol digeser.
  document.addEventListener('click', function (e) {
    var b = e.target.closest('.tabs button');
    if (b) requestAnimationFrame(function () { placeTabs(b.closest('.tabs'), false); });
  });

  /* Pesan hasil aksi dari server */
  function showFlash(el) {
    if (!el) return;
    try {
      var msg = JSON.parse(el.textContent);
      if (msg.ok) toast(msg.ok);
      if (msg.error) toast(msg.error, true);
    } catch (e) {}
  }

  /* ---------- Jempol hitam yang meluncur ke menu aktif (ala kontrol segmen macOS) ---------- */
  var adminNav = $('.admin-nav'), navThumb = null;
  function placeThumb(instant) {
    if (!navThumb) return;
    var on = $('a[aria-current="page"]', adminNav);
    navThumb.hidden = !on;
    if (!on) return;
    navThumb.classList.toggle('is-instant', !!instant || reduce);
    navThumb.style.width = on.offsetWidth + 'px';
    navThumb.style.height = on.offsetHeight + 'px';
    navThumb.style.transform = 'translate(' + on.offsetLeft + 'px,' + on.offsetTop + 'px)';
  }
  if (adminNav) {
    navThumb = document.createElement('span');
    navThumb.className = 'admin-nav-thumb';
    navThumb.setAttribute('aria-hidden', 'true');
    adminNav.insertBefore(navThumb, adminNav.firstChild);
    adminNav.classList.add('has-thumb');
    placeThumb(true);
    window.addEventListener('resize', function () { placeThumb(true); });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { placeThumb(true); });
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

  function swap(html, quiet) {
    var doc = new DOMParser().parseFromString(html, 'text/html');
    var next = doc.querySelector('.content');
    if (!next || !doc.querySelector('.admin-nav')) throw new Error('muat biasa');
    document.title = doc.title;

    /* Menu aktif & jumlah booking menunggu */
    $$('.admin-nav a[data-nav]').forEach(function (a) {
      var twin = doc.querySelector('.admin-nav a[data-nav="' + a.getAttribute('data-nav') + '"]');
      if (!twin) return;
      if (twin.hasAttribute('aria-current')) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current');
      var c = $('.nav-count', a), nc = twin.querySelector('.nav-count');
      if (nc && c) c.textContent = nc.textContent;
      else if (nc) a.appendChild(nc.cloneNode(true));
      else if (c) a.removeChild(c);
    });
    placeThumb(false);
    var search = $('#globalSearch'), ns = doc.getElementById('globalSearch');
    if (search && ns) search.value = ns.value;

    // Posisi jempol tab sebelum ditukar, agar gerakannya bersambung di isi yang baru
    var oldThumb = $('.tabs-thumb', content);
    var from = quiet && oldThumb ? { w: getComputedStyle(oldThumb).width, tf: getComputedStyle(oldThumb).transform } : null;

    content.innerHTML = next.innerHTML;
    content.classList.remove('is-swapping');
    clearTimeout(enterTimer);
    content.classList.remove('is-entering');
    if (quiet) {
      // Ganti filter di halaman yang sama: kartu langsung tampil, posisi gulir tetap, baris tabel memudar masuk.
      $$('[data-reveal]', content).forEach(function (el) { el.classList.add('is-in'); });
      $$('.table', content).forEach(function (t) { t.classList.add('is-fresh'); });
    } else {
      if (!reduce) {
        void content.offsetWidth;
        content.classList.add('is-entering');
        enterTimer = setTimeout(function () { content.classList.remove('is-entering'); }, 320);
      }
      window.scrollTo(0, 0);
    }
    initContent(content);
    if (from) $$('.tabs', content).forEach(function (x) { placeTabs(x, false, from); });
    runScripts(doc.getElementById('pageScripts') || doc.createElement('div'));
    showFlash(doc.getElementById('flashData'));
    if (!quiet) {
      var h = $('h1', content);
      if (h) { h.setAttribute('tabindex', '-1'); h.focus({ preventScroll: true }); }
    }
  }

  var enterTimer = null;
  function go(url, push, quiet) {
    var mine = ++ticket;
    if (!quiet) {
      main.classList.add('is-loading');
      content.classList.add('is-swapping');
    } else {
      $$('.table-wrap', content).forEach(function (w) { w.classList.add('is-refreshing'); });
    }
    load(url).then(function (html) {
      if (mine !== ticket) return;
      if (push) history.pushState({ fzm: 1, quiet: !!quiet }, '', url);
      swap(html, quiet);
      main.classList.remove('is-loading');
    }).catch(function () {
      location.href = url;
    });
  }

  if (canSwap) {
    history.replaceState({ fzm: 1 }, '', location.href);
    $$('.admin-nav a[data-nav], .topbar [data-nav-link]').forEach(function (a) {
      a.addEventListener('mouseenter', function () { prefetch(a.href); });
      a.addEventListener('focus', function () { prefetch(a.href); });
      a.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        e.preventDefault();
        if (a.href === location.href) return;
        // Menu aktif & jempol langsung pindah saat diklik (tidak menunggu halaman termuat),
        // sehingga klik beruntun tetap mulus: jempol selalu menuju menu terakhir yang diklik.
        if (a.hasAttribute('data-nav')) {
          $$('.admin-nav a[data-nav]').forEach(function (x) { if (x === a) x.setAttribute('aria-current', 'page'); else x.removeAttribute('aria-current'); });
          placeThumb(false);
        }
        go(a.href, true);
      });
    });
    window.addEventListener('popstate', function (e) {
      if (e.state && e.state.fzm) go(location.href, false, e.state.quiet);
    });

    /* Tab filter status (Semua/Menunggu/Dikonfirmasi/Selesai): isi ditukar diam-diam, tanpa kedip */
    content.addEventListener('click', function (e) {
      var a = e.target.closest('.tabs a[href]');
      if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      e.preventDefault();
      if (a.href === location.href) return;
      $$('.tabs a', content).forEach(function (x) { if (x === a) x.setAttribute('aria-current', 'page'); else x.removeAttribute('aria-current'); });
      placeTabs(a.closest('.tabs'), false);
      go(a.href, true, true);
    });
    content.addEventListener('mouseover', function (e) {
      var a = e.target.closest('.tabs a[href]');
      if (a) prefetch(a.href);
    });
  }
})();
