/* Formulir layanan: tampilkan isian sesuai tipe harga, tambah/hapus baris paket. */
(function () {
  'use strict';
  var type = document.getElementById('pricing_type');
  var tier = document.getElementById('tierBox');
  var unit = document.getElementById('unitBox');
  var rows = document.getElementById('optionRows');
  var next = rows.children.length;

  function sync() {
    tier.hidden = type.value !== 'tier';
    unit.hidden = type.value === 'tier';
    unit.style.display = unit.hidden ? 'none' : '';
  }
  function add() {
    var i = next++;
    var row = document.createElement('div');
    row.className = 'option-row';
    row.innerHTML = '<input class="input" name="options[' + i + '][name]" placeholder="Nama paket" aria-label="Nama paket">' +
      '<input class="input" name="options[' + i + '][price]" inputmode="numeric" placeholder="Harga (Rp)" aria-label="Harga paket">' +
      '<button class="btn btn-sm btn-danger" type="button" data-remove-option>Hapus</button>';
    rows.appendChild(row);
    row.querySelector('input').focus();
  }

  type.addEventListener('change', sync);
  document.getElementById('addOption').addEventListener('click', add);
  rows.addEventListener('click', function (e) {
    var b = e.target.closest('[data-remove-option]');
    if (b) b.closest('.option-row').remove();
  });
  sync();
  if (!rows.children.length && type.value === 'tier') add();
})();
