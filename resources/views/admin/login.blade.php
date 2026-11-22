<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Masuk — FZM Admin</title>
  <meta name="theme-color" content="#FFFFFF">
  <script>document.documentElement.classList.add('js')</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Inter+Tight:wght@500;600;700&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
</head>
<body>
  <div class="auth">
    <aside class="auth-art" id="authArt" aria-hidden="true">
      <a class="brand" href="{{ route('home') }}" tabindex="-1"><span class="brand-mark">FZ</span><span class="brand-name">FZM Production</span></a>
      <div>
        <span class="meta">(Admin) Panel pengelola</span>
        <h2 style="margin-top:16px">Kelola panggung dari satu tempat.</h2>
        <p>Pesanan, pembayaran, jadwal, dan portofolio — semuanya di sini.</p>
      </div>
      <span class="meta">© <span data-year>{{ date('Y') }}</span> FZM Production Mediatama</span>
    </aside>
    <main class="auth-main">
      <form class="auth-card" id="loginForm" method="post" action="{{ route('admin.login.attempt') }}" novalidate>
        @csrf
        <a class="brand" href="{{ route('home') }}" style="margin-bottom:8px"><span class="brand-mark">FZ</span><span class="brand-name">FZM Admin</span></a>
        <h1>Masuk</h1>
        <p>Gunakan akun admin yang diberikan pengelola.</p>

        <div class="auth-alert" id="loginAlert" role="alert" @unless ($errors->any()) hidden @endunless>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
          <span id="loginAlertText">{{ $errors->first() ?: 'Email atau kata sandi salah.' }}</span>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input class="input" id="email" name="email" type="email" autocomplete="username" placeholder="admin@fzm.id" value="{{ old('email') }}" required>
        </div>
        <div class="field">
          <label for="password">Kata sandi</label>
          <div class="pw-wrap">
            <input class="input" id="password" name="password" type="password" autocomplete="current-password" placeholder="••••••••" required>
            <button class="pw-toggle" type="button" id="pwToggle" aria-label="Tampilkan kata sandi" aria-pressed="false">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>
        <div class="auth-row">
          <label class="check"><input type="checkbox" id="remember" name="remember"> Ingat saya</label>
          <a class="link-arrow" href="mailto:{{ setting('email') }}?subject=Reset%20kata%20sandi%20admin"><span>Lupa kata sandi</span></a>
        </div>
        <button class="btn btn-red btn-lg btn-block" type="submit" id="loginBtn"><span id="loginLabel">Masuk</span> <span class="arrow" aria-hidden="true">→</span></button>
        <a class="link-arrow" href="{{ route('home') }}" style="align-self:center;font-size:14px;color:var(--muted)"><span>Kembali ke website</span></a>
      </form>
    </main>
  </div>
  <script src="{{ asset('assets/js/login.js') }}"></script>
</body>
</html>
