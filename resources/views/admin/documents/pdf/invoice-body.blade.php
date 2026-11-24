{{-- Isi invoice (dipakai PDF dan pratinjau layar). Urutan isi mengikuti docs/design-reference/contoh-invoice.png:
     identitas perusahaan → info invoice → ditagihkan kepada → tabel rincian berkepala merah → total → info pembayaran.
     Variabel: $booking, $payments, $paid, $statusLabel, $stamp, $signature (URL/data URI, boleh null). --}}
@php($rest = $booking->total - $paid)
<div class="doc">
  {{-- Kepala: merek di kiri, judul & nomor invoice di kanan --}}
  <table>
    <tr>
      <td style="width:58%">
        <div class="wordmark">FZM <span>PRODUCTION</span></div>
        <div class="company">
          <b style="color:#101010">{{ setting('company_name') }}</b><br>
          {{ setting('address') }}<br>
          {{ setting('email') }} · WA {{ setting('wa_number') }}
          @if (setting('npwp'))<br>NPWP {{ setting('npwp') }}@endif
        </div>
      </td>
      <td>
        <div class="doc-title">INVOICE</div>
        <div class="doc-no">{{ $booking->invoice_number }}</div>
        <div style="text-align:right"><span class="pill pill-{{ $booking->payment_status }}">{{ $statusLabel }}</span></div>
      </td>
    </tr>
  </table>
  <div class="rule"></div>

  {{-- Info invoice, pelanggan, dan acara --}}
  <table class="info">
    <tr>
      <td style="width:30%">
        <div class="card" style="height:86px">
          <span class="label">Tanggal terbit</span><div class="value">{{ $booking->created_at->translatedFormat('j F Y') }}</div>
          <span class="label" style="margin-top:7px">Jatuh tempo</span><div class="value red">{{ $booking->event_date->translatedFormat('j F Y') }}</div>
        </div>
      </td>
      <td style="width:32%">
        <div class="card" style="height:86px">
          <span class="label">Ditagihkan kepada</span>
          <div class="value">{{ $booking->customer_name }}</div>
          <div class="muted">WA {{ $booking->customer_wa }}</div>
          <span class="label" style="margin-top:7px">Kode booking</span><div class="value">{{ $booking->booking_code }}</div>
        </div>
      </td>
      <td>
        <div class="card" style="height:86px">
          <span class="label">Acara</span>
          <div class="value">{{ $booking->event_date->translatedFormat('l, j F Y') }}</div>
          @if ($booking->event_time)<div class="muted">{{ $booking->eventTimeLabel('.') }} WIB</div>@endif
          <div class="muted">{{ $booking->event_location }}</div>
        </div>
      </td>
    </tr>
  </table>

  {{-- Rincian layanan --}}
  <table class="items" style="margin-top:16px">
    <thead>
      <tr><th>Deskripsi</th><th style="width:34px">Qty</th><th style="width:52px">Satuan</th><th class="num" style="width:88px">Harga satuan</th><th class="num" style="width:88px">Subtotal</th></tr>
    </thead>
    <tbody>
      @foreach ($booking->items as $item)
        <tr><td><b>{{ $item->description }}</b></td><td>{{ $item->qty }}</td><td>{{ $item->unit }}</td><td class="num">{{ rupiah($item->unit_price) }}</td><td class="num">{{ rupiah($item->subtotal) }}</td></tr>
      @endforeach
    </tbody>
  </table>

  {{-- Riwayat pembayaran (kiri) dan total (kanan) --}}
  <table style="margin-top:16px">
    <tr>
      <td style="width:56%;padding-right:18px">
        <span class="label">Riwayat pembayaran</span>
        <table class="items items-dark small">
          <thead><tr><th>No. Kuitansi</th><th>Tanggal</th><th>Jenis</th><th class="num">Nominal</th></tr></thead>
          <tbody>
            @forelse ($payments as $payment)
              <tr><td>{{ $payment->receipt_number }}</td><td>{{ $payment->paid_at->translatedFormat('j M Y') }}</td><td>{{ \App\Models\Payment::TYPE_LABELS[$payment->type] }} · {{ \App\Models\Payment::METHOD_LABELS[$payment->method] }}</td><td class="num">{{ rupiah($payment->amount) }}</td></tr>
            @empty
              <tr><td colspan="4" class="empty">Belum ada pembayaran.</td></tr>
            @endforelse
          </tbody>
        </table>
      </td>
      <td>
        <table class="totals">
          <tr><td>Subtotal</td><td class="num">{{ rupiah($booking->subtotal) }}</td></tr>
          @if ($booking->tax_amount)<tr><td>PPN {{ $booking->tax_rate }}%</td><td class="num">{{ rupiah($booking->tax_amount) }}</td></tr>@endif
          <tr class="grand"><td>Total Tagihan</td><td class="num">{{ rupiah($booking->total) }}</td></tr>
          <tr><td style="padding-top:8px">DP 50%</td><td class="num" style="padding-top:8px">{{ rupiah($booking->dp_amount) }}</td></tr>
          <tr><td>Sudah Dibayar</td><td class="num">{{ rupiah($paid) }}</td></tr>
          @if ($rest > 0)
            <tr class="rest"><td>Sisa Tagihan</td><td class="num">{{ rupiah($rest) }}</td></tr>
          @else
            <tr><td><b>Sisa Tagihan</b></td><td class="num"><b>{{ rupiah(0) }}</b></td></tr>
          @endif
        </table>
      </td>
    </tr>
  </table>

  {{-- Info pembayaran --}}
  <div class="payinfo">
    <span class="label" style="color:#101010">Informasi pembayaran</span>
    <b>{{ setting('bank_name') }}</b> · No. Rekening <b>{{ setting('bank_account') }}</b> · a.n {{ setting('bank_holder') }}<br>
    <span class="muted">Mohon cantumkan nomor invoice <b style="color:#101010">{{ $booking->invoice_number }}</b> sebagai keterangan transfer. DP 50% mengamankan tanggal acara; sisa tagihan dilunasi paling lambat pada tanggal acara.</span>
  </div>

  {{-- Cap status & tanda tangan --}}
  <table style="margin-top:22px">
    <tr>
      <td style="vertical-align:middle;padding-left:10px">
        @if ($stamp)<span class="stamp @if ($booking->payment_status === 'paid') stamp--paid @endif">{{ $stamp }}</span>@endif
      </td>
      <td class="sign" style="width:190px">
        <span class="muted">Hormat kami,</span>
        <div class="space">@if ($signature)<img src="{{ $signature }}" style="max-height:56px" alt="Tanda tangan">@endif</div>
        <b>Admin {{ setting('company_name') }}</b>
      </td>
    </tr>
  </table>

  <div class="doc-footer">
    <table><tr>
      <td><span class="thanks">Terima kasih telah mempercayakan acaramu kepada kami.</span><br>Invoice ini sah sebagai bukti tagihan dan pembayaran untuk booking {{ $booking->booking_code }}.</td>
      <td class="num" style="vertical-align:bottom">{{ setting('company_name') }}<br>WA {{ setting('wa_number') }}</td>
    </tr></table>
  </div>
</div>
