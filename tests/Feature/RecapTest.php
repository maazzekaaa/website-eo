<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Services\RevenueService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class RecapTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_rekap_butuh_login(): void
    {
        $this->get(route('admin.recap'))->assertRedirect(route('admin.login'));
    }

    public function test_rekap_bulanan_dan_tahunan_berupa_pdf(): void
    {
        $this->actingAs(Admin::first(), 'admin');
        $booking = $this->makeBooking('2030-01-20');
        $this->pay($booking, 5800000, 'dp', '2030-01-15');

        foreach (['2030-01' => 'rekap-pendapatan-2030-01.pdf', '2030' => 'rekap-pendapatan-2030.pdf'] as $periode => $file) {
            $response = $this->get(route('admin.recap', ['periode' => $periode]))->assertOk()
                ->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringContainsString($file, (string) $response->headers->get('Content-Disposition'));
            $this->assertStringStartsWith('%PDF', $response->getContent());
        }
        $this->get(route('admin.recap'))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_periode_tidak_valid_404(): void
    {
        $this->actingAs(Admin::first(), 'admin');
        $this->get(route('admin.recap', ['periode' => '2030-13']))->assertNotFound();
        $this->get(route('admin.recap', ['periode' => 'abc']))->assertNotFound();
    }

    public function test_data_rekap_menghitung_pembayaran_valid_pada_periode(): void
    {
        $a = $this->makeBooking('2030-01-20');
        $b = $this->makeBooking('2030-02-10');
        $this->pay($a, 5800000, 'dp', '2030-01-15');
        $this->pay($b, 1000000, 'dp', '2030-02-01');                 // di luar Januari
        $void = $this->pay($a, 500000, 'lainnya', '2030-01-16');
        $void->update(['voided_at' => now()]);                       // di-void: tidak dihitung

        $from = CarbonImmutable::parse('2030-01-01');
        $data = app(RevenueService::class)->recap($from, $from->endOfMonth()->startOfDay(), false);

        $this->assertSame(5800000, $data['total']);
        $this->assertSame(1, $data['count']);
        $this->assertSame(5800000, $data['byType']['dp']);
        $this->assertCount(31, $data['buckets']);
        $this->assertSame(5800000, $data['buckets'][14]['total']);     // tanggal 15
        $this->assertSame(5800000, $data['peak']);
        $this->assertCount(1, $data['events']);                       // hanya acara 20 Jan
        $this->assertSame($a->total - 5800000, $data['outstanding']);

        $yearly = app(RevenueService::class)->recap($from, $from->endOfYear()->startOfDay(), true);
        $this->assertCount(12, $yearly['buckets']);
        $this->assertSame(6800000, $yearly['total']);
    }
}
