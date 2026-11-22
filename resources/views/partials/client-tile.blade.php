<a class="client" href="{{ route('klien.show', $client) }}" @if ($hidden ?? false) tabindex="-1" aria-hidden="true" @endif>
  <span class="client-logo">@if ($client->logo)<img src="{{ Storage::disk('public')->url($client->logo) }}" alt="{{ $client->name }}">@else{{ $client->name }}@endif</span>
  <span class="meta">{{ $client->portfolios_count }} proyek</span>
</a>
