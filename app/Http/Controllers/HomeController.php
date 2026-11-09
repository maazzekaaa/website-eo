<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Portfolio;
use App\Models\Service;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('site.home', [
            'heroLines' => preg_split('/\R/', trim((string) setting('hero_title'))),
            'services' => Service::active()->with('options')->orderBy('sort_order')->get(),
            'portfolios' => Portfolio::with(['client', 'images'])->where('is_featured', true)->latest('event_date')->limit(8)->get(),
            'clients' => Client::withCount('portfolios')->orderByDesc('portfolios_count')->limit(6)->get(),
        ]);
    }
}
