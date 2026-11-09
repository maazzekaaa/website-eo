@extends('layouts.document')

@section('title', 'Kuitansi '.$payment->receipt_number)
@section('back', route('admin.bookings.show', $booking))

@section('content')
  <article class="sheet sheet--a5l">
    <header class="kw-head">
      <div>
        <h1>KUITANSI</h1>
        <b>No. <span id="kwNo">{{ $payment->receipt_number }}</span></b>
      </div>
      <div class="kw-company">
        <b>{{ setting('company_name') }}</b>
        <span>{{ setting('address') }}</span>
        <span>{{ setting('email') }} · {{ setting('wa_number') }}</span>
      </div>
    </header>

    <dl class="kw-rows">
      <dt>Telah diterima dari</dt><dd>{{ $booking->customer_name }}</dd>
      <dt>Uang sejumlah</dt><dd class="say">{{ ucfirst(terbilang($payment->amount)) }} rupiah</dd>
      <dt>Untuk pembayaran</dt><dd>{{ \App\Models\Payment::TYPE_LABELS[$payment->type] }} invoice {{ $booking->invoice_number }} · booking {{ $booking->booking_code }} (acara {{ $booking->event_date->translatedFormat('j F Y') }})</dd>
      <dt>Metode &amp; tanggal</dt><dd>{{ \App\Models\Payment::METHOD_LABELS[$payment->method] }} · {{ $payment->paid_at->translatedFormat('j F Y') }}</dd>
      @if ($payment->voided_at)
        <dt>Status</dt><dd><b style="color:var(--red-ink)">DIBATALKAN</b> · {{ $payment->void_reason }}</dd>
      @endif
    </dl>

    <footer class="doc-foot">
      <div class="kw-left">
        <div class="kw-amount"><span>Jumlah</span><b>{{ rupiah($payment->amount) }}</b></div>
        <small>Total tagihan {{ rupiah($booking->total) }} · Sudah dibayar {{ rupiah($paidUntil) }} · <b>Sisa {{ rupiah($booking->total - $paidUntil) }}</b></small>
      </div>
      <div class="doc-sign" style="width:200px">
        <span>{{ $payment->paid_at->translatedFormat('j F Y') }}</span>
        <div class="space">@if (setting('signature_path'))<img src="{{ Storage::disk('public')->url(setting('signature_path')) }}" alt="Tanda tangan" style="max-height:60px">@endif</div>
        <b>Admin {{ setting('company_name') }}</b>
      </div>
    </footer>
  </article>
@endsection
