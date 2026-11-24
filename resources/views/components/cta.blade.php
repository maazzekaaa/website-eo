{{-- Komponen: blok ajakan hitam. Judul lewat slot (boleh berisi <x-accent>). --}}
@props(['lead' => null, 'href' => null, 'label' => 'Cek jadwal & pesan'])
<section class="section">
  <div class="container">
    <div class="cta">
      <div class="cta-copy">
        <h2 data-words>{{ $slot }}</h2>
        @if ($lead)<p>{{ $lead }}</p>@endif
      </div>
      <a class="btn btn-red btn-lg" href="{{ $href ?? route('pesan.create') }}">{{ $label }} <span class="arrow" aria-hidden="true">→</span></a>
    </div>
  </div>
</section>
