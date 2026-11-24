@extends('layouts.admin')

@section('title', $portfolio->exists ? 'Ubah Portofolio' : 'Tambah Portofolio')

@section('content')
  <a href="{{ route('admin.portfolios.index') }}" style="font-size:14px;color:var(--muted);padding-top:8px;text-decoration:none">← Kembali ke portofolio</a>
  <div class="page-head"><div><h1>{{ $portfolio->exists ? $portfolio->title : 'Tambah portofolio' }}</h1></div></div>

  <form class="form-stack" method="post" enctype="multipart/form-data" action="{{ $portfolio->exists ? route('admin.portfolios.update', $portfolio) : route('admin.portfolios.store') }}" novalidate>
    @csrf
    @if ($portfolio->exists) @method('PUT') @endif

    <article class="card form-stack">
      <div class="field"><label for="title">Judul acara</label><input class="input" id="title" name="title" value="{{ old('title', $portfolio->title) }}" required>@include('admin.partials.field-error', ['name' => 'title'])</div>
      <div class="form-2">
        <div class="field">
          <label for="category">Kategori</label>
          <select class="select input" id="category" name="category">
            @foreach (\App\Models\Portfolio::CATEGORY_LABELS as $category => $label)
              <option value="{{ $category }}" @selected(old('category', $portfolio->category) === $category)>{{ $label }}</option>
            @endforeach
          </select>
          @include('admin.partials.field-error', ['name' => 'category'])
        </div>
        <div class="field">
          <label for="folder_color">Warna folder</label>
          <select class="select input" id="folder_color" name="folder_color">
            @foreach (['red' => 'Merah', 'black' => 'Hitam', 'maroon' => 'Marun', 'silver' => 'Perak'] as $value => $label)
              <option value="{{ $value }}" @selected(old('folder_color', $portfolio->folder_color) === $value)>{{ $label }}</option>
            @endforeach
          </select>
          @include('admin.partials.field-error', ['name' => 'folder_color'])
        </div>
      </div>
      <div class="form-2">
        <div class="field">
          <label for="client_id">Klien</label>
          <select class="select input" id="client_id" name="client_id">
            <option value="">Klien pribadi</option>
            @foreach ($clients as $client)
              <option value="{{ $client->id }}" @selected((string) old('client_id', $portfolio->client_id) === (string) $client->id)>{{ $client->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="field"><label for="event_date">Tanggal acara</label><input class="input" id="event_date" name="event_date" type="date" value="{{ old('event_date', $portfolio->event_date?->toDateString()) }}">@include('admin.partials.field-error', ['name' => 'event_date'])</div>
      </div>
      <div class="field"><label for="location">Lokasi</label><input class="input" id="location" name="location" value="{{ old('location', $portfolio->location) }}"></div>
      <div class="field"><label for="caption">Keterangan singkat</label><input class="input" id="caption" name="caption" value="{{ old('caption', $portfolio->caption) }}"></div>
      <div class="field"><label for="services">Layanan yang dipakai</label><input class="input" id="services" name="services" value="{{ old('services', $portfolio->services) }}" placeholder="Sound, Lighting, Rigging"></div>
      <div class="field"><label for="story">Cerita acara</label><textarea class="textarea" id="story" name="story">{{ old('story', $portfolio->story) }}</textarea></div>
      <label class="check-line"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $portfolio->is_featured))> Unggulan (tampil paling depan di halaman Portofolio)</label>
    </article>

    <article class="card form-stack">
      <div class="card-head"><div><h2>Foto</h2><p>JPG, PNG, atau WebP, maks. 5 MB per foto. Otomatis diubah ke WebP. Batas server saat ini: {{ round(\Illuminate\Http\UploadedFile::getMaxFilesize() / 1048576, 1) }} MB per sekali simpan.</p></div></div>
      <div class="field"><label for="photos">Tambah foto</label><input class="input" id="photos" name="photos[]" type="file" multiple accept="image/jpeg,image/png,image/webp">
        @error('photos')<span class="field-error">{{ $message }}</span>@enderror
        @error('photos.*')<span class="field-error">{{ $message }}</span>@enderror
      </div>
    </article>

    <div class="form-actions"><button class="btn btn-red" type="submit">Simpan</button><a class="btn btn-ghost" href="{{ route('admin.portfolios.index') }}">Batal</a></div>
  </form>

  @if ($portfolio->exists && $portfolio->images->isNotEmpty())
    <article class="card form-stack" style="max-width:760px">
      <div class="card-head"><div><h2>Foto tersimpan</h2></div></div>
      <div class="thumb-grid">
        @foreach ($portfolio->images as $image)
          <figure>
            <img src="{{ $image->thumbUrl() }}" alt="" loading="lazy">
            <form method="post" action="{{ route('admin.portfolios.images.destroy', $image) }}" onsubmit="return confirm('Hapus foto ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-block" type="submit">Hapus</button></form>
          </figure>
        @endforeach
      </div>
    </article>
  @endif
@endsection
