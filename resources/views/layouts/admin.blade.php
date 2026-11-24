<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <x-favicon />
  <meta name="robots" content="noindex">
  <title>@yield('title', 'Dashboard') — FZM Admin</title>
  <meta name="theme-color" content="#FAFAFA">
  <script>document.documentElement.classList.add('js')</script>
  <link rel="preload" href="{{ asset('assets/fonts/open-sauce-one-latin-800-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="{{ asset_v('assets/css/base.css') }}">
  <link rel="stylesheet" href="{{ asset_v('assets/css/admin.css') }}">
</head>
<body class="admin-body" data-upload-limit="{{ \Illuminate\Http\UploadedFile::getMaxFilesize() }}">
<div class="app">
  <div class="main">
    <div class="nav-progress" aria-hidden="true"></div>
    <header class="topbar">
      <a class="brand" href="{{ route('admin.dashboard') }}" data-nav-link><span class="brand-mark"><x-logo-mark /></span><span class="brand-name">FZM Admin</span></a>
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
          <button class="icon-btn avatar-btn" type="button" data-dd aria-expanded="false" aria-label="Menu akun"><span class="avatar">AD</span></button>
          <div class="dd-menu" role="menu" style="width:240px">
            <div style="padding:10px"><b style="font-size:15px">{{ auth('admin')->user()->name }}</b><br><small style="color:var(--muted)">{{ auth('admin')->user()->email }}</small></div>
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

  {{-- Navbar admin menempel penuh di dasar halaman --}}
  <div class="admin-bar">
    <nav class="admin-nav" aria-label="Menu admin">
      <span class="admin-nav-brand"><span class="brand-mark"><x-logo-mark /></span>Admin</span>
      <a href="{{ route('admin.dashboard') }}" data-nav="dashboard" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>Dashboard</a>
      <a href="{{ route('admin.bookings.index') }}" data-nav="bookings" @if (request()->routeIs('admin.bookings.*')) aria-current="page" @endif><span>Pemesanan</span>@if ($pendingCount)<span class="nav-count">{{ $pendingCount }}</span>@endif</a>
      <a href="{{ route('admin.services.index') }}" data-nav="services" @if (request()->routeIs('admin.services.*')) aria-current="page" @endif>Layanan</a>
      <a href="{{ route('admin.portfolios.index') }}" data-nav="portfolios" @if (request()->routeIs('admin.portfolios.*')) aria-current="page" @endif>Portofolio</a>
      <a href="{{ route('admin.clients.index') }}" data-nav="clients" @if (request()->routeIs('admin.clients.*')) aria-current="page" @endif>Klien</a>
      <a href="{{ route('admin.settings.edit') }}" data-nav="settings" @if (request()->routeIs('admin.settings.*')) aria-current="page" @endif>Pengaturan</a>
      <a href="#" class="admin-nav-out" data-logout>Keluar</a>
    </nav>
  </div>
</div>
<div class="toasts" aria-live="polite"></div>
<form id="logoutForm" method="post" action="{{ route('admin.logout') }}" hidden>@csrf</form>
@php
  $flash = ['ok' => session('toast'), 'error' => session('toast_error') ?: $errors->first()];
@endphp
<script id="flashData" type="application/json">@json($flash)</script>
<script src="{{ asset_v('assets/js/admin.js') }}"></script>
{{-- Skrip khusus halaman; ditukar oleh navigasi tanpa muat ulang di admin.js --}}
<div id="pageScripts" hidden>@stack('scripts')</div>
</body>
</html>
