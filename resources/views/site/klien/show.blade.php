@extends('layouts.site')

@section('title', $client->name.' — FZM Production Mediatama')
@section('description', (string) ($client->description ?: 'Proyek FZM Production bersama '.$client->name.'.'))

@section('content')
  <main class="page-top">
    <div class="container">
      <div class="page-head-site">
        <div class="sec-label" data-reveal><a class="link-arrow meta" href="{{ route('klien.index') }}"><span>← Semua klien</span></a></div>
        <h1 class="split"><span class="line"><span>{{ $client->name }}</span></span></h1>
        @if ($client->description)<p data-reveal style="--d:2">{{ $client->description }}</p>@endif
        @if ($client->website)<p data-reveal style="--d:3"><a class="link-arrow" href="{{ $client->website }}" target="_blank" rel="noopener"><span>Kunjungi website</span></a></p>@endif
      </div>
      <div class="folder-grid" id="folderGrid">
        @forelse ($client->portfolios as $portfolio)
          @include('partials.folder-card')
        @empty
          <p class="search-empty">Belum ada proyek yang ditampilkan.</p>
        @endforelse
      </div>
    </div>
  </main>
@endsection

@push('after')
  @include('partials.lightbox')
@endpush
