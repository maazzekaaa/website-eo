<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    public function test_normalize(): void
    {
        $wa = app(WhatsAppService::class);

        $this->assertSame('6281234567890', $wa->normalize('0812-3456-7890'));
        $this->assertSame('6281234567890', $wa->normalize('+62 812 3456 7890'));
        $this->assertSame('6281234567890', $wa->normalize('81234567890'));
    }

    public function test_pesan_wa(): void
    {
        $this->seedCatalog();
        Setting::current()->update(['wa_number' => '628111222333']);
        $booking = $this->makeBooking('2030-06-01');
        $wa = app(WhatsAppService::class);

        $message = $wa->bookingMessage($booking);
        foreach ([$booking->booking_code, '- Rigging (12 m): Rp1.800.000', 'Total: Rp11.600.000', 'DP 50%: Rp5.800.000', 'Tanggal: 1 Juni 2030, 18:00', 'Lokasi: Gedung Serbaguna', 'Nama: CV Cahaya Abadi'] as $needle) {
            $this->assertStringContainsString($needle, $message);
        }
        $this->assertSame('https://wa.me/628111222333?text='.rawurlencode($message), $wa->bookingUrl($booking));
    }
}
