<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Contracts\View\View;

class ServiceController extends Controller
{
    public function show(string $slug): View
    {
        return view('site.layanan.show', [
            'service' => Service::active()->with(['options' => fn ($q) => $q->where('is_active', true)])
                ->where('slug', $slug)->firstOrFail(),
        ]);
    }
}
