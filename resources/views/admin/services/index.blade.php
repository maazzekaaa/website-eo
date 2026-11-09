@extends('layouts.admin')

@section('title', 'Layanan & Harga')

@section('content')
  <div class="page-head" data-reveal>
    <div><span class="meta">Katalog</span><h1 style="margin-top:6px">Layanan &amp; harga</h1><p>Perubahan harga tidak memengaruhi booking yang sudah ada.</p></div>
    <a class="btn btn-red" href="{{ route('admin.services.create') }}">+ Tambah layanan</a>
  </div>
  <article class="card" data-reveal>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Layanan</th><th>Tipe</th><th>Harga</th><th>Badge</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
        <tbody>
          @forelse ($services as $service)
            <tr tabindex="0" data-href="{{ route('admin.services.edit', $service) }}">
              <td><a href="{{ route('admin.services.edit', $service) }}" style="color:inherit;text-decoration:none;font-weight:500">{{ $service->name }}</a></td>
              <td>{{ $service->isTier() ? 'Per paket' : 'Per '.$service->unit_label }}</td>
              <td>{{ $service->priceLabel() }}</td>
              <td>@if ($service->badge)<span class="badge badge-dp">{{ ucfirst($service->badge) }}</span>@else — @endif</td>
              <td><span class="badge {{ $service->is_active ? 'badge-paid' : 'badge-pending' }}">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
              <td><form class="table-actions" method="post" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Hapus layanan ini? Booking lama tetap menyimpan salinan harganya.')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></td>
            </tr>
          @empty
            <tr><td colspan="6" class="empty">Belum ada layanan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </article>
@endsection
