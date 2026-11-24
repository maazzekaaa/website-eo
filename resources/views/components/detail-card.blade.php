{{-- Komponen: kartu putih di halaman detail (layanan, portofolio, tentang). --}}
@props(['title' => null])
<section {{ $attributes->class('detail-card') }}>
  @if ($title)<h2>{{ $title }}</h2>@endif
  {{ $slot }}
</section>
