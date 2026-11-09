<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\RevenueController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/layanan', [ServiceController::class, 'index'])->name('layanan.index');
Route::get('/layanan/{slug}', [ServiceController::class, 'show'])->name('layanan.show');
Route::get('/portofolio', [PortfolioController::class, 'index'])->name('portofolio.index');
Route::get('/portofolio/{portfolio}', [PortfolioController::class, 'show'])->name('portofolio.show');
Route::get('/klien', [ClientController::class, 'index'])->name('klien.index');
Route::get('/klien/{client}', [ClientController::class, 'show'])->name('klien.show');
Route::get('/tentang', [PageController::class, 'about'])->name('tentang');
Route::get('/kontak', [PageController::class, 'contact'])->name('kontak');
Route::get('/cari', SearchController::class)->name('cari');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/api/availability', AvailabilityController::class)->name('availability');
Route::post('/api/cart/quote', CartController::class)->middleware('throttle:10,1')->name('cart.quote');

Route::get('/pesan', [BookingController::class, 'create'])->name('pesan.create');
Route::post('/pesan', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('pesan.store');
Route::get('/pesan/sukses/{code}', [BookingController::class, 'success'])->name('pesan.success');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AuthController::class, 'show'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/revenue', RevenueController::class)->name('revenue');

        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm'])->name('bookings.confirm');
        Route::post('/bookings/{booking}/reject', [AdminBookingController::class, 'reject'])->name('bookings.reject');
        Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/complete', [AdminBookingController::class, 'complete'])->name('bookings.complete');
        Route::get('/bookings/{booking}/invoice', [DocumentController::class, 'invoice'])->name('bookings.invoice');

        Route::post('/bookings/{booking}/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('/payments/{payment}/void', [PaymentController::class, 'void'])->name('payments.void');
        Route::get('/payments/{payment}/proof', [PaymentController::class, 'proof'])->name('payments.proof');
        Route::get('/payments/{payment}/receipt', [DocumentController::class, 'receipt'])->name('payments.receipt');

        Route::resource('services', AdminServiceController::class)->except('show');
        Route::resource('portfolios', AdminPortfolioController::class)->except('show');
        Route::delete('/portfolio-images/{image}', [AdminPortfolioController::class, 'destroyImage'])->name('portfolios.images.destroy');
        Route::resource('clients', AdminClientController::class)->except('show');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::put('/password', [SettingController::class, 'updatePassword'])->name('password.update');
    });
});
