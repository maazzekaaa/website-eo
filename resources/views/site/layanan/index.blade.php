@extends('layouts.site')

@section('title', 'Layanan & Harga — FZM Production Mediatama')
@section('description', 'Daftar layanan dan harga FZM Production: sound, lighting, videotron, rigging, dan multimedia.')

@section('content')
  <main class="page-top">
    <div class="container">
      <div class="page-head-site">
        <div class="sec-label" data-reveal><span class="meta-num">(Layanan)</span><span class="meta">Harga transparan</span></div>
        <h1 class="split"><span class="line"><span>Layanan &amp; harga</span></span></h1>
        <p data-reveal style="--d:2">Pilih paket atau isi ukuran per meter. Total dan DP 50% langsung terlihat saat memesan.</p>
      </div>
      <ul class="svc-list">
        @foreach ($services as $service)
          @include('partials.service-row')
        @endforeach
      </ul>
    </div>
  </main>
@endsection
