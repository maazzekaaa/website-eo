@extends('layouts.admin')

@section('title', 'Pemesanan')

@section('content')
  <div class="page-head" data-reveal>
    <div>
      <span class="meta">Pemesanan</span>
      <h1 style="margin-top:6px">Daftar booking</h1>
      <p>{{ $bookings->total() }} booking ditemukan</p>
    </div>
  </div>

  <nav class="tabs" aria-label="Filter status">
    <a href="{{ route('admin.bookings.index') }}" @if (! request('status')) aria-current="page" @endif>Semua <span>{{ $statusCounts->sum() }}</span></a>
    @foreach (['pending', 'confirmed', 'completed'] as $value)
      <a href="{{ route('admin.bookings.index', ['status' => $value]) }}" @if (request('status') === $value) aria-current="page" @endif>{{ \App\Models\Booking::STATUS_LABELS[$value] }} <span>{{ $statusCounts[$value] ?? 0 }}</span></a>
    @endforeach
  </nav>

  <article class="card" data-reveal>
    <form method="get" class="filter-bar">
      <div class="field">
        <label for="fStatus">Status booking</label>
        <select class="select input" id="fStatus" name="status" onchange="this.form.submit()">
          <option value="">Semua</option>
          @foreach (\App\Models\Booking::STATUS_LABELS as $value => $label)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label for="fPay">Status bayar</label>
        <select class="select input" id="fPay" name="payment_status" onchange="this.form.submit()">
          <option value="">Semua</option>
          @foreach (\App\Models\Booking::PAYMENT_LABELS as $value => $label)
            <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="field" style="flex:1;min-width:200px">
        <label for="fQ">Cari</label>
        <input class="input" id="fQ" type="search" name="q" value="{{ $q }}" placeholder="Kode atau nama pemesan">
      </div>
      <button class="btn btn-light" type="submit">Terapkan</button>
    </form>

    <div class="table-wrap">
      {{-- Lebar kolom dikunci (table-layout: fixed) agar tabel tidak melebar/menyempit saat pindah filter --}}
      <table class="table table-fixed">
        <colgroup><col style="width:24%"><col style="width:19%"><col style="width:14%"><col style="width:15%"><col style="width:12%"><col style="width:12%"><col style="width:4%"></colgroup>
        <thead><tr><th>Pemesan</th><th>Kode</th><th>Tanggal acara</th><th class="num">Total</th><th>Status</th><th>Bayar</th><th><span class="sr-only">Buka</span></th></tr></thead>
        <tbody>
          @forelse ($bookings as $booking)
            <tr tabindex="0" data-href="{{ route('admin.bookings.show', $booking) }}">
              <td><a href="{{ route('admin.bookings.show', $booking) }}" style="text-decoration:none;color:inherit;font-weight:500">{{ $booking->customer_name }}</a></td>
              <td class="mono">{{ $booking->booking_code }}</td>
              <td>{{ $booking->event_date->translatedFormat('j M Y') }}</td>
              <td class="num"><b style="font-weight:500">{{ rupiah($booking->total) }}</b></td>
              <td><span class="badge badge-{{ $booking->status }}">{{ \App\Models\Booking::STATUS_LABELS[$booking->status] }}</span></td>
              <td><span class="badge badge-{{ $booking->payment_status }}">{{ \App\Models\Booking::PAYMENT_LABELS[$booking->payment_status] }}</span></td>
              <td class="num"><span class="row-go" aria-hidden="true">→</span></td>
            </tr>
          @empty
            <tr><td colspan="7" class="empty">Tidak ada booking yang cocok.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @include('admin.partials.pager', ['page' => $bookings])
  </article>
@endsection
