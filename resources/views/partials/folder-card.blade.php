@php
  $photos = $portfolio->images->map(fn ($image) => $image->url())->values();
  $count = $photos->count();
  $year = $portfolio->event_date?->format('Y');
  $serviceLabel = str_replace(', ', ' · ', (string) $portfolio->services);
@endphp
<button class="folder folder--{{ $portfolio->folder_color }}" type="button" data-reveal style="--d:{{ $loop->index % 4 }}"
  data-category="{{ $portfolio->category }}" data-title="{{ $portfolio->title }}"
  data-date="{{ $portfolio->event_date?->translatedFormat('F Y') }}" data-client="{{ $portfolio->client?->name ?? 'Klien Pribadi' }}"
  data-location="{{ $portfolio->location }}" data-services="{{ $portfolio->services }}" data-count="{{ max($count, 1) }}"
  data-photos="{{ $photos->toJson() }}" data-story="{{ $portfolio->story }}" aria-label="Buka galeri {{ $portfolio->title }}">
  <span class="folder-icon" aria-hidden="true"><span class="folder-shadow"></span><span class="folder-tab"></span><span class="folder-back"></span>
    <span class="folder-paper folder-paper--1">@if ($portfolio->images->get(0))<img src="{{ $portfolio->images->get(0)->thumbUrl() }}" alt="" loading="lazy">@else<span class="ph"></span>@endif</span>
    <span class="folder-paper folder-paper--2">@if ($portfolio->images->get(1))<img src="{{ $portfolio->images->get(1)->thumbUrl() }}" alt="" loading="lazy">@else<span class="ph ph--alt"></span>@endif</span>
    <span class="folder-front"><span class="folder-cat">{{ $portfolio->category }}</span><span class="folder-count">{{ $count }} foto</span></span></span>
  <span class="folder-meta"><span class="folder-title">{{ $portfolio->title }}</span><span class="folder-cap">{{ $portfolio->caption }}</span><span class="folder-info"><span class="meta">{{ collect([$portfolio->location, $year])->filter()->implode(' · ') }}</span><span class="meta">{{ $serviceLabel }}</span></span></span>
</button>
