<?php

namespace Tests\Feature;

use App\Exceptions\BookingRuleException;
use App\Exceptions\DateUnavailable;
use App\Models\Booking;
use App\Models\Service;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesBookings;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    private function service(): BookingService
    {
        return app(BookingService::class);
    }

    public function test_create_menyimpan_snapshot(): void
    {
        $booking = $this->makeBooking();
        Service::where('slug', 'rigging')->update(['unit_price' => 999]);

        $item = $booking->items()->where('description', 'Rigging')->first();

        $this->assertSame(150000, $item->unit_price);
        $this->assertSame(1800000, $item->subtotal);
        $this->assertCount(3, $booking->items);
        $this->assertMatchesRegularExpression('/^FZM-\d{8}-\d{4}$/', $booking->booking_code);
        $this->assertMatchesRegularExpression('#^INV/\d{4}/\d{2}/\d{3}$#', $booking->invoice_number);
        $this->assertSame('pending', $booking->status);
        $this->assertSame('unpaid', $booking->payment_status);
        $this->assertSame(11600000, $booking->total);
        $this->assertSame(5800000, $booking->dp_amount);
    }

    public function test_create_tanggal_terkunci(): void
    {
        $this->confirmedBooking('2030-06-01');

        $this->expectException(DateUnavailable::class);
        $this->makeBooking('2030-06-01');
    }

    public function test_konfirmasi_butuh_dp(): void
    {
        $booking = $this->makeBooking();
        $this->pay($booking, $booking->dp_amount - 1000);

        $this->expectException(BookingRuleException::class);
        $this->service()->confirm($booking);
    }

    public function test_konfirmasi_mengunci_tanggal(): void
    {
        $booking = $this->confirmedBooking();

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('2030-06-01', substr((string) $booking->confirmed_date, 0, 10));
    }

    public function test_konfirmasi_tanggal_sudah_dikunci(): void
    {
        $other = $this->makeBooking('2030-06-01');
        $this->pay($other, $other->dp_amount);
        $this->confirmedBooking('2030-06-01');

        $this->expectException(DateUnavailable::class);
        $this->service()->confirm($other);
    }

    public function test_konfirmasi_menangkap_pelanggaran_unique(): void
    {
        $other = $this->makeBooking('2030-06-01');
        $this->pay($other, $other->dp_amount);
        $this->confirmedBooking('2030-06-01');

        // Simulasi balapan: pemeriksaan awal lolos, tetapi unique index menolak.
        $racing = new class(app(\App\Services\DocumentNumberService::class)) extends BookingService
        {
            protected function dateTaken(string $date, ?int $exceptId = null): bool
            {
                return false;
            }
        };

        $this->expectException(DateUnavailable::class);
        $racing->confirm($other);
    }

    public function test_selesai_butuh_lunas(): void
    {
        $booking = $this->confirmedBooking();

        $this->expectException(BookingRuleException::class);
        $this->service()->complete($booking);
    }

    public function test_selesai_saat_lunas(): void
    {
        $booking = $this->confirmedBooking();
        $this->pay($booking, $booking->restAmount(), 'pelunasan');

        $this->service()->complete($booking);

        $this->assertSame('completed', $booking->fresh()->status);
    }

    #[DataProvider('transisiTidakSah')]
    public function test_transisi_tidak_sah_ditolak(string $status, string $action): void
    {
        $booking = $this->makeBooking();
        Booking::whereKey($booking->id)->update(['status' => $status]);
        $this->pay($this->makeBooking('2030-07-01'), 1000);

        $this->expectException(BookingRuleException::class);
        match ($action) {
            'confirm' => $this->service()->confirm($booking->fresh()),
            'reject' => $this->service()->reject($booking->fresh(), null),
            'cancel' => $this->service()->cancel($booking->fresh(), null),
            'complete' => $this->service()->complete($booking->fresh()),
        };
    }

    public static function transisiTidakSah(): array
    {
        return [
            ['rejected', 'confirm'], ['cancelled', 'confirm'], ['completed', 'cancel'],
            ['confirmed', 'reject'], ['completed', 'reject'], ['pending', 'complete'],
            ['rejected', 'cancel'],
        ];
    }

    public function test_completed_tetap_mengunci_tanggal(): void
    {
        $booking = $this->confirmedBooking('2030-06-01');
        $this->pay($booking, $booking->restAmount(), 'pelunasan');
        $this->service()->complete($booking);

        $this->expectException(DateUnavailable::class);
        $this->makeBooking('2030-06-01');
    }

    public function test_batal_membuka_tanggal(): void
    {
        $booking = $this->confirmedBooking('2030-06-01');
        $this->service()->cancel($booking, null);

        $this->assertSame('pending', $this->makeBooking('2030-06-01')->status);
    }

    public function test_alasan_tolak_masuk_admin_note(): void
    {
        $booking = $this->makeBooking();
        $this->service()->reject($booking, 'Jadwal kru penuh');

        $this->assertStringContainsString('Jadwal kru penuh', $booking->fresh()->admin_note);
        $this->assertSame('rejected', $booking->fresh()->status);
    }
}
