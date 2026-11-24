<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'FZM Production Mediatama — Event Organizer &amp; Produksi Panggung')</title>
  <meta name="description" content="@yield('description', 'Event organizer dan produksi panggung untuk pernikahan dan acara korporat: sound, lighting, videotron, rigging, dan siaran langsung. Cek jadwal dan pesan online dalam hitungan menit.')">
  <meta property="og:title" content="@yield('title', 'FZM Production Mediatama')">
  <meta property="og:description" content="@yield('description', 'Cek jadwal, susun layanan, dan pesan acara secara online.')">
  <meta name="theme-color" content="@yield('theme', '#FAFAFA')">
  {{-- .vt: peramban mendukung transisi antarhalaman bawaan; .no-vt: pudar dari site.js --}}
  <script>
    (function (h) {
      h.classList.add('js', 'onpagereveal' in window ? 'vt' : 'no-vt');
      try { if (!sessionStorage.getItem('fzmSeen')) { h.classList.add('first-visit'); sessionStorage.setItem('fzmSeen', '1'); } } catch (e) {}
    })(document.documentElement);
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@1&display=swap" rel="stylesheet">
  {{-- Inter Tight hanya untuk wordmark footer; text= memuat huruf yang dipakai saja --}}
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700&text=FZMPRODUCTI&display=swap" rel="stylesheet">
  <link rel="preload" href="{{ asset('assets/fonts/open-sauce-one-latin-800-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="{{ asset_v('assets/css/base.css') }}">
  <link rel="stylesheet" href="{{ asset_v('assets/css/site.css') }}">
</head>
<body>

  <x-site.nav />

  @yield('content')

  @unless (View::hasSection('no-footer'))
    <x-site.footer :compact="View::hasSection('compact-footer')" />
  @endunless

  @unless (View::hasSection('no-wa-float'))
    <x-site.wa-float :text="html_entity_decode(View::yieldContent('wa-text', 'Halo FZM, saya ingin bertanya tentang layanan.'))" :label="html_entity_decode(View::yieldContent('wa-label', 'Tanya via WhatsApp'))" />
  @endunless

  @stack('after')
  <script src="{{ asset_v('assets/js/site.js') }}"></script>
  @stack('scripts')
</body>
</html>
