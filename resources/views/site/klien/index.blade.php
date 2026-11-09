@extends('layouts.site')

@section('title', 'Klien & Mitra — FZM Production Mediatama')
@section('description', 'Klien dan mitra FZM Production beserta proyek yang pernah dikerjakan.')

@section('content')
  <main class="page-top">
    <div class="container">
      <div class="page-head-site">
        <div class="sec-label" data-reveal><span class="meta-num">(Klien)</span><span class="meta">Klien &amp; mitra</span></div>
        <h1 class="split"><span class="line"><span>Dipercaya oleh</span></span><span class="line"><span style="--d:1">mitra kami.</span></span></h1>
      </div>
      @if ($clients->isEmpty())
        <p class="search-empty">Belum ada klien.</p>
      @else
        <div class="clients" data-reveal>
          @foreach ($clients as $client)
            @include('partials.client-tile')
          @endforeach
        </div>
      @endif
    </div>
  </main>
@endsection
