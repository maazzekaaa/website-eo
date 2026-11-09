@extends('layouts.site')

@section('title', $portfolio->title.' — FZM Production Mediatama')
@section('description', (string) ($portfolio->caption ?: $portfolio->story))

@section('content')
  <main class="page-top">
    <div class="container">
      <div class="page-head-site">
        <div class="sec-label" data-reveal><a class="link-arrow meta" href="{{ route('portofolio.index') }}"><span>← Semua portofolio</span></a></div>
        <h1 class="split"><span class="line"><span>{{ $portfolio->title }}</span></span></h1>
      </div>
      <div class="detail">
        <section class="detail-card" data-reveal>
          <h2>Cerita acara</h2>
          <p class="prose">{{ $portfolio->story }}</p>
          @if ($portfolio->images->isNotEmpty())
            <div class="photo-grid">
              @foreach ($portfolio->images as $image)
                <img src="{{ $image->url() }}" alt="{{ $image->caption ?: $portfolio->title }}" loading="lazy">
              @endforeach
            </div>
          @endif
        </section>
        <section class="detail-card" data-reveal style="--d:1">
          <h2>Detail</h2>
          <ul class="price-list">
            <li><span>Kategori</span><strong>{{ $portfolio->category }}</strong></li>
            @if ($portfolio->event_date)<li><span>Tanggal</span><strong>{{ $portfolio->event_date->translatedFormat('F Y') }}</strong></li>@endif
            @if ($portfolio->client)<li><span>Klien</span><strong><a href="{{ route('klien.show', $portfolio->client) }}">{{ $portfolio->client->name }}</a></strong></li>@endif
            @if ($portfolio->location)<li><span>Lokasi</span><strong>{{ $portfolio->location }}</strong></li>@endif
            @if ($portfolio->services)<li><span>Layanan</span><strong>{{ $portfolio->services }}</strong></li>@endif
          </ul>
          <a class="btn btn-red" href="{{ route('pesan.create') }}">Pesan layanan serupa <span class="arrow" aria-hidden="true">→</span></a>
        </section>
      </div>
    </div>
  </main>
@endsection
