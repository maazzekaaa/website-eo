/* =========================================================
   FZM Production v2 — dashboard.js
   Statistik count-up + sparkline, grafik pendapatan harian/
   bulanan dengan navigasi periode & tooltip, donut status
   pembayaran, agenda, tabel booking (tab + cari) dan drawer.
   ========================================================= */
(function () {
  'use strict';
  if (!window.FZM) return;                 // belum login → admin.js sudah mengalihkan
  var F = window.FZM, $ = F.$, $$ = F.$$, rp = F.rp;
  var MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var MON = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  var DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

  var data = JSON.parse($('#dashData').textContent);
  var tp = data.today.split('-');
  var TODAY = new Date(+tp[0], +tp[1] - 1, +tp[2]);
  var Y = TODAY.getFullYear(), M = TODAY.getMonth(), D = TODAY.getDate();
  var ym = function (y, m) { return y + '-' + (m < 9 ? '0' : '') + (m + 1); };
  var sum = function (a) { return a.reduce(function (x, y) { return x + y; }, 0); };
  var short = function (n) {
    if (n >= 1e9) return (n / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M';
    if (n >= 1e6) return (n / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
    if (n >= 1e3) return Math.round(n / 1e3) + ' rb';
    return String(Math.round(n));
  };
  var fmtDate = function (iso, withDay) {
    var p = iso.split('-'), d = new Date(+p[0], +p[1] - 1, +p[2]);
    return (withDay ? DAYS[d.getDay()] + ', ' : '') + d.getDate() + ' ' + (withDay ? MONTHS : MON)[d.getMonth()] + ' ' + p[0];
  };

  /* ---------- Sapaan ---------- */
  var h = new Date().getHours();
  $('#greeting').textContent = (h < 11 ? 'Selamat pagi' : h < 15 ? 'Selamat siang' : h < 19 ? 'Selamat sore' : 'Selamat malam') + ', Admin';
  $('#todayLabel').textContent = DAYS[TODAY.getDay()] + ', ' + D + ' ' + MONTHS[M] + ' ' + Y + ' · WIB';

  /* ---------- Statistik ---------- */
  var cur = data.daily[ym(Y, M)] || [];
  var prevM = M === 0 ? 11 : M - 1, prevY = M === 0 ? Y - 1 : Y;
  var prev = data.daily[ym(prevY, prevM)] || [];
  var todayVal = cur[D - 1] || 0, yesterday = D > 1 ? (cur[D - 2] || 0) : (prev[prev.length - 1] || 0);
  var monthVal = sum(cur), prevSame = sum(prev.slice(0, D));

  function setDelta(el, now, before) {
    if (!before) { el.textContent = now ? 'baru' : '—'; return; }
    var pct = Math.round((now - before) / before * 100);
    el.textContent = (pct >= 0 ? '↑ ' : '↓ ') + Math.abs(pct) + '%';
    el.classList.toggle('is-down', pct < 0);
  }
  setDelta($('#stTodayDelta'), todayVal, yesterday);
  setDelta($('#stMonthDelta'), monthVal, prevSame);
  $('#stMonthNote').textContent = 'vs 1–' + D + ' ' + MONTHS[prevM];

  function spark(svg, values) {
    var max = Math.max.apply(null, values) || 1, w = 96, hh = 30;
    var pts = values.map(function (v, i) { return [i / (values.length - 1) * w, 32 - v / max * hh]; });
    var d = pts.map(function (p, i) {
      if (!i) return 'M' + p[0].toFixed(1) + ' ' + p[1].toFixed(1);
      var q = pts[i - 1], cx = (q[0] + p[0]) / 2;
      return 'C' + cx.toFixed(1) + ' ' + q[1].toFixed(1) + ' ' + cx.toFixed(1) + ' ' + p[1].toFixed(1) + ' ' + p[0].toFixed(1) + ' ' + p[1].toFixed(1);
    }).join(' ');
    $('path', svg).setAttribute('d', d);
  }
  var last7 = prev.concat(cur).slice(-7);
  spark($('#sparkToday'), last7);
  spark($('#sparkMonth'), (data.monthly[Y] || []).slice(-7));

  /* ---------- Booking (dipakai statistik, donut, agenda, tabel) ---------- */
  var bookings = data.bookings.map(function (b) { b.dp = Math.ceil(b.total / 2 / 1000) * 1000; return b; });
  var payKey = function (b) { return b.paid >= b.total ? 'paid' : b.paid >= b.dp ? 'dp' : 'unpaid'; };
  var PAY = { unpaid: 'Belum bayar', dp: 'DP', paid: 'Lunas' };
  var ST = { pending: 'Menunggu', confirmed: 'Dikonfirmasi', completed: 'Selesai', rejected: 'Ditolak', cancelled: 'Dibatalkan' };
  var active = function (b) { return b.status === 'pending' || b.status === 'confirmed'; };

  function refreshStats(first) {
    var pend = bookings.filter(function (b) { return b.status === 'pending'; });
    var ready = pend.filter(function (b) { return b.paid >= b.dp; }).length;
    var owing = bookings.filter(function (b) { return active(b) && b.paid < b.total; });
    var rest = sum(owing.map(function (b) { return b.total - b.paid; }));
    F.countUp($('#stPending'), pend.length, function (n) { return String(Math.round(n)); }, first ? 1000 : 600);
    $('#stPendingNote').textContent = ready + ' siap dikonfirmasi (DP masuk)';
    F.countUp($('#stRest'), rest, rp, first ? 1300 : 700);
    $('#stRestNote').textContent = owing.length + ' booking belum lunas';
    var nav = $('.side-nav a[href="booking.html"] .side-count'); if (nav) nav.textContent = pend.length;
  }
  F.countUp($('#stToday'), todayVal, rp, 1300);
  F.countUp($('#stMonth'), monthVal, rp, 1500);
  refreshStats(true);

  /* ---------- Grafik ---------- */
  var mode = 'daily';
  var per = { y: Y, m: M };
  var barsEl = $('#bars'), tip = $('#tip');

  function niceMax(v) {
    if (v <= 0) return 1e6;
    var e = Math.pow(10, Math.floor(Math.log10(v)));
    var s = [1, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10];
    for (var i = 0; i < s.length; i++) if (s[i] * e >= v) return s[i] * e;
    return 10 * e;
  }
  function series() {
    var out = [];
    if (mode === 'daily') {
      var arr = data.daily[ym(per.y, per.m)] || [];
      var dim = new Date(per.y, per.m + 1, 0).getDate();
      var isCur = per.y === Y && per.m === M;
      for (var d = 1; d <= dim; d++) {
        var future = isCur && d > D;
        out.push({ v: future ? null : (arr[d - 1] || 0), x: (d === 1 || d % 5 === 0) ? String(d) : '', name: d + ' ' + MONTHS[per.m] + ' ' + per.y, now: isCur && d === D });
      }
    } else {
      var yr = data.monthly[per.y] || [];
      for (var m = 0; m < 12; m++) {
        var fut = per.y === Y && m > M;
        out.push({ v: fut ? null : (yr[m] || 0), x: MON[m], name: MONTHS[m] + ' ' + per.y, now: per.y === Y && m === M });
      }
    }
    return out;
  }
  function hasPeriod(y, m) {
    return mode === 'daily' ? !!data.daily[ym(y, m)] : !!data.monthly[y];
  }
  function renderChart() {
    var s = series();
    var max = niceMax(Math.max.apply(null, s.map(function (b) { return b.v || 0; })));
    ['y0', 'y1', 'y2', 'y3'].forEach(function (id, i) { $('#' + id).textContent = short(max * (1 - i * .25)); });
    var gap = mode === 'daily' ? '4px' : '10px';
    barsEl.style.setProperty('--gap', gap);
    $('#xAxis').style.setProperty('--gap', gap);

    var existing = $$('.bar', barsEl);
    if (existing.length !== s.length) {
      barsEl.innerHTML = s.map(function (_, i) { return '<button type="button" class="bar" data-i="' + i + '" style="--k:' + i + '"><i></i></button>'; }).join('');
      existing = $$('.bar', barsEl);
      existing.forEach(function (b) { $('i', b).style.setProperty('--h', '0%'); });
      void barsEl.offsetWidth;
    }
    existing.forEach(function (b, i) {
      var p = s[i];
      b.className = 'bar' + (p.now ? ' is-now' : '') + (p.v === null ? ' is-future' : p.v === 0 ? ' is-zero' : '');
      b.setAttribute('aria-label', p.name + ': ' + (p.v === null ? 'belum berjalan' : rp(p.v)));
      b._p = p;
      requestAnimationFrame(function () { $('i', b).style.setProperty('--h', (p.v ? p.v / max * 100 : 0) + '%'); });
    });
    $('#xAxis').innerHTML = s.map(function (p) { return '<span class="' + (p.now ? 'is-now' : '') + '">' + p.x + '</span>'; }).join('');

    var total = sum(s.map(function (p) { return p.v || 0; }));
    F.countUp($('#chartTotal'), total, rp, 800);
    $('#chartTotalLabel').textContent = mode === 'daily' ? 'Total ' + MONTHS[per.m] + ' ' + per.y : 'Total tahun ' + per.y;
    $('#perLabel').textContent = mode === 'daily' ? MONTHS[per.m] + ' ' + per.y : String(per.y);
    var pv = mode === 'daily' ? (per.m === 0 ? [per.y - 1, 11] : [per.y, per.m - 1]) : [per.y - 1, 0];
    var nx = mode === 'daily' ? (per.m === 11 ? [per.y + 1, 0] : [per.y, per.m + 1]) : [per.y + 1, 0];
    $('#perPrev').disabled = !hasPeriod(pv[0], pv[1]);
    $('#perNext').disabled = !hasPeriod(nx[0], nx[1]);
    hideTip();
  }
  function showTip(b) {
    var p = b._p; if (!p) return;
    $$('.bar', barsEl).forEach(function (x) { x.classList.toggle('is-focus', x === b); });
    barsEl.classList.add('is-hover');
    $('#tipLabel').textContent = p.name + (p.now ? (mode === 'daily' ? ' · hari ini' : ' · berjalan') : '');
    $('#tipValue').textContent = p.v === null ? 'Belum berjalan' : rp(p.v);
    var r = b.getBoundingClientRect(), pr = barsEl.getBoundingClientRect();
    var bh = $('i', b).getBoundingClientRect().height;
    tip.style.left = (r.left - pr.left + r.width / 2) + 'px';
    tip.style.top = (pr.height - bh - 6) + 'px';
    tip.classList.add('is-on');
  }
  function hideTip() { tip.classList.remove('is-on'); barsEl.classList.remove('is-hover'); $$('.bar', barsEl).forEach(function (x) { x.classList.remove('is-focus'); }); }
  barsEl.addEventListener('pointerover', function (e) { var b = e.target.closest('.bar'); if (b) showTip(b); });
  barsEl.addEventListener('focusin', function (e) { var b = e.target.closest('.bar'); if (b) showTip(b); });
  barsEl.addEventListener('pointerleave', hideTip);
  barsEl.addEventListener('focusout', function (e) { if (!barsEl.contains(e.relatedTarget)) hideTip(); });

  var seg = $('#modeSeg'), thumb = $('.seg-thumb', seg);
  function moveThumb(btn) { thumb.style.width = btn.offsetWidth + 'px'; thumb.style.transform = 'translateX(' + btn.offsetLeft + 'px)'; }
  $$('button', seg).forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (btn.getAttribute('aria-selected') === 'true') return;
      $$('button', seg).forEach(function (b) { b.setAttribute('aria-selected', String(b === btn)); });
      moveThumb(btn);
      mode = btn.getAttribute('data-mode');
      per = { y: Y, m: M };
      barsEl.innerHTML = '';
      renderChart();
    });
  });
  $('#perPrev').addEventListener('click', function () {
    if (mode === 'daily') { per.m--; if (per.m < 0) { per.m = 11; per.y--; } } else per.y--;
    renderChart();
  });
  $('#perNext').addEventListener('click', function () {
    if (mode === 'daily') { per.m++; if (per.m > 11) { per.m = 0; per.y++; } } else per.y++;
    renderChart();
  });
  requestAnimationFrame(function () { moveThumb($('button[aria-selected="true"]', seg)); });
  if (document.fonts) document.fonts.ready.then(function () { moveThumb($('button[aria-selected="true"]', seg)); });
  renderChart();

  /* ---------- Donut ---------- */
  var COLORS = { unpaid: '#F6C3C8', dp: '#0D0D0D', paid: '#E11D2E' };
  function renderDonut() {
    var act = bookings.filter(active);
    var counts = { unpaid: 0, dp: 0, paid: 0 };
    act.forEach(function (b) { counts[payKey(b)]++; });
    var C = 2 * Math.PI * 62, off = 0, n = act.length || 1, gapLen = act.length > 1 ? 3 : 0;
    var arcs = '', legend = '';
    ['unpaid', 'dp', 'paid'].forEach(function (k) {
      var len = counts[k] / n * C;
      if (counts[k]) arcs += '<circle class="seg-arc" data-k="' + k + '" cx="80" cy="80" r="62" stroke="' + COLORS[k] + '" stroke-dasharray="0 ' + C + '" stroke-dashoffset="' + (-off) + '" data-len="' + Math.max(0, len - gapLen) + '"></circle>';
      off += len;
      legend += '<li data-k="' + k + '"><i style="background:' + COLORS[k] + '"></i>' + PAY[k] + '<b>' + counts[k] + '</b></li>';
    });
    $('#donutArcs').innerHTML = arcs;
    $('#legend').innerHTML = legend;
    $('#donutNum').textContent = act.length;
    $('#donutLbl').textContent = 'booking aktif';
    requestAnimationFrame(function () {
      $$('#donutArcs circle').forEach(function (c) { c.setAttribute('stroke-dasharray', c.getAttribute('data-len') + ' ' + C); });
    });
    var hot = function (k) {
      $$('#donutArcs circle').forEach(function (c) { c.classList.toggle('is-hot', c.getAttribute('data-k') === k); c.classList.toggle('is-dim', !!k && c.getAttribute('data-k') !== k); });
      $$('#legend li').forEach(function (l) { l.classList.toggle('is-hot', l.getAttribute('data-k') === k); });
      $('#donutNum').textContent = k ? counts[k] : act.length;
      $('#donutLbl').textContent = k ? PAY[k] : 'booking aktif';
    };
    $$('#legend li, #donutArcs circle').forEach(function (el) {
      el.addEventListener('pointerenter', function () { hot(el.getAttribute('data-k')); });
      el.addEventListener('pointerleave', function () { hot(null); });
    });
  }
  renderDonut();

  /* ---------- Agenda ---------- */
  function renderAgenda() {
    var lockedThisMonth = {};
    bookings.forEach(function (b) {
      var p = b.date.split('-');
      if (b.status === 'confirmed' && +p[0] === Y && +p[1] - 1 === M) lockedThisMonth[+p[2]] = true;
    });
    var lead = (new Date(Y, M, 1).getDay() + 6) % 7, dim = new Date(Y, M + 1, 0).getDate();
    var html = ['Sn', 'Sl', 'Rb', 'Km', 'Jm', 'Sb', 'Mg'].map(function (w) { return '<span class="wd">' + w + '</span>'; }).join('');
    for (var i = 0; i < lead; i++) html += '<span></span>';
    for (var d = 1; d <= dim; d++) html += '<span class="' + (d === D ? 'is-today ' : '') + (lockedThisMonth[d] ? 'has-event' : '') + '">' + d + '</span>';
    $('#miniCal').innerHTML = html;
    $('#agMonth').textContent = MONTHS[M] + ' ' + Y + ' · titik merah = tanggal terkunci';
    var up = bookings.filter(function (b) { return b.status === 'confirmed' && b.date >= data.today; })
      .sort(function (a, b) { return a.date < b.date ? -1 : 1; }).slice(0, 4);
    $('#agenda').innerHTML = up.map(function (b) {
      var p = b.date.split('-');
      return '<li><time datetime="' + b.date + '"><b>' + (+p[2]) + '</b><small>' + MON[+p[1] - 1].toUpperCase() + '</small></time><div><strong>' + esc(b.name) + '</strong><span>' + PAY[payKey(b)] + ' · ' + rp(b.total) + '</span></div></li>';
    }).join('') || '<li class="empty">Belum ada jadwal terkunci.</li>';
  }
  renderAgenda();

  /* ---------- Tabel + tab + cari ---------- */
  var tab = 'all', q = '';
  var tabs = $('#tabs'), line = $('.tabs-line', tabs);
  function moveLine(btn) { line.style.width = btn.offsetWidth + 'px'; line.style.transform = 'translateX(' + btn.offsetLeft + 'px)'; }
  function initials(n) { return n.split(/\s+/).filter(function (w) { return /^[A-Za-z]/.test(w); }).slice(0, 2).map(function (w) { return w[0]; }).join('').toUpperCase(); }
  function renderTable() {
    var counts = { all: bookings.length, pending: 0, confirmed: 0, completed: 0 };
    bookings.forEach(function (b) { if (counts[b.status] !== undefined) counts[b.status]++; });
    $$('button', tabs).forEach(function (b) { $('span', b).textContent = counts[b.getAttribute('data-tab')]; });
    var rows = bookings.filter(function (b) {
      return (tab === 'all' || b.status === tab) && (!q || (b.name + ' ' + b.code).toLowerCase().indexOf(q) > -1);
    });
    $('#tbody').innerHTML = rows.map(function (b, i) {
      var pk = payKey(b);
      return '<tr tabindex="0" data-code="' + b.code + '" style="animation-delay:' + (i * 40) + 'ms">' +
        '<td><span class="who"><i>' + initials(b.name) + '</i><b style="font-weight:500">' + esc(b.name) + '</b></span></td>' +
        '<td class="mono">' + b.code + '</td>' +
        '<td>' + fmtDate(b.date) + '</td>' +
        '<td class="num"><b style="font-weight:500">' + rp(b.total) + '</b></td>' +
        '<td><span class="badge badge-' + b.status + '">' + ST[b.status] + '</span></td>' +
        '<td><span class="badge badge-' + pk + '">' + PAY[pk] + '</span></td>' +
        '<td class="num"><span class="row-go" aria-hidden="true">→</span></td></tr>';
    }).join('') || '<tr><td colspan="7" class="empty">Tidak ada booking yang cocok.</td></tr>';
  }
  $$('button', tabs).forEach(function (btn) {
    btn.addEventListener('click', function () {
      $$('button', tabs).forEach(function (b) { b.setAttribute('aria-selected', String(b === btn)); });
      moveLine(btn); tab = btn.getAttribute('data-tab'); renderTable();
    });
  });
  requestAnimationFrame(function () { moveLine($('button[aria-selected="true"]', tabs)); });
  if (document.fonts) document.fonts.ready.then(function () { moveLine($('button[aria-selected="true"]', tabs)); });
  window.addEventListener('resize', function () { moveLine($('button[aria-selected="true"]', tabs)); moveThumb($('button[aria-selected="true"]', seg)); });
  var search = $('#globalSearch');
  if (search) search.addEventListener('input', function () { q = search.value.trim().toLowerCase(); renderTable(); });
  renderTable();

  /* ---------- Drawer ---------- */
  var drawer = $('#drawer'), current = null, lastFocus = null;
  function openDrawer(code) {
    var b = bookings.filter(function (x) { return x.code === code; })[0]; if (!b) return;
    current = b; lastFocus = document.activeElement;
    var pk = payKey(b), pct = Math.min(100, Math.round(b.paid / b.total * 100));
    $('#dwCode').textContent = b.code;
    $('#dwTitle').textContent = b.name;
    $('#dwBadges').innerHTML = '<span class="badge badge-' + b.status + '" style="background:rgba(255,255,255,.12);color:#fff">' + ST[b.status] + '</span><span class="badge badge-' + pk + '">' + PAY[pk] + '</span>';
    $('#dwDate').textContent = fmtDate(b.date, true);
    $('#dwItems').textContent = b.items;
    $('#dwWa').textContent = b.wa;
    $('#dwTotal').textContent = rp(b.total);
    $('#dwPaid').textContent = rp(b.paid);
    $('#dwRest').textContent = rp(b.total - b.paid);
    $('#dwPct').textContent = pct + '%';
    $('#dwBar').style.width = '0';
    var can = b.status === 'pending' && b.paid >= b.dp;
    var btn = $('#dwConfirm');
    btn.hidden = b.status !== 'pending';
    btn.setAttribute('aria-disabled', String(!can));
    $('#dwHint').textContent = b.status === 'pending'
      ? (can ? 'DP 50% sudah masuk. Konfirmasi akan mengunci ' + fmtDate(b.date) + ' di kalender publik.' : 'Belum bisa dikonfirmasi: DP 50% (' + rp(b.dp) + ') belum tercatat.')
      : 'Status: ' + ST[b.status] + '.';
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    setTimeout(function () { $('#dwBar').style.width = pct + '%'; }, 60);
    $('[data-close].icon-btn', drawer).focus({ preventScroll: true });
  }
  function closeDrawer() {
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    if (lastFocus) lastFocus.focus({ preventScroll: true });
  }
  $('#tbody').addEventListener('click', function (e) { var tr = e.target.closest('tr[data-code]'); if (tr) openDrawer(tr.getAttribute('data-code')); });
  $('#tbody').addEventListener('keydown', function (e) {
    var tr = e.target.closest('tr[data-code]'); if (!tr) return;
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openDrawer(tr.getAttribute('data-code')); }
  });
  $$('[data-close]', drawer).forEach(function (x) { x.addEventListener('click', closeDrawer); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && drawer.classList.contains('is-open')) closeDrawer(); });
  $('#dwConfirm').addEventListener('click', function () {
    var b = current; if (!b) return;
    if (b.paid < b.dp) { F.toast('DP 50% belum tercatat untuk ' + b.code + '.', true); return; }
    var clash = bookings.filter(function (x) { return x !== b && x.status === 'confirmed' && x.date === b.date; })[0];
    if (clash) { F.toast('Tanggal ' + fmtDate(b.date) + ' sudah dikunci ' + clash.code + '.', true); return; }
    b.status = 'confirmed';                                // Django: POST /admin/bookings/<id>/confirm
    F.toast(b.code + ' dikonfirmasi. ' + fmtDate(b.date) + ' kini terkunci.');
    closeDrawer(); renderTable(); refreshStats(false); renderDonut(); renderAgenda();
  });

  /* ---------- Unduh rekap (CSV) ---------- */
  $('#exportBtn').addEventListener('click', function () {
    var rows = [['Tanggal', 'Pendapatan']];
    (data.daily[ym(Y, M)] || []).forEach(function (v, i) { rows.push([ym(Y, M) + '-' + (i < 9 ? '0' : '') + (i + 1), v]); });
    var csv = rows.map(function (r) { return r.join(','); }).join('\n');
    var a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
    a.download = 'rekap-pendapatan-' + ym(Y, M) + '.csv';
    document.body.appendChild(a); a.click(); a.remove();
    F.toast('Rekap ' + MONTHS[M] + ' ' + Y + ' diunduh (CSV).');
  });

  function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
})();
