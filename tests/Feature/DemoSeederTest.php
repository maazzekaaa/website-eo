<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Portfolio;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_tanpa_data_demo(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, Booking::count());
        $this->assertSame(0, Portfolio::count());
    }

    public function test_demo_seeder_berjalan_dan_konsisten(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoSeeder::class);

        $this->assertSame(8, Portfolio::count());
        $this->assertSame(3, Client::count());
        $this->assertSame(0, Portfolio::whereNull('client_id')->count());
        $this->assertSame(0, Portfolio::where('location', 'like', '[%')->count());
        $this->assertGreaterThanOrEqual(3, Portfolio::first()->images()->count());
        $this->assertGreaterThanOrEqual(7, Booking::count());

        foreach (Booking::all() as $booking) {
            $paid = $booking->paidAmount();
            $expected = $paid >= $booking->total ? 'paid' : ($paid >= $booking->dp_amount ? 'dp' : 'unpaid');
            $this->assertSame($expected, $booking->payment_status, $booking->customer_name);
        }

        $locked = Booking::whereNotNull('confirmed_date')->pluck('confirmed_date');
        $this->assertSame($locked->count(), $locked->unique()->count());
        $this->assertSame(1, Booking::where('customer_name', 'CV Cahaya Abadi')->where('status', 'pending')->where('payment_status', 'dp')->count());
    }
}
