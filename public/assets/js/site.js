/* =========================================================
   FZM Production — site.js (dipakai semua halaman publik)
   - Pudar singkat saat scroll (data-reveal)
   - Menu ponsel dari navbar kapsul
   - Tautan WhatsApp terisi otomatis, tahun di footer
   ========================================================= */
(function () {
  'use strict';

  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  /* ---------- Muncul saat scroll: lihat "Muncul mengikuti scroll" di bawah ----------
     Anak dalam satu kelompok diberi urutan lewat --d. */
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Judul [data-words]: tiap kata dibungkus .w > span agar bisa naik dari balik garis.
     Elemen di dalam judul (mis. kata aksen merah .serif) diperlakukan sebagai satu kata. */
  $$('[data-words]').forEach(function (h) {
    var n = 0;
    var wrap = function (node) {
      var w = document.createElement('span'); w.className = 'w';
      var inner = document.createElement('span'); inner.style.setProperty('--i', n++);
      w.appendChild(inner);
      return { w: w, inner: inner };
    };
    Array.prototype.slice.call(h.childNodes).forEach(function (node) {
      if (node.nodeType === 3) {
        var frag = document.createDocumentFragment();
        node.textContent.split(/(\s+)/).forEach(function (part) {
          if (!part) return;
          if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
          var x = wrap(); x.inner.textContent = part; frag.appendChild(x.w);
        });
        h.replaceChild(frag, node);
      } else if (node.nodeType === 1 && node.tagName !== 'BR') {
        var x = wrap(); h.replaceChild(x.w, node); x.inner.appendChild(node);
      }
    });
    // Urutan kata dinormalkan ke 0…1 (--t) agar kata terakhir tetap tampil penuh saat --p = 1
    $$('.w > span', h).forEach(function (s, k, all) { s.style.setProperty('--t', all.length > 1 ? (k / (all.length - 1)).toFixed(3) : 0); });
  });

  function mark(el, d) {
    if (el.hasAttribute('data-reveal') || el.hasAttribute('data-words')) return;
    el.setAttribute('data-reveal', '');
    if (d) el.style.setProperty('--d', d);
  }
  $$('.page-head, .sec-head, .footer-top, .contact, .order-steps, .detail, .search-list').forEach(function (group) {
    Array.prototype.forEach.call(group.children, function (child, k) { mark(child, Math.min(k, 6)); });
  });
  $$('.cta, .logo-strip, .footer-mark, .footer-bottom, .summary, .search-form, .search-group h2, .success').forEach(function (el) { mark(el, 0); });

  /* ---------- Muncul mengikuti scroll (tanpa jeda waktu) ----------
     Bukan animasi berdurasi: tiap elemen diberi --p (0 → 1) sesuai posisinya di layar, dihitung
     setiap frame saat halaman digulir. CSS memetakan --p ke pudar, naik, dan blur. Karena terikat
     langsung ke posisi scroll, efeknya selalu mengikuti gulir, cepat maupun lambat, tanpa
     tertinggal, dan berbalik halus saat digulir ke atas. */
  var revealEls = $$('[data-reveal], [data-words], [data-clip]');
  var revealQueued = false;
  function revealUpdate() {
    revealQueued = false;
    var vh = window.innerHeight, span = vh * 0.32;          // jarak gulir dari mulai muncul → tampil penuh
    for (var k = 0; k < revealEls.length; k++) {
      var el = revealEls[k];
      var top = el.getBoundingClientRect().top;
      var d = parseFloat(el.style.getPropertyValue('--d')) || 0;
      var start = vh - d * vh * 0.035;                      // anak berikutnya mulai sedikit lebih akhir
      var p = Math.round(Math.min(1, Math.max(0, (start - top) / span)) * 1000) / 1000;
      if (el._p !== p) {
        el._p = p;
        el.style.setProperty('--p', p);
        el.classList.toggle('is-in', p === 1);               // tampil penuh: filter dilepas (lebih ringan)
      }
    }
  }
  function revealQueue() { if (!revealQueued) { revealQueued = true; requestAnimationFrame(revealUpdate); } }
  if (reduceMotion) {
    revealEls.forEach(function (el) { el.classList.add('is-in'); });
  } else {
    document.documentElement.classList.add('scroll-reveal');
    revealUpdate();
    window.addEventListener('scroll', revealQueue, { passive: true });
    window.addEventListener('resize', revealQueue);
    window.addEventListener('load', revealQueue);
  }

  /* ---------- Paralaks foto: bergeser pelan berlawanan arah scroll ---------- */
  var para = $$('[data-parallax]');
  if (para.length && !reduceMotion) {
    var ticking = false;
    var update = function () {
      ticking = false;
      var vh = window.innerHeight;
      para.forEach(function (img) {
        var r = img.parentNode.getBoundingClientRect();
        if (r.bottom < -100 || r.top > vh + 100) return;
        var p = Math.min(1, Math.max(0, (vh - r.top) / (vh + r.height)));   // 0 = baru masuk bawah, 1 = keluar atas
        img.style.setProperty('--py', (-p * 9).toFixed(2) + '%');
      });
    };
    var queue = function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } };
    window.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', queue);
    update();
  }

  window.FZM_reveal = function (el) {
    if (reduceMotion) { el.classList.add('is-in'); return; }
    if (revealEls.indexOf(el) < 0) revealEls.push(el);
    revealQueue();
  };

  /* ---------- Menu ponsel ---------- */
  var sheet = $('#navSheet');
  var burger = $('.nav-burger');
  if (burger && sheet) {
    var setOpen = function (open) {
      sheet.hidden = !open;
      burger.setAttribute('aria-expanded', String(open));
      burger.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    };
    burger.addEventListener('click', function () { setOpen(sheet.hidden); });
    $$('a', sheet).forEach(function (a) { a.addEventListener('click', function () { setOpen(false); }); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !sheet.hidden) { setOpen(false); burger.focus(); }
    });
  }

  /* ---------- WhatsApp ---------- */
  $$('[data-wa]').forEach(function (a) {
    a.href = 'https://wa.me/' + a.getAttribute('data-wa') + '?text=' + encodeURIComponent(a.getAttribute('data-text') || 'Halo FZM');
    a.target = '_blank'; a.rel = 'noopener';
  });

  $$('[data-year]').forEach(function (y) { y.textContent = new Date().getFullYear(); });

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var root = document.documentElement;

  /* ---------- Judul beranda diketik huruf per huruf ---------- */
  var title = $('.hero-type');
  var hero = title && title.closest('.hero');
  if (hero) {
    var chars = $$('.ch', title);
    if (reduce || !chars.length) {
      hero.classList.add('is-typed');
    } else {
      hero.classList.add('is-typing');
      var caret = document.createElement('span');
      caret.className = 'caret';
      caret.setAttribute('aria-hidden', 'true');
      var i = 0;
      var tick = function () {
        if (i >= chars.length) {
          hero.classList.add('is-typed');
          caret.hidden = true;                                     // kursor langsung hilang begitu selesai
          return;
        }
        var c = chars[i++], next = chars[i];
        c.classList.add('is-on');
        c.parentNode.insertBefore(caret, c.nextSibling);
        var wait = 62 + Math.random() * 16;                       // irama rata, sedikit variasi agar tidak kaku
        if (next && next.parentNode !== c.parentNode) wait += 90;  // jeda antarkata
        if (next && next.closest('.hero-row') !== c.closest('.hero-row')) wait += 180; // pindah baris
        setTimeout(tick, wait);
      };
      setTimeout(tick, 380);
    }
  }

  /* ---------- Sorotan meluncur di navbar ---------- */
  var navLinks = $('.nav-links');
  if (navLinks && window.matchMedia('(hover: hover)').matches) {
    var glow = document.createElement('span');
    glow.className = 'nav-hover';
    glow.setAttribute('aria-hidden', 'true');
    navLinks.insertBefore(glow, navLinks.firstChild);
    navLinks.addEventListener('mouseover', function (e) {
      var a = e.target.closest('a');
      if (!a) return;
      var first = !navLinks.classList.contains('is-hovering');
      glow.classList.toggle('is-instant', first);                // masuk pertama: langsung di tempat
      glow.style.width = a.offsetWidth + 'px';
      glow.style.transform = 'translateX(' + a.offsetLeft + 'px)';
      navLinks.classList.add('is-hovering');
    });
    navLinks.addEventListener('mouseleave', function () { navLinks.classList.toggle('is-hovering', false); });
  }

  /* ---------- Pil hitam menu aktif: meluncur ke menu tujuan sebelum pindah halaman ---------- */
  var nav = $('.nav');
  var pill = null;
  function placePill(link, instant) {
    if (!pill) return;
    pill.classList.toggle('is-instant', !!instant || reduce);
    pill.classList.toggle('is-off', !link);
    if (!link) return;
    pill.style.width = link.offsetWidth + 'px';
    pill.style.transform = 'translateX(' + link.offsetLeft + 'px)';
  }
  if (navLinks) {
    pill = document.createElement('span');
    pill.className = 'nav-active';
    pill.setAttribute('aria-hidden', 'true');
    navLinks.insertBefore(pill, navLinks.firstChild);
    navLinks.classList.add('has-pill');
    var current = function () { return $('a[aria-current="page"]', navLinks); };
    placePill(current(), true);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { placePill(current(), true); });
    window.addEventListener('resize', function () { placePill(current(), true); });
    // Kembali lewat tombol Back (bfcache): kembalikan menu aktif semula
    var initial = $$('a[aria-current]', nav);
    window.addEventListener('pageshow', function (e) {
      if (!e.persisted) return;
      $$('a[aria-current]', nav).forEach(function (x) { x.removeAttribute('aria-current'); });
      initial.forEach(function (x) { x.setAttribute('aria-current', 'page'); });
      placePill(current(), true);
    });
  }

  /* ---------- Web satu halaman: pil navbar mengikuti bagian yang sedang terlihat ----------
     Menu ber-data-section menunjuk ke #id di beranda. Saat menu diklik, pil langsung pindah ke
     tujuan dan tidak ikut "mampir" ke bagian yang dilewati selama gulir halus berlangsung. */
  var spyLinks = navLinks ? $$('a[data-section]', navLinks) : [];
  var spySections = spyLinks.map(function (a) { return document.getElementById(a.getAttribute('data-section')); });
  if (spyLinks.length && spySections.slice(1).some(Boolean)) {
    var alias = { klien: 'portofolio' };                       // strip klien termasuk bagian Portofolio
    var extra = $$('#klien');
    var spyHold = false, spyTimer = null, spyFirst = true;
    var setActive = function (id) {
      var link = spyLinks.filter(function (a) { return a.getAttribute('data-section') === id; })[0] || spyLinks[0];
      if (link.getAttribute('aria-current') === 'page' && !spyFirst) return;
      spyLinks.forEach(function (a) { if (a !== link) a.removeAttribute('aria-current'); });
      link.setAttribute('aria-current', 'page');
      placePill(link, spyFirst);
      spyFirst = false;
    };
    var spy = function () {
      if (spyHold) return;
      // Bagian yang sudah melewati 40% tinggi layar dan paling dekat dengannya = bagian aktif
      var line = window.innerHeight * 0.4, id = 'beranda', best = -Infinity;
      spySections.concat(extra).forEach(function (sec) {
        if (!sec || !sec.id) return;
        var top = sec.getBoundingClientRect().top;
        if (top <= line && top > best) { best = top; id = alias[sec.id] || sec.id; }
      });
      if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) id = 'kontak';  // dasar halaman
      setActive(id);
    };
    var spyQueued = false;
    window.addEventListener('scroll', function () {
      if (spyHold) { clearTimeout(spyTimer); spyTimer = setTimeout(release, 140); return; }   // tunggu gulir berhenti
      if (!spyQueued) { spyQueued = true; requestAnimationFrame(function () { spyQueued = false; spy(); }); }
    }, { passive: true });
    var release = function () { spyHold = false; spy(); };
    spyLinks.forEach(function (a) {
      a.addEventListener('click', function () {
        setActive(a.getAttribute('data-section'));
        spyHold = true;
        clearTimeout(spyTimer); spyTimer = setTimeout(release, 900);
      });
    });
    spy();
    // Dibuka langsung ke /#bagian: peramban baru menggulir setelah halaman termuat
    window.addEventListener('load', function () { spyFirst = true; spy(); });
    window.addEventListener('hashchange', function () { if (!spyHold) spy(); });
  }

  /* ---------- Navbar menjadi lebih padat & buram saat halaman digulir ---------- */
  if (nav) {
    var onScroll = function () { nav.classList.toggle('is-scrolled', window.scrollY > 24); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------- Perpindahan halaman ----------
     Peramban dengan View Transitions: pudar silang lembut (CSS), dan pil navbar dipindah
     oleh peramban sendiri dari posisi lama ke posisi baru (view-transition-name: nav-pill).
     Peramban lama: pil digeser di sini, halaman memudar, lalu pindah. */
  var noVt = root.classList.contains('no-vt');
  var leaving = false, leaveTimer = null;
  window.addEventListener('pageshow', function () { leaving = false; });
  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href]');
    if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var href = a.getAttribute('href');
    if (!href || href.charAt(0) === '#' || (a.target && a.target !== '_self') || a.hasAttribute('download') || a.hasAttribute('data-wa')) return;
    var url = new URL(a.href, location.href);
    if (url.origin !== location.origin) return;
    if (url.pathname === location.pathname && url.search === location.search) {
      // Tautan ke halaman ini sendiri tanpa #bagian (logo, Beranda): gulir ke atas, tanpa muat ulang
      if (!url.hash) {
        e.preventDefault();
        history.replaceState(null, '', url.pathname);
        window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
      }
      return;
    }
    // Klik beruntun saat halaman sedang berpindah diabaikan: transisi yang terpotong di tengah
    // jalan membuat pil navbar melompat. Kunci dilepas otomatis bila halaman tak kunjung pindah.
    if (leaving) { e.preventDefault(); return; }
    leaving = true;
    clearTimeout(leaveTimer);
    leaveTimer = setTimeout(function () { leaving = false; }, 1500);
    if (navLinks) navLinks.classList.toggle('is-hovering', false);   // sorotan abu tidak ikut terekam transisi
    if (reduce || !noVt) return;                      // View Transitions bawaan yang bekerja
    e.preventDefault();
    if (pill && nav.contains(a)) {
      $$('a[aria-current]', nav).forEach(function (x) { x.removeAttribute('aria-current'); });
      a.setAttribute('aria-current', 'page');
      placePill(navLinks.contains(a) ? a : null, false);
    }
    root.classList.add('is-leaving');
    setTimeout(function () { location.href = a.href; }, 220);
  });
  window.addEventListener('pageshow', function (e) { if (e.persisted) root.classList.toggle('is-leaving', false); });
})();
