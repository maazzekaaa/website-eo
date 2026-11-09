<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(RevenueService $revenue): View
    {
        $data = $revenue->dashboardData();
        $data['bookings'] = array_map(fn (array $b) => $b + [
            'url' => route('admin.bookings.show', $b['id']),
            'confirmUrl' => route('admin.bookings.confirm', $b['id']),
        ], $data['bookings']);

        return view('admin.dashboard', ['dashboard' => $data]);
    }
}
