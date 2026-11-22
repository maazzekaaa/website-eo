<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'FZM Production Mediatama — Event Organizer &amp; Produksi Panggung')</title>
  <meta name="description" content="@yield('description', 'Sound, lighting, videotron, rigging, dan multimedia untuk pernikahan, acara korporat, konser, dan photoshoot. Cek jadwal dan pesan online.')">
  <meta property="og:title" content="@yield('title', 'FZM Production Mediatama')">
  <meta property="og:description" content="@yield('description', 'Cek jadwal, susun layanan, dan pesan acara secara online.')">
  <meta name="theme-color" content="@yield('theme', '#FFFFFF')">
  <script>document.documentElement.classList.add('js')</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Inter+Tight:wght@500;600;700&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body>

  <a class="brand-pill" href="{{ route('home') }}" aria-label="FZM Production, beranda">FZM</a>
  @hasSection('corner')
    <span class="corner-pill" id="cornerPill">@yield('corner')</span>
  @endif

  <nav class="nav" aria-label="Navigasi utama">
    <div class="nav-links">
      <a href="{{ route('home') }}"@if (request()->routeIs('home')) aria-current="page"@endif>Beranda</a>
      <a href="{{ route('layanan.index') }}"@if (request()->routeIs('layanan.*')) aria-current="page"@endif>Layanan</a>
      <a href="{{ route('portofolio.index') }}"@if (request()->routeIs('portofolio.*')) aria-current="page"@endif>Portofolio</a>
      <a href="{{ route('klien.index') }}"@if (request()->routeIs('klien.*')) aria-current="page"@endif>Klien</a>
      <a href="{{ route('tentang') }}"@if (request()->routeIs('tentang')) aria-current="page"@endif>Tentang</a>
      <a href="{{ route('kontak') }}"@if (request()->routeIs('kontak')) aria-current="page"@endif>Kontak</a>
    </div>
    <div class="nav-actions">
      <a class="btn nav-cta" href="{{ route('pesan.create') }}"@if (request()->routeIs('pesan.*')) aria-current="page"@endif>Pesan</a>
      <button class="icon-btn nav-burger" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="navSheet"><span></span></button>
    </div>
  </nav>
  <div class="nav-sheet" id="navSheet" hidden>
    <a href="{{ route('home') }}">Beranda</a>
    <a href="{{ route('layanan.index') }}">Layanan</a>
    <a href="{{ route('portofolio.index') }}">Portofolio</a>
    <a href="{{ route('klien.index') }}">Klien</a>
    <a href="{{ route('tentang') }}">Tentang</a>
    <a href="{{ route('kontak') }}">Kontak</a>
    <a href="{{ route('cari') }}">Cari</a>
    <a class="btn btn-red" href="{{ route('pesan.create') }}">Pesan Sekarang</a>
  </div>

  @yield('content')

  @unless (View::hasSection('no-footer'))
    <footer class="footer" @hasSection('compact-footer') style="padding-top:0" @endif>
      <div class="container">
        @unless (View::hasSection('compact-footer'))
          <div class="footer-top">
            <div>
              <p class="footer-intro">Produksi panggung untuk acara yang ingin dikenang.</p>
              <span class="footer-status">Menerima pesanan</span>
            </div>
            <div>
              <h4>Navigasi</h4>
              <ul>
                <li><a class="link-arrow" href="{{ route('layanan.index') }}"><span>Layanan</span></a></li>
                <li><a class="link-arrow" href="{{ route('portofolio.index') }}"><span>Portofolio</span></a></li>
                <li><a class="link-arrow" href="{{ route('pesan.create') }}"><span>Pesan</span></a></li>
                <li><a class="link-arrow" href="{{ route('cari') }}"><span>Cari</span></a></li>
                <li><a class="link-arrow" href="{{ route('kontak') }}"><span>Kontak</span></a></li>
              </ul>
            </div>
            <div>
              <h4>Kontak</h4>
              <ul><li>{{ setting('address') }}</li><li>WhatsApp {{ setting('wa_number') }}</li><li>{{ setting('opening_hours') }}</li></ul>
            </div>
            @if (setting('instagram_url') || setting('tiktok_url') || setting('youtube_url'))
              <div>
                <h4>Sosial</h4>
                <ul>
                  @if (setting('instagram_url'))<li><a class="link-arrow" href="{{ setting('instagram_url') }}" target="_blank" rel="noopener"><span>Instagram</span></a></li>@endif
                  @if (setting('tiktok_url'))<li><a class="link-arrow" href="{{ setting('tiktok_url') }}" target="_blank" rel="noopener"><span>TikTok</span></a></li>@endif
                  @if (setting('youtube_url'))<li><a class="link-arrow" href="{{ setting('youtube_url') }}" target="_blank" rel="noopener"><span>YouTube</span></a></li>@endif
                </ul>
              </div>
            @endif
          </div>
        @endunless
        <div class="footer-mark" aria-hidden="true">FZM</div>
        <div class="footer-bottom"><span class="meta">© <span data-year>{{ date('Y') }}</span> {{ setting('company_name') }}</span><span class="meta">Event organizer &amp; produksi panggung</span></div>
      </div>
    </footer>
  @endunless

  @unless (View::hasSection('no-wa-float'))
    <a class="wa-float" href="#" data-wa="{{ setting('wa_number') }}" data-text="@yield('wa-text', 'Halo FZM, saya ingin bertanya tentang layanan.')" aria-label="Tanya via WhatsApp">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-5A8 8 0 1 1 21 12z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5"/></svg>
      <span>@yield('wa-label', 'Tanya via WhatsApp')</span>
    </a>
  @endunless

  @stack('after')
  <script src="{{ asset('assets/js/site.js') }}"></script>
  @stack('scripts')
</body>
</html>
