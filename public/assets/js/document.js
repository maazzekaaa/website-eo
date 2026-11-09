/* =========================================================
   FZM Production — document.js (invoice & kuitansi)
   - Tombol "Cetak / Simpan PDF"
   - ?print=1 → langsung buka dialog cetak
   ========================================================= */
(function () {
  'use strict';

  document.querySelectorAll('[data-print]').forEach(function (b) {
    b.addEventListener('click', function () { window.print(); });
  });
  if (new URLSearchParams(location.search).get('print') === '1') {
    window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });
  }
})();
