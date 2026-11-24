{{-- Komponen: daftar layanan bergaris (beranda & halaman Layanan). Harga ditandai merah. --}}
@props(['services'])
<ul class="svc-list">
  @foreach ($services as $service)
    <li class="svc-item" data-reveal style="--d:{{ $loop->index % 6 }}"><a href="{{ route('layanan.show', $service->slug) }}">
      <span class="svc-no">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
      <span class="svc-name">{{ $service->name }}@if ($service->badge)<span class="svc-badge">({{ ucfirst($service->badge) }})</span>@endif</span>
      <span class="svc-desc">{{ $service->description }}</span>
      <span class="svc-price"><small>{{ $service->priceNote() }}</small>{{ $service->priceLabel() }}</span>
      <span class="svc-go" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
    </a></li>
  @endforeach
</ul>
