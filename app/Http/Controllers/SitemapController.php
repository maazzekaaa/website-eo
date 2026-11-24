<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect(['home', 'pesan.create'])
            ->map(fn ($name) => route($name))
            ->merge(Service::active()->pluck('slug')->map(fn ($slug) => route('layanan.show', $slug)))
            ->merge(Portfolio::pluck('slug')->map(fn ($slug) => route('portofolio.show', $slug)));

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }
}
