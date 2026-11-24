<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Portofolio kini hanya Pernikahan (Wedding) & Korporat (Corporate).
 * Data lama dipindahkan agar tidak hilang dari halaman publik:
 * Concert → Corporate, Photoshoot → Corporate (kecuali yang judulnya berbau wedding).
 * Teks bawah judul beranda diganti sesuai revisi.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('portfolios')->where('category', 'Photoshoot')
            ->where(fn ($q) => $q->where('title', 'like', '%wedding%')->orWhere('title', 'like', '%nikah%'))
            ->update(['category' => 'Wedding']);
        DB::table('portfolios')->whereNotIn('category', ['Wedding', 'Corporate'])->update(['category' => 'Corporate']);

        DB::table('settings')->update([
            'hero_title' => 'Ada cerita di balik setiap perayaan.',
            'hero_subtitle' => 'Mari tulis dan wujudkan ceritamu bersama kami.',
        ]);
        Cache::forget('settings.current');   // pengaturan disimpan di cache (lihat Setting::current)
    }

    public function down(): void
    {
        // Kategori asal tidak disimpan; tidak dapat dikembalikan.
    }
};
