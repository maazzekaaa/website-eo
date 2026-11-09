<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $like = '%'.addcslashes($q, '%_\\').'%';

        return view('site.cari', [
            'q' => $q,
            'services' => $q === '' ? collect() : Service::active()->with('options')
                ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('description', 'like', $like))
                ->orderBy('sort_order')->get(),
            'portfolios' => $q === '' ? collect() : Portfolio::query()
                ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('category', 'like', $like)->orWhere('story', 'like', $like))
                ->latest('event_date')->get(),
        ]);
    }
}
