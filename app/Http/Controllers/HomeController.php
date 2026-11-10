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
    private const MAX_PRINTS = 11;

    public function __invoke(): View
    {
        return view('site.home', [
            'prints' => $this->prints(),
            'services' => Service::active()->with('options')->orderBy('sort_order')->get(),
            'clients' => Client::withCount('portfolios')->orderByDesc('portfolios_count')->limit(6)->get(),
        ]);
    }

    /** Foto di tumpukan beranda: gambar hero dari pengaturan, lalu foto portofolio unggulan. */
    private function prints(): Collection
    {
        $prints = Portfolio::with(['images', 'client'])->where('is_featured', true)->latest('event_date')->get()
            ->flatMap(fn (Portfolio $portfolio) => $portfolio->images->take(2)->values()->map(fn ($image, $i) => [
                'url' => $image->url(),
                'title' => $portfolio->title,
                'link' => route('portofolio.show', $portfolio),
                'order' => $i,
                'index' => $i,
                'client' => $portfolio->client?->name ?? 'Klien Pribadi',
                'category' => $portfolio->category,
                'location' => $portfolio->location,
                'date' => $portfolio->event_date?->translatedFormat('j F Y'),
                'photos' => $portfolio->images->map(fn ($photo) => $photo->url())->values()->all(),
            ]))
            ->sortBy('order')->values();    // foto pertama tiap acara dulu agar tampilannya beragam

        if (setting('hero_image')) {
            $prints->prepend([
                'url' => Storage::disk('public')->url(setting('hero_image')),
                'title' => (string) setting('company_name'),
                'link' => route('portofolio.index'),
                'order' => -1,
                'index' => 0,
                'client' => null,
                'category' => null,
                'location' => null,
                'date' => null,
                'photos' => [Storage::disk('public')->url(setting('hero_image'))],
            ]);
        }

        return $prints->take(self::MAX_PRINTS);
    }
}
