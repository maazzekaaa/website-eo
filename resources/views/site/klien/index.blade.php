@extends('layouts.site')

@section('title', 'Klien & Mitra — FZM Production Mediatama')
@section('description', 'Klien dan mitra FZM Production beserta proyek yang pernah dikerjakan.')
@section('corner', $rows->count().' klien')

@section('content')
  <main class="ledger-page">
    <div class="ledger-left">
      <h1 class="poster split"><span class="line"><span>Klien</span></span></h1>
      <span class="poster-rule" aria-hidden="true"></span>
      <p class="poster-lead" data-reveal style="--d:2">Klien dan mitra yang mempercayakan panggungnya kepada kami. Arahkan kursor ke baris untuk melihat fotonya, klik untuk melihat proyeknya.</p>

      @php $first = $rows->first(); @endphp
      <figure class="ledger-preview" data-reveal style="--d:3">
        <div class="ledger-frame" id="ledgerPreview">
          <span class="ph" aria-hidden="true"></span>
          <img id="ledgerImg" src="{{ $first['preview'] ?? '' }}" alt="" @empty($first['preview']) hidden @endempty>
        </div>
        <figcaption class="meta" id="ledgerCaption">{{ $first['caption'] ?? '' }}</figcaption>
      </figure>
    </div>

    <div class="ledger-right">
      <div class="ledger-head meta" aria-hidden="true"><span></span><span>No.</span><span>Klien</span><span>Kategori</span><span class="num">Proyek</span><span class="num">Tahun</span></div>
      @if ($rows->isEmpty())
        <p class="search-empty">Belum ada klien.</p>
      @else
        <ul class="ledger">
          @foreach ($rows as $row)
            <li>
              <a class="ledger-row" href="{{ route('klien.show', $row['client']) }}" data-preview="{{ $row['preview'] }}" data-caption="{{ $row['caption'] }}">
                <span class="ledger-thumb">@if ($row['thumb'])<img src="{{ $row['thumb'] }}" alt="" loading="lazy">@else<span class="ph ph--alt"></span>@endif</span>
                <span class="meta">{{ $row['number'] }}</span>
                <span class="ledger-name">{{ $row['client']->name }}</span>
                <span class="meta ledger-cat">{{ $row['category'] ?? '—' }}</span>
                <span class="meta num">{{ $row['client']->portfolios_count }}</span>
                <span class="meta num">{{ $row['year'] ?? '—' }}</span>
              </a>
            </li>
          @endforeach
        </ul>
      @endif
    </div>
  </main>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/ledger.js') }}"></script>
@endpush
