@extends('layouts.admin')

@section('title', 'Portofolio')

@section('content')
  <div class="page-head" data-reveal>
    <div><span class="meta">Katalog</span><h1 style="margin-top:6px">Portofolio</h1><p>Folder yang ditandai unggulan tampil di beranda.</p></div>
    <a class="btn btn-red" href="{{ route('admin.portfolios.create') }}">+ Tambah portofolio</a>
  </div>
  <article class="card" data-reveal>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Judul</th><th>Kategori</th><th>Klien</th><th>Tanggal</th><th class="num">Foto</th><th>Unggulan</th><th><span class="sr-only">Aksi</span></th></tr></thead>
        <tbody>
          @forelse ($portfolios as $portfolio)
            <tr tabindex="0" data-href="{{ route('admin.portfolios.edit', $portfolio) }}">
              <td><a href="{{ route('admin.portfolios.edit', $portfolio) }}" style="color:inherit;text-decoration:none;font-weight:500">{{ $portfolio->title }}</a></td>
              <td>{{ $portfolio->category }}</td>
              <td>{{ $portfolio->client?->name ?? '—' }}</td>
              <td>{{ $portfolio->event_date?->translatedFormat('j M Y') ?? '—' }}</td>
              <td class="num">{{ $portfolio->images_count }}</td>
              <td>{{ $portfolio->is_featured ? 'Ya' : '—' }}</td>
              <td><form class="table-actions" method="post" action="{{ route('admin.portfolios.destroy', $portfolio) }}" onsubmit="return confirm('Hapus portofolio ini beserta fotonya?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></td>
            </tr>
          @empty
            <tr><td colspan="7" class="empty">Belum ada portofolio.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @include('admin.partials.pager', ['page' => $portfolios])
  </article>
@endsection
