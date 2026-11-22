/* =========================================================
   FZM Production — stage.js (beranda)
   Etalase proyek pilihan: satu foto besar dengan keterangan
   proyek, berganti otomatis; thumbnail bernomor untuk memilih.
   Berhenti saat kursor/fokus di etalase atau tab tersembunyi.
   ========================================================= */
(function () {
  'use strict';
  var root = document.getElementById('showcase');
  if (!root) return;
  var $ = function (s) { return root.querySelector(s); };
  var slides = Array.prototype.slice.call(root.querySelectorAll('.sc-slide'));
  var thumbs = Array.prototype.slice.call(root.querySelectorAll('.sc-thumb'));
  var caption = $('.sc-caption');
  var corner = document.getElementById('cornerPill');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var n = slides.length, cur = 0, paused = false, timer = null;
  var INTERVAL = 5600;
  if (n < 2 || !caption) return;
  root.style.setProperty('--sc-dur', INTERVAL + 'ms');

  var pad = function (v) { return (v < 10 ? '0' : '') + v; };
  var set = function (sel, text) { var el = $(sel); if (el) el.textContent = text; };

  function show(i) {
    if (i === cur) return;
    var prev = slides[cur], next = slides[i];
    prev.classList.remove('is-active'); prev.setAttribute('aria-hidden', 'true'); prev.tabIndex = -1;
    next.classList.add('is-active'); next.removeAttribute('aria-hidden'); next.removeAttribute('tabindex');
    thumbs.forEach(function (t, k) { t.setAttribute('aria-selected', String(k === i)); });
    cur = i;

    var d = next.dataset;
    caption.classList.add('is-swapping');
    setTimeout(function () {
      set('.sc-no', '(' + d.no + ')');
      set('.sc-cat', d.category || '');
      set('.sc-loc', d.location ? ' · ' + d.location : '');
      set('.sc-title', d.title || '');
      set('.sc-client', d.client || '');
      set('.sc-date', d.date ? ' — ' + d.date : '');
      caption.classList.remove('is-swapping');
    }, reduce ? 0 : 260);
    if (corner) corner.textContent = pad(i + 1) + ' — ' + pad(n);
    restart();
  }

  /* Bilah kemajuan di thumbnail aktif dijalankan ulang tiap ganti. */
  function restart() {
    clearTimeout(timer);
    root.classList.remove('is-running');
    void root.offsetWidth;
    if (reduce) return;
    root.classList.add('is-running');
    timer = setTimeout(tick, INTERVAL);
  }
  function tick() {
    if (paused || document.hidden) { timer = setTimeout(tick, 600); return; }
    show((cur + 1) % n);
  }
  function pause(on) {
    paused = on;
    root.classList.toggle('is-paused', on);
  }

  thumbs.forEach(function (t) {
    t.addEventListener('click', function () { show(+t.getAttribute('data-i')); });
    t.addEventListener('keydown', function (e) {
      var k = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
      if (!k) return;
      e.preventDefault();
      var to = (cur + k + n) % n;
      show(to);
      thumbs[to].focus();
    });
  });

  /* Geser di layar sentuh */
  var x0 = null;
  var frame = $('.sc-frame');
  frame.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
  frame.addEventListener('touchend', function (e) {
    if (x0 === null) return;
    var dx = e.changedTouches[0].clientX - x0; x0 = null;
    if (Math.abs(dx) > 40) show((cur + (dx < 0 ? 1 : -1) + n) % n);
  });

  root.addEventListener('pointerenter', function (e) { if (e.pointerType === 'mouse') pause(true); });
  root.addEventListener('pointerleave', function () { pause(false); });
  root.addEventListener('focusin', function () { pause(true); });
  root.addEventListener('focusout', function (e) { if (!root.contains(e.relatedTarget)) pause(false); });

  restart();
})();
