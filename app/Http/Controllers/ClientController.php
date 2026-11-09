<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Contracts\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        return view('site.klien.index', ['clients' => Client::withCount('portfolios')->orderBy('name')->get()]);
    }

    public function show(Client $client): View
    {
        return view('site.klien.show', ['client' => $client->load(['portfolios.images', 'portfolios.client'])]);
    }
}
