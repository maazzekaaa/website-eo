{{-- Komponen: strip logo klien yang berjalan (tanpa kotak, hanya garis batas).
     Daftar diulang agar satu putaran selalu lebih lebar dari layar, lalu digandakan untuk loop mulus. --}}
@props(['clients'])
@php
  $loopClients = collect();
  while ($clients->isNotEmpty() && $loopClients->count() < 10) { $loopClients = $loopClients->concat($clients); }
@endphp
<div class="logo-strip" aria-label="Daftar klien dan mitra">
  @foreach ([false, true] as $copy)
    <ul class="logo-strip-track" @if ($copy) aria-hidden="true" @endif>
      @foreach ($loopClients as $client)
        <x-client-tile :client="$client" :hidden="$copy || $loop->index >= $clients->count()" />
      @endforeach
    </ul>
  @endforeach
</div>
