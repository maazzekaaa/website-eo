<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    private function booking(string $code, string $status): Booking
    {
        return Booking::create([
            'booking_code' => $code, 'invoice_number' => 'INV/'.$code, 'customer_name' => 'A',
            'customer_wa' => '628123456789', 'event_date' => '2030-01-01', 'event_location' => 'X',
            'subtotal' => 1, 'tax_rate' => 0, 'tax_amount' => 0, 'total' => 1, 'dp_amount' => 1,
            'status' => $status, 'payment_status' => 'unpaid',
        ]);
    }

    public function test_dua_booking_confirmed_di_tanggal_sama_ditolak_database(): void
    {
        $this->booking('A', 'confirmed');
        $this->expectException(UniqueConstraintViolationException::class);
        $this->booking('B', 'confirmed');
    }

    public function test_banyak_pending_di_tanggal_sama_boleh(): void
    {
        $this->booking('A', 'pending');
        $this->booking('B', 'pending');
        $this->assertSame(2, Booking::count());
    }

    public function test_seeder_mengisi_harga_prd(): void
    {
        $this->seed(DatabaseSeeder::class);

        $prices = fn (string $slug) => Service::where('slug', $slug)->firstOrFail()
            ->options()->orderBy('sort_order')->pluck('price')->all();

        $this->assertSame([3000000, 5000000, 7000000, 10000000], $prices('sound'));
        $this->assertSame([1500000, 2250000, 3000000], $prices('lighting'));
        $this->assertSame([1500000, 2500000, 3000000], $prices('multimedia'));
        foreach ([['videotron-indoor', 400000, 'm²'], ['videotron-outdoor', 650000, 'm²'], ['rigging', 150000, 'm']] as [$slug, $price, $unit]) {
            $s = Service::where('slug', $slug)->firstOrFail();
            $this->assertSame('per_unit', $s->pricing_type);
            $this->assertSame($price, $s->unit_price);
            $this->assertSame($unit, $s->unit_label);
        }
        $this->assertSame('baru', Service::where('slug', 'multimedia')->first()->badge);

        $setting = Setting::current();
        $this->assertFalse($setting->tax_enabled);
        $this->assertSame(11, $setting->tax_rate);
        $this->assertSame('62XXXXXXXXXX', $setting->wa_number);
    }
}
