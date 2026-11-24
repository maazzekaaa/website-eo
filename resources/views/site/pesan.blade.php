@extends('layouts.site')

@section('title', 'Pesan — FZM Production Mediatama')
@section('description', 'Rangkai layanan, kunci tanggal yang masih kosong, dan pesan acaramu tanpa akun dalam tiga langkah.')
@section('theme', '#FAFAFA')
@section('no-footer', '1')
@section('wa-text', 'Halo FZM, saya ingin bertanya sebelum memesan.')
@section('wa-label', 'Tanya dulu')

@section('content')
  <main class="order">
    <div class="container">
      <form id="orderForm" method="post" action="{{ route('pesan.store') }}" novalidate>
        @csrf
        <x-page-head eyebrow="Tanpa akun · DP 50%" size="md">
          Susun acara <x-accent>Anda.</x-accent>
          <x-slot:lead><strong class="accent">Tiga langkah</strong> sederhana: pilih layanan, kunci tanggal yang masih kosong, lalu isi data. Tanpa akun dan tanpa formulir panjang. Rincian pesanan langsung terkirim ke WhatsApp tim kami.</x-slot:lead>
        </x-page-head>

        <div class="order-layout">
          <div class="order-steps">
            <section class="order-step order-step--wide" aria-labelledby="s1">
              <div class="step-head"><span class="step-num">1</span><h2 id="s1">Pilih layanan</h2><span class="step-hint">Klik paket sekali lagi untuk batal</span></div>
              <div class="svc-grid">
              @foreach ($services as $service)
                @if ($service->isTier())
                  <div class="svc-row" data-service="{{ $service->slug }}" data-name="{{ $service->name }}">
                    <div class="svc-row-head">
                      <div><div class="svc-row-name">{{ $service->name }}</div><div class="svc-row-desc">Pilih satu paket</div></div>
                      <span class="svc-check" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
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
                    <div class="svc-row-head">
                      <div class="info"><div class="svc-row-name">{{ $service->name }}</div><div class="svc-row-desc">{{ rupiah($service->unit_price) }} / {{ $service->unit_label }}</div></div>
                      <span class="svc-check" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
                    </div>
                    <div class="unit-foot">
                    <div class="stepper">
                      <button type="button" data-step="-1" aria-label="Kurangi {{ $service->name }}">−</button>
                      <label><span class="sr-only">Jumlah {{ $service->name }} dalam {{ $service->unit_label }}</span><input type="number" name="qty[{{ $service->slug }}]" min="0" max="500" value="{{ old('qty.'.$service->slug, 0) }}" inputmode="numeric" data-price="{{ $service->unit_price }}" data-unit="{{ $service->unit_label }}"><span class="unit">{{ $service->unit_label }}</span></label>
                      <button type="button" data-step="1" aria-label="Tambah {{ $service->name }}">+</button>
                    </div>
                    <span class="unit-sub">Rp0</span>
                    </div>
                  </div>
                @endif
              @endforeach
              </div>
              <p class="field-error" id="errItems" role="alert" @unless ($errors->has('items')) hidden @endunless>{{ $errors->first('items') ?: 'Pilih minimal satu layanan.' }}</p>
            </section>

            <section class="order-step" aria-labelledby="s2">
              <div class="step-head"><span class="step-num">2</span><h2 id="s2">Pilih tanggal</h2></div>
              <div class="panel-card cal-card">
                <div class="cal-head">
                  <button class="icon-btn" type="button" id="calPrev" aria-label="Bulan sebelumnya"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
                  <span class="cal-month" id="calMonth" aria-live="polite">—</span>
                  <button class="icon-btn" type="button" id="calNext" aria-label="Bulan berikutnya"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
                </div>
              <div class="calendar" id="calendar"></div>
              <input type="hidden" name="event_date" id="eventDate">
              <div class="cal-legend"><span><i class="sel"></i>Dipilih</span><span><i class="locked"></i>Sudah terisi</span><span><i></i>Tersedia</span></div>
              </div>
              <p class="field-error" id="errDate" role="alert" @unless ($errors->has('event_date')) hidden @endunless>{{ $errors->first('event_date') ?: 'Pilih tanggal acara yang masih tersedia.' }}</p>
            </section>

            <section class="order-step" aria-labelledby="s3">
              <div class="step-head"><span class="step-num">3</span><h2 id="s3">Data pemesan</h2></div>
              <div class="panel-card">
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
                  <label for="time">Jam mulai acara</label>
                  <input class="input @error('event_time') is-invalid @enderror" id="time" name="event_time" type="time" value="{{ old('event_time', '18:00') }}">
                  @error('event_time')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                  <label for="timeEnd">Jam selesai acara</label>
                  <input class="input @error('event_end_time') is-invalid @enderror" id="timeEnd" name="event_end_time" type="time" value="{{ old('event_end_time') }}">
                  @error('event_end_time')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field field--full">
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
              </div>
            </section>
          </div>

          <aside class="summary" id="summary" aria-labelledby="sumTitle">
            <div style="display:flex;justify-content:space-between;align-items:baseline"><h2 id="sumTitle">Ringkasan</h2><span class="summary-count" id="sumCount">0 item</span></div>
            <p class="summary-empty" id="sumEmpty">Belum ada layanan. Pilih paket atau isi ukuran di sebelah kiri.</p>
            <ul class="summary-lines" id="sumLines"></ul>
            <div class="summary-totals">
              <div class="s-row"><span>Tanggal</span><strong id="sumDate">Belum dipilih</strong></div>
              <div class="s-row s-total"><span>Total</span><strong id="sumTotal">Rp0</strong></div>
              <div class="s-row s-dp"><span>DP 50%</span><strong id="sumDp">Rp0</strong></div>
              <small>Tanggal dikunci setelah DP diterima dan dikonfirmasi admin.</small>
            </div>
            <button class="btn btn-red btn-block" type="submit">Kirim &amp; lanjut ke WhatsApp <span class="arrow" aria-hidden="true">→</span></button>
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
  <script src="{{ asset_v('assets/js/pesan.js') }}"></script>
@endpush
