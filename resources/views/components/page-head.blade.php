{{-- Komponen: kepala halaman dalam (label merah, judul besar, paragraf pembuka).
     Pemakaian:
       <x-page-head eyebrow="Harga transparan" lead="…">Layanan &amp; <x-accent>harga.</x-accent></x-page-head>
     Slot opsional: <x-slot:back> (tautan kembali + tag), <x-slot:lead> (lead dengan HTML), <x-slot:after>. --}}
@props(['eyebrow' => null, 'lead' => null, 'size' => null])
<div {{ $attributes->class('page-head') }}>
  @isset($back)<div class="page-back">{{ $back }}</div>@endisset
  @if ($eyebrow)<span class="eyebrow">{{ $eyebrow }}</span>@endif
  <h1 @class(['page-title', 'page-title--md' => $size === 'md']) data-words>{{ $slot }}</h1>
  @if ($lead)<p class="page-lead">{{ $lead }}</p>@endif
  {{ $after ?? '' }}
</div>
