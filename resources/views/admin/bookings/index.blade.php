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
      <table class="table">
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
