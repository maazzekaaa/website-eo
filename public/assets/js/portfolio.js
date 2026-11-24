/* =========================================================
   FZM Production — portfolio.js (bagian Portofolio di beranda)
   Grid portofolio di beranda: klik foto untuk membuka galeri detail
   (klien, tanggal, lokasi, layanan, navigasi foto).
   Galeri membesar dari foto yang diklik di desktop, dan
   menjadi lembar bawah yang bisa ditarik di ponsel.
   ========================================================= */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var dataEl = $('#portfolioData');
  var grid = $('#pfGrid');
  if (!dataEl || !grid) return;
  var prints = [];
  try { prints = JSON.parse(dataEl.textContent) || []; } catch (e) { prints = []; }
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Detail proyek ---------- */
  var dlg = $('#pfDetail');
  if (!dlg) return;
  var img = $('#pdImg'), empty = $('#pdEmpty'), prev = $('#pdPrev'), next = $('#pdNext'), count = $('#pdCount');
  var st = { p: null, k: 0, opener: null };

  /* Pindah foto: salinan foto lama ("ghost") ditumpuk di atas, foto baru dipasang utuh di bawahnya,
     lalu ghost memudar (pudar silang murni, tanpa geser, jadi tidak ada tepi yang bocor). */
  var swapTimer = null;
  function swapTo(src, dir) {
    var frame = img.parentNode;
    $$('.pd-ghost', frame).forEach(function (g) { frame.removeChild(g); });
    var ghost = document.createElement('img');
    ghost.className = 'pd-ghost'; ghost.alt = ''; ghost.setAttribute('aria-hidden', 'true');
    ghost.src = img.currentSrc || img.src;
    frame.insertBefore(ghost, img.nextSibling);
    frame.style.setProperty('--dir', dir);
    frame.classList.toggle('is-swap', false);
    img.src = src;
    var start = function () {
      void frame.offsetWidth;                         // mulai ulang animasi bila diklik beruntun
      frame.classList.add('is-swap');
      clearTimeout(swapTimer);
      swapTimer = setTimeout(function () {
        frame.classList.toggle('is-swap', false);
        if (ghost.parentNode) frame.removeChild(ghost);
      }, 760);
    };
    // Tunggu foto baru siap ditampilkan agar tidak ada kedip kosong
    if (img.decode) img.decode().then(start, start); else start();
  }

  function photo(dir) {
    var photos = st.p.photos || [];
    var has = photos.length > 0;
    img.hidden = !has; empty.hidden = has;
    if (has && dir && !reduce && img.getAttribute('src')) {
      swapTo(photos[st.k], dir);
      img.alt = st.p.title + ', foto ' + (st.k + 1);
    } else if (has) {
      img.classList.add('is-loading');               // foto berganti dengan pudar silang
      img.onload = function () { img.classList.toggle('is-loading', false); };
      img.src = photos[st.k]; img.alt = st.p.title + ', foto ' + (st.k + 1);
      if (img.complete) img.classList.toggle('is-loading', false);
    }
    prev.hidden = next.hidden = photos.length < 2;
    count.textContent = photos.length > 1 ? (st.k + 1) + ' / ' + photos.length : '';
  }
  function set(id, v) { var el = $(id); el.textContent = v || '—'; }

  function open(i, opener) {
    var p = prints[i];
    if (!p) return;
    st.p = p; st.k = Math.min(p.index || 0, Math.max((p.photos || []).length - 1, 0)); st.opener = opener;
    set('#pdCat', p.category); set('#pdTitle', p.title); set('#pdClient', p.client);
    set('#pdDate', p.date); set('#pdLoc', p.location); set('#pdSvc', (p.services || '').replace(/,\s*/g, ' · '));
    $('#pdLink').href = p.link;
    photo();
    clearTimeout(st.t);
    /* Kartu membesar dari posisi foto yang diklik (titik tengahnya relatif ke tengah layar) */
    var card = $('.pd-card', dlg);
    card.style.setProperty('--drag', '0px');
    if (opener && opener.getBoundingClientRect) {
      var r = opener.getBoundingClientRect();
      card.style.setProperty('--dx', Math.round(r.left + r.width / 2 - innerWidth / 2) + 'px');
      card.style.setProperty('--dy', Math.round(r.top + r.height / 2 - innerHeight / 2) + 'px');
    }
    dlg.hidden = false;
    void dlg.offsetWidth;                            // mulai dari keadaan tertutup agar transisi berjalan
    dlg.classList.add('is-open');
    document.body.classList.add('no-scroll');
    $('#pdClose').focus({ preventScroll: true });
  }
  function close() {
    if (dlg.hidden || !dlg.classList.contains('is-open')) return;
    dlg.classList.toggle('is-open', false);
    st.t = setTimeout(function () { dlg.hidden = true; }, reduce ? 0 : 320);
    document.body.classList.toggle('no-scroll', false);
    if (st.opener && document.contains(st.opener)) st.opener.focus({ preventScroll: true });
  }
  function step(n) {
    var len = (st.p.photos || []).length;
    if (len < 2) return;
    st.k = (st.k + n + len) % len;
    photo(n > 0 ? 1 : -1);
  }

  grid.addEventListener('click', function (e) {
    var t = e.target.closest('[data-i]');
    if (t) open(+t.getAttribute('data-i'), t);
  });
  $('#pdClose').addEventListener('click', close);
  prev.addEventListener('click', function () { step(-1); });
  next.addEventListener('click', function () { step(1); });
  dlg.addEventListener('click', function (e) { if (e.target === dlg) close(); });
  document.addEventListener('keydown', function (e) {
    if (dlg.hidden || !dlg.classList.contains('is-open')) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') step(-1);
    if (e.key === 'ArrowRight') step(1);
    if (e.key === 'Tab') {                         // fokus tetap di dalam jendela
      var f = $$('button, a[href]', dlg).filter(function (x) { return !x.hidden; });
      if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
      else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
    }
  });

  /* Ponsel: tarik lembar ke bawah untuk menutup (seperti iOS) */
  var sheet = $('.pd-card', dlg), dy0 = null, dy = 0;
  var mobile = window.matchMedia('(max-width: 640px)');
  sheet.addEventListener('touchstart', function (e) {
    if (!mobile.matches || sheet.scrollTop > 0 || e.target.closest('.pd-photo')) return;
    dy0 = e.touches[0].clientY; dy = 0;
    sheet.classList.add('is-dragging');
  }, { passive: true });
  sheet.addEventListener('touchmove', function (e) {
    if (dy0 === null) return;
    dy = Math.max(0, e.touches[0].clientY - dy0);
    sheet.style.setProperty('--drag', dy + 'px');
  }, { passive: true });
  sheet.addEventListener('touchend', function () {
    if (dy0 === null) return;
    dy0 = null;
    sheet.classList.toggle('is-dragging', false);
    if (dy > 110) close(); else sheet.style.setProperty('--drag', '0px');
  });

  /* Geser foto ke kiri/kanan */
  var sx = null, frame = $('.pd-photo', dlg);
  frame.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
  frame.addEventListener('touchend', function (e) {
    if (sx === null) return;
    var dx = e.changedTouches[0].clientX - sx; sx = null;
    if (Math.abs(dx) > 40) step(dx < 0 ? 1 : -1);
  });
})();
