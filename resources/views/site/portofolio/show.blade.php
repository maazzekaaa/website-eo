@extends('layouts.site')

@section('title', $portfolio->title.' — FZM Production Mediatama')
@section('description', (string) ($portfolio->caption ?: $portfolio->story))

@section('content')
  @php($cover = $portfolio->images->first())
  <main class="page-top">
    <div class="container">
      <x-page-head :eyebrow="$portfolio->client?->name ?? 'Klien pribadi'" :lead="$portfolio->caption">
        <x-slot:back><a class="back-link" href="{{ route('home') }}#portofolio">← Semua portofolio</a><span class="tag">{{ $portfolio->categoryLabel() }}</span></x-slot:back>
        {{ $portfolio->title }}
      </x-page-head>

      @if ($cover)
        {{-- Foto utama selebar kontainer: terbuka dari bawah lalu bergeser pelan saat di-scroll --}}
        <figure class="case-cover" data-clip>
          <img src="{{ $cover->url() }}" alt="{{ $cover->caption ?: $portfolio->title }}" data-parallax>
        </figure>
      @endif

      <div class="detail">
        <x-detail-card title="Di balik layar">
          <p class="prose">{{ $portfolio->story }}</p>
          @if ($portfolio->images->count() > 1)
            <div class="photo-grid">
              @foreach ($portfolio->images->skip(1) as $image)
                <img src="{{ $image->url() }}" alt="{{ $image->caption ?: $portfolio->title }}" loading="lazy" data-reveal style="--d:{{ $loop->index % 4 }}">
              @endforeach
            </div>
          @endif
        </x-detail-card>
        <x-detail-card title="Detail">
          <ul class="price-list">
            <li><span>Kategori</span><strong>{{ $portfolio->categoryLabel() }}</strong></li>
            @if ($portfolio->event_date)<li><span>Tanggal</span><strong>{{ $portfolio->event_date->translatedFormat('F Y') }}</strong></li>@endif
            @if ($portfolio->client)<li><span>Klien</span><strong class="accent">{{ $portfolio->client->name }}</strong></li>@endif
            @if ($portfolio->location)<li><span>Lokasi</span><strong>{{ $portfolio->location }}</strong></li>@endif
            @if ($portfolio->services)<li><span>Layanan</span><strong>{{ $portfolio->services }}</strong></li>@endif
          </ul>
          <a class="btn btn-red" href="{{ route('pesan.create') }}">Pesan layanan serupa <span class="arrow" aria-hidden="true">→</span></a>
        </x-detail-card>
      </div>
    </div>
  </main>
@endsection
