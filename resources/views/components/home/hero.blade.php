{{-- Komponen: hero beranda. Judul diketik huruf per huruf oleh site.js (.hero-type).
     Teks pembuka diambil dari Admin → Pengaturan (hero_title + hero_subtitle). --}}
@php
  $heroRows = [[['Rangkai', false], ['Cipta,', true]], [['Hidupkan', false], ['Karya.', true]]];
  $heroLead = trim(str_replace(["\r\n", "\n"], ' ', (string) setting('hero_title')).' '.setting('hero_subtitle'));
@endphp
<header class="hero">
  <h1 class="hero-title hero-type" aria-label="Rangkai Cipta, Hidupkan Karya.">
    @foreach ($heroRows as $row)
      <span class="hero-row" aria-hidden="true">
        @foreach ($row as [$word, $serif])
          <span class="hero-word{{ $serif ? ' serif' : '' }}">@foreach (mb_str_split($word) as $char)<span class="ch">{{ $char }}</span>@endforeach</span>
        @endforeach
      </span>
    @endforeach
  </h1>
  @if ($heroLead !== '')
    <p class="hero-lead">{{ $heroLead }}</p>
  @endif
  <div class="hero-actions">
    <a class="btn btn-red btn-lg" href="{{ route('pesan.create') }}">Cek jadwal &amp; pesan <span class="arrow" aria-hidden="true">→</span></a>
    <a class="btn btn-ghost btn-lg" href="{{ route('home') }}#portofolio">Lihat portofolio</a>
  </div>
</header>
