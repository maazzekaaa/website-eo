/* =========================================================
   FZM Production v2 — site.js (dipakai semua halaman publik)
   - Animasi muncul saat scroll (reveal + judul per baris)
   - Navbar melayang: transparan → lebih pekat saat scroll,
     sembunyi saat scroll ke bawah, muncul saat scroll ke atas
   - Menu ponsel, marquee, parallax hero, LED panggung
   - Filter portofolio (indikator meluncur) + lightbox galeri
   - Sorotan cursor pada CTA, tombol WhatsApp melayang
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
  var lastY = window.scrollY;
  var sheet = $('#navSheet');
  var burger = $('.nav-burger');
  function onScroll() {
    var y = window.scrollY;
    if (nav) {
      nav.classList.toggle('is-scrolled', y > 24);
      var goingDown = y > lastY && y > 240;
      nav.classList.toggle('is-hidden', goingDown && !(sheet && !sheet.hidden));
    }
    lastY = y;
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
  var folders = $$('.folder');
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

  /* ---------- Lightbox galeri ---------- */
  var lb = $('#lightbox');
  if (lb) {
    var el = {
      img: $('#lbImg'), empty: $('#lbEmpty'), counter: $('#lbCounter'), thumbs: $('#lbThumbs'),
      cat: $('#lbCat'), title: $('#lbTitle'), date: $('#lbDate'), client: $('#lbClient'),
      loc: $('#lbLoc'), services: $('#lbServices'), story: $('#lbStory')
    };
    var st = { photos: [], count: 0, i: 0, opener: null };
    var total = function () { return st.photos.length || st.count; };

    var render = function () {
      var has = st.photos.length > 0;
      if (has) { el.img.src = st.photos[st.i]; el.img.alt = el.title.textContent + ', foto ' + (st.i + 1); }
      el.img.hidden = !has;
      el.empty.hidden = has;
      el.counter.textContent = 'Foto ' + (st.i + 1) + ' dari ' + total();
      $$('button', el.thumbs).forEach(function (b, k) { b.setAttribute('aria-current', String(k === st.i)); });
      var cur = $('button[aria-current="true"]', el.thumbs);
      if (cur) cur.scrollIntoView({ block: 'nearest', inline: 'center', behavior: reduce ? 'auto' : 'smooth' });
      // animasi ulang placeholder
      el.empty.style.animation = 'none'; void el.empty.offsetWidth; el.empty.style.animation = '';
    };

    var open = function (f) {
      var d = f.dataset;
      try { st.photos = JSON.parse(d.photos || '[]'); } catch (e) { st.photos = []; }
      st.count = parseInt(d.count, 10) || st.photos.length || 1;
      st.i = 0; st.opener = f;
      var cs = getComputedStyle(f);
      el.cat.textContent = d.category;
      el.cat.style.background = 'linear-gradient(180deg,' + cs.getPropertyValue('--f1') + ',' + cs.getPropertyValue('--f2') + ')';
      el.cat.style.color = cs.getPropertyValue('--label');
      el.title.textContent = d.title;
      el.date.textContent = d.date || '-';
      el.client.textContent = d.client || '-';
      el.loc.textContent = d.location || '-';
      el.services.textContent = d.services || '-';
      el.story.textContent = d.story || '';
      var html = '';
      for (var k = 0; k < Math.min(total(), 40); k++) {
        html += '<button type="button" data-i="' + k + '" aria-label="Foto ' + (k + 1) + '">' +
          (st.photos[k] ? '<img src="' + st.photos[k] + '" alt="" loading="lazy">' : (k + 1)) + '</button>';
      }
      el.thumbs.innerHTML = html;
      render();
      lb.hidden = false;
      requestAnimationFrame(function () { lb.classList.add('is-open'); });
      document.body.classList.add('no-scroll');
      $('#lbClose').focus({ preventScroll: true });
    };
    var close = function () {
      lb.classList.remove('is-open');
      document.body.classList.remove('no-scroll');
      setTimeout(function () { lb.hidden = true; }, reduce ? 0 : 450);
      if (st.opener) st.opener.focus({ preventScroll: true });
    };
    var go = function (n) { st.i = (st.i + n + total()) % total(); render(); };

    folders.forEach(function (f) { f.addEventListener('click', function () { open(f); }); });
    $('#lbClose').addEventListener('click', close);
    $('#lbPrev').addEventListener('click', function () { go(-1); });
    $('#lbNext').addEventListener('click', function () { go(1); });
    el.thumbs.addEventListener('click', function (e) {
      var b = e.target.closest('button'); if (!b) return;
      st.i = +b.getAttribute('data-i'); render();
    });
    lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
    document.addEventListener('keydown', function (e) {
      if (lb.hidden) return;
      if (e.key === 'Escape') close();
      if (e.key === 'ArrowLeft') go(-1);
      if (e.key === 'ArrowRight') go(1);
      if (e.key === 'Tab') {                       // jaga fokus tetap di dalam dialog
        var f = $$('button, a[href]', lb).filter(function (x) { return x.offsetParent !== null; });
        if (!f.length) return;
        if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
        else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
      }
    });
    var sx = null, stage = $('.lb-stage', lb);
    stage.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    stage.addEventListener('touchend', function (e) {
      if (sx === null) return;
      var dx = e.changedTouches[0].clientX - sx;
      if (Math.abs(dx) > 40) go(dx < 0 ? 1 : -1);
      sx = null;
    });
  }

  /* ---------- Sorotan cursor pada CTA ---------- */
  $$('.cta').forEach(function (c) {
    c.addEventListener('pointermove', function (e) {
      var r = c.getBoundingClientRect();
      c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      c.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  });

  /* ---------- WhatsApp melayang ---------- */
  $$('[data-wa]').forEach(function (a) {
    a.href = 'https://wa.me/' + a.getAttribute('data-wa') + '?text=' + encodeURIComponent(a.getAttribute('data-text') || 'Halo FZM');
    a.target = '_blank'; a.rel = 'noopener';
  });

  $$('[data-year]').forEach(function (y) { y.textContent = new Date().getFullYear(); });
  onScroll();
})();
