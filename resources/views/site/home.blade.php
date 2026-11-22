@extends('layouts.site')

@php
  $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
  $first = $slides->first();
@endphp

@section('corner', $slides->isEmpty() ? '00 — 00' : '01 — '.$pad($slides->count()))

@section('content')
  <main>
    <header class="stage" id="stage">
      <div class="stage-copy">
        <div class="sec-label" data-reveal><span class="meta-num">(00)</span><span class="meta">Event organizer · Portofolio</span></div>
        <h1 class="poster split"><span class="line"><span>FZM Production</span></span></h1>
        <span class="poster-rule" aria-hidden="true"></span>
        <p class="stage-title" data-reveal style="--d:1">{{ str_replace(["\r\n", "\n"], ' ', (string) setting('hero_title')) }}</p>
        <p class="poster-lead" data-reveal style="--d:2">{{ setting('hero_subtitle') }}</p>
        <div class="stage-actions" data-reveal style="--d:3">
          <a class="btn btn-red" href="{{ route('pesan.create') }}">Cek jadwal &amp; pesan <span class="arrow" aria-hidden="true">→</span></a>
          <a class="btn btn-ghost" href="{{ route('portofolio.index') }}">Lihat portofolio</a>
        </div>
        <dl class="stage-stats" data-reveal style="--d:4">
          <div><dt class="meta">Acara ditangani</dt><dd>{{ $pad($stats['events']) }}</dd></div>
          <div><dt class="meta">Klien &amp; mitra</dt><dd>{{ $pad($stats['clients']) }}</dd></div>
          <div><dt class="meta">Jenis acara</dt><dd>{{ $pad($stats['categories']) }}</dd></div>
        </dl>
      </div>

      <section class="showcase" id="showcase" aria-label="Proyek pilihan" aria-roledescription="carousel">
        <div class="sc-head">
          <span class="meta">Proyek pilihan</span>
          <a class="link-arrow sc-all" href="{{ route('portofolio.index') }}"><span>Semua proyek</span></a>
        </div>

        <div class="sc-frame">
          @forelse ($slides as $slide)
            <a class="sc-slide{{ $loop->first ? ' is-active' : '' }}" href="{{ $slide['link'] }}" id="scSlide{{ $loop->index }}"
              @unless ($loop->first) aria-hidden="true" tabindex="-1" @endunless
              aria-label="{{ $slide['title'] }}, lihat proyek"
              data-no="{{ $pad($loop->iteration) }}" data-title="{{ $slide['title'] }}" data-category="{{ $slide['category'] }}"
              data-client="{{ $slide['client'] }}" data-location="{{ $slide['location'] }}" data-date="{{ $slide['date'] }}"
              data-photos="{{ $slide['photos'] }}">
              <img src="{{ $slide['url'] }}" alt="" draggable="false" @unless ($loop->first) loading="lazy" @endunless>
            </a>
          @empty
            <span class="sc-slide is-active is-placeholder" aria-hidden="true"><span class="ph"></span></span>
          @endforelse

          @if ($first)
            <div class="sc-caption" aria-live="polite">
              <span class="meta sc-meta"><span class="sc-no">({{ $pad(1) }})</span> <span class="sc-cat">{{ $first['category'] }}</span><span class="sc-loc">{{ $first['location'] ? ' · '.$first['location'] : '' }}</span></span>
              <h2 class="sc-title">{{ $first['title'] }}</h2>
              <span class="sc-sub"><span class="sc-client">{{ $first['client'] }}</span><span class="sc-date">{{ $first['date'] ? ' — '.$first['date'] : '' }}</span></span>
            </div>
            <span class="sc-open" aria-hidden="true">Lihat proyek ↗</span>
          @else
            <div class="sc-caption"><span class="meta sc-meta">Portofolio</span><h2 class="sc-title">Proyek segera hadir</h2></div>
          @endif
        </div>

        @if ($slides->count() > 1)
          <div class="sc-thumbs" role="tablist" aria-label="Pilih proyek">
            @foreach ($slides as $slide)
              <button class="sc-thumb" type="button" role="tab" aria-controls="scSlide{{ $loop->index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-i="{{ $loop->index }}">
                <img src="{{ $slide['url'] }}" alt="" loading="lazy">
                <span class="sc-thumb-no">{{ $pad($loop->iteration) }}</span>
                <span class="sr-only">{{ $slide['title'] }}</span>
                <span class="sc-bar" aria-hidden="true"></span>
              </button>
            @endforeach
          </div>
        @endif
      </section>
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

    <section class="section" id="klien">
      <div class="container">
        <div class="sec-head">
          <div>
            <div class="sec-label" data-reveal><span class="meta-num">(03)</span><span class="meta">Klien &amp; mitra</span></div>
            <h2 class="split"><span class="line"><span>Dipercaya oleh</span></span><span class="line"><span style="--d:1">mitra kami.</span></span></h2>
          </div>
          <p data-reveal style="--d:2"><a class="link-arrow" href="{{ route('klien.index') }}"><span>Lihat semua klien &amp; proyeknya</span></a></p>
        </div>
      </div>
      @if ($clients->isNotEmpty())
        @php
          // Ulangi daftar agar satu putaran selalu lebih lebar dari layar, lalu gandakan untuk loop mulus.
          $loopClients = collect();
          while ($loopClients->count() < 10) { $loopClients = $loopClients->concat($clients); }
        @endphp
        <div class="logo-strip" data-reveal aria-label="Daftar klien dan mitra">
          @foreach ([false, true] as $copy)
            <div class="logo-strip-track" @if ($copy) aria-hidden="true" @endif>
              @foreach ($loopClients as $client)
                @include('partials.client-tile', ['hidden' => $copy || $loop->index >= $clients->count()])
              @endforeach
            </div>
          @endforeach
        </div>
      @endif
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

@push('scripts')
  <script src="{{ asset('assets/js/stage.js') }}"></script>
@endpush
