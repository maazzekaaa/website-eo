@extends('layouts.admin')

@section('title', $service->exists ? 'Ubah Layanan' : 'Tambah Layanan')

@section('content')
  <a href="{{ route('admin.services.index') }}" style="font-size:14px;color:var(--muted);padding-top:8px;text-decoration:none">← Kembali ke layanan</a>
  <div class="page-head"><div><h1>{{ $service->exists ? $service->name : 'Tambah layanan' }}</h1></div></div>

  <form class="form-stack" method="post" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" novalidate>
    @csrf
    @if ($service->exists) @method('PUT') @endif

    <article class="card form-stack">
      <div class="field"><label for="name">Nama layanan</label><input class="input" id="name" name="name" value="{{ old('name', $service->name) }}" required>@include('admin.partials.field-error', ['name' => 'name'])</div>
      <div class="field"><label for="description">Deskripsi</label><textarea class="textarea" id="description" name="description">{{ old('description', $service->description) }}</textarea>@include('admin.partials.field-error', ['name' => 'description'])</div>
      <div class="form-2">
        <div class="field">
          <label for="badge">Badge</label>
          <select class="select input" id="badge" name="badge">
            <option value="">Tanpa badge</option>
            <option value="promo" @selected(old('badge', $service->badge) === 'promo')>Promo</option>
            <option value="baru" @selected(old('badge', $service->badge) === 'baru')>Baru</option>
          </select>
        </div>
        <div class="field"><label for="sort_order">Urutan</label><input class="input" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $service->sort_order ?? 0) }}"></div>
      </div>
      <label class="check-line"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active))> Tampilkan di website dan bisa dipesan</label>
    </article>

    <article class="card form-stack">
      <div class="card-head"><div><h2>Harga</h2></div></div>
      <div class="field">
        <label for="pricing_type">Tipe harga</label>
        <select class="select input" id="pricing_type" name="pricing_type">
          <option value="tier" @selected(old('pricing_type', $service->pricing_type) === 'tier')>Per paket (pilih satu)</option>
          <option value="per_unit" @selected(old('pricing_type', $service->pricing_type) === 'per_unit')>Per satuan (harga × jumlah)</option>
        </select>
      </div>

      <div id="tierBox">
        <div id="optionRows">
          @foreach (old('options', $service->options->map(fn ($o) => ['id' => $o->id, 'name' => $o->name, 'price' => $o->price])->all()) as $i => $option)
            <div class="option-row">
              <input type="hidden" name="options[{{ $i }}][id]" value="{{ $option['id'] ?? '' }}">
              <input class="input" name="options[{{ $i }}][name]" value="{{ $option['name'] ?? '' }}" placeholder="Nama paket" aria-label="Nama paket">
              <input class="input" name="options[{{ $i }}][price]" value="{{ $option['price'] ?? '' }}" inputmode="numeric" placeholder="Harga (Rp)" aria-label="Harga paket">
              <button class="btn btn-sm btn-danger" type="button" data-remove-option>Hapus</button>
            </div>
          @endforeach
        </div>
        @include('admin.partials.field-error', ['name' => 'options'])
        <button class="btn btn-sm btn-light" type="button" id="addOption">+ Tambah paket</button>
      </div>

      <div id="unitBox" class="form-2">
        <div class="field"><label for="unit_label">Satuan</label><input class="input" id="unit_label" name="unit_label" value="{{ old('unit_label', $service->unit_label) }}" placeholder="m² atau m">@include('admin.partials.field-error', ['name' => 'unit_label'])</div>
        <div class="field"><label for="unit_price">Harga per satuan (Rp)</label><input class="input" id="unit_price" name="unit_price" inputmode="numeric" value="{{ old('unit_price', $service->unit_price) }}">@include('admin.partials.field-error', ['name' => 'unit_price'])</div>
      </div>
    </article>

    <div class="form-actions"><button class="btn btn-red" type="submit">Simpan</button><a class="btn btn-ghost" href="{{ route('admin.services.index') }}">Batal</a></div>
  </form>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/service-form.js') }}"></script>
@endpush
