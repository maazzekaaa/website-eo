@extends('layouts.admin')

@section('title', 'Klien & Logo')

@section('content')
  <div class="page-head" data-reveal>
    <div><h1>Klien &amp; logo</h1><p>Logo tampil berjalan di bagian Klien &amp; mitra beranda. Perubahan langsung tersimpan.</p></div>
    <a class="btn btn-red" href="{{ route('admin.clients.create') }}">+ Tambah klien</a>
  </div>
  <article class="card" data-reveal>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th style="width:120px">Logo</th><th>Nama</th><th>Website</th><th class="num">Proyek</th><th><span class="sr-only">Aksi</span></th></tr></thead>
        <tbody>
          @forelse ($clients as $client)
            <tr tabindex="0" data-href="{{ route('admin.clients.edit', $client) }}">
              <td>
                @if ($client->logo)
                  <span class="logo-thumb"><img src="{{ Storage::disk('public')->url($client->logo) }}" alt="Logo {{ $client->name }}" loading="lazy"></span>
                @else
                  <span class="logo-thumb logo-thumb--empty">Tanpa logo</span>
                @endif
              </td>
              <td><a href="{{ route('admin.clients.edit', $client) }}" style="color:inherit;text-decoration:none;font-weight:500">{{ $client->name }}</a></td>
              <td>{{ $client->website ?: '—' }}</td>
              <td class="num">{{ $client->portfolios_count }}</td>
              <td><div class="table-actions"><a class="btn btn-sm btn-light" href="{{ route('admin.clients.edit', $client) }}" aria-label="Edit {{ $client->name }}">Edit</a><form method="post" action="{{ route('admin.clients.destroy', $client) }}" onsubmit="return confirm('Hapus klien {{ $client->name }} beserta logonya? Portofolionya tetap ada.')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></div></td>
            </tr>
          @empty
            <tr><td colspan="5" class="empty">Belum ada klien. Klik <b>+ Tambah klien</b> untuk menambahkan logo pertama. Selama kosong, bagian klien di beranda disembunyikan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @include('admin.partials.pager', ['page' => $clients])
  </article>
@endsection
