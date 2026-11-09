@extends('layouts.site')

@section('content')
  <main>
    <header class="hero">
      <div class="container">
        <div class="hero-top" data-reveal>
          <span class="meta">(FZM) Event Organizer &amp; Produksi Panggung</span>
          <span class="meta">Indonesia · WIB</span>
        </div>
        <h1 class="split">
          @foreach ($heroLines as $line)
            <span class="line"><span style="--d:{{ $loop->index }}">@if ($loop->last && str_contains($line, ' ')){{ Str::beforeLast($line, ' ') }} <span class="text-gradient">{{ Str::afterLast($line, ' ') }}</span>@else{{ $line }}@endif</span></span>
          @endforeach
        </h1>
        <div class="hero-bottom">
          <p class="hero-lead" data-reveal style="--d:3">{{ setting('hero_subtitle') }}</p>
          <div class="hero-actions" data-reveal style="--d:4">
            <a class="btn btn-red btn-lg" href="{{ route('pesan.create') }}">Cek jadwal &amp; pesan <span class="arrow" aria-hidden="true">→</span></a>
            <a class="btn btn-ghost btn-lg" href="#portofolio">Lihat portofolio</a>
          </div>
        </div>

        <div class="hero-media" data-reveal style="--d:5" aria-hidden="true">
          <div class="hero-media-inner">
            @if (setting('hero_image'))
              <img src="{{ Storage::disk('public')->url(setting('hero_image')) }}" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover">
            @else
              <span class="beam beam--1"></span><span class="beam beam--2"></span><span class="beam beam--3"></span>
              <div class="lamps"><i></i><i></i><i></i></div>
              <div class="led" id="led"></div>
              <div class="floor"></div>
            @endif
          </div>
          @if ($portfolios->first())
            <div class="hero-caption"><span class="meta">{{ $portfolios->first()->title }}@if ($portfolios->first()->event_date) — {{ $portfolios->first()->event_date->translatedFormat('F Y') }}@endif</span><span class="meta">{{ str_replace(', ', ' · ', (string) $portfolios->first()->services) }}</span></div>
          @endif
        </div>
      </div>

      <div class="marquee" aria-hidden="true">
        <div class="marquee-track">
          @foreach ($services as $service)<span>{{ $service->name }}</span>@endforeach
        </div>
      </div>
    </header>

    <section class="section" id="layanan">
      <div class="container">
        <div class="sec-head">
          <div>
            <div class="sec-label" data-reveal><span class="meta-num">(01)</span><span class="meta">Layanan &amp; harga</span></div>
            <h2 class="split"><span class="line"><span>Susun kebutuhan</span></span><span class="line"><span style="--d:1">panggungmu.</span></span></h2>
          </div>
          <p data-reveal style="--d:2">Pilih paket atau isi ukuran per meter. Total dan DP 50% langsung terlihat saat memesan.</p>
        </div>
        <ul class="svc-list">
          @foreach ($services as $service)
            @include('partials.service-row')
          @endforeach
        </ul>
      </div>
    </section>

    <section class="section section--paper" id="portofolio">
      <div class="container">
        <div class="sec-head">
          <div>
            <div class="sec-label" data-reveal><span class="meta-num">(02)</span><span class="meta">Portofolio</span></div>
            <h2 class="split"><span class="line"><span>Arsip acara yang</span></span><span class="line"><span style="--d:1">pernah kami tangani.</span></span></h2>
          </div>
          <p data-reveal style="--d:2">Arahkan kursor ke folder untuk mengintip isinya, klik untuk membuka galeri foto acara. <a class="link-arrow" href="{{ route('portofolio.index') }}"><span>Lihat semua</span></a></p>
        </div>

        @include('partials.folder-filters')

        <div class="folder-grid" id="folderGrid">
          @foreach ($portfolios as $portfolio)
            @include('partials.folder-card')
          @endforeach
        </div>
      </div>
    </section>

    <section class="section" id="klien">
      <div class="container">
        <div class="sec-head">
          <div>
            <div class="sec-label" data-reveal><span class="meta-num">(03)</span><span class="meta">Klien &amp; mitra</span></div>
            <h2 class="split"><span class="line"><span>Dipercaya oleh</span></span><span class="line"><span style="--d:1">mitra kami.</span></span></h2>
          </div>
          <p data-reveal style="--d:2"><a class="link-arrow" href="{{ route('klien.index') }}"><span>Lihat semua klien &amp; proyeknya</span></a></p>
        </div>
        <div class="clients" data-reveal>
          @foreach ($clients as $client)
            @include('partials.client-tile')
          @endforeach
        </div>
      </div>
    </section>

    <section class="section" style="padding-top:0">
      <div class="container">
        <div class="cta" data-reveal>
          <div class="cta-row">
            <div>
              <span class="meta" style="color:rgba(255,255,255,.6)">(04) Pemesanan</span>
              <h2 style="margin-top:16px">Tanggal acaramu masih tersedia?</h2>
              <p>Cek kalender, susun layanan, dan kirim pesanan. Kami lanjutkan lewat WhatsApp.</p>
            </div>
            <a class="btn btn-lg" href="{{ route('pesan.create') }}">Cek jadwal &amp; pesan <span class="arrow" aria-hidden="true">→</span></a>
          </div>
        </div>
      </div>
    </section>
  </main>
@endsection

@push('after')
  @include('partials.lightbox')
@endpush
