/* =========================================================
   FZM Production v2 — kontak.js
   Form pertanyaan 2 langkah (01 / 02) → pesan WhatsApp.
   ========================================================= */
(function () {
  'use strict';
  var $ = function (s) { return document.querySelector(s); };
  var cfg = window.FZM_CONFIG || {};
  var form = $('#enquiry');
  var steps = form.querySelectorAll('.enquiry-step');
  var MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

  function show(n, back) {
    steps.forEach(function (s) {
      var on = +s.getAttribute('data-step') === n;
      s.hidden = !on;
      s.classList.toggle('is-back', !!back);
      if (on) { s.style.animation = 'none'; void s.offsetWidth; s.style.animation = ''; }
    });
    $('#enqStep').textContent = '0' + n + ' / 02';
    $('#enqBar').style.width = (n * 50) + '%';
    var first = steps[n - 1].querySelector('input, textarea');
    if (first) first.focus({ preventScroll: true });
  }

  function err(input, el, bad) {
    input.classList.toggle('is-invalid', bad);
    input.setAttribute('aria-invalid', String(bad));
    el.hidden = !bad;
    return bad;
  }
  [['#eName', '#eNameErr'], ['#eWa', '#eWaErr'], ['#eMsg', '#eMsgErr']].forEach(function (p) {
    $(p[0]).addEventListener('input', function () { err($(p[0]), $(p[1]), false); });
  });

  $('#enqNext').addEventListener('click', function () {
    var name = $('#eName'), wa = $('#eWa');
    var d = wa.value.replace(/\D/g, '');
    var b1 = err(name, $('#eNameErr'), !name.value.trim());
    var b2 = err(wa, $('#eWaErr'), d.length < 10 || d.length > 14);
    if (b1 || b2) { (b1 ? name : wa).focus(); return; }
    show(2);
  });
  $('#enqBack').addEventListener('click', function () { show(1, true); });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if ($('#eSite').value) return;                        // honeypot
    var msg = $('#eMsg');
    if (err(msg, $('#eMsgErr'), msg.value.trim().length < 10)) { msg.focus(); return; }
    var type = form.querySelector('input[name="type"]:checked').value;
    var date = $('#eDate').value;
    var dateText = '';
    if (date) { var p = date.split('-'); dateText = +p[2] + ' ' + MONTHS[+p[1] - 1] + ' ' + p[0]; }
    var text = 'Halo FZM, saya ingin bertanya.\n' +
      'Nama: ' + $('#eName').value.trim() + '\n' +
      'WA: ' + $('#eWa').value.trim() + '\n' +
      'Jenis acara: ' + type + '\n' +
      (dateText ? 'Rencana tanggal: ' + dateText + '\n' : '') +
      'Pesan: ' + msg.value.trim();
    var url = 'https://wa.me/' + (cfg.adminWa || '') + '?text=' + encodeURIComponent(text);
    $('#enqWa').href = url;
    form.hidden = true;
    $('#enqStep').textContent = 'Selesai';
    $('#enqBar').style.width = '100%';
    $('#enqDone').hidden = false;
    window.open(url, '_blank', 'noopener');
  });

  $('#enqAgain').addEventListener('click', function () {
    form.reset();
    form.hidden = false;
    $('#enqDone').hidden = true;
    show(1, true);
  });
})();
