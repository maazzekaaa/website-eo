<!doctype html>
<html lang="id">
<head><meta charset="utf-8">@include('admin.documents.pdf.style')</head>
<body>
  <table class="plain">
    <tr>
      <td><h1>KUITANSI</h1><b>No. {{ $payment->receipt_number }}</b></td>
      <td class="num"><b>{{ setting('company_name') }}</b><br>{{ setting('address') }}<br>{{ setting('email') }} · {{ setting('wa_number') }}</td>
    </tr>
  </table>
  <table class="plain" style="margin-top:12px">
    <tr><td style="width:130px"><b>Telah diterima dari</b></td><td>{{ $booking->customer_name }}</td></tr>
    <tr><td><b>Uang sejumlah</b></td><td><i>{{ ucfirst(terbilang($payment->amount)) }} rupiah</i></td></tr>
    <tr><td><b>Untuk pembayaran</b></td><td>{{ \App\Models\Payment::TYPE_LABELS[$payment->type] }} invoice {{ $booking->invoice_number }} · booking {{ $booking->booking_code }} (acara {{ $booking->event_date->translatedFormat('j F Y') }})</td></tr>
    <tr><td><b>Metode &amp; tanggal</b></td><td>{{ \App\Models\Payment::METHOD_LABELS[$payment->method] }} · {{ $payment->paid_at->translatedFormat('j F Y') }}</td></tr>
    @if ($payment->voided_at)<tr><td><b>Status</b></td><td><b style="color:#C8102E">DIBATALKAN</b> · {{ $payment->void_reason }}</td></tr>@endif
  </table>
  <table class="plain" style="margin-top:14px">
    <tr>
      <td><span class="label">Jumlah</span><span style="font-size:20px;font-weight:bold">{{ rupiah($payment->amount) }}</span><br>Total tagihan {{ rupiah($booking->total) }} · Sudah dibayar {{ rupiah($paidUntil) }} · <b>Sisa {{ rupiah($booking->total - $paidUntil) }}</b></td>
      <td class="sign" style="width:190px">{{ $payment->paid_at->translatedFormat('j F Y') }}<div class="space">@if ($signature)<img src="{{ $signature }}" style="max-height:55px" alt="">@endif</div><b>Admin {{ setting('company_name') }}</b></td>
    </tr>
  </table>
</body>
</html>
