@extends('layouts.admin')

@section('title', $client->exists ? 'Ubah Klien' : 'Tambah Klien')

@section('content')
  <a href="{{ route('admin.clients.index') }}" style="font-size:14px;color:var(--muted);padding-top:8px;text-decoration:none">← Kembali ke klien &amp; logo</a>
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
        <div class="logo-field">
          <span class="logo-thumb logo-thumb--lg" id="logoPreview" @unless ($client->logo) hidden @endunless>
            <img src="{{ $client->logo ? Storage::disk('public')->url($client->logo) : '' }}" alt="Pratinjau logo">
          </span>
          <div class="logo-field-body">
            <input class="input" id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp">
            <small class="hint">PNG/JPG/WebP, maks. 2 MB. PNG berlatar transparan terlihat paling rapi.{{ $client->logo ? ' Pilih file baru untuk mengganti logo.' : '' }}</small>
            @if ($client->logo)
              <label class="check"><input type="checkbox" name="remove_logo" value="1" id="removeLogo" @checked(old('remove_logo'))> Hapus logo (tampilkan nama saja)</label>
            @endif
          </div>
        </div>
        @include('admin.partials.field-error', ['name' => 'logo'])
      </div>
    </article>
    <div class="form-actions"><button class="btn btn-red" type="submit">Simpan</button><a class="btn btn-ghost" href="{{ route('admin.clients.index') }}">Batal</a></div>
  </form>
@endsection

@push('scripts')
<script>
  /* Pratinjau logo sebelum disimpan; centang "Hapus logo" menyembunyikan pratinjau */
  (function () {
    var input = document.getElementById('logo'), box = document.getElementById('logoPreview'), remove = document.getElementById('removeLogo');
    if (!input || !box) return;
    var img = box.querySelector('img'), original = img.getAttribute('src');
    input.addEventListener('change', function () {
      var f = input.files && input.files[0];
      if (f) { img.src = URL.createObjectURL(f); box.hidden = false; if (remove) remove.checked = false; }
      else { img.src = original; box.hidden = !original; }
    });
    if (remove) remove.addEventListener('change', function () { box.hidden = remove.checked || !img.getAttribute('src'); });
  })();
</script>
@endpush
