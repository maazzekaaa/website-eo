@extends('layouts.site')

@section('title', 'Portofolio — FZM Production Mediatama')
@section('description', 'Arsip acara yang pernah ditangani FZM Production: wedding, corporate, concert, dan photoshoot.')

@section('content')
  <main class="page-top">
    <div class="container">
      <div class="page-head-site">
        <div class="sec-label" data-reveal><span class="meta-num">(Portofolio)</span><span class="meta">Arsip acara</span></div>
        <h1 class="split"><span class="line"><span>Portofolio</span></span></h1>
        <p data-reveal style="--d:2">Arahkan kursor ke folder untuk mengintip isinya, klik untuk membuka galeri foto acara.</p>
      </div>
      @include('partials.folder-filters')
      <div class="folder-grid" id="folderGrid">
        @forelse ($portfolios as $portfolio)
          @include('partials.folder-card')
        @empty
          <p class="search-empty">Belum ada portofolio.</p>
        @endforelse
      </div>
    </div>
  </main>
@endsection

@push('after')
  @include('partials.lightbox')
@endpush
