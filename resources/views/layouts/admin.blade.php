<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>@yield('title', 'Dashboard') — FZM Admin</title>
  <meta name="theme-color" content="#F7F7F6">
  <script>document.documentElement.classList.add('js')</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Inter+Tight:wght@500;600;700&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
</head>
<body class="admin-body" data-upload-limit="{{ \Illuminate\Http\UploadedFile::getMaxFilesize() }}">
<div class="app">
  <aside class="side" aria-label="Sidebar admin">
    <div class="side-top">
      <a class="brand" href="{{ route('admin.dashboard') }}"><span class="brand-mark">FZ</span><span class="brand-name">FZM Admin</span></a>
      <button class="icon-btn side-collapse" type="button" aria-label="Lipat sidebar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="M9 4v16M15 10l-2 2 2 2"/></svg></button>
    </div>
    <span class="side-section">Menu</span>
    <nav class="side-nav" aria-label="Menu admin">
      <span class="side-ind" aria-hidden="true"></span>
      <a href="{{ route('admin.dashboard') }}" data-nav="dashboard" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/></svg><span class="side-label">Dashboard</span><span class="tip">Dashboard</span></a>
      <a href="{{ route('admin.bookings.index') }}" data-nav="bookings" @if (request()->routeIs('admin.bookings.*')) aria-current="page" @endif><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M3 10h18M8 3v4M16 3v4"/></svg><span class="side-label">Pemesanan</span>@if ($pendingCount)<span class="side-count">{{ $pendingCount }}</span>@endif<span class="tip">Pemesanan</span></a>
      <a href="{{ route('admin.services.index') }}" data-nav="services" @if (request()->routeIs('admin.services.*')) aria-current="page" @endif><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12l-8 8-9-9V3h8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg><span class="side-label">Layanan &amp; Harga</span><span class="tip">Layanan &amp; Harga</span></a>
      <a href="{{ route('admin.portfolios.index') }}" data-nav="portfolios" @if (request()->routeIs('admin.portfolios.*')) aria-current="page" @endif><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><span class="side-label">Portofolio</span><span class="tip">Portofolio</span></a>
      <a href="{{ route('admin.clients.index') }}" data-nav="clients" @if (request()->routeIs('admin.clients.*')) aria-current="page" @endif><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14a5 5 0 0 1 3.5 6"/></svg><span class="side-label">Klien / Mitra</span><span class="tip">Klien / Mitra</span></a>
      <a href="{{ route('admin.settings.edit') }}" data-nav="settings" @if (request()->routeIs('admin.settings.*')) aria-current="page" @endif><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/></svg><span class="side-label">Konten &amp; Pengaturan</span><span class="tip">Konten &amp; Pengaturan</span></a>
    </nav>
    <div class="side-user">
      <span class="avatar" aria-hidden="true">AD</span>
      <div class="side-user-info"><b>{{ auth('admin')->user()->name }}</b><a href="#" data-logout>Keluar ↗</a></div>
    </div>
  </aside>
  <div class="scrim" aria-hidden="true"></div>

  <div class="main">
    <header class="topbar">
      <button class="icon-btn topbar-menu" type="button" aria-label="Buka menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg></button>
      <form method="get" action="{{ route('admin.bookings.index') }}" style="display:contents">
        <label class="search">
          <span class="sr-only">Cari</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
          <input type="search" id="globalSearch" name="q" value="{{ request('q') }}" placeholder="Cari kode booking atau nama pemesan…" autocomplete="off">
          <kbd>/</kbd>
        </label>
      </form>
      <div class="topbar-actions">
        <a class="btn btn-sm btn-light" href="{{ route('home') }}" target="_blank" rel="noopener">Lihat website ↗</a>
        <div class="dd">
          <button class="icon-btn" type="button" data-dd aria-expanded="false" aria-label="Notifikasi, {{ $notifications->count() }} booking menunggu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            @if ($notifications->isNotEmpty())<span class="dot" aria-hidden="true"></span>@endif
          </button>
          <div class="dd-menu" role="menu">
            <h4>Notifikasi <span class="meta">{{ $pendingCount }} menunggu</span></h4>
            @forelse ($notifications as $item)
              <a class="dd-item" href="{{ route('admin.bookings.show', $item) }}" role="menuitem"><span class="ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M3 10h18"/></svg></span><span><b>{{ $item->payment_status === 'unpaid' ? 'Booking baru dari' : 'DP masuk,' }} {{ $item->customer_name }}</b><small>{{ $item->booking_code }} · {{ $item->event_date->translatedFormat('j M Y') }}</small></span></a>
            @empty
              <p class="empty" style="padding:16px">Tidak ada booking menunggu.</p>
            @endforelse
          </div>
        </div>
        <div class="dd">
          <button class="icon-btn" type="button" data-dd aria-expanded="false" aria-label="Menu akun" style="padding:0;box-shadow:none;background:none"><span class="avatar" style="width:40px;height:40px">AD</span></button>
          <div class="dd-menu" role="menu" style="width:240px">
            <div style="padding:10px"><b style="font-size:14px;font-weight:500">{{ auth('admin')->user()->name }}</b><br><small class="meta" style="text-transform:none">{{ auth('admin')->user()->email }}</small></div>
            <a class="dd-item" href="{{ route('admin.settings.edit') }}" role="menuitem"><span><b>Pengaturan</b></span></a>
            <a class="dd-item" href="#" data-logout role="menuitem"><span><b style="color:var(--red-ink)">Keluar</b></span></a>
          </div>
        </div>
      </div>
    </header>

    <main class="content">
      @yield('content')
    </main>
  </div>
</div>
<div class="toasts" aria-live="polite"></div>
<form id="logoutForm" method="post" action="{{ route('admin.logout') }}" hidden>@csrf</form>
@php
  $flash = ['ok' => session('toast'), 'error' => session('toast_error') ?: $errors->first()];
@endphp
<script id="flashData" type="application/json">@json($flash)</script>
<script src="{{ asset('assets/js/admin.js') }}"></script>
@stack('scripts')
</body>
</html>
