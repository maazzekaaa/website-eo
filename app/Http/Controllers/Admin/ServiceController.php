<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', ['services' => Service::with('options')->orderBy('sort_order')->get()]);
    }

    public function create(): View
    {
        return view('admin.services.form', ['service' => new Service(['pricing_type' => 'tier', 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $service = Service::create($this->attributes($request, $data) + ['slug' => $this->uniqueSlug($data['name'])]);
        $this->syncOptions($service, $data);

        return redirect()->route('admin.services.index')->with('toast', "Layanan {$service->name} ditambahkan.");
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', ['service' => $service->load('options')]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $this->validated($request);

        // Slug tidak berubah agar tautan lama tetap berlaku. Booking lama aman karena harga sudah disalin.
        $service->update($this->attributes($request, $data));
        $this->syncOptions($service, $data);

        return redirect()->route('admin.services.index')->with('toast', "Layanan {$service->name} disimpan.");
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('toast', "Layanan {$service->name} dihapus.");
    }

    private function validated(Request $request): array
    {
        $request->merge([
            'unit_price' => preg_replace('/\D/', '', (string) $request->input('unit_price')),
            'options' => collect($request->input('options', []))
                ->filter(fn ($o) => is_array($o) && (filled($o['name'] ?? null) || filled($o['price'] ?? null)))
                ->map(fn ($o) => ['price' => preg_replace('/\D/', '', (string) ($o['price'] ?? ''))] + $o)
                ->values()->all(),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:60'],
            'pricing_type' => ['required', Rule::in(['tier', 'per_unit'])],
            'badge' => ['nullable', Rule::in(['promo', 'baru'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'unit_label' => ['required_if:pricing_type,per_unit', 'nullable', 'string', 'max:20'],
            'unit_price' => ['required_if:pricing_type,per_unit', 'nullable', 'integer', 'min:1'],
            'options' => ['required_if:pricing_type,tier', 'array'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.name' => ['required', 'string', 'max:80'],
            'options.*.price' => ['required', 'integer', 'min:1'],
        ]);
    }

    private function attributes(Request $request, array $data): array
    {
        $isUnit = $data['pricing_type'] === 'per_unit';

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'pricing_type' => $data['pricing_type'],
            'badge' => $data['badge'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'unit_label' => $isUnit ? $data['unit_label'] : null,
            'unit_price' => $isUnit ? $data['unit_price'] : null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function syncOptions(Service $service, array $data): void
    {
        $rows = $data['pricing_type'] === 'tier' ? ($data['options'] ?? []) : [];
        $keep = [];

        foreach (array_values($rows) as $i => $row) {
            $attributes = ['name' => $row['name'], 'price' => (int) $row['price'], 'sort_order' => $i + 1];
            $option = ! empty($row['id']) ? $service->options()->whereKey($row['id'])->first() : null;
            $option ? $option->update($attributes) : $option = $service->options()->create($attributes);
            $keep[] = $option->id;
        }

        $service->options()->whereNotIn('id', $keep)->delete();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'layanan';
        $slug = $base;
        for ($i = 2; Service::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
