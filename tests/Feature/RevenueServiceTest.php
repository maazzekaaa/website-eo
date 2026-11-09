<?php

namespace Tests\Feature;

use App\Services\PaymentService;
use App\Services\RevenueService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class RevenueServiceTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function revenue(): RevenueService
    {
        return app(RevenueService::class);
    }

    public function test_kartu_hari_ini_dan_bulan_ini(): void
    {
        Carbon::setTestNow('2030-03-31 23:00:00');
        $booking = $this->makeBooking();
        $this->pay($booking, 1000000, 'dp', '2030-03-31');
        $this->pay($booking, 2000000, 'dp', '2030-03-01');
        $void = $this->pay($booking, 500000, 'dp', '2030-03-31');
        app(PaymentService::class)->void($void, 'Salah');

        $this->assertSame(1000000, $this->revenue()->today());
        $this->assertSame(3000000, $this->revenue()->month());

        Carbon::setTestNow('2030-04-01 00:30:00');
        $this->assertSame(0, $this->revenue()->today());
        $this->assertSame(0, $this->revenue()->month());

        $data = $this->revenue()->dashboardData();
        $this->assertSame(1000000, $data['daily']['2030-03'][30]);
        $this->assertSame(3000000, $data['monthly']['2030'][2]);
    }

    public function test_bentuk_dashboard_data(): void
    {
        Carbon::setTestNow('2030-03-09 10:00:00');
        $booking = $this->makeBooking();
        $this->pay($booking, 1000000, 'dp', '2030-02-10');
        $this->pay($booking, 3000000, 'dp', '2030-03-02');

        $data = $this->revenue()->dashboardData();

        $this->assertSame('2030-03-09', $data['today']);
        $this->assertCount(28, $data['daily']['2030-02']);
        $this->assertCount(9, $data['daily']['2030-03']);
        $this->assertSame(1000000, $data['daily']['2030-02'][9]);
        $this->assertCount(3, $data['monthly']['2030']);
        $row = collect($data['bookings'])->firstWhere('code', $booking->booking_code);
        $this->assertSame(4000000, $row['paid']);
        $this->assertSame(11600000, $row['total']);
        $this->assertSame(5800000, $row['dp']);
        $this->assertSame('pending', $row['status']);
        $this->assertSame('2030-06-01', $row['date']);
        $this->assertStringContainsString('Rigging 12 m', $row['items']);
    }

    public function test_tanpa_pembayaran_periode_berjalan_tetap_ada(): void
    {
        Carbon::setTestNow('2030-03-09 10:00:00');

        $data = $this->revenue()->dashboardData();

        $this->assertCount(9, $data['daily']['2030-03']);
        $this->assertCount(3, $data['monthly']['2030']);
    }
}
