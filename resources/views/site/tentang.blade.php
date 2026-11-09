@extends('layouts.site')

@section('title', 'Tentang Kami — FZM Production Mediatama')
@section('description', 'Profil FZM Production Mediatama, event organizer dan penyedia produksi panggung.')

@section('content')
  <main class="page-top">
    <div class="container">
      <div class="page-head-site">
        <div class="sec-label" data-reveal><span class="meta-num">(Tentang)</span><span class="meta">Profil perusahaan</span></div>
        <h1 class="split"><span class="line"><span>Tentang {{ setting('company_name') }}</span></span></h1>
      </div>
      <div class="detail">
        <section class="detail-card" data-reveal>
          <p class="prose">{{ setting('about') }}</p>
        </section>
        <section class="detail-card" data-reveal style="--d:1">
          <h2>Hubungi kami</h2>
          <ul class="price-list">
            <li><span>Alamat</span><strong>{{ setting('address') }}</strong></li>
            <li><span>Jam</span><strong>{{ setting('opening_hours') }}</strong></li>
          </ul>
          <a class="btn btn-red" href="{{ route('kontak') }}">Kontak <span class="arrow" aria-hidden="true">→</span></a>
        </section>
      </div>
    </div>
  </main>
@endsection
