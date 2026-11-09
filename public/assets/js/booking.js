/* =========================================================
   FZM Production — booking.js (detail booking admin)
   Menampilkan status dari server dan mengirim aksi ke server.
   Pemeriksaan di sini hanya untuk umpan balik cepat; aturan
   DP, pelunasan, dan sisa tagihan ditegakkan di server.
   ========================================================= */
(function () {
  'use strict';
  if (!window.FZM) return;
  var F = window.FZM, $ = F.$, $$ = F.$$, rp = F.rp;
  var MON = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  var b = JSON.parse($('#bookingData').textContent);
  var ST = { pending: 'Menunggu', confirmed: 'Dikonfirmasi', completed: 'Selesai', rejected: 'Ditolak', cancelled: 'Dibatalkan' };
  var PAY = { unpaid: 'Belum bayar', dp: 'DP', paid: 'Lunas' };

  function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  var valid = function () { return b.payments.filter(function (p) { return !p.voided; }); };
  var paid = function () { return valid().reduce(function (a, p) { return a + p.amount; }, 0); };
  var rest = function () { return b.total - paid(); };
  var payKey = function () { var p = paid(); return p >= b.total ? 'paid' : p >= b.dp ? 'dp' : 'unpaid'; };
  var open = function () { return b.status === 'pending' || b.status === 'confirmed'; };
  var fmt = function (iso) { var p = iso.split('-'); return +p[2] + ' ' + MON[+p[1] - 1] + ' ' + p[0]; };

  /* ---------- Tampilan ---------- */
  var C = 2 * Math.PI * 54;
  var ring = $('#ringVal');
  ring.style.strokeDasharray = C;
  ring.style.strokeDashoffset = C;

  function render() {
    var pk = payKey(), p = paid(), pct = b.total ? Math.min(100, Math.round(p / b.total * 100)) : 0;
    var badge = $('#stBadge');
    badge.className = 'badge badge-' + b.status;
    badge.textContent = ST[b.status];
    if (b.status === 'pending') { badge.style.background = 'rgba(255,255,255,.14)'; badge.style.color = '#fff'; }
    if (b.status === 'confirmed') { badge.style.background = '#fff'; badge.style.color = '#0D0D0D'; }
    $('#payBadge').className = 'badge badge-' + pk;
    $('#payBadge').textContent = PAY[pk];

    var idx = ['pending', 'confirmed', 'completed'].indexOf(b.status);
    var stopped = idx === -1;
    $('#steps').classList.toggle('is-stopped', stopped);
    $$('.step').forEach(function (s, i) {
      s.classList.toggle('is-done', !stopped && i <= idx);
      s.classList.toggle('is-current', !stopped && i === idx);
    });
    $('#stepsFill').style.width = stopped ? '0' : ['0px', 'calc(50% - 16px)', 'calc(100% - 32px)'][idx];
    $$('.step')[0].querySelector('span').textContent = stopped ? ST[b.status] : 'Menunggu';

    F.countUp($('#mTotal'), b.total, rp, 600);
    F.countUp($('#mDp'), b.dp, rp, 600);
    F.countUp($('#mPaid'), p, rp, 900);
    F.countUp($('#mRest'), rest(), rp, 900);
    F.countUp($('#ringPct'), pct, function (n) { return Math.round(n) + '%'; }, 1100);
    requestAnimationFrame(function () { ring.style.strokeDashoffset = C * (1 - pct / 100); });

    $('#timeline').innerHTML = b.payments.slice().reverse().map(function (x, i) {
      var note = x.voided ? ' · dibatalkan: ' + esc(x.voidReason || '-') : '';
      return '<li style="animation-delay:' + (i * 70) + 'ms' + (x.voided ? ';opacity:.55' : '') + '"><span class="pt is-ok" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>' +
        '<div class="body"><div><b' + (x.voided ? ' style="text-decoration:line-through"' : '') + '>' + esc(x.type) + ' · ' + rp(x.amount) + '</b><small>' + esc(x.no) + ' · ' + fmt(x.date) + ' · ' + esc(x.method) + note + '</small></div>' +
        '<div style="display:flex;gap:6px;flex-wrap:wrap">' +
        (x.proofUrl ? '<a class="btn btn-sm btn-light" href="' + esc(x.proofUrl) + '" target="_blank" rel="noopener">Bukti ↗</a>' : '') +
        '<a class="btn btn-sm btn-light" href="' + esc(x.receiptUrl) + '">Kuitansi ↗</a>' +
        (x.canVoid ? '<button class="btn btn-sm btn-danger" type="button" data-void="' + esc(x.voidUrl) + '" data-no="' + esc(x.no) + '">Batalkan</button>' : '') +
        '</div></div></li>';
    }).join('') || '<li class="empty">Belum ada pembayaran.</li>';

    $('#openPay').hidden = !(open() && rest() > 0);
    var acts = {
      confirm: b.status === 'pending' && p >= b.dp,
      complete: b.status === 'confirmed' && rest() === 0,
      reject: b.status === 'pending',
      cancel: open()
    };
    $$('[data-act]').forEach(function (btn) { btn.setAttribute('aria-disabled', String(!acts[btn.getAttribute('data-act')])); });
    $('#acHint').textContent =
      b.status === 'pending' ? (p >= b.dp ? 'DP 50% sudah masuk — siap dikonfirmasi.' : 'Catat DP minimal ' + rp(b.dp) + ' untuk bisa mengonfirmasi.') :
      b.status === 'confirmed' ? (p < b.dp ? 'Perhatian: pembayaran di bawah DP setelah pembatalan kuitansi. Tanggal masih terkunci.' : rest() ? 'Tanggal terkunci. Sisa ' + rp(rest()) + ' dilunasi paling lambat ' + b.eventDate + '.' : 'Lunas — tandai selesai setelah acara.') :
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
    $('#ptype').value = paid() < b.dp ? 'dp' : 'pelunasan';
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
  $$('[data-close-dialog]').forEach(function (x) { x.addEventListener('click', function () { x.closest('dialog').close(); }); });
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
    var n = parseAmt();
    if (n <= 0 || n > rest()) { e.preventDefault(); amount.classList.add('is-invalid'); $('#amountErr').hidden = false; amount.focus(); return; }
    var btn = $('#paySave');
    btn.setAttribute('aria-disabled', 'true');
    btn.innerHTML = '<span class="spinner" aria-hidden="true"></span> Menyimpan…';
  });

  /* ---------- Aksi status ---------- */
  var ask = $('#askDialog'), askForm = $('#askForm');
  function confirmAsk(title, text, yes, url) {
    $('#askTitle').textContent = title; $('#askText').textContent = text; $('#askYes').textContent = yes; $('#askReason').value = '';
    askForm.action = url;
    if (ask.showModal) ask.showModal(); else ask.setAttribute('open', '');
  }
  function post(url) { var f = $('#actForm'); f.action = url; f.submit(); }

  var actions = {
    confirm: function () {
      if (b.status !== 'pending') return F.toast('Hanya booking Menunggu yang bisa dikonfirmasi.', true);
      if (paid() < b.dp) return F.toast('DP 50% (' + rp(b.dp) + ') belum tercatat.', true);
      post(b.urls.confirm);
    },
    complete: function () {
      if (b.status !== 'confirmed') return F.toast('Konfirmasi booking dulu sebelum menandai selesai.', true);
      if (rest() > 0) return F.toast('Masih ada sisa tagihan ' + rp(rest()) + '.', true);
      post(b.urls.complete);
    },
    reject: function () {
      if (b.status !== 'pending') return F.toast('Hanya booking Menunggu yang bisa ditolak.', true);
      confirmAsk('Tolak booking ini?', 'Pemesan perlu diberi tahu lewat WhatsApp.', 'Ya, tolak', b.urls.reject);
    },
    cancel: function () {
      if (!open()) return F.toast('Booking ini sudah tidak aktif.', true);
      confirmAsk('Batalkan booking ini?', 'Tanggal ' + b.eventDate + ' akan dibuka kembali di kalender.', 'Ya, batalkan', b.urls.cancel);
    }
  };
  $$('[data-act]').forEach(function (btn) { btn.addEventListener('click', function () { actions[btn.getAttribute('data-act')](); }); });

  /* ---------- Batalkan pembayaran (void) ---------- */
  var voidDlg = $('#voidDialog');
  $('#timeline').addEventListener('click', function (e) {
    var v = e.target.closest('[data-void]'); if (!v) return;
    $('#voidForm').action = v.getAttribute('data-void');
    $('#voidNo').textContent = v.getAttribute('data-no');
    $('#voidReason').value = '';
    if (voidDlg.showModal) voidDlg.showModal(); else voidDlg.setAttribute('open', '');
  });

  $$('[data-wa]').forEach(function (a) {
    a.href = 'https://wa.me/' + a.getAttribute('data-wa') + '?text=' + encodeURIComponent(a.getAttribute('data-text') || '');
    a.target = '_blank'; a.rel = 'noopener';
  });

  render();
})();
