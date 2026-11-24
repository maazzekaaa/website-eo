<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <x-favicon />
  <meta name="robots" content="noindex">
  <title>Masuk — FZM Admin</title>
  <meta name="theme-color" content="#101010">
  <script>document.documentElement.classList.add('js')</script>
  <link rel="stylesheet" href="{{ asset_v('assets/css/base.css') }}">
  <link rel="stylesheet" href="{{ asset_v('assets/css/admin.css') }}">
</head>
<body>
  <div class="auth">
    <aside class="auth-art" id="authArt" aria-hidden="true">
      <a class="brand" href="{{ route('home') }}" tabindex="-1"><span class="brand-mark"><x-logo-mark /></span><span class="brand-name">FZM Admin</span></a>
      <div>
        <h2>Kelola pesanan dan konten dalam satu tempat.</h2>
        <p>Pesanan, pembayaran, jadwal, dan portofolio. Khusus pengelola {{ setting('company_name') }}.</p>
      </div>
      <span class="meta">© <span data-year>{{ date('Y') }}</span> FZM Production Mediatama</span>
    </aside>
    <main class="auth-main">
      <form class="auth-card" id="loginForm" method="post" action="{{ route('admin.login.attempt') }}" novalidate>
        @csrf
        <h1>Masuk</h1>
        <p>Gunakan email dan kata sandi admin yang diberikan pengelola.</p>

        <div class="auth-alert" id="loginAlert" role="alert" @unless ($errors->any()) hidden @endunless>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
          <span id="loginAlertText">{{ $errors->first() ?: 'Email atau kata sandi salah.' }}</span>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input class="input" id="email" name="email" type="email" autocomplete="username" placeholder="nama@email.com" value="{{ old('email') }}" required>
        </div>
        <div class="field">
          <label for="password">Kata sandi</label>
          <div class="pw-wrap">
            <input class="input" id="password" name="password" type="password" autocomplete="current-password" placeholder="••••••••" required>
            <button class="pw-toggle" type="button" id="pwToggle" aria-label="Tampilkan kata sandi" aria-pressed="false">
              {{-- Mata tertutup saat sandi disembunyikan, mata terbuka saat sandi ditampilkan --}}
              <svg class="pw-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.6 5.1A10.6 10.6 0 0 1 12 5c6.5 0 10 7 10 7a17.6 17.6 0 0 1-2.6 3.5M6.6 6.6C3.7 8.4 2 12 2 12s3.5 7 10 7c1.9 0 3.6-.6 5-1.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><path d="M3 3l18 18"/></svg>
              <svg class="pw-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>
        <div class="auth-row">
          <label class="check"><input type="checkbox" id="remember" name="remember"> Ingat saya</label>
          <a class="link-arrow" href="mailto:{{ setting('email') }}?subject=Reset%20kata%20sandi%20admin"><span>Lupa kata sandi</span></a>
        </div>
        <button class="btn btn-red btn-lg btn-block" type="submit" id="loginBtn"><span id="loginLabel">Masuk</span> <span class="arrow" aria-hidden="true">→</span></button>
        <a class="auth-back" href="{{ route('home') }}">‹ Kembali ke website</a>
      </form>
    </main>
  </div>
  <script src="{{ asset_v('assets/js/login.js') }}"></script>
</body>
</html>
