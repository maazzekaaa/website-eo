{{-- Komponen: logo kiri atas + navbar kapsul bawah + menu ponsel.
     Web satu halaman: menu menggulir ke bagian beranda (#layanan, #portofolio, …).
     Di beranda, site.js memindah pil hitam mengikuti bagian yang sedang terlihat. --}}
@php
  $home = route('home');
  $links = [
      ['beranda', $home, 'home', 'Beranda'],
      ['layanan', $home.'#layanan', 'layanan.*', 'Layanan'],
      ['portofolio', $home.'#portofolio', 'portofolio.*', 'Portofolio'],
      ['tentang', $home.'#tentang', null, 'Tentang'],
      ['kontak', $home.'#kontak', null, 'Kontak'],
  ];
@endphp
<a class="brand-pill" href="{{ $home }}" aria-label="FZM Production, beranda"><span class="brand-mark"><x-logo-mark /></span>FZM</a>

<nav class="nav" aria-label="Navigasi utama">
  <div class="nav-links">
    @foreach ($links as [$id, $href, $pattern, $label])
      <a href="{{ $href }}" data-section="{{ $id }}"@if ($pattern && request()->routeIs($pattern)) aria-current="page"@endif>{{ $label }}</a>
    @endforeach
  </div>
  <div class="nav-actions">
    <a class="btn nav-cta" href="{{ route('pesan.create') }}"@if (request()->routeIs('pesan.*')) aria-current="page"@endif>Pesan</a>
    <button class="icon-btn nav-burger" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="navSheet"><span></span></button>
  </div>
</nav>
<div class="nav-sheet" id="navSheet" hidden>
  @foreach ($links as [, $href, , $label])
    <a href="{{ $href }}">{{ $label }}</a>
  @endforeach
  <a href="{{ route('cari') }}">Cari</a>
  <a class="btn btn-red" href="{{ route('pesan.create') }}">Pesan Sekarang</a>
</div>
