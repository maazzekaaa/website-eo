@extends('layouts.site')

{{-- Web satu halaman (one-scroll). Tiap bagian adalah komponen di resources/views/components:
     hero → layanan → portofolio (+ klien) → ajakan → tentang → kontak.
     Navbar menggulir ke #id bagian, dan pil hitamnya mengikuti bagian yang sedang terlihat (site.js). --}}
@section('content')
  <main>
    <x-home.hero />

    <section class="section" id="layanan">
      <div class="container">
        <x-section-head eyebrow="Layanan & harga" lead="Sound, lighting, videotron, rigging, hingga siaran langsung, dirancang dan dioperasikan oleh kru yang sama. Harga terbuka sejak awal, tanpa biaya kejutan.">
          Satu tim untuk<br>seluruh <x-accent>panggung.</x-accent>
        </x-section-head>
        <x-service-list :services="$services" />
      </div>
    </section>

    <x-home.showcase :projects="$projects" :gallery="$gallery" />

    @if ($clients->isNotEmpty())
      <section class="section section--tight" id="klien">
        <div class="container">
          <x-section-head eyebrow="Klien & mitra" lead="Dari pernikahan keluarga hingga panggung korporat berskala nasional, mereka kembali karena hasilnya.">
            Dipercaya mereka yang<br>tak mau <x-accent>kompromi.</x-accent>
          </x-section-head>
        </div>
        <x-client-strip :clients="$clients" />
      </section>
    @endif

    <x-cta lead="Cek ketersediaan dalam hitungan detik, rangkai layanan sesuai kebutuhan, lalu biarkan kami mengurus sisanya.">
      Tanggal impianmu mungkin masih <x-accent>kosong.</x-accent>
    </x-cta>

    <x-home.about />

    <x-home.contact />
  </main>
@endsection
