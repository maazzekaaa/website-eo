/* =========================================================
   FZM Production v2 — pesan.js
   Keranjang layanan (total & DP 50% beranimasi), kalender
   ketersediaan, validasi, dan pesan WhatsApp otomatis.
   Perhitungan di sini hanya pratinjau: server wajib
   menghitung ulang dari database.
   ========================================================= */
(function () {
  'use strict';

  var cfg = window.FZM_CONFIG || { adminWa: '', today: new Date().toISOString().slice(0, 10), locked: [] };
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var rp = function (n) { return 'Rp' + Math.round(n).toLocaleString('id-ID'); };
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var WD = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

  var form = $('#orderForm');
  var rows = $$('.svc-row');
  var locked = {};
  (cfg.locked || []).forEach(function (d) { locked[d] = true; });

  /* ---------- Angka beranimasi ---------- */
  var tweens = {};
  function tweenTo(el, to) {
    var id = el.id;
    var from = tweens[id] ? tweens[id].value : 0;
    if (tweens[id] && tweens[id].raf) cancelAnimationFrame(tweens[id].raf);
    if (reduce || from === to) { el.textContent = rp(to); tweens[id] = { value: to }; return; }
    var t0 = performance.now(), dur = 700;
    var state = tweens[id] = { value: from };
    (function step(now) {
      var k = Math.min(1, (now - t0) / dur);
      var e = 1 - Math.pow(1 - k, 4);
      state.value = from + (to - from) * e;
      el.textContent = rp(state.value);
      if (k < 1) state.raf = requestAnimationFrame(step); else state.value = to;
    })(t0);
  }

  /* ---------- Keranjang ---------- */
  function clamp(v) { var n = parseInt(v, 10); if (isNaN(n) || n < 0) n = 0; return Math.min(n, 500); }
  function readCart() {
    var lines = [];
    rows.forEach(function (row) {
      var name = row.getAttribute('data-name');
      var r = $('input[type="radio"]:checked', row);
      var q = $('input[type="number"]', row);
      if (r) lines.push({ desc: name + ' – ' + r.getAttribute('data-label'), value: +r.getAttribute('data-price') });
      else if (q) {
        var n = clamp(q.value);
        if (n > 0) lines.push({ desc: name + ' (' + n + ' ' + q.getAttribute('data-unit') + ')', value: n * +q.getAttribute('data-price') });
      }
    });
    var subtotal = lines.reduce(function (a, l) { return a + l.value; }, 0);
    var count = lines.length;
    var rate = +cfg.taxRate || 0;
    var tax = rate ? Math.floor((subtotal * rate + 50) / 100) : 0;
    if (tax) lines.push({ desc: 'PPN ' + rate + '%', value: tax });
    var total = subtotal + tax;
    return { lines: lines, count: count, total: total, dp: Math.min(total, Math.ceil(total / 2 / 1000) * 1000) };
  }

  var prevKeys = '';
  function renderSummary() {
    var c = readCart();
    $('#sumEmpty').hidden = c.lines.length > 0;
    var keys = c.lines.map(function (l) { return l.desc + l.value; }).join('|');
    if (keys !== prevKeys) {
      $('#sumLines').innerHTML = c.lines.map(function (l) {
        return '<li><span>' + esc(l.desc) + '</span><span>' + rp(l.value) + '</span></li>';
      }).join('');
      prevKeys = keys;
    }
    $('#sumCount').textContent = c.count + ' item';
    tweenTo($('#sumTotal'), c.total);
    tweenTo($('#sumDp'), c.dp);
    tweenTo($('#barTotal'), c.total);
    $('#barDp').textContent = rp(c.dp);
    rows.forEach(function (row) {
      var clear = $('.svc-clear', row);
      if (clear) clear.hidden = !$('input[type="radio"]:checked', row);
      var q = $('input[type="number"]', row);
      if (q) {
        var n = clamp(q.value), sub = $('.unit-sub', row);
        sub.textContent = rp(n * +q.getAttribute('data-price'));
        sub.classList.toggle('is-on', n > 0);
      }
    });
    if (c.lines.length) $('#errItems').hidden = true;
    return c;
  }

  rows.forEach(function (row) {
    $$('input[type="radio"]', row).forEach(function (r) {
      r.addEventListener('pointerdown', function () { r.dataset.was = r.checked ? '1' : ''; });
      r.addEventListener('click', function () {
        if (r.dataset.was === '1') r.checked = false;      // klik lagi = batal pilih
        r.dataset.was = '';
        renderSummary();
      });
      r.addEventListener('change', renderSummary);
    });
    var clear = $('.svc-clear', row);
    if (clear) clear.addEventListener('click', function () {
      $$('input[type="radio"]', row).forEach(function (r) { r.checked = false; });
      renderSummary();
    });
    var q = $('input[type="number"]', row);
    if (q) {
      q.addEventListener('input', renderSummary);
      q.addEventListener('change', function () { q.value = clamp(q.value); renderSummary(); });
      $$('[data-step]', row).forEach(function (b) {
        b.addEventListener('click', function () { q.value = clamp(clamp(q.value) + +b.getAttribute('data-step')); renderSummary(); });
      });
    }
  });

  /* ---------- Kalender ---------- */
  function parse(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function key(y, m, d) { return y + '-' + pad(m + 1) + '-' + pad(d); }
  function label(k) { var d = parse(k); return d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear(); }
  var today = parse(cfg.today);
  var view = { y: today.getFullYear(), m: today.getMonth() };
  var selected = null;
  var oldDate = cfg.old && cfg.old.event_date;
  if (oldDate && /^\d{4}-\d{2}-\d{2}$/.test(oldDate) && parse(oldDate) >= today) {
    selected = oldDate;
    view = { y: parse(oldDate).getFullYear(), m: parse(oldDate).getMonth() };
    $('#eventDate').value = selected;
    $('#sumDate').textContent = label(selected);
  }

  function renderCal(animate) {
    var first = new Date(view.y, view.m, 1);
    var lead = (first.getDay() + 6) % 7;
    var dim = new Date(view.y, view.m + 1, 0).getDate();
    var html = WD.map(function (w) { return '<span class="cal-wd" aria-hidden="true">' + w + '</span>'; }).join('');
    for (var i = 0; i < lead; i++) html += '<span aria-hidden="true"></span>';
    for (var d = 1; d <= dim; d++) {
      var k = key(view.y, view.m, d);
      var past = new Date(view.y, view.m, d) < today;
      var lk = !!locked[k], sel = selected === k, isToday = k === cfg.today;
      var cls = 'cal-day' + (past ? ' is-past' : '') + (lk ? ' is-locked' : '') + (sel ? ' is-selected' : '') + (isToday ? ' is-today' : '');
      var aria = d + ' ' + MONTHS[view.m] + (lk ? ', terisi' : past ? ', sudah lewat' : ', tersedia') + (isToday ? ', hari ini' : '');
      html += '<button type="button" class="' + cls + '" data-date="' + k + '" aria-label="' + aria + '" aria-pressed="' + sel + '"' + ((past || lk) ? ' disabled' : '') + '><span>' + d + '</span></button>';
    }
    var cal = $('#calendar');
    cal.innerHTML = html;
    if (animate) { cal.classList.remove('is-switching'); void cal.offsetWidth; cal.classList.add('is-switching'); }
    $('#calMonth').textContent = MONTHS[view.m] + ' ' + view.y;
    $('#calPrev').disabled = view.y === today.getFullYear() && view.m === today.getMonth();
  }
  $('#calendar').addEventListener('click', function (e) {
    var b = e.target.closest('.cal-day');
    if (!b || b.disabled) return;
    selected = b.getAttribute('data-date');
    $('#eventDate').value = selected;
    $('#sumDate').textContent = label(selected);
    $('#errDate').hidden = true;
    $$('.cal-day', this).forEach(function (x) {
      var on = x === b;
      x.classList.toggle('is-selected', on);
      x.setAttribute('aria-pressed', String(on));
    });
  });
  function shift(n) {
    view.m += n;
    if (view.m < 0) { view.m = 11; view.y--; }
    if (view.m > 11) { view.m = 0; view.y++; }
    renderCal(true);
    loadMonth();
  }
  function loadMonth() {
    if (!cfg.availabilityUrl) return;
    var y = view.y, m = view.m;
    fetch(cfg.availabilityUrl + '?month=' + y + '-' + pad(m + 1), { headers: { Accept: 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : { locked: [] }; })
      .then(function (d) {
        (d.locked || []).forEach(function (k) { locked[k] = true; });
        if (selected && locked[selected]) {
          selected = null;
          $('#eventDate').value = '';
          $('#sumDate').textContent = 'Belum dipilih';
          $('#errDate').hidden = false;
        }
        if (view.y === y && view.m === m) renderCal(false);
      })
      .catch(function () { /* jaringan gagal: server tetap memeriksa saat dikirim */ });
  }
  $('#calPrev').addEventListener('click', function () { shift(-1); });
  $('#calNext').addEventListener('click', function () { shift(1); });

  /* ---------- Validasi & kirim ---------- */
  function setErr(input, el, bad) {
    if (input) { input.classList.toggle('is-invalid', bad); input.setAttribute('aria-invalid', String(bad)); }
    el.hidden = !bad;
  }
  [['#name', '#errName'], ['#wa', '#errWa'], ['#loc', '#errLoc']].forEach(function (p) {
    $(p[0]).addEventListener('input', function () { setErr($(p[0]), $(p[1]), false); });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var c = readCart();
    var name = $('#name'), wa = $('#wa'), loc = $('#loc');
    var d = wa.value.replace(/\D/g, '');
    var bad = { items: !c.lines.length, date: !selected, name: !name.value.trim(), wa: d.length < 10 || d.length > 14, loc: !loc.value.trim() };
    setErr(null, $('#errItems'), bad.items);
    setErr(null, $('#errDate'), bad.date);
    setErr(name, $('#errName'), bad.name);
    setErr(wa, $('#errWa'), bad.wa);
    setErr(loc, $('#errLoc'), bad.loc);
    var first = ['items', 'date', 'name', 'wa', 'loc'].filter(function (k) { return bad[k]; })[0];
    if (first) {
      var t = $({ items: '#s1', date: '#s2', name: '#name', wa: '#wa', loc: '#loc' }[first]);
      t.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
      if (t.tagName === 'INPUT') t.focus({ preventScroll: true });
      return;
    }
    if (form.dataset.sent) return;                        // cegah kirim ganda
    form.dataset.sent = '1';
    form.submit();
  });

  /* ---------- Bar ponsel: sembunyi saat ringkasan terlihat ---------- */
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (en) { $('#mbar').classList.toggle('is-away', en[0].isIntersecting); }, { threshold: 0.2 }).observe($('#summary'));
  }

  /* ---------- Sorot layanan dari ?layanan=… ---------- */
  var want = new URLSearchParams(location.search).get('layanan');
  if (want) {
    var row = $('.svc-row[data-service="' + want.replace(/[^a-z-]/g, '') + '"]');
    if (row) {
      setTimeout(function () {
        row.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
        row.classList.add('is-flash');
        var f = $('input', row); if (f) f.focus({ preventScroll: true });
      }, 500);
    }
  }

  function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

  renderCal(false);
  if (selected) loadMonth();
  renderSummary();
})();
