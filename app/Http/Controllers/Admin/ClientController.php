<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ImageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(private ImageService $images) {}

    public function index(): View
    {
        return view('admin.clients.index', ['clients' => Client::withCount('portfolios')->orderBy('name')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.clients.form', ['client' => new Client]);
    }

    public function store(Request $request): RedirectResponse
    {
        $client = Client::create($this->validated($request, new Client));

        return redirect()->route('admin.clients.index')->with('toast', "Klien {$client->name} ditambahkan.");
    }

    public function edit(Client $client): View
    {
        return view('admin.clients.form', ['client' => $client]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($this->validated($request, $client));

        return redirect()->route('admin.clients.index')->with('toast', "Klien {$client->name} disimpan.");
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->images->delete($client->logo);
        $client->delete();

        return redirect()->route('admin.clients.index')->with('toast', "Klien {$client->name} dihapus.");
    }

    private function validated(Request $request, Client $client): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        unset($data['logo']);

        if ($request->file('logo')) {
            $this->images->delete($client->logo);
            $data['logo'] = $this->images->store($request->file('logo'), 'clients');
        }

        return $data;
    }
}
