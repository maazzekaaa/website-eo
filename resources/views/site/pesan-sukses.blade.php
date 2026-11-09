@extends('layouts.site')

@section('title', 'Pesanan terkirim — FZM Production Mediatama')
@section('theme', '#F7F7F6')
@section('nav-back', '1')
@section('no-footer', '1')
@section('no-wa-float', '1')

@section('content')
  <main class="order">
    <div class="container">
      <section class="success" aria-live="polite">
        <span class="check"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
        <div>
          <span class="meta">Kode booking</span>
          <h1 style="margin-top:6px">{{ $booking->booking_code }}</h1>
          <p>Status <b>menunggu</b>. Lanjutkan ke WhatsApp agar admin bisa mengirim invoice dan instruksi DP.</p>
        </div>
        <div class="wa-preview"><span class="meta">Pesan WhatsApp (terisi otomatis)</span><pre>{{ $message }}</pre></div>
        <div class="success-actions">
          <a class="btn btn-red btn-lg" href="{{ $waUrl }}" target="_blank" rel="noopener">Buka WhatsApp <span class="arrow" aria-hidden="true">→</span></a>
          <a class="btn btn-ghost btn-lg" href="{{ route('home') }}">Kembali ke beranda</a>
        </div>
      </section>
    </div>
  </main>
@endsection
