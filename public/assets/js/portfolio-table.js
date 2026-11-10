/* =========================================================
   FZM Production — portfolio-table.js (halaman portofolio)
   Foto tersebar acak di atas meja tanpa saling menimpa, dengan
   ukuran, bentuk, dan kemiringan berbeda. Meja bisa digeser
   (seret, roda tetikus, atau papan ketik) dengan gerak halus.
   Filter dan tampilan foto ditangani site.js. Di ponsel memakai
   kolom CSS biasa.
   ========================================================= */
(function () {
  'use strict';
  var page = document.getElementById('tablePage');
  var surface = document.getElementById('tableSurface');
  var copy = document.getElementById('tableCopy');
  if (!page || !surface) return;

  var prints = Array.prototype.slice.call(surface.querySelectorAll('.table-print'));
  var phone = window.matchMedia('(max-width: 820px)');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var target = { x: 0, y: 0 }, pan = { x: 0, y: 0 }, bounds = { w: 0, h: 0 };
  var EDGE = 24, TOP = 80, NAV_ROOM = 96, GAP = 18, ATTEMPTS = 90, AREA_PER_PRINT = 150000, MAX_ROTATE = 10, SMOOTH = 0.13;
  var RATIOS = ['4 / 3', '3 / 4', '1 / 1', '3 / 2', '4 / 5', '5 / 4'];
  var RATIO_VALUES = [4 / 3, 3 / 4, 1, 3 / 2, 4 / 5, 5 / 4];

  /* Acak yang tetap: susunan tidak berubah setiap halaman dimuat. */
  function rand(seed) {
    var v = Math.sin(seed * 127.1 + 311.7) * 43758.5453;
    return v - Math.floor(v);
  }

  /* ---------- Gerak halus: posisi meja mengejar posisi tujuan ---------- */
  var ticking = false;
  function draw() {
    surface.style.transform = phone.matches ? '' : 'translate3d(' + pan.x.toFixed(2) + 'px,' + pan.y.toFixed(2) + 'px,0)';
  }
  function tick() {
    pan.x += (target.x - pan.x) * SMOOTH;
    pan.y += (target.y - pan.y) * SMOOTH;
    if (Math.abs(target.x - pan.x) + Math.abs(target.y - pan.y) < 0.2) {
      pan.x = target.x; pan.y = target.y;
      ticking = false;
    } else {
      requestAnimationFrame(tick);
    }
    draw();
  }
  function moveTo() {
    if (reduce) { pan.x = target.x; pan.y = target.y; draw(); return; }
    if (!ticking) { ticking = true; requestAnimationFrame(tick); }
  }
  function clampTarget() {
    target.x = Math.min(0, Math.max(target.x, Math.min(0, page.clientWidth - bounds.w)));
    target.y = Math.min(0, Math.max(target.y, Math.min(0, page.clientHeight - bounds.h)));
  }

  /* Bentuk dan kemiringan tiap foto tetap sama di semua ukuran layar. */
  function shape() {
    prints.forEach(function (print, i) {
      var seed = i + 1, k = Math.floor(rand(seed + 7) * RATIOS.length);
      var turn = (rand(seed + 120) - 0.5) * (phone.matches ? 3 : MAX_ROTATE);
      print.style.setProperty('--ar', RATIOS[k]);
      print.style.setProperty('--r', turn.toFixed(2) + 'deg');
      print.style.setProperty('--my', Math.round(rand(seed + 200) * 20) + 'px');
      print.dataset.ratio = RATIO_VALUES[k];
      print.dataset.turn = turn;
    });
  }

  /* Tiap foto dicoba di banyak titik acak. Titik yang menimpa foto lain atau judul ditolak,
     lalu dipilih titik yang paling lapang. Bila tidak ada tempat, meja diperluas. */
  function layout() {
    shape();
    var visible = prints.filter(function (p) { return !p.classList.contains('is-out'); });

    if (phone.matches) {
      prints.forEach(function (p) { p.style.width = ''; });
      target.x = target.y = pan.x = pan.y = 0;
      draw();
      return;
    }

    var vw = page.clientWidth, vh = page.clientHeight;
    var pageBox = page.getBoundingClientRect(), box = copy.getBoundingClientRect();
    var keepOut = { left: box.left - pageBox.left - 28, right: box.right - pageBox.left + 32, top: box.top - pageBox.top - 28, bottom: box.bottom - pageBox.top + 28 };
    var needed = visible.length * AREA_PER_PRINT + (keepOut.right - keepOut.left) * (keepOut.bottom - keepOut.top);
    var grow = Math.max(1, Math.sqrt(needed / (vw * vh)));
    var W = vw * grow, H = vh * grow;
    var placed = [], maxX = 0, maxY = 0;

    visible.forEach(function (print) {
      var seed = prints.indexOf(print) + 1;
      var w = 170 + Math.round(rand(seed + 3) * 150);
      var h = (w - 14) / (+print.dataset.ratio) + 14;
      var rad = Math.abs(+print.dataset.turn) * Math.PI / 180;
      var bw = w * Math.cos(rad) + h * Math.sin(rad);     // kotak setelah dimiringkan
      var bh = w * Math.sin(rad) + h * Math.cos(rad);
      var best = null, bestScore = -Infinity, round = 0;

      while (!best) {
        for (var t = 0; t < ATTEMPTS; t++) {
          var cx = EDGE + bw / 2 + rand(seed * 13.7 + t * 3.1 + round * 91) * Math.max(1, W - bw - EDGE * 2);
          var cy = TOP + bh / 2 + rand(seed * 29.3 + t * 5.7 + round * 53) * Math.max(1, H - bh - TOP - NAV_ROOM);
          var c = { left: cx - bw / 2, right: cx + bw / 2, top: cy - bh / 2, bottom: cy + bh / 2 };
          if (c.left < keepOut.right && c.right > keepOut.left && c.top < keepOut.bottom && c.bottom > keepOut.top) continue;

          var clash = false, score = Infinity;
          for (var o = 0; o < placed.length; o++) {
            var q = placed[o];
            if (c.left < q.right + GAP && c.right + GAP > q.left && c.top < q.bottom + GAP && c.bottom + GAP > q.top) { clash = true; break; }
            score = Math.min(score, Math.hypot(cx - q.cx, cy - q.cy));
          }
          if (clash) continue;
          if (score > bestScore) { bestScore = score; c.cx = cx; c.cy = cy; best = c; }
        }
        if (!best) { H += 180; W += 120; round++; }
      }

      placed.push(best);
      print.style.setProperty('--x', Math.round(best.cx - w / 2) + 'px');
      print.style.setProperty('--y', Math.round(best.cy - h / 2) + 'px');
      print.style.width = w + 'px';
      maxX = Math.max(maxX, best.right);
      maxY = Math.max(maxY, best.bottom);
    });

    bounds.w = maxX + EDGE;
    bounds.h = maxY + NAV_ROOM;
    clampTarget();
    moveTo();
    page.classList.toggle('is-draggable', bounds.w > vw || bounds.h > vh);
  }

  /* ---------- Seret ---------- */
  var drag = null, moved = false;

  page.addEventListener('pointerdown', function (e) {
    if (phone.matches || e.button !== 0 || e.target.closest('.table-copy a, .filter-btn')) return;
    drag = { id: e.pointerId, x: e.clientX, y: e.clientY, tx: target.x, ty: target.y };
    moved = false;
  });

  page.addEventListener('pointermove', function (e) {
    if (!drag || e.pointerId !== drag.id) return;
    var dx = e.clientX - drag.x, dy = e.clientY - drag.y;
    if (!moved && Math.abs(dx) + Math.abs(dy) < 6) return;
    if (!moved) {
      moved = true;
      page.classList.add('is-dragging');
      page.setPointerCapture(e.pointerId);        // baru ditangkap di sini agar klik foto tetap sampai
    }
    target.x = drag.tx + dx; target.y = drag.ty + dy;
    clampTarget();
    moveTo();
  });

  function endDrag() {
    if (!drag) return;
    drag = null;
    page.classList.remove('is-dragging');
    setTimeout(function () { moved = false; }, 0);   // klik berikutnya tidak boleh ikut tertelan
  }
  page.addEventListener('pointerup', endDrag);
  page.addEventListener('pointercancel', endDrag);

  /* Klik yang sebenarnya seretan tidak boleh membuka foto. */
  page.addEventListener('click', function (e) {
    if (moved) { e.stopPropagation(); e.preventDefault(); }
    moved = false;
  }, true);

  /* ---------- Roda tetikus ---------- */
  page.addEventListener('wheel', function (e) {
    if (phone.matches) return;
    if (bounds.w <= page.clientWidth && bounds.h <= page.clientHeight) return;
    e.preventDefault();
    target.x -= e.deltaX; target.y -= e.deltaY;
    clampTarget();
    moveTo();
  }, { passive: false });

  /* ---------- Papan ketik: foto yang difokus selalu terlihat ---------- */
  surface.addEventListener('focusin', function (e) {
    var print = e.target.closest('.table-print');
    if (!print || phone.matches) return;
    var box = print.getBoundingClientRect(), pageBox = page.getBoundingClientRect();
    target.x = pan.x; target.y = pan.y;
    if (box.left < pageBox.left + 16) target.x += pageBox.left + 16 - box.left;
    if (box.right > pageBox.right - 16) target.x -= box.right - pageBox.right + 16;
    if (box.top < pageBox.top + 16) target.y += pageBox.top + 16 - box.top;
    if (box.bottom > pageBox.bottom - NAV_ROOM) target.y -= box.bottom - pageBox.bottom + NAV_ROOM;
    clampTarget();
    moveTo();
    page.scrollTop = 0; page.scrollLeft = 0;
  });

  /* site.js sudah menandai foto is-out, lalu foto yang tersisa disusun ulang. */
  var filters = page.querySelector('.filters');
  if (filters) filters.addEventListener('click', function (e) {
    if (!e.target.closest('.filter-btn')) return;
    target.x = 0; target.y = 0;
    layout();
  });

  var timer;
  window.addEventListener('resize', function () {
    clearTimeout(timer);
    timer = setTimeout(layout, 150);
  });

  layout();
  pan.x = target.x; pan.y = target.y;
  draw();
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(layout);

  /* Foto muncul satu per satu saat halaman dibuka. */
  prints.forEach(function (print, i) {
    setTimeout(function () { print.classList.add('is-in'); }, reduce ? 0 : 200 + i * 110);
  });
})();
