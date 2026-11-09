@extends('layouts.document')

@section('title', 'Invoice '.$booking->invoice_number)
@section('back', route('admin.bookings.show', $booking))

@section('content')
  <article class="sheet sheet--a4">
    <header class="inv-head">
      <h1>INVOICE</h1>
      <strong>{{ setting('company_name') }}</strong>
      <span>{{ setting('address') }}</span>
      <span>{{ setting('email') }} · {{ setting('wa_number') }}</span>
      @if (setting('npwp'))<span>NPWP {{ setting('npwp') }}</span>@endif
    </header>

    <section class="inv-info">
      <div><b>Invoice</b><span>{{ $booking->invoice_number }}</span></div>
      <div><b>Kode Booking</b><span>{{ $booking->booking_code }}</span></div>
      <div><b>Tanggal Terbit</b><span>{{ $booking->created_at->translatedFormat('j F Y') }}</span></div>
      <div><b>Jatuh Tempo</b><span>{{ $booking->event_date->translatedFormat('j F Y') }}</span></div>
      <div class="group">
        <div><b>Status</b><span>{{ $statusLabel }}</span></div>
        <div><b>Acara</b><span>{{ $booking->event_date->translatedFormat('l, j F Y') }}@if ($booking->event_time) · {{ str_replace(':', '.', $booking->event_time) }} WIB @endif</span><span>{{ $booking->event_location }}</span></div>
      </div>
      <div><b>Ditagihkan Kepada</b><span>{{ $booking->customer_name }}</span><span>WA {{ $booking->customer_wa }}</span></div>
    </section>

    <table class="inv-table">
      <thead>
        <tr><th>Deskripsi</th><th style="width:48px">Qty</th><th style="width:70px">Satuan</th><th style="width:120px">Harga Satuan</th><th style="width:120px">Subtotal</th></tr>
      </thead>
      <tbody>
        @foreach ($booking->items as $item)
          <tr><td>{{ $item->description }}</td><td class="num">{{ $item->qty }}</td><td>{{ $item->unit }}</td><td class="num">{{ rupiah($item->unit_price) }}</td><td class="num">{{ rupiah($item->subtotal) }}</td></tr>
        @endforeach
      </tbody>
    </table>

    <section class="inv-bottom">
      <div class="inv-history">
        <b>Riwayat Pembayaran</b>
        <table>
          <thead><tr><th>No. Kuitansi</th><th>Tanggal</th><th>Jenis</th><th style="text-align:right">Nominal</th></tr></thead>
          <tbody>
            @forelse ($payments as $payment)
              <tr><td>{{ $payment->receipt_number }}</td><td>{{ $payment->paid_at->translatedFormat('j M Y') }}</td><td>{{ \App\Models\Payment::TYPE_LABELS[$payment->type] }} · {{ \App\Models\Payment::METHOD_LABELS[$payment->method] }}</td><td style="text-align:right">{{ rupiah($payment->amount) }}</td></tr>
            @empty
              <tr><td colspan="4">Belum ada pembayaran.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="inv-totals">
        <div><span>Subtotal</span><span>{{ rupiah($booking->subtotal) }}</span></div>
        @if ($booking->tax_amount)<div><span>PPN {{ $booking->tax_rate }}%</span><span>{{ rupiah($booking->tax_amount) }}</span></div>@endif
        <div class="strong"><span>Total Tagihan</span><span>{{ rupiah($booking->total) }}</span></div>
        <div><span>DP 50%</span><span>{{ rupiah($booking->dp_amount) }}</span></div>
        <div><span>Sudah Dibayar</span><span>{{ rupiah($paid) }}</span></div>
        <div class="rest"><span>Sisa Tagihan</span><span>{{ rupiah($booking->total - $paid) }}</span></div>
      </div>
    </section>

    <section class="inv-payinfo">
      <b>Informasi Pembayaran</b>
      <span>{{ setting('bank_name') }} · No Rekening {{ setting('bank_account') }} · a.n {{ setting('bank_holder') }}</span>
      <span>Mohon cantumkan nomor invoice {{ $booking->invoice_number }} sebagai keterangan transfer. Sisa tagihan dilunasi paling lambat pada tanggal acara.</span>
    </section>

    <footer class="doc-foot">
      @if ($stamp)
        <div class="stamp @if ($booking->payment_status === 'paid') stamp--paid @endif" aria-label="Cap status">{{ $stamp }}</div>
      @else
        <div></div>
      @endif
      <div class="doc-sign">
        <span>Hormat kami,</span>
        <div class="space">@if (setting('signature_path'))<img src="{{ Storage::disk('public')->url(setting('signature_path')) }}" alt="Tanda tangan" style="max-height:70px">@endif</div>
        <b>Admin {{ setting('company_name') }}</b>
      </div>
    </footer>
  </article>
@endsection
