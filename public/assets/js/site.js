/* =========================================================
   FZM Production v2 — site.js (dipakai semua halaman publik)
   - Animasi muncul saat scroll (reveal + judul per baris)
   - Navbar melayang: transparan → lebih pekat saat scroll,
     selalu tampil saat di-scroll
   - Menu ponsel, marquee, parallax hero, LED panggung
   - Filter portofolio + tampilan foto besar
   - Tombol WhatsApp melayang
   ========================================================= */
(function () {
  'use strict';

  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Reveal saat scroll ---------- */
  var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 }) : null;
  $$('[data-reveal], .split').forEach(function (el) {
    if (io) io.observe(el); else el.classList.add('is-in');
  });
  window.FZM_reveal = function (el) { if (io) io.observe(el); else el.classList.add('is-in'); };

  /* ---------- Navbar ---------- */
  var nav = $('.nav');
  var sheet = $('#navSheet');
  var burger = $('.nav-burger');
  function onScroll() {
    var y = window.scrollY;
    if (nav) {
      nav.classList.toggle('is-scrolled', y > 24);
    }
    parallax(y);
  }
  window.addEventListener('scroll', onScroll, { passive: true });

  if (burger && sheet) {
    burger.addEventListener('click', function () {
      var open = sheet.hidden;
      sheet.hidden = !open;
      burger.setAttribute('aria-expanded', String(open));
      burger.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    });
    $$('a', sheet).forEach(function (a) {
      a.addEventListener('click', function () { sheet.hidden = true; burger.setAttribute('aria-expanded', 'false'); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !sheet.hidden) { sheet.hidden = true; burger.setAttribute('aria-expanded', 'false'); burger.focus(); }
    });
  }

  /* ---------- Hero: LED + parallax ---------- */
  var led = $('#led');
  if (led) {
    var cells = '';
    for (var i = 0; i < 64; i++) cells += '<i></i>';
    led.innerHTML = cells;
    var dots = $$('i', led);
    var frame = 0;
    var paint = function () {
      dots.forEach(function (d, k) {
        var r = Math.floor(k / 16), c = k % 16;
        var on = ((c + r + frame) % 7 === 0) || (r === 1 && Math.abs(c - 8) < 3 + (frame % 3)) || (r === 2 && Math.abs(c - 8) < 4 + (frame % 2));
        d.className = on ? 'on' : ((k + frame) % 5 === 0 ? 'dim' : '');
      });
      frame++;
    };
    paint();
    if (!reduce) setInterval(paint, 1400);
  }
  var heroInner = $('.hero-media-inner');
  function parallax(y) {
    if (!heroInner || reduce) return;
    var t = Math.min(y, 900);
    heroInner.style.transform = 'translateY(' + (t * 0.08) + 'px) scale(' + (1 + t * 0.00012) + ')';
  }

  /* ---------- Marquee: gandakan isi agar mulus ---------- */
  $$('.marquee-track').forEach(function (t) { t.innerHTML += t.innerHTML; });

  /* ---------- Filter portofolio ---------- */
  var filters = $('.filters');
  var folders = $$('.folder, .table-print');
  if (filters) {
    var thumb = $('.filters-thumb', filters);
    var btns = $$('.filter-btn', filters);
    var moveThumb = function (btn) {
      thumb.style.width = btn.offsetWidth + 'px';
      thumb.style.transform = 'translateX(' + btn.offsetLeft + 'px)';
    };
    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var cat = btn.getAttribute('data-filter');
        btns.forEach(function (b) { b.setAttribute('aria-pressed', String(b === btn)); });
        moveThumb(btn);
        var n = 0;
        folders.forEach(function (f) {
          var show = cat === 'Semua' || f.getAttribute('data-category') === cat;
          f.classList.toggle('is-out', !show);
          f.classList.remove('is-entering');
          if (show) { void f.offsetWidth; f.style.animationDelay = (n++ * 60) + 'ms'; f.classList.add('is-entering'); }
        });
      });
    });
    var active = $('.filter-btn[aria-pressed="true"]', filters) || btns[0];
    requestAnimationFrame(function () { moveThumb(active); });
    window.addEventListener('resize', function () { moveThumb($('.filter-btn[aria-pressed="true"]', filters)); });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { moveThumb($('.filter-btn[aria-pressed="true"]', filters)); });
  }

  /* ---------- Tampilan foto besar ---------- */
  var lb = $('#lightbox');
  if (lb) {
    var plate = $('#lbPlate'), lbImg = $('#lbImg'), lbEmpty = $('#lbEmpty'), lbClose = $('#lbClose');
    var lbPrev = $('#lbPrev'), lbNext = $('#lbNext');
    var CLOSE_MS = 560;
    var st = { photos: [], i: 0, opener: null, token: 0 };

    var decoded = function () {
      if (lbImg.hidden) return Promise.resolve();
      var wait = lbImg.decode ? lbImg.decode().catch(function () {}) : Promise.resolve();
      return Promise.race([wait, new Promise(function (r) { setTimeout(r, 700); })]);
    };
    var showPhoto = function () {
      var has = st.photos.length > 0;
      lbImg.hidden = !has;
      lbEmpty.hidden = has;
      if (has) {
        lbImg.src = st.photos[st.i];
        lbImg.alt = $('#lbTitle').textContent + ', foto ' + (st.i + 1);
      }
      lbPrev.hidden = lbNext.hidden = st.photos.length < 2;
    };
    /* Foto seolah terangkat dari tempatnya di halaman menuju tengah layar. */
    var fromOpener = function () {
      var o = st.opener && st.opener.getBoundingClientRect();
      var p = plate.getBoundingClientRect();
      if (!o || !o.width || !p.width) return 'scale(.96)';
      return 'translate(' + ((o.left + o.width / 2) - (p.left + p.width / 2)) + 'px,' + ((o.top + o.height / 2) - (p.top + p.height / 2)) + 'px) scale(' + (o.width / p.width) + ')';
    };
    var openPlate = function (f) {
      var d = f.dataset, token = ++st.token;
      try { st.photos = JSON.parse(d.photos || '[]'); } catch (e) { st.photos = []; }
      st.i = Math.min(parseInt(d.index, 10) || 0, Math.max(st.photos.length - 1, 0));
      st.opener = f;
      $('#lbTitle').textContent = d.title || '';
      $('#lbMeta').textContent = [d.no, d.client, d.category, d.location, d.date].filter(Boolean).join(' · ');
      lb.hidden = false;
      lb.classList.remove('is-open');
      document.body.classList.add('no-scroll');
      plate.style.visibility = 'hidden';
      showPhoto();
      decoded().then(function () {
        if (token !== st.token) return;
        plate.style.visibility = '';
        if (!reduce) {
          plate.style.transition = 'none';
          plate.style.transform = fromOpener();
          void plate.offsetWidth;
          plate.style.transition = '';
        }
        requestAnimationFrame(function () {
          lb.classList.add('is-open');
          plate.style.transform = '';
        });
        lbClose.focus({ preventScroll: true });
      });
    };
    var closePlate = function () {
      if (lb.hidden) return;
      var token = ++st.token;
      lb.classList.remove('is-open');
      if (!reduce) plate.style.transform = fromOpener();
      document.body.classList.remove('no-scroll');
      if (st.opener) st.opener.focus({ preventScroll: true });
      setTimeout(function () {
        if (token !== st.token) return;
        lb.hidden = true;
        plate.style.transform = '';
      }, reduce ? 0 : CLOSE_MS);
    };
    var go = function (n) {
      if (st.photos.length < 2) return;
      st.i = (st.i + n + st.photos.length) % st.photos.length;
      plate.classList.add('is-swapping');
      setTimeout(function () {
        showPhoto();
        decoded().then(function () { plate.classList.remove('is-swapping'); });
      }, reduce ? 0 : 220);
    };

    $$('.folder, .table-print, .deck-card').forEach(function (f) {
      if (f.classList.contains('is-placeholder')) return;
      f.addEventListener('click', function (e) {
        if (e.metaKey || e.ctrlKey || e.shiftKey) return;   // buka di tab baru tetap bisa
        e.preventDefault();
        openPlate(f);
      });
    });
    lbClose.addEventListener('click', closePlate);
    lbPrev.addEventListener('click', function () { go(-1); });
    lbNext.addEventListener('click', function () { go(1); });
    lb.addEventListener('click', function (e) {
      if (!e.target.closest('.lb-frame, .lb-caption, .lb-nav, .lb-close')) closePlate();
    });
    document.addEventListener('keydown', function (e) {
      if (lb.hidden) return;
      if (e.key === 'Escape') closePlate();
      if (e.key === 'ArrowLeft') go(-1);
      if (e.key === 'ArrowRight') go(1);
      if (e.key === 'Tab') {                       // jaga fokus tetap di dalam dialog
        var f = $$('button', lb).filter(function (x) { return !x.hidden; });
        if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
        else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
      }
    });
    var sx = null;
    plate.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    plate.addEventListener('touchend', function (e) {
      if (sx === null) return;
      var dx = e.changedTouches[0].clientX - sx;
      if (Math.abs(dx) > 40) go(dx < 0 ? 1 : -1);
      sx = null;
    });
  }

  /* ---------- WhatsApp melayang ---------- */
  $$('[data-wa]').forEach(function (a) {
    a.href = 'https://wa.me/' + a.getAttribute('data-wa') + '?text=' + encodeURIComponent(a.getAttribute('data-text') || 'Halo FZM');
    a.target = '_blank'; a.rel = 'noopener';
  });

  $$('[data-year]').forEach(function (y) { y.textContent = new Date().getFullYear(); });
  onScroll();
})();
