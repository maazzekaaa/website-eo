/* =========================================================
   FZM Production — ledger.js (halaman klien)
   Mengganti foto pratinjau sesuai baris yang disorot.
   ========================================================= */
(function () {
  'use strict';
  var frame = document.getElementById('ledgerPreview');
  var img = document.getElementById('ledgerImg');
  var caption = document.getElementById('ledgerCaption');
  if (!frame || !img) return;

  var current = img.getAttribute('src') || '';

  function show(row) {
    var url = row.getAttribute('data-preview') || '';
    caption.textContent = row.getAttribute('data-caption') || '';
    if (url === current) return;
    current = url;
    if (!url) { img.hidden = true; return; }
    var next = new Image();
    next.onload = function () {
      if (current !== url) return;
      frame.classList.add('is-swapping');
      setTimeout(function () {
        img.src = url;
        img.hidden = false;
        frame.classList.remove('is-swapping');
      }, 160);
    };
    next.src = url;
  }

  Array.prototype.forEach.call(document.querySelectorAll('.ledger-row'), function (row) {
    row.addEventListener('pointerenter', function () { show(row); });
    row.addEventListener('focus', function () { show(row); });
  });
})();
