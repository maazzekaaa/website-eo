<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Portfolio;
use App\Models\Service;
use Illuminate\Contracts\View\View;

/**
 * Beranda satu halaman (one-scroll): hero, layanan, portofolio + klien, tentang, kontak.
 * Halaman /layanan, /portofolio, /tentang, /kontak dialihkan ke bagiannya di sini (routes/web.php).
 */
class HomeController extends Controller
{
    /** Jumlah proyek di grid foto portofolio (bento: 1 besar + 4 kecil). */
    public const PROJECTS = 5;

    public function __invoke(): View
    {
        // Proyek bergambar, hanya Pernikahan & Korporat: unggulan lebih dulu, lalu terbaru.
        $projects = Portfolio::with(['client', 'images'])
            ->whereIn('category', Portfolio::CATEGORIES)
            ->has('images')
            ->orderByDesc('is_featured')
            ->latest('event_date')
            ->take(self::PROJECTS)
            ->get();

        return view('site.home', [
            'services' => Service::active()->with('options')->orderBy('sort_order')->get(),
            'projects' => $projects,
            'gallery' => $projects->map(fn (Portfolio $portfolio) => $portfolio->galleryItem())->values(),
            'clients' => Client::withCount('portfolios')->orderByDesc('portfolios_count')->limit(12)->get(),
        ]);
    }
}
