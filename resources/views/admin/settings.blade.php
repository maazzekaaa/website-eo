@extends('layouts.admin')

@section('title', 'Konten & Pengaturan')

@section('content')
  <div class="page-head" data-reveal>
    <div><span class="meta">Pengaturan</span><h1 style="margin-top:6px">Konten &amp; pengaturan</h1><p>Berlaku di website, invoice, dan kuitansi.</p></div>
  </div>

  <form class="form-stack" method="post" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}" novalidate>
    @csrf
    @method('PUT')

    <article class="card form-stack">
      <div class="card-head"><div><h2>Perusahaan</h2></div></div>
      <div class="field"><label for="company_name">Nama perusahaan</label><input class="input" id="company_name" name="company_name" value="{{ old('company_name', $setting->company_name) }}">@include('admin.partials.field-error', ['name' => 'company_name'])</div>
      <div class="field"><label for="address">Alamat</label><input class="input" id="address" name="address" value="{{ old('address', $setting->address) }}"></div>
      <div class="form-2">
        <div class="field"><label for="email">Email</label><input class="input" id="email" name="email" value="{{ old('email', $setting->email) }}"></div>
        <div class="field"><label for="wa_number">Nomor WhatsApp admin</label><input class="input" id="wa_number" name="wa_number" value="{{ old('wa_number', $setting->wa_number) }}">@include('admin.partials.field-error', ['name' => 'wa_number'])</div>
      </div>
      <div class="form-2">
        <div class="field"><label for="opening_hours">Jam operasional</label><input class="input" id="opening_hours" name="opening_hours" value="{{ old('opening_hours', $setting->opening_hours) }}"></div>
        <div class="field"><label for="npwp">NPWP</label><input class="input" id="npwp" name="npwp" value="{{ old('npwp', $setting->npwp) }}"></div>
      </div>
      <div class="field"><label for="map_embed">Tautan sematan Google Maps</label><input class="input" id="map_embed" name="map_embed" value="{{ old('map_embed', $setting->map_embed) }}" placeholder="https://www.google.com/maps/embed?pb=…">@include('admin.partials.field-error', ['name' => 'map_embed'])<span class="field-hint">Di Google Maps: Bagikan → Sematkan peta, lalu salin isi atribut src saja.</span></div>
    </article>

    <article class="card form-stack">
      <div class="card-head"><div><h2>Pembayaran</h2></div></div>
      <div class="form-2">
        <div class="field"><label for="bank_name">Nama bank</label><input class="input" id="bank_name" name="bank_name" value="{{ old('bank_name', $setting->bank_name) }}"></div>
        <div class="field"><label for="bank_account">Nomor rekening</label><input class="input" id="bank_account" name="bank_account" value="{{ old('bank_account', $setting->bank_account) }}"></div>
      </div>
      <div class="field"><label for="bank_holder">Atas nama</label><input class="input" id="bank_holder" name="bank_holder" value="{{ old('bank_holder', $setting->bank_holder) }}"></div>
      <label class="check-line"><input type="checkbox" name="tax_enabled" value="1" @checked(old('tax_enabled', $setting->tax_enabled))> Kenakan PPN pada booking baru</label>
      <div class="field"><label for="tax_rate">Tarif PPN (%)</label><input class="input" id="tax_rate" name="tax_rate" type="number" min="0" max="100" value="{{ old('tax_rate', $setting->tax_rate ?? 11) }}">@include('admin.partials.field-error', ['name' => 'tax_rate'])</div>
      <div class="field">
        <label for="signature">Tanda tangan (gambar)</label>
        @if ($setting->signature_path)<img class="logo-preview" src="{{ Storage::disk('public')->url($setting->signature_path) }}" alt="Tanda tangan">@endif
        <input class="input" id="signature" name="signature" type="file" accept="image/jpeg,image/png,image/webp">
        @include('admin.partials.field-error', ['name' => 'signature'])
      </div>
    </article>

    <article class="card form-stack">
      <div class="card-head"><div><h2>Konten website</h2></div></div>
      <div class="field"><label for="hero_title">Judul beranda</label><textarea class="textarea" id="hero_title" name="hero_title" style="min-height:90px">{{ old('hero_title', $setting->hero_title) }}</textarea><span class="field-hint">Setiap baris tampil sebagai satu baris judul. Kata terakhir diberi warna merah.</span></div>
      <div class="field"><label for="hero_subtitle">Subjudul beranda</label><textarea class="textarea" id="hero_subtitle" name="hero_subtitle" style="min-height:90px">{{ old('hero_subtitle', $setting->hero_subtitle) }}</textarea></div>
      <div class="field">
        <label for="hero">Gambar beranda</label>
        @if ($setting->hero_image)<img class="logo-preview" src="{{ Storage::disk('public')->url($setting->hero_image) }}" alt="Gambar beranda">@endif
        <input class="input" id="hero" name="hero" type="file" accept="image/jpeg,image/png,image/webp">
        @include('admin.partials.field-error', ['name' => 'hero'])
      </div>
      <div class="field"><label for="about">Tentang kami</label><textarea class="textarea" id="about" name="about">{{ old('about', $setting->about) }}</textarea></div>
    </article>

    <article class="card form-stack">
      <div class="card-head"><div><h2>Sosial media</h2></div></div>
      <div class="field"><label for="instagram_url">Instagram</label><input class="input" id="instagram_url" name="instagram_url" type="url" value="{{ old('instagram_url', $setting->instagram_url) }}">@include('admin.partials.field-error', ['name' => 'instagram_url'])</div>
      <div class="field"><label for="tiktok_url">TikTok</label><input class="input" id="tiktok_url" name="tiktok_url" type="url" value="{{ old('tiktok_url', $setting->tiktok_url) }}">@include('admin.partials.field-error', ['name' => 'tiktok_url'])</div>
      <div class="field"><label for="youtube_url">YouTube</label><input class="input" id="youtube_url" name="youtube_url" type="url" value="{{ old('youtube_url', $setting->youtube_url) }}">@include('admin.partials.field-error', ['name' => 'youtube_url'])</div>
    </article>

    <div class="form-actions"><button class="btn btn-red" type="submit">Simpan pengaturan</button></div>
  </form>

  <form class="form-stack" method="post" action="{{ route('admin.password.update') }}" novalidate>
    @csrf
    @method('PUT')
    <article class="card form-stack">
      <div class="card-head"><div><h2>Ganti kata sandi</h2></div></div>
      <div class="field"><label for="current_password">Kata sandi saat ini</label><input class="input" id="current_password" name="current_password" type="password" autocomplete="current-password">@include('admin.partials.field-error', ['name' => 'current_password'])</div>
      <div class="form-2">
        <div class="field"><label for="password">Kata sandi baru</label><input class="input" id="password" name="password" type="password" autocomplete="new-password">@include('admin.partials.field-error', ['name' => 'password'])</div>
        <div class="field"><label for="password_confirmation">Ulangi kata sandi baru</label><input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div>
      </div>
      <div class="form-actions"><button class="btn btn-light" type="submit">Ganti kata sandi</button></div>
    </article>
  </form>
@endsection
