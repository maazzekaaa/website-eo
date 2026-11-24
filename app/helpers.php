<?php

use App\Models\Setting;

if (! function_exists('rupiah')) {
    /** Format bilangan bulat Rupiah, contoh Rp5.800.000. */
    function rupiah(int $n): string
    {
        return 'Rp'.number_format($n, 0, ',', '.');
    }
}

if (! function_exists('setting')) {
    /** Ambil satu nilai pengaturan perusahaan. */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::current()->getAttribute($key) ?? $default;
    }
}

if (! function_exists('terbilang')) {
    /** Angka ke kata, contoh 5800000 menjadi "lima juta delapan ratus ribu". */
    function terbilang(int $n): string
    {
        if ($n === 0) {
            return 'nol';
        }

        $satuan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $sisa = fn (int $x) => $x > 0 ? ' '.terbilang($x) : '';

        return match (true) {
            $n < 12 => $satuan[$n],
            $n < 20 => terbilang($n - 10).' belas',
            $n < 100 => terbilang(intdiv($n, 10)).' puluh'.$sisa($n % 10),
            $n < 200 => 'seratus'.$sisa($n - 100),
            $n < 1000 => terbilang(intdiv($n, 100)).' ratus'.$sisa($n % 100),
            $n < 2000 => 'seribu'.$sisa($n - 1000),
            $n < 1000000 => terbilang(intdiv($n, 1000)).' ribu'.$sisa($n % 1000),
            $n < 1000000000 => terbilang(intdiv($n, 1000000)).' juta'.$sisa($n % 1000000),
            $n < 1000000000000 => terbilang(intdiv($n, 1000000000)).' miliar'.$sisa($n % 1000000000),
            default => terbilang(intdiv($n, 1000000000000)).' triliun'.$sisa($n % 1000000000000),
        };
    }
}

if (! function_exists('asset_v')) {
    /** URL aset + ?v=<waktu ubah berkas>, agar peramban langsung memuat CSS/JS terbaru setelah diedit. */
    function asset_v(string $path): string
    {
        $file = public_path($path);

        return asset($path).(is_file($file) ? '?v='.filemtime($file) : '');
    }
}
