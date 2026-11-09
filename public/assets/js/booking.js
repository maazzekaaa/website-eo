/* =========================================================
   FZM Production v2 — booking.js (detail booking admin)
   Aturan PRD (disimulasikan di browser):
   - Pembayaran tidak boleh melebihi sisa tagihan
   - Status bayar: belum bayar → DP (≥ 50%) → lunas
   - Konfirmasi butuh DP 50%; "Selesai" butuh lunas
   Django: semua aturan divalidasi ulang di server.
   ========================================================= */
(function () {
  'use strict';
  if (!window.FZM) return;
  var F = window.FZM, $ = F.$, $$ = F.$$, rp = F.rp;
  var MON = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  var b = JSON.parse($('#bookingData').textContent);
  var ST = { pending: 'Menunggu', confirmed: 'Dikonfirmasi', completed: 'Selesai', rejected: 'Ditolak', cancelled: 'Dibatalkan' };

  var paid = function () { return b.payments.reduce(function (a, p) { return a + p.amount; }, 0); };
  var rest = function () { return b.total - paid(); };
  var payKey = function () { var p = paid(); return p >= b.total ? 'paid' : p >= b.dp ? 'dp' : 'unpaid'; };
  var PAY = { unpaid: 'Belum bayar', dp: 'DP', paid: 'Lunas' };
  var open = function () { return b.status === 'pending' || b.status === 'confirmed'; };
  var fmt = function (iso) { var p = iso.split('-'); return +p[2] + ' ' + MON[+p[1] - 1] + ' ' + p[0]; };

  /* ---------- Render ---------- */
  var C = 2 * Math.PI * 54;
  var ring = $('#ringVal');
  ring.style.strokeDasharray = C;
  ring.style.strokeDashoffset = C;

  function render() {
    var pk = payKey(), p = paid(), pct = Math.min(100, Math.round(p / b.total * 100));
    $('#stBadge').className = 'badge badge-' + b.status;
    $('#stBadge').textContent = ST[b.status];
    if (b.status === 'pending') { $('#stBadge').style.background = 'rgba(255,255,255,.14)'; $('#stBadge').style.color = '#fff'; }
    else { $('#stBadge').style.background = ''; $('#stBadge').style.color = ''; }
    if (b.status === 'confirmed') { $('#stBadge').style.background = '#fff'; $('#stBadge').style.color = '#0D0D0D'; }
    $('#payBadge').className = 'badge badge-' + pk;
    $('#payBadge').textContent = PAY[pk];

    // Stepper status
    var order = ['pending', 'confirmed', 'completed'];
    var idx = order.indexOf(b.status);
    var stopped = idx === -1;
    $('#steps').classList.toggle('is-stopped', stopped);
    $$('.step').forEach(function (s, i) {
      s.classList.toggle('is-done', !stopped && i <= idx);
      s.classList.toggle('is-current', !stopped && i === idx);
    });
    $('#stepsFill').style.width = stopped ? '0' : ['0px', 'calc(50% - 16px)', 'calc(100% - 32px)'][idx];
    if (stopped) $$('.step')[0].querySelector('span').textContent = ST[b.status];
    else $$('.step')[0].querySelector('span').textContent = 'Menunggu';

    // Tagihan
    F.countUp($('#mTotal'), b.total, rp, 600);
    F.countUp($('#mDp'), b.dp, rp, 600);
    F.countUp($('#mPaid'), p, rp, 900);
    F.countUp($('#mRest'), rest(), rp, 900);
    F.countUp($('#ringPct'), pct, function (n) { return Math.round(n) + '%'; }, 1100);
    requestAnimationFrame(function () { ring.style.strokeDashoffset = C * (1 - pct / 100); });

    // Timeline
    $('#timeline').innerHTML = b.payments.slice().reverse().map(function (x, i) {
      return '<li style="animation-delay:' + (i * 70) + 'ms"><span class="pt is-ok" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>' +
        '<div class="body"><div><b>' + x.type + ' · ' + rp(x.amount) + '</b><small>' + x.no + ' · ' + fmt(x.date) + ' · ' + x.method + '</small></div>' +
        '<a class="btn btn-sm btn-light" href="kuitansi.html?no=' + encodeURIComponent(x.no) + '">Kuitansi ↗</a></div></li>';
    }).join('') || '<li class="empty">Belum ada pembayaran.</li>';

    // Aksi
    var canPay = open() && rest() > 0;
    $('#openPay').hidden = !canPay;
    var acts = {
      confirm: b.status === 'pending' && p >= b.dp,
      complete: b.status === 'confirmed' && rest() === 0,
      reject: b.status === 'pending',
      cancel: open()
    };
    $$('[data-act]').forEach(function (btn) { btn.setAttribute('aria-disabled', String(!acts[btn.getAttribute('data-act')])); });
    $('#acHint').textContent =
      b.status === 'pending' ? (p >= b.dp ? 'DP 50% sudah masuk — siap dikonfirmasi.' : 'Catat DP minimal ' + rp(b.dp) + ' untuk bisa mengonfirmasi.') :
      b.status === 'confirmed' ? (rest() ? 'Tanggal terkunci. Sisa ' + rp(rest()) + ' dilunasi paling lambat ' + b.eventDate + '.' : 'Lunas — tandai selesai setelah acara.') :
      'Booking ' + ST[b.status].toLowerCase() + '.';
  }

  /* ---------- Dialog pembayaran ---------- */
  var dlg = $('#payDialog'), amount = $('#amount');
  var parseAmt = function () { return parseInt(amount.value.replace(/\D/g, ''), 10) || 0; };
  amount.addEventListener('input', function () {
    var n = parseAmt();
    amount.value = n ? n.toLocaleString('id-ID') : '';
    amount.classList.remove('is-invalid'); $('#amountErr').hidden = true;
  });
  function openPay() {
    var r = rest();
    $('#pdRest').textContent = rp(r);
    amount.value = r.toLocaleString('id-ID');
    $('#ptype').value = paid() < b.dp ? 'DP' : 'Pelunasan';
    var quick = [['Lunasi sisa', r]];
    if (paid() < b.dp) quick.unshift(['Pas DP 50%', b.dp - paid()]);
    quick.push(['Rp1 jt', 1000000]);
    $('#quick').innerHTML = quick.filter(function (q) { return q[1] <= r; }).map(function (q) { return '<button type="button" data-v="' + q[1] + '">' + q[0] + '</button>'; }).join('');
    $('#amountErr').hidden = true; amount.classList.remove('is-invalid');
    $('#dropText').textContent = 'Unggah bukti transfer (opsional) · JPG/PNG maks. 2 MB';
    if (dlg.showModal) dlg.showModal(); else dlg.setAttribute('open', '');
    amount.focus(); amount.select();
  }
  $('#quick').addEventListener('click', function (e) {
    var q = e.target.closest('button'); if (!q) return;
    amount.value = (+q.getAttribute('data-v')).toLocaleString('id-ID');
    amount.dispatchEvent(new Event('input'));
  });
  $('#openPay').addEventListener('click', openPay);
  $$('[data-close-dialog]').forEach(function (x) { x.addEventListener('click', function () { dlg.close(); }); });
  dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });

  var drop = $('#drop'), proof = $('#proof');
  ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); }); });
  ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); }); });
  drop.addEventListener('drop', function (e) { if (e.dataTransfer.files[0]) { proof.files = e.dataTransfer.files; proof.dispatchEvent(new Event('change')); } });
  proof.addEventListener('change', function () {
    var f = proof.files[0]; if (!f) return;
    if (!/^image\/(jpeg|png|webp)$/.test(f.type) || f.size > 2 * 1024 * 1024) {
      F.toast('Bukti harus gambar JPG/PNG/WebP maks. 2 MB.', true); proof.value = ''; return;
    }
    $('#dropText').textContent = '✓ ' + f.name + ' (' + Math.round(f.size / 1024) + ' KB)';
  });

  $('#payForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var n = parseAmt();
    if (n <= 0 || n > rest()) { amount.classList.add('is-invalid'); $('#amountErr').hidden = false; amount.focus(); return; }
    var btn = $('#paySave'); btn.setAttribute('aria-disabled', 'true');
    var label = btn.innerHTML; btn.innerHTML = '<span class="spinner" aria-hidden="true"></span> Menyimpan…';
    setTimeout(function () {                                 // Django: POST /admin/bookings/<id>/payments
      var no = 'KWT/2026/11/' + String(b.nextReceipt++).padStart(3, '0');
      b.payments.push({ no: no, date: $('#pdate').value || '2026-11-09', method: $('input[name="method"]:checked').value, type: $('#ptype').value, amount: n });
      btn.removeAttribute('aria-disabled'); btn.innerHTML = label;
      dlg.close();
      render();
      F.toast('Pembayaran ' + rp(n) + ' tercatat · kuitansi ' + no + ' terbit.');
    }, 650);
  });

  /* ---------- Dialog konfirmasi tindakan ---------- */
  var ask = $('#askDialog');
  function confirmAsk(title, text, yes, cb) {
    $('#askTitle').textContent = title; $('#askText').textContent = text; $('#askYes').textContent = yes; $('#askReason').value = '';
    ask.returnValue = '';
    ask.addEventListener('close', function h() { ask.removeEventListener('close', h); if (ask.returnValue === 'yes') cb(); });
    if (ask.showModal) ask.showModal(); else ask.setAttribute('open', '');
  }

  var actions = {
    confirm: function () {
      if (b.status !== 'pending') return F.toast('Hanya booking Menunggu yang bisa dikonfirmasi.', true);
      if (paid() < b.dp) return F.toast('DP 50% (' + rp(b.dp) + ') belum tercatat.', true);
      b.status = 'confirmed'; render();
      F.toast('Dikonfirmasi. ' + b.eventDate + ' kini terkunci di kalender publik.');
    },
    complete: function () {
      if (b.status !== 'confirmed') return F.toast('Konfirmasi booking dulu sebelum menandai selesai.', true);
      if (rest() > 0) return F.toast('Masih ada sisa tagihan ' + rp(rest()) + '.', true);
      b.status = 'completed'; render(); F.toast('Booking ditandai selesai.');
    },
    reject: function () {
      if (b.status !== 'pending') return F.toast('Hanya booking Menunggu yang bisa ditolak.', true);
      confirmAsk('Tolak booking ini?', 'Pemesan perlu diberi tahu lewat WhatsApp.', 'Ya, tolak', function () { b.status = 'rejected'; render(); F.toast('Booking ditolak.', true); });
    },
    cancel: function () {
      if (!open()) return F.toast('Booking ini sudah tidak aktif.', true);
      confirmAsk('Batalkan booking ini?', 'Tanggal ' + b.eventDate + ' akan dibuka kembali di kalender.', 'Ya, batalkan', function () { b.status = 'cancelled'; render(); F.toast('Booking dibatalkan. Tanggal kembali terbuka.', true); });
    }
  };
  $$('[data-act]').forEach(function (btn) { btn.addEventListener('click', function () { actions[btn.getAttribute('data-act')](); }); });

  $$('[data-wa]').forEach(function (a) {
    a.href = 'https://wa.me/' + a.getAttribute('data-wa') + '?text=' + encodeURIComponent(a.getAttribute('data-text') || '');
    a.target = '_blank'; a.rel = 'noopener';
  });

  render();
})();
