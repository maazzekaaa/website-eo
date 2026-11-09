/* =========================================================
   FZM Production v2 — login.js
   Prototipe login: validasi, tampil/sembunyi kata sandi,
   status memuat, getar saat gagal, lalu masuk ke dashboard.
   Di Django: autentikasi di server (django.contrib.auth),
   CSRF, dan pembatasan percobaan login.
   ========================================================= */
(function () {
  'use strict';
  var $ = function (s) { return document.querySelector(s); };
  var DEMO = { email: 'admin@fzm.id', password: 'admin123' };
  var form = $('#loginForm'), email = $('#email'), pw = $('#password');
  var tries = 0, lockUntil = 0;

  function store(remember) {
    try {
      // Prototipe: sesi 8 jam, "Ingat saya" 30 hari (Django: SESSION_COOKIE_AGE / set_expiry)
      var ttl = remember ? 30 * 24 * 3600e3 : 8 * 3600e3;
      localStorage.setItem('fzm_admin', JSON.stringify({ email: email.value.trim(), exp: Date.now() + ttl }));
    } catch (e) { /* penyimpanan tidak tersedia: tetap lanjut */ }
  }
  function loggedIn() {
    try { var s = JSON.parse(localStorage.getItem('fzm_admin') || 'null'); return !!(s && s.exp > Date.now()); } catch (e) { return false; }
  }
  if (loggedIn()) location.replace('index.html');

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
    e.preventDefault();
    if (Date.now() < lockUntil) return fail('Terlalu banyak percobaan. Coba lagi dalam ' + Math.ceil((lockUntil - Date.now()) / 1000) + ' detik.');
    if (!/^\S+@\S+\.\S+$/.test(email.value.trim())) return fail('Masukkan email yang valid.', email);
    if (!pw.value) return fail('Kata sandi wajib diisi.', pw);

    var btn = $('#loginBtn');
    btn.setAttribute('aria-disabled', 'true');
    $('#loginLabel').innerHTML = '<span class="spinner" aria-hidden="true"></span> Memeriksa…';

    setTimeout(function () {                               // simulasi permintaan ke server
      btn.removeAttribute('aria-disabled');
      $('#loginLabel').textContent = 'Masuk';
      if (email.value.trim().toLowerCase() === DEMO.email && pw.value === DEMO.password) {
        store($('#remember').checked);
        $('#loginLabel').textContent = 'Berhasil';
        document.body.style.transition = 'opacity .5s cubic-bezier(.22,1,.36,1)';
        document.body.style.opacity = '0';
        setTimeout(function () { location.href = 'index.html'; }, 450);
      } else {
        tries++;
        if (tries >= 5) { lockUntil = Date.now() + 30000; tries = 0; }
        fail('Email atau kata sandi salah.', pw);
        pw.select();
      }
    }, 900);
  });

  // Sorotan cursor di panel visual
  var art = $('#authArt');
  art.addEventListener('pointermove', function (e) {
    var r = art.getBoundingClientRect();
    art.style.setProperty('--mx', (e.clientX - r.left) + 'px');
    art.style.setProperty('--my', (e.clientY - r.top) + 'px');
  });
  document.querySelectorAll('[data-year]').forEach(function (y) { y.textContent = new Date().getFullYear(); });
})();
