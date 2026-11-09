/* =========================================================
   FZM Production — document.js (invoice & kuitansi)
   - Tombol "Cetak / Simpan PDF"
   - ?print=1 → langsung buka dialog cetak
   - Terbilang (angka → kata) untuk kuitansi
   ========================================================= */
(function () {
  'use strict';

  document.querySelectorAll('[data-print]').forEach(function (b) {
    b.addEventListener('click', function () { window.print(); });
  });
  if (new URLSearchParams(location.search).get('print') === '1') {
    window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });
  }

  /* Terbilang: 5800000 → "Lima juta delapan ratus ribu rupiah".
     Di Laravel bisa pakai helper PHP yang setara. */
  var SATUAN = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
  function terbilang(n) {
    n = Math.floor(n);
    if (n < 12) return SATUAN[n];
    if (n < 20) return terbilang(n - 10) + ' belas';
    if (n < 100) return terbilang(Math.floor(n / 10)) + ' puluh' + spaced(n % 10);
    if (n < 200) return 'seratus' + spaced(n - 100);
    if (n < 1000) return terbilang(Math.floor(n / 100)) + ' ratus' + spaced(n % 100);
    if (n < 2000) return 'seribu' + spaced(n - 1000);
    if (n < 1e6) return terbilang(Math.floor(n / 1000)) + ' ribu' + spaced(n % 1000);
    if (n < 1e9) return terbilang(Math.floor(n / 1e6)) + ' juta' + spaced(n % 1e6);
    if (n < 1e12) return terbilang(Math.floor(n / 1e9)) + ' miliar' + spaced(n % 1e9);
    return terbilang(Math.floor(n / 1e12)) + ' triliun' + spaced(n % 1e12);
  }
  function spaced(n) { return n ? ' ' + terbilang(n) : ''; }

  var say = document.getElementById('kwSay');
  if (say && say.dataset.amount) {
    var t = terbilang(+say.dataset.amount) + ' rupiah';
    say.textContent = t.charAt(0).toUpperCase() + t.slice(1);
  }
  window.terbilang = terbilang;
})();
