<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\PortfolioImage;
use Illuminate\Contracts\View\View;

class PortfolioController extends Controller
{
    private const MAX_PRINTS = 15;

    public function index(): View
    {
        $portfolios = Portfolio::with(['client', 'images'])->latest('event_date')->get();

        // Satu cetakan per foto. Acara tanpa foto tetap muncul sebagai satu cetakan kosong.
        $prints = $portfolios->flatMap(function (Portfolio $portfolio) {
            $photos = $portfolio->images->map(fn (PortfolioImage $image) => $image->url())->values();

            return ($portfolio->images->isEmpty() ? collect([null]) : $portfolio->images->values())
                ->map(fn (?PortfolioImage $image, int $index) => [
                    'portfolio' => $portfolio,
                    'photos' => $photos,
                    'thumb' => $image?->thumbUrl(),
                    'index' => $index,
                ]);
        })->sortBy('index')->values()->take(self::MAX_PRINTS);   // foto pertama tiap acara dulu agar beragam

        return view('site.portofolio.index', [
            'prints' => $prints,
            'counts' => $prints->countBy(fn (array $print) => $print['portfolio']->category),
        ]);
    }

    public function show(Portfolio $portfolio): View
    {
        return view('site.portofolio.show', ['portfolio' => $portfolio->load(['client', 'images'])]);
    }
}
