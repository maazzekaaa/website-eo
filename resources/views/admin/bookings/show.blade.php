@extends('layouts.admin')

@section('title', 'Detail Booking '.$booking->booking_code)

@section('content')

      <a href="{{ route('admin.bookings.index') }}" style="align-self:flex-start;font-size:14px;color:var(--muted);padding-top:8px;text-decoration:none">← Kembali ke daftar pemesanan</a>

      <!-- Kepala booking -->
      @if ($booking->status === 'confirmed' && $booking->paidAmount() < $booking->dp_amount)
        <p class="auth-alert" role="alert">Perhatian: pembayaran di bawah DP setelah ada kuitansi yang dibatalkan. Tanggal acara masih terkunci.</p>
      @endif

      <section class="hero-card" data-reveal>
        <div class="row">
          <div>
            <span class="meta">Booking · Invoice {{ $booking->invoice_number }}</span>
            <h1>{{ $booking->booking_code }}</h1>
            <div class="badges"><span class="badge" id="stBadge">Menunggu</span><span class="badge" id="payBadge">DP</span></div>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a class="btn btn-light" href="{{ route('admin.bookings.invoice', $booking) }}">Cetak invoice</a>
            <a class="btn btn-light" href="{{ route('admin.bookings.invoice', [$booking, 'pdf' => 1]) }}">Unduh PDF ↓</a>
          </div>
        </div>
        <div class="steps" id="steps" aria-label="Alur status booking">
          <span class="steps-fill" id="stepsFill"></span>
          <div class="step" data-s="pending"><i>1</i><span>Menunggu</span></div>
          <div class="step" data-s="confirmed"><i>2</i><span>Dikonfirmasi · tanggal terkunci</span></div>
          <div class="step" data-s="completed"><i>3</i><span>Selesai</span></div>
        </div>
      </section>

      <div class="split2">
        <div class="stack">
          <article class="card" data-reveal aria-labelledby="itTitle">
            <div class="card-head"><div><h2 id="itTitle">Rincian layanan</h2><p>Harga disimpan saat booking dibuat</p></div></div>
            <div style="overflow-x:auto">
              <table class="items">
                <thead><tr><th>Deskripsi</th><th class="num">Qty</th><th>Satuan</th><th class="num">Harga</th><th class="num">Subtotal</th></tr></thead>
                <tbody>
                  @foreach ($booking->items as $item)
                    <tr><td>{{ $item->description }}</td><td class="num">{{ $item->qty }}</td><td>{{ $item->unit }}</td><td class="num">{{ rupiah($item->unit_price) }}</td><td class="num">{{ rupiah($item->subtotal) }}</td></tr>
                  @endforeach
                </tbody>
                <tfoot>
                  @if ($booking->tax_amount)
                    <tr><td colspan="4">Subtotal</td><td class="num">{{ rupiah($booking->subtotal) }}</td></tr>
                    <tr><td colspan="4">PPN {{ $booking->tax_rate }}%</td><td class="num">{{ rupiah($booking->tax_amount) }}</td></tr>
                  @endif
                  <tr><td colspan="4">Total</td><td class="num">{{ rupiah($booking->total) }}</td></tr>
                </tfoot>
              </table>
            </div>
          </article>

          <article class="card" data-reveal aria-labelledby="pyTitle">
            <div class="card-head">
              <div><h2 id="pyTitle">Riwayat pembayaran</h2><p>Setiap pembayaran menerbitkan kuitansi</p></div>
              <button class="btn btn-red btn-sm" type="button" id="openPay">+ Catat pembayaran</button>
            </div>
            <ul class="timeline" id="timeline"></ul>
          </article>
        </div>

        <div class="stack">
          <article class="card" data-reveal style="--d:1" aria-labelledby="blTitle">
            <div class="card-head"><div><h2 id="blTitle">Tagihan</h2><p>Jatuh tempo: {{ $booking->event_date->translatedFormat('j F Y') }}</p></div></div>
            <div class="ring-wrap">
              <div class="ring">
                <svg viewBox="0 0 132 132" aria-hidden="true">
                  <defs><linearGradient id="ringGrad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#C2272D"/><stop offset="1" stop-color="#C2272D"/></linearGradient></defs>
                  <circle class="track" cx="66" cy="66" r="54"/>
                  <circle class="val" id="ringVal" cx="66" cy="66" r="54"/>
                </svg>
                <div class="ring-center"><b id="ringPct">0%</b><small>terbayar</small></div>
              </div>
              <div class="money">
                <div><span>Total</span><b id="mTotal">—</b></div>
                <div><span>DP wajib 50%</span><b id="mDp">—</b></div>
                <div><span>Sudah dibayar</span><b id="mPaid">—</b></div>
                <div class="rest"><span>Sisa</span><b id="mRest">—</b></div>
              </div>
            </div>
          </article>

          <article class="card actions-card" data-reveal style="--d:2" aria-labelledby="acTitle">
            <div class="card-head"><div><h2 id="acTitle">Aksi</h2><p id="acHint">—</p></div></div>
            <div class="stack" style="gap:8px">
              <button class="btn btn-red btn-block" type="button" data-act="confirm">Konfirmasi &amp; kunci tanggal <span class="arrow" aria-hidden="true">→</span></button>
              <button class="btn btn-light btn-block" type="button" data-act="complete">Tandai selesai <span aria-hidden="true">✓</span></button>
              <div style="display:flex;gap:8px">
                <button class="btn btn-danger" style="flex:1" type="button" data-act="reject">Tolak</button>
                <button class="btn btn-danger" style="flex:1" type="button" data-act="cancel">Batalkan</button>
              </div>
            </div>
          </article>

          <article class="card" data-reveal style="--d:3" aria-labelledby="cuTitle">
            <div class="card-head"><div><h2 id="cuTitle">Pemesan</h2></div></div>
            <dl class="kv">
              <dt>Nama</dt><dd>{{ $booking->customer_name }}</dd>
              <dt>WhatsApp</dt><dd>{{ $booking->customer_wa }}</dd>
              <dt>Acara</dt><dd>{{ $booking->event_date->translatedFormat('l, j F Y') }}@if ($booking->event_time) · {{ $booking->eventTimeLabel('.') }}@endif</dd>
              <dt>Lokasi</dt><dd>{{ $booking->event_location }}</dd>
              @if ($booking->note)<dt>Catatan</dt><dd style="font-weight:400;white-space:pre-line">{{ $booking->note }}</dd>@endif
              @if ($booking->admin_note)<dt>Catatan admin</dt><dd style="font-weight:400;white-space:pre-line">{{ $booking->admin_note }}</dd>@endif
            </dl>
            <a class="btn btn-light btn-block" style="margin-top:16px" href="#" data-wa="{{ $booking->customer_wa }}" data-text="Halo {{ $booking->customer_name }}, terkait booking {{ $booking->booking_code }}…">Hubungi via WhatsApp ↗</a>
          </article>
        </div>
      </div>

      <!-- Dialog: catat pembayaran -->
      <dialog class="modal" id="payDialog" aria-labelledby="pdTitle">
        <form method="post" action="{{ route('admin.payments.store', $booking) }}" enctype="multipart/form-data" id="payForm" novalidate>
          @csrf
          <div class="modal-head">
            <div><h3 id="pdTitle">Catat pembayaran</h3><p>Sisa tagihan <b id="pdRest">—</b> · kuitansi terbit otomatis</p></div>
            <button class="icon-btn" type="button" data-close-dialog aria-label="Tutup"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
          </div>
          <div class="modal-body">
            <div class="field">
              <label for="amount">Nominal (Rp)</label>
              <input class="input" id="amount" name="amount" type="text" inputmode="numeric" autocomplete="off">
              <div class="quick" id="quick"></div>
              <span class="field-error" id="amountErr" hidden>Nominal harus lebih dari 0 dan tidak melebihi sisa tagihan.</span>
            </div>
            <div class="form-2">
              <div class="field">
                <label for="ptype">Jenis</label>
                <select class="select input" id="ptype" name="type"><option value="dp">DP</option><option value="pelunasan" selected>Pelunasan</option><option value="lainnya">Lainnya</option></select>
              </div>
              <div class="field">
                <label for="pdate">Tanggal bayar</label>
                <input class="input" id="pdate" name="paid_at" type="date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}">
              </div>
            </div>
            <fieldset class="field" style="border:0;padding:0;margin:0">
              <legend class="field-label" style="margin-bottom:8px">Metode</legend>
              <div class="chips"><label class="chip"><input type="radio" name="method" value="transfer" checked><span>Transfer</span></label><label class="chip"><input type="radio" name="method" value="tunai"><span>Tunai</span></label></div>
            </fieldset>
            <label class="drop" id="drop">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5M4 20h16"/></svg>
              <span id="dropText">Unggah bukti transfer (opsional) · JPG/PNG maks. 2 MB</span>
              <input type="file" id="proof" name="proof" accept="image/jpeg,image/png,image/webp">
            </label>
          </div>
          <div class="modal-foot">
            <button class="btn btn-ghost" type="button" data-close-dialog>Batal</button>
            <button class="btn btn-red" type="submit" id="paySave">Simpan &amp; terbitkan kuitansi</button>
          </div>
        </form>
      </dialog>

      <!-- Dialog: konfirmasi tindakan -->
      <dialog class="modal" id="askDialog" aria-labelledby="askTitle">
        <form method="post" id="askForm">
          @csrf
          <div class="modal-head"><div><h3 id="askTitle">Yakin?</h3><p id="askText"></p></div></div>
          <div class="modal-body">
            <div class="field"><label for="askReason">Alasan (opsional, disimpan di catatan admin)</label><textarea class="textarea" id="askReason" name="reason" maxlength="500" style="min-height:90px"></textarea></div>
          </div>
          <div class="modal-foot">
            <button class="btn btn-ghost" type="button" data-close-dialog>Batal</button>
            <button class="btn btn-red" type="submit" id="askYes">Ya, lanjutkan</button>
          </div>
        </form>
      </dialog>

      <dialog class="modal" id="voidDialog" aria-labelledby="voidTitle">
        <form method="post" id="voidForm">
          @csrf
          <div class="modal-head"><div><h3 id="voidTitle">Batalkan pembayaran?</h3><p>Kuitansi <b id="voidNo"></b> ditandai batal. Nomornya tidak dipakai ulang.</p></div></div>
          <div class="modal-body">
            <div class="field"><label for="voidReason">Alasan (wajib)</label><textarea class="textarea" id="voidReason" name="reason" maxlength="255" required style="min-height:90px"></textarea></div>
          </div>
          <div class="modal-foot">
            <button class="btn btn-ghost" type="button" data-close-dialog>Tutup</button>
            <button class="btn btn-red" type="submit">Ya, batalkan</button>
          </div>
        </form>
      </dialog>

      <form id="actForm" method="post" hidden>@csrf</form>
@endsection

@push('scripts')
  <script id="bookingData" type="application/json">@json($bookingData)</script>
  <script src="{{ asset_v('assets/js/booking.js') }}"></script>
@endpush
