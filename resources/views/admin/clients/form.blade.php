@extends('layouts.admin')

@section('title', $client->exists ? 'Ubah Klien' : 'Tambah Klien')

@section('content')
  <a href="{{ route('admin.clients.index') }}" style="font-size:14px;color:var(--muted);padding-top:8px;text-decoration:none">← Kembali ke klien</a>
  <div class="page-head"><div><h1>{{ $client->exists ? $client->name : 'Tambah klien' }}</h1></div></div>

  <form class="form-stack" method="post" enctype="multipart/form-data" action="{{ $client->exists ? route('admin.clients.update', $client) : route('admin.clients.store') }}" novalidate>
    @csrf
    @if ($client->exists) @method('PUT') @endif
    <article class="card form-stack">
      <div class="field"><label for="name">Nama</label><input class="input" id="name" name="name" value="{{ old('name', $client->name) }}" required>@include('admin.partials.field-error', ['name' => 'name'])</div>
      <div class="field"><label for="description">Deskripsi</label><textarea class="textarea" id="description" name="description">{{ old('description', $client->description) }}</textarea></div>
      <div class="field"><label for="website">Website</label><input class="input" id="website" name="website" type="url" value="{{ old('website', $client->website) }}" placeholder="https://…">@include('admin.partials.field-error', ['name' => 'website'])</div>
      <div class="field">
        <label for="logo">Logo</label>
        @if ($client->logo)<img class="logo-preview" src="{{ Storage::disk('public')->url($client->logo) }}" alt="Logo {{ $client->name }}">@endif
        <input class="input" id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp">
        @include('admin.partials.field-error', ['name' => 'logo'])
      </div>
    </article>
    <div class="form-actions"><button class="btn btn-red" type="submit">Simpan</button><a class="btn btn-ghost" href="{{ route('admin.clients.index') }}">Batal</a></div>
  </form>
@endsection
