@extends('layouts.site')

@section('title', $service->name.' — FZM Production Mediatama')
@section('description', (string) $service->description)
@section('wa-text', 'Halo FZM, saya ingin bertanya tentang layanan '.$service->name.'.')

@section('content')
  <main class="page-top">
    <div class="container">
      <x-page-head :lead="$service->description">
        <x-slot:back><a class="back-link" href="{{ route('home') }}#layanan">← Semua layanan</a>@if ($service->badge)<span class="tag">{{ ucfirst($service->badge) }}</span>@endif</x-slot:back>
        {{ $service->name }}
      </x-page-head>
      <div class="detail">
        <x-detail-card title="Harga">
          <ul class="price-list">
            @if ($service->isTier())
              @foreach ($service->options as $option)
                <li><span>{{ $option->name }}@if ($option->description)<br><small class="price-note">{{ $option->description }}</small>@endif</span><strong class="accent">{{ rupiah($option->price) }}</strong></li>
              @endforeach
            @else
              <li><span>Per {{ $service->unit_label }}</span><strong class="accent">{{ rupiah($service->unit_price) }}</strong></li>
            @endif
          </ul>
        </x-detail-card>
        <x-detail-card title="Siap mewujudkannya?">
          <p>Pastikan tanggalmu masih tersedia, rangkai layanan yang dibutuhkan, lalu amankan jadwal dengan <strong class="accent">DP 50%</strong> setelah pesanan dikonfirmasi.</p>
          <div class="detail-actions">
            <a class="btn btn-red" href="{{ route('pesan.create', ['layanan' => $service->slug]) }}">Tambah ke pesanan <span class="arrow" aria-hidden="true">→</span></a>
            <a class="btn btn-ghost" href="#" data-wa="{{ setting('wa_number') }}" data-text="Halo FZM, saya ingin bertanya tentang layanan {{ $service->name }}.">Tanya layanan ini</a>
          </div>
        </x-detail-card>
      </div>
    </div>
  </main>
@endsection
