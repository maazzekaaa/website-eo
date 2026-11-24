@extends('layouts.site')

@section('title', 'Cari — FZM Production Mediatama')
@section('description', 'Cari layanan dan portofolio FZM Production.')

@section('content')
  <main class="page-top">
    <div class="container">
      <x-page-head eyebrow="Layanan & portofolio">
        <x-accent>Cari.</x-accent>
        <x-slot:after>
        <form class="search-form" method="get" action="{{ route('cari') }}" role="search">
          <label class="sr-only" for="q">Kata kunci</label>
          <input class="input" id="q" type="search" name="q" value="{{ $q }}" placeholder="Contoh: videotron, wedding">
          <button class="btn" type="submit">Cari</button>
        </form>
        </x-slot:after>
      </x-page-head>

      @if ($q !== '')
        @if ($services->isEmpty() && $portfolios->isEmpty())
          <p class="search-empty">Tidak ada hasil untuk “{{ $q }}”.</p>
        @endif
        @if ($services->isNotEmpty())
          <section class="search-group">
            <h2>Layanan</h2>
            <ul class="search-list">
              @foreach ($services as $service)
                <li><a class="link-arrow" href="{{ route('layanan.show', $service->slug) }}"><span>{{ $service->name }}</span></a><span class="search-sub">{{ $service->priceNote() }} · {{ $service->priceLabel() }}</span></li>
              @endforeach
            </ul>
          </section>
        @endif
        @if ($portfolios->isNotEmpty())
          <section class="search-group">
            <h2>Portofolio</h2>
            <ul class="search-list">
              @foreach ($portfolios as $portfolio)
                <li><a class="link-arrow" href="{{ route('portofolio.show', $portfolio) }}"><span>{{ $portfolio->title }}</span></a><span class="search-sub">{{ $portfolio->categoryLabel() }}</span></li>
              @endforeach
            </ul>
          </section>
        @endif
      @endif
    </div>
  </main>
@endsection
