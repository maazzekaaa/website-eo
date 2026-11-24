{{-- Komponen: bagian Portofolio di beranda — grid foto bento (foto pertama paling besar).
     Klik foto = galeri detail berlatar buram (portfolio.js + <x-portfolio.lightbox>), klik judul = studi kasus.
     Data dari HomeController: $projects (Portfolio + client + images) dan $gallery (JSON galeri). --}}
@props(['projects', 'gallery'])
<section class="section" id="portofolio">
  <div class="container">
    <x-section-head eyebrow="Portofolio" lead="Pernikahan yang intim hingga perhelatan korporat berskala besar. Ketuk foto untuk melihat galerinya.">
      Panggung yang <x-accent>sudah</x-accent><br>kami hidupkan.
    </x-section-head>
    @if ($projects->isEmpty())
      <p class="search-empty">Portofolio sedang kami siapkan. Kembali lagi sebentar lagi.</p>
    @else
      <ul class="showcase" id="pfGrid">
        @foreach ($projects as $project)
          <li class="show-item" data-reveal style="--d:{{ $loop->index % 3 }}">
            <button class="show-media" type="button" data-i="{{ $loop->index }}" aria-label="Lihat foto {{ $project->title }}">
              <img src="{{ $project->images->first()->url() }}" alt="" loading="lazy" draggable="false">
              @if ($project->images->count() > 1)<span class="show-count">{{ $project->images->count() }} foto</span>@endif
            </button>
            <div class="show-caption">
              <span class="show-client">{{ $project->client?->name ?? 'Klien pribadi' }}</span>
              <a class="show-title" href="{{ route('portofolio.show', $project) }}">{{ $project->title }}</a>
              <span class="show-meta">{{ $project->categoryLabel() }}@if ($project->event_date) · {{ $project->event_date->translatedFormat('Y') }}@endif @if ($project->location) · {{ $project->location }}@endif</span>
            </div>
          </li>
        @endforeach
      </ul>
    @endif
  </div>
</section>

@push('after')
  <x-portfolio.lightbox />
  <script id="portfolioData" type="application/json">@json($gallery)</script>
@endpush

@push('scripts')
  <script src="{{ asset_v('assets/js/portfolio.js') }}"></script>
@endpush
