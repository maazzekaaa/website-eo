@extends('layouts.site')

@section('title', $service->name.' — FZM Production Mediatama')
@section('description', (string) $service->description)
@section('wa-text', 'Halo FZM, saya ingin bertanya tentang layanan '.$service->name.'.')

@section('content')
  <main class="page-top">
    <div class="container">
      <div class="page-head-site">
        <div class="sec-label" data-reveal><a class="link-arrow meta" href="{{ route('layanan.index') }}"><span>← Semua layanan</span></a>@if ($service->badge)<span class="svc-badge">({{ ucfirst($service->badge) }})</span>@endif</div>
        <h1 class="split"><span class="line"><span>{{ $service->name }}</span></span></h1>
        <p data-reveal style="--d:2">{{ $service->description }}</p>
      </div>
      <div class="detail">
        <section class="detail-card" data-reveal aria-labelledby="hargaTitle">
          <h2 id="hargaTitle">Harga</h2>
          <ul class="price-list">
            @if ($service->isTier())
              @foreach ($service->options as $option)
                <li><span>{{ $option->name }}@if ($option->description)<br><small class="meta">{{ $option->description }}</small>@endif</span><strong>{{ rupiah($option->price) }}</strong></li>
              @endforeach
            @else
              <li><span>Per {{ $service->unit_label }}</span><strong>{{ rupiah($service->unit_price) }}</strong></li>
            @endif
          </ul>
        </section>
        <section class="detail-card" data-reveal style="--d:1">
          <h2>Tertarik?</h2>
          <p>Cek tanggal yang masih kosong dan susun pesananmu. DP 50% mengunci jadwal setelah dikonfirmasi admin.</p>
          <div class="detail-actions">
            <a class="btn btn-red" href="{{ route('pesan.create', ['layanan' => $service->slug]) }}">Tambah ke pesanan <span class="arrow" aria-hidden="true">→</span></a>
            <a class="btn btn-ghost" href="#" data-wa="{{ setting('wa_number') }}" data-text="Halo FZM, saya ingin bertanya tentang layanan {{ $service->name }}.">Tanya layanan ini</a>
          </div>
        </section>
      </div>
    </div>
  </main>
@endsection
