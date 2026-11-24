{{-- Komponen: satu logo/nama klien di strip. --}}
@props(['client', 'hidden' => false])
<li class="client" @if ($hidden) aria-hidden="true" @endif>
  @if ($client->logo)<img src="{{ Storage::disk('public')->url($client->logo) }}" alt="{{ $client->name }}" loading="lazy">@else<span class="client-name">{{ $client->name }}</span>@endif
</li>
