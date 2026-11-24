<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Rekap Pendapatan {{ $label }}</title>
  @include('admin.documents.pdf.brand')
  <style>
    @page { margin: 30px 34px 46px; }
    body { margin: 0; }
    /* Kaki halaman tetap di setiap halaman, dengan nomor halaman */
    .page-foot { position: fixed; bottom: -26px; left: 0; right: 0; font-size: 7px; color: #8C8C8C; }
  </style>
</head>
<body>
<div class="doc">
  <div class="page-foot">
    {{ setting('company_name') }} · Rekap pendapatan {{ $label }} · Dicetak {{ $printedAt }} WIB
    {{-- "Halaman X dari Y" ditulis RecapController lewat canvas dompdf (counter(pages) tidak didukung) --}}
  </div>

  {{-- Kepala --}}
  <table>
    <tr>
      <td style="width:55%">
        <div class="wordmark">FZM <span>PRODUCTION</span></div>
        <div class="company">{{ setting('company_name') }}<br>{{ setting('address') }}<br>{{ setting('email') }} · WA {{ setting('wa_number') }}</div>
      </td>
      <td>
        <div class="eyebrow" style="text-align:right">Rekap pendapatan</div>
        <div class="doc-title" style="margin-top:4px">{{ $label }}</div>
        <div class="doc-no muted" style="font-weight:normal">{{ $range }}@if ($running) · berjalan @endif</div>
      </td>
    </tr>
  </table>
  <div class="rule"></div>

  {{-- Angka utama --}}
  <table class="kpi">
    <tr>
      <td class="k">
        <div class="box box-dark">
          <span class="label">Total pendapatan</span>
          <div class="big">{{ rupiah($total) }}</div>
          <div class="note">
            @if ($change === null) Belum ada pembanding
            @else <span class="{{ $change >= 0 ? 'up' : 'down' }}">{{ $change >= 0 ? '▲' : '▼' }} {{ abs($change) }}%</span> vs {{ $previousLabel }}
            @endif
          </div>
        </div>
      </td>
      <td class="k"><div class="box"><span class="label">Transaksi</span><div class="big">{{ $count }}</div><div class="note">pembayaran tercatat</div></div></td>
      <td class="k"><div class="box"><span class="label">DP diterima</span><div class="big">{{ rupiah($byType['dp']) }}</div><div class="note">Pelunasan {{ rupiah($byType['pelunasan']) }}</div></div></td>
      <td class="k"><div class="box"><span class="label">Sisa tagihan</span><div class="big red">{{ rupiah($outstanding) }}</div><div class="note">{{ $events->count() }} acara pada periode ini</div></div></td>
    </tr>
  </table>

  {{-- Grafik --}}
  <h2>Pendapatan {{ $yearly ? 'per bulan' : 'per hari' }}</h2>
  <table class="chart">
    <tr>
      @foreach ($buckets as $b)
        <td>
          @if ($b['total'] > 0)
            <div class="bar @if ($b['total'] === $peak) bar-peak @endif" style="height:{{ max(2, round($b['total'] / max($peak, 1) * 92)) }}px"></div>
          @else
            <div class="bar-zero"></div>
          @endif
        </td>
      @endforeach
    </tr>
  </table>
  <table class="chart-axis" style="table-layout:fixed">
    <tr>@foreach ($buckets as $b)<td>{{ $b['label'] }}</td>@endforeach</tr>
  </table>
  <table style="margin-top:6px">
    <tr>
      <td class="muted" style="font-size:7.5px">Batang merah = {{ $yearly ? 'bulan' : 'hari' }} dengan pendapatan tertinggi{{ $peak > 0 ? ' ('.rupiah($peak).')' : '' }}.</td>
      <td class="num muted" style="font-size:7.5px">Transfer {{ rupiah($byMethod['transfer']) }} · Tunai {{ rupiah($byMethod['tunai']) }}</td>
    </tr>
  </table>

  {{-- Rincian pembayaran --}}
  <h2>Rincian pembayaran</h2>
  <table class="items items-dark small">
    <thead>
      <tr><th style="width:62px">Tanggal</th><th style="width:88px">No. Kuitansi</th><th>Pemesan</th><th style="width:92px">Kode booking</th><th style="width:100px">Jenis</th><th class="num" style="width:78px">Nominal</th></tr>
    </thead>
    <tbody>
      @forelse ($payments as $payment)
        <tr>
          <td>{{ $payment->paid_at->translatedFormat('j M Y') }}</td>
          <td>{{ $payment->receipt_number }}</td>
          <td>{{ $payment->booking?->customer_name }}</td>
          <td>{{ $payment->booking?->booking_code }}</td>
          <td>{{ \App\Models\Payment::TYPE_LABELS[$payment->type] ?? $payment->type }} · {{ \App\Models\Payment::METHOD_LABELS[$payment->method] ?? $payment->method }}</td>
          <td class="num">{{ rupiah($payment->amount) }}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="empty">Belum ada pembayaran pada periode ini.</td></tr>
      @endforelse
    </tbody>
    @if ($count)
      <tfoot><tr><td colspan="5">Total pendapatan</td><td class="num red">{{ rupiah($total) }}</td></tr></tfoot>
    @endif
  </table>

  {{-- Acara pada periode --}}
  <h2>Acara pada periode ini</h2>
  <table class="items items-dark small">
    <thead>
      <tr><th style="width:62px">Tanggal acara</th><th>Pemesan</th><th style="width:92px">Kode booking</th><th style="width:62px">Status</th><th class="num" style="width:70px">Total</th><th class="num" style="width:70px">Dibayar</th><th class="num" style="width:70px">Sisa</th></tr>
    </thead>
    <tbody>
      @forelse ($events as $event)
        @php($rest = max(0, $event->total - (int) $event->paid))
        <tr>
          <td>{{ $event->event_date->translatedFormat('j M Y') }}</td>
          <td>{{ $event->customer_name }}</td>
          <td>{{ $event->booking_code }}</td>
          <td>{{ \App\Models\Booking::STATUS_LABELS[$event->status] ?? $event->status }}</td>
          <td class="num">{{ rupiah($event->total) }}</td>
          <td class="num">{{ rupiah((int) $event->paid) }}</td>
          <td class="num @if ($rest > 0) red @endif">{{ rupiah($rest) }}</td>
        </tr>
      @empty
        <tr><td colspan="7" class="empty">Tidak ada acara pada periode ini.</td></tr>
      @endforelse
    </tbody>
    @if ($events->isNotEmpty())
      <tfoot><tr><td colspan="4">Jumlah</td><td class="num">{{ rupiah($eventsValue) }}</td><td class="num">{{ rupiah($eventsValue - $outstanding) }}</td><td class="num red">{{ rupiah($outstanding) }}</td></tr></tfoot>
    @endif
  </table>

  <p class="muted" style="margin-top:14px;font-size:7.5px">Pendapatan dihitung dari pembayaran yang tercatat (DP, pelunasan, dan lainnya) berdasarkan tanggal bayar, zona waktu WIB. Pembayaran yang dibatalkan (void) tidak dihitung.</p>
</div>
</body>
</html>
