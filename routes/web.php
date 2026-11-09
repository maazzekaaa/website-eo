<?php

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
