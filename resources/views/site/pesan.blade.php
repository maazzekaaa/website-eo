@extends('layouts.site')

@section('title', 'Pesan — FZM Production Mediatama')
@section('description', 'Susun layanan, cek tanggal, dan pesan acara di FZM Production Mediatama.')
@section('theme', '#F7F7F6')
@section('nav-back', '1')
@section('no-footer', '1')
@section('wa-text', 'Halo FZM, saya ingin bertanya sebelum memesan.')
@section('wa-label', 'Tanya dulu')

@section('content')
  <main class="order">
    <div class="container">
      <form id="orderForm" method="post" action="{{ route('pesan.store') }}" novalidate>
        @csrf
        <div class="order-head">
          <div>
            <div class="sec-label" data-reveal><span class="meta-num">(Pesan)</span><span class="meta">Tanpa akun · DP 50%</span></div>
            <h1 class="split"><span class="line"><span>Susun pesanan</span></span><span class="line"><span style="--d:1">acaramu.</span></span></h1>
          </div>
          <p data-reveal style="--d:2">Pilih layanan, tentukan tanggal yang masih kosong, lalu kirim. Admin melanjutkan lewat WhatsApp.</p>
        </div>

        <div class="order-layout">
          <div class="order-steps">
            <section class="panel-card" aria-labelledby="s1" data-reveal>
              <div class="step-head"><span class="step-num">01</span><h2 id="s1">Pilih layanan</h2></div>
              @foreach ($services as $service)
                @if ($service->isTier())
                  <div class="svc-row" data-service="{{ $service->slug }}" data-name="{{ $service->name }}">
                    <div class="svc-row-head">
                      <div><div class="svc-row-name">{{ $service->name }}</div><div class="svc-row-desc">Pilih satu paket</div></div>
                      <button class="svc-clear" type="button" hidden>Hapus</button>
                    </div>
                    <fieldset class="tiers">
                      <legend class="sr-only">Paket {{ $service->name }}</legend>
                      @foreach ($service->options as $option)
                        <label class="tier"><input type="radio" name="items[{{ $service->slug }}]" value="{{ $option->id }}"{{ (string) old('items.'.$service->slug) === (string) $option->id ? ' checked' : '' }} data-label="{{ $option->name }}" data-price="{{ $option->price }}"><span><small>{{ $option->name }}</small><strong>{{ rupiah($option->price) }}</strong></span></label>
                      @endforeach
                    </fieldset>
                  </div>
                @else
                  <div class="svc-row unit-row" data-service="{{ $service->slug }}" data-name="{{ $service->name }}">
                    <div class="info"><div class="svc-row-name">{{ $service->name }}</div><div class="svc-row-desc">{{ rupiah($service->unit_price) }} / {{ $service->unit_label }}</div></div>
                    <div class="stepper">
                      <button type="button" data-step="-1" aria-label="Kurangi {{ $service->name }}">−</button>
                      <label><span class="sr-only">Jumlah {{ $service->name }} dalam {{ $service->unit_label }}</span><input type="number" name="qty[{{ $service->slug }}]" min="0" max="500" value="{{ old('qty.'.$service->slug, 0) }}" inputmode="numeric" data-price="{{ $service->unit_price }}" data-unit="{{ $service->unit_label }}"><span class="unit">{{ $service->unit_label }}</span></label>
                      <button type="button" data-step="1" aria-label="Tambah {{ $service->name }}">+</button>
                    </div>
                    <span class="unit-sub">Rp0</span>
                  </div>
                @endif
              @endforeach
              <p class="field-error" id="errItems" role="alert" @unless ($errors->has('items')) hidden @endunless>{{ $errors->first('items') ?: 'Pilih minimal satu layanan.' }}</p>
            </section>

            <section class="panel-card" aria-labelledby="s2" data-reveal>
              <div class="step-head">
                <span class="step-num">02</span><h2 id="s2">Pilih tanggal</h2>
                <div class="cal-head">
                  <button class="icon-btn" type="button" id="calPrev" aria-label="Bulan sebelumnya"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
                  <span class="cal-month" id="calMonth" aria-live="polite">—</span>
                  <button class="icon-btn" type="button" id="calNext" aria-label="Bulan berikutnya"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
                </div>
              </div>
              <div class="calendar" id="calendar"></div>
              <input type="hidden" name="event_date" id="eventDate">
              <div class="cal-legend"><span><i></i>Tersedia</span><span><i class="locked"></i>Terisi (dikonfirmasi)</span><span><i class="sel"></i>Pilihanmu</span></div>
              <p class="field-error" id="errDate" role="alert" @unless ($errors->has('event_date')) hidden @endunless>{{ $errors->first('event_date') ?: 'Pilih tanggal acara yang masih tersedia.' }}</p>
            </section>

            <section class="panel-card" aria-labelledby="s3" data-reveal>
              <div class="step-head"><span class="step-num">03</span><h2 id="s3">Data pemesan</h2></div>
              <div class="form-grid">
                <div class="field">
                  <label for="name">Nama lengkap / instansi</label>
                  <input class="input @error('customer_name') is-invalid @enderror" id="name" name="customer_name" type="text" autocomplete="name" placeholder="Contoh: Budi Santoso" value="{{ old('customer_name') }}">
                  <span class="field-error" id="errName" @unless ($errors->has('customer_name')) hidden @endunless>{{ $errors->first('customer_name') ?: 'Nama wajib diisi.' }}</span>
                </div>
                <div class="field">
                  <label for="wa">Nomor WhatsApp</label>
                  <input class="input @error('customer_wa') is-invalid @enderror" id="wa" name="customer_wa" type="tel" inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx" value="{{ old('customer_wa') }}">
                  <span class="field-error" id="errWa" @unless ($errors->has('customer_wa')) hidden @endunless>{{ $errors->first('customer_wa') ?: 'Nomor WA tidak valid (10–14 digit).' }}</span>
                </div>
                <div class="field">
                  <label for="time">Jam mulai</label>
                  <input class="input" id="time" name="event_time" type="time" value="{{ old('event_time', '18:00') }}">
                </div>
                <div class="field">
                  <label for="loc">Lokasi acara</label>
                  <input class="input @error('event_location') is-invalid @enderror" id="loc" name="event_location" type="text" placeholder="Nama gedung / alamat" value="{{ old('event_location') }}">
                  <span class="field-error" id="errLoc" @unless ($errors->has('event_location')) hidden @endunless>{{ $errors->first('event_location') ?: 'Lokasi wajib diisi.' }}</span>
                </div>
              </div>
              <div class="field">
                <label for="note">Catatan (opsional)</label>
                <textarea class="textarea" id="note" name="note" placeholder="Jenis acara, jumlah tamu, kebutuhan khusus…">{{ old('note') }}</textarea>
              </div>
              <div class="honeypot" aria-hidden="true"><label for="website">Website</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
            </section>
          </div>

          <aside class="summary" id="summary" aria-labelledby="sumTitle" data-reveal style="--d:2">
            <div style="display:flex;justify-content:space-between;align-items:baseline"><h2 id="sumTitle">Ringkasan</h2><span class="meta" style="color:rgba(255,255,255,.55)" id="sumCount">0 item</span></div>
            <p class="summary-empty" id="sumEmpty">Belum ada layanan. Pilih paket atau isi ukuran di sebelah kiri.</p>
            <ul class="summary-lines" id="sumLines"></ul>
            <div class="summary-totals">
              <div class="s-row"><span>Tanggal</span><strong id="sumDate">Belum dipilih</strong></div>
              <div class="s-row s-total"><span>Total</span><strong id="sumTotal">Rp0</strong></div>
              <div class="s-row s-dp"><span>DP 50%</span><strong id="sumDp">Rp0</strong></div>
              <small>Tanggal dikunci setelah DP diterima dan dikonfirmasi admin.</small>
            </div>
            <button class="btn btn-block" type="submit">Kirim &amp; lanjut ke WhatsApp <span class="arrow" aria-hidden="true">→</span></button>
          </aside>
        </div>
      </form>
    </div>
  </main>

  <div class="mbar" id="mbar">
    <div><small>Total · DP <span id="barDp">Rp0</span></small><strong id="barTotal">Rp0</strong></div>
    <a class="btn btn-sm" href="#summary">Ringkasan</a>
  </div>
@endsection

@push('scripts')
  @php
    $config = [
        'adminWa' => setting('wa_number'),
        'today' => now()->toDateString(),
        'locked' => $locked,
        'taxRate' => setting('tax_enabled') ? (int) setting('tax_rate') : 0,
        'availabilityUrl' => route('availability'),
        'old' => ['event_date' => old('event_date')],
    ];
  @endphp
  <script>
    window.FZM_CONFIG = @json($config);
  </script>
  <script src="{{ asset('assets/js/pesan.js') }}"></script>
@endpush
