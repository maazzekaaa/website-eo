/* =========================================================
   FZM Production — login.js
   Validasi di browser, tampil/sembunyi kata sandi, status
   memuat, dan getar saat gagal. Kredensial diperiksa server.
   ========================================================= */
(function () {
  'use strict';
  var $ = function (s) { return document.querySelector(s); };
  var form = $('#loginForm'), email = $('#email'), pw = $('#password');

  $('#pwToggle').addEventListener('click', function () {
    var show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    this.setAttribute('aria-pressed', String(show));
    this.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    pw.focus();
  });

  function fail(msg, field) {
    $('#loginAlertText').textContent = msg;
    $('#loginAlert').hidden = false;
    form.classList.remove('is-shake'); void form.offsetWidth; form.classList.add('is-shake');
    if (field) { field.classList.add('is-invalid'); field.focus(); }
  }
  [email, pw].forEach(function (i) { i.addEventListener('input', function () { i.classList.remove('is-invalid'); $('#loginAlert').hidden = true; }); });

  form.addEventListener('submit', function (e) {
    if (!/^\S+@\S+\.\S+$/.test(email.value.trim())) { e.preventDefault(); return fail('Masukkan email yang valid.', email); }
    if (!pw.value) { e.preventDefault(); return fail('Kata sandi wajib diisi.', pw); }
    $('#loginBtn').setAttribute('aria-disabled', 'true');
    $('#loginLabel').innerHTML = '<span class="spinner" aria-hidden="true"></span> Memeriksa…';
  });

  // Gagal dari server: tampilkan getar dan fokus ke kata sandi
  if (!$('#loginAlert').hidden) {
    form.classList.add('is-shake');
    pw.focus();
  }

  document.querySelectorAll('[data-year]').forEach(function (y) { y.textContent = new Date().getFullYear(); });
})();
