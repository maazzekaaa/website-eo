<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Contracts\View\View;

class PortfolioController extends Controller
{
    /** Halaman studi kasus satu proyek. Daftar portofolio ada di beranda (#portofolio). */
    public function show(Portfolio $portfolio): View
    {
        return view('site.portofolio.show', ['portfolio' => $portfolio->load(['client', 'images'])]);
    }
}
