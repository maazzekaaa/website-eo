<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index(): View
    {
        $rows = Client::with('portfolios.images')->withCount('portfolios')
            ->orderByDesc('portfolios_count')->orderBy('name')->get()
            ->values()->map(function (Client $client, int $i) {
                $latest = $client->portfolios->sortByDesc('event_date')->first();
                $image = $client->portfolios->sortByDesc('event_date')
                    ->flatMap->images->first();
                $logo = $client->logo ? Storage::disk('public')->url($client->logo) : null;
                $year = $latest?->event_date?->format('Y');
                $number = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);

                return [
                    'client' => $client,
                    'number' => $number,
                    'category' => $latest?->category,
                    'year' => $year,
                    'thumb' => $image?->thumbUrl() ?? $logo,
                    'preview' => $image?->url() ?? $logo,
                    'caption' => collect([$number, $client->name, $latest?->category, $year])->filter()->implode(' · '),
                ];
            });

        return view('site.klien.index', ['rows' => $rows]);
    }

    public function show(Client $client): View
    {
        return view('site.klien.show', ['client' => $client->load(['portfolios.images', 'portfolios.client'])]);
    }
}
