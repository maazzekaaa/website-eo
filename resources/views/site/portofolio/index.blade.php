@extends('layouts.site')

@section('title', 'Portofolio — FZM Production Mediatama')
@section('description', 'Arsip acara yang pernah ditangani FZM Production: wedding, corporate, concert, dan photoshoot.')
@section('corner', $prints->count().' foto')
@section('no-footer', '1')

@section('content')
  <main class="table-page" id="tablePage">
    <div class="table-surface" id="tableSurface">
      @foreach ($prints as $print)
        @php($portfolio = $print['portfolio'])
        <button class="table-print folder--{{ $portfolio->folder_color }}" type="button"
          data-category="{{ $portfolio->category }}" data-title="{{ $portfolio->title }}"
          data-date="{{ $portfolio->event_date?->translatedFormat('j F Y') }}" data-client="{{ $portfolio->client?->name ?? 'Klien Pribadi' }}"
          data-location="{{ $portfolio->location }}" data-services="{{ $portfolio->services }}"
          data-count="{{ max($print['photos']->count(), 1) }}" data-photos="{{ $print['photos']->toJson() }}"
          data-index="{{ $print['index'] }}" data-no="{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}" aria-label="Buka galeri {{ $portfolio->title }}">
          @if ($print['thumb'])
            <img src="{{ $print['thumb'] }}" alt="" draggable="false" loading="lazy">
          @else
            <span class="ph" aria-hidden="true"></span>
          @endif
        </button>
      @endforeach
    </div>

    <div class="table-copy" id="tableCopy">
      <h1 class="poster split"><span class="line"><span>Portofolio</span></span></h1>
      <span class="poster-rule" aria-hidden="true"></span>
      <p class="poster-lead" data-reveal style="--d:2">Arsip acara yang pernah kami tangani, tersebar di atas meja. Klik foto untuk melihat galerinya.</p>
      <p class="table-hint meta" data-reveal style="--d:3"><span aria-hidden="true"></span>Geser untuk menjelajah</p>
      @include('partials.folder-filters')
    </div>

    @if ($prints->isEmpty())
      <p class="table-empty search-empty">Belum ada portofolio.</p>
    @endif
  </main>
@endsection

@push('after')
  @include('partials.lightbox')
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/portfolio-table.js') }}"></script>
@endpush
