@extends('layouts.admin')

@section('title', 'Klien / Mitra')

@section('content')
  <div class="page-head" data-reveal>
    <div><a class="back-link" href="{{ route('admin.portfolios.index') }}">← Portofolio</a><h1 style="margin-top:6px">Klien / mitra</h1><p>Logo klien tampil berjalan di beranda.</p></div>
    <a class="btn btn-red" href="{{ route('admin.clients.create') }}">+ Tambah klien</a>
  </div>
  <article class="card" data-reveal>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Nama</th><th>Website</th><th class="num">Proyek</th><th><span class="sr-only">Aksi</span></th></tr></thead>
        <tbody>
          @forelse ($clients as $client)
            <tr tabindex="0" data-href="{{ route('admin.clients.edit', $client) }}">
              <td><a href="{{ route('admin.clients.edit', $client) }}" style="color:inherit;text-decoration:none;font-weight:500">{{ $client->name }}</a></td>
              <td>{{ $client->website ?: '—' }}</td>
              <td class="num">{{ $client->portfolios_count }}</td>
              <td><div class="table-actions"><a class="btn btn-sm btn-light" href="{{ route('admin.clients.edit', $client) }}" aria-label="Edit {{ $client->name }}">Edit</a><form method="post" action="{{ route('admin.clients.destroy', $client) }}" onsubmit="return confirm('Hapus klien ini? Portofolionya tetap ada.')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></div></td>
            </tr>
          @empty
            <tr><td colspan="4" class="empty">Belum ada klien.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @include('admin.partials.pager', ['page' => $clients])
  </article>
@endsection
