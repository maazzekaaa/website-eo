@extends('layouts.site')

@section('corner', $prints->isEmpty() ? '00 — 00' : '01 — '.str_pad((string) $prints->count(), 2, '0', STR_PAD_LEFT))

@section('content')
  <main>
    <header class="stage" id="stage">
      <div class="stage-copy">
        <h1 class="poster split"><span class="line"><span>FZM Production</span></span></h1>
        <span class="poster-rule" aria-hidden="true"></span>
        <p class="stage-title" data-reveal style="--d:1">{{ str_replace(["\r\n", "\n"], ' ', (string) setting('hero_title')) }}</p>
        <p class="poster-lead" data-reveal style="--d:2">{{ setting('hero_subtitle') }}</p>
        <div class="stage-actions" data-reveal style="--d:3">
          <a class="btn btn-red" href="{{ route('pesan.create') }}">Cek jadwal &amp; pesan <span class="arrow" aria-hidden="true">→</span></a>
          <a class="btn btn-ghost" href="{{ route('portofolio.index') }}">Lihat portofolio</a>
        </div>
      </div>

      <div class="deck" id="deck">
        @forelse ($prints as $print)
          <a class="deck-card" href="{{ $print['link'] }}" aria-label="Lihat foto {{ $print['title'] }}"
            data-title="{{ $print['title'] }}" data-client="{{ $print['client'] }}" data-category="{{ $print['category'] }}"
            data-location="{{ $print['location'] }}" data-date="{{ $print['date'] }}" data-photos="{{ json_encode($print['photos']) }}"
            data-index="{{ $print['index'] }}" data-no="{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}">
            <img src="{{ $print['url'] }}" alt="" draggable="false">
          </a>
        @empty
          @foreach (['', ' ph--alt', '', ' ph--alt'] as $variant)
            <span class="deck-card is-placeholder" aria-hidden="true"><span class="ph{{ $variant }}"></span></span>
          @endforeach
        @endforelse
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

@push('scripts')
  <script src="{{ asset('assets/js/stage.js') }}"></script>
@endpush
