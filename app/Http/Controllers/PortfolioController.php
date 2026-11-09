<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Contracts\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        return view('site.portofolio.index', [
            'portfolios' => Portfolio::with(['client', 'images'])->latest('event_date')->get(),
        ]);
    }

    public function show(Portfolio $portfolio): View
    {
        return view('site.portofolio.show', ['portfolio' => $portfolio->load(['client', 'images'])]);
    }
}
