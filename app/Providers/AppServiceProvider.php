<?php

namespace App\Providers;

use App\Models\Booking;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('layouts.admin', function ($view) {
            $pending = Booking::where('status', 'pending');

            $view->with([
                'pendingCount' => (clone $pending)->count(),
                'notifications' => (clone $pending)->latest('id')->limit(5)->get(),
            ]);
        });
    }
}
