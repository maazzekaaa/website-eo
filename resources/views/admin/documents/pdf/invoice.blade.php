<!doctype html>
<html lang="id">
<head><meta charset="utf-8">@include('admin.documents.pdf.style')</head>
<body>
  <h1>INVOICE</h1>
  <div><b>{{ setting('company_name') }}</b><br>{{ setting('address') }}<br>{{ setting('email') }} · {{ setting('wa_number') }}@if (setting('npwp'))<br>NPWP {{ setting('npwp') }}@endif</div>

  <table class="plain" style="margin-top:14px">
    <tr>
      <td style="width:34%"><span class="label">Invoice</span>{{ $booking->invoice_number }}<br><br><span class="label">Kode Booking</span>{{ $booking->booking_code }}</td>
      <td style="width:33%"><span class="label">Tanggal Terbit</span>{{ $booking->created_at->translatedFormat('j F Y') }}<br><br><span class="label">Jatuh Tempo</span>{{ $booking->event_date->translatedFormat('j F Y') }}</td>
      <td><span class="label">Status</span>{{ $statusLabel }}<br><br><span class="label">Ditagihkan Kepada</span>{{ $booking->customer_name }}<br>WA {{ $booking->customer_wa }}</td>
    </tr>
    <tr><td colspan="3"><span class="label">Acara</span>{{ $booking->event_date->translatedFormat('l, j F Y') }}@if ($booking->event_time) · {{ str_replace(':', '.', $booking->event_time) }} WIB @endif · {{ $booking->event_location }}</td></tr>
  </table>

  <table class="items">
    <thead><tr><th>Deskripsi</th><th style="width:36px">Qty</th><th style="width:50px">Satuan</th><th class="num" style="width:90px">Harga Satuan</th><th class="num" style="width:90px">Subtotal</th></tr></thead>
    <tbody>
      @foreach ($booking->items as $item)
        <tr><td>{{ $item->description }}</td><td>{{ $item->qty }}</td><td>{{ $item->unit }}</td><td class="num">{{ rupiah($item->unit_price) }}</td><td class="num">{{ rupiah($item->subtotal) }}</td></tr>
      @endforeach
    </tbody>
  </table>

  <table class="plain" style="margin-top:14px">
    <tr>
      <td style="width:58%">
        <span class="label">Riwayat Pembayaran</span>
        <table class="small">
          <thead><tr><th>No. Kuitansi</th><th>Tanggal</th><th>Jenis</th><th class="num">Nominal</th></tr></thead>
          <tbody>
            @forelse ($payments as $payment)
              <tr><td>{{ $payment->receipt_number }}</td><td>{{ $payment->paid_at->translatedFormat('j M Y') }}</td><td>{{ \App\Models\Payment::TYPE_LABELS[$payment->type] }} · {{ \App\Models\Payment::METHOD_LABELS[$payment->method] }}</td><td class="num">{{ rupiah($payment->amount) }}</td></tr>
            @empty
              <tr><td colspan="4">Belum ada pembayaran.</td></tr>
            @endforelse
          </tbody>
        </table>
      </td>
      <td>
        <table class="totals">
          <tr><td>Subtotal</td><td class="num">{{ rupiah($booking->subtotal) }}</td></tr>
          @if ($booking->tax_amount)<tr><td>PPN {{ $booking->tax_rate }}%</td><td class="num">{{ rupiah($booking->tax_amount) }}</td></tr>@endif
          <tr class="strong"><td>Total Tagihan</td><td class="num">{{ rupiah($booking->total) }}</td></tr>
          <tr><td>DP 50%</td><td class="num">{{ rupiah($booking->dp_amount) }}</td></tr>
          <tr><td>Sudah Dibayar</td><td class="num">{{ rupiah($paid) }}</td></tr>
          <tr class="rest"><td>Sisa Tagihan</td><td class="num">{{ rupiah($booking->total - $paid) }}</td></tr>
        </table>
      </td>
    </tr>
  </table>

  <div class="box">
    <span class="label">Informasi Pembayaran</span>
    {{ setting('bank_name') }} · No Rekening {{ setting('bank_account') }} · a.n {{ setting('bank_holder') }}<br>
    Mohon cantumkan nomor invoice {{ $booking->invoice_number }} sebagai keterangan transfer. Sisa tagihan dilunasi paling lambat pada tanggal acara.
  </div>

  <table class="plain" style="margin-top:24px">
    <tr>
      <td>@if ($stamp)<span class="stamp @if ($booking->payment_status === 'paid') stamp-paid @endif">{{ $stamp }}</span>@endif</td>
      <td class="sign" style="width:200px">Hormat kami,<div class="space">@if ($signature)<img src="{{ $signature }}" style="max-height:60px" alt="">@endif</div><b>Admin {{ setting('company_name') }}</b></td>
    </tr>
  </table>
</body>
</html>
