<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Portfolio;
use App\Models\PortfolioImage;
use App\Services\ImageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PortfolioController extends Controller
{
    public function __construct(private ImageService $images) {}

    public function index(): View
    {
        return view('admin.portfolios.index', ['portfolios' => Portfolio::with('client')->withCount('images')->latest('event_date')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.portfolios.form', ['portfolio' => new Portfolio(['folder_color' => 'red', 'category' => 'Wedding']), 'clients' => Client::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $portfolio = Portfolio::create($data + ['slug' => $this->uniqueSlug($data['title']), 'is_featured' => $request->boolean('is_featured')]);
        $this->storePhotos($request, $portfolio);

        return redirect()->route('admin.portfolios.index')->with('toast', "Portofolio {$portfolio->title} ditambahkan.");
    }

    public function edit(Portfolio $portfolio): View
    {
        return view('admin.portfolios.form', ['portfolio' => $portfolio->load('images'), 'clients' => Client::orderBy('name')->get()]);
    }

    public function update(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $portfolio->update($this->validated($request) + ['is_featured' => $request->boolean('is_featured')]);
        $this->storePhotos($request, $portfolio);

        return redirect()->route('admin.portfolios.edit', $portfolio)->with('toast', "Portofolio {$portfolio->title} disimpan.");
    }

    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        $portfolio->images->each(fn (PortfolioImage $image) => $this->images->delete($image->path));
        $portfolio->delete();

        return redirect()->route('admin.portfolios.index')->with('toast', "Portofolio {$portfolio->title} dihapus.");
    }

    public function destroyImage(PortfolioImage $image): RedirectResponse
    {
        $this->images->delete($image->path);
        $image->delete();

        return back()->with('toast', 'Foto dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'category' => ['required', Rule::in(Portfolio::CATEGORIES)],
            'event_date' => ['nullable', 'date_format:Y-m-d'],
            'location' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'services' => ['nullable', 'string', 'max:255'],
            'story' => ['nullable', 'string', 'max:5000'],
            'folder_color' => ['required', Rule::in(Portfolio::COLORS)],
            'photos' => ['nullable', 'array', 'max:40'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        unset($data['photos']);

        return $data;
    }

    private function storePhotos(Request $request, Portfolio $portfolio): void
    {
        $order = (int) $portfolio->images()->max('sort_order');
        foreach ($request->file('photos', []) as $photo) {
            $portfolio->images()->create(['path' => $this->images->store($photo, 'portfolios'), 'sort_order' => ++$order]);
        }
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'portofolio';
        $slug = $base;
        for ($i = 2; Portfolio::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
