{{-- Komponen: judul seksi (kiri) + keterangan singkat (kanan). Judul muncul kata per kata saat di-scroll. --}}
@props(['lead' => null, 'eyebrow' => null])
<div {{ $attributes->class('sec-head') }}>
  <div class="sec-title">
    @if ($eyebrow)<span class="eyebrow">{{ $eyebrow }}</span>@endif
    <h2 data-words>{{ $slot }}</h2>
  </div>
  @if ($lead)<p>{{ $lead }}</p>@endif
</div>
