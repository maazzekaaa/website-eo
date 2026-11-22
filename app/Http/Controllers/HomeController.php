<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Portfolio;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    private const MAX_SLIDES = 6;

    public function __invoke(): View
    {
        return view('site.home', [
            'slides' => $this->slides(),
            'stats' => [
                'events' => Portfolio::count(),
                'clients' => Client::count(),
                'categories' => Portfolio::query()->distinct()->count('category'),
            ],
            'services' => Service::active()->with('options')->orderBy('sort_order')->get(),
            'clients' => Client::withCount('portfolios')->orderByDesc('portfolios_count')->limit(12)->get(),
        ]);
    }

    /** Proyek di etalase beranda: gambar hero dari pengaturan (jika ada), lalu satu sampul per portofolio unggulan. */
    private function slides(): Collection
    {
        $slides = Portfolio::with(['images', 'client'])->where('is_featured', true)->latest('event_date')->get()
            ->filter(fn (Portfolio $portfolio) => $portfolio->images->isNotEmpty())
            ->map(fn (Portfolio $portfolio) => [
                'url' => $portfolio->images->first()->url(),
                'title' => $portfolio->title,
                'link' => route('portofolio.show', $portfolio),
                'client' => $portfolio->client?->name ?? 'Klien pribadi',
                'category' => $portfolio->category,
                'location' => $portfolio->location,
                'date' => $portfolio->event_date?->translatedFormat('j F Y'),
                'photos' => $portfolio->images->count(),
            ])
            ->values();

        if (setting('hero_image')) {
            $slides->prepend([
                'url' => Storage::disk('public')->url(setting('hero_image')),
                'title' => (string) setting('company_name'),
                'link' => route('portofolio.index'),
                'client' => null,
                'category' => 'Portofolio',
                'location' => null,
                'date' => null,
                'photos' => null,
            ]);
        }

        return $slides->take(self::MAX_SLIDES);
    }
}
