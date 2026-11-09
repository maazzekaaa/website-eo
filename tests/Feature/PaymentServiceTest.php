<?php

namespace Tests\Feature;

use App\Exceptions\BookingRuleException;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\RevenueService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesBookings;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    private function service(): PaymentService
    {
        return app(PaymentService::class);
    }

    public function test_nominal_melebihi_sisa_ditolak(): void
    {
        $booking = $this->makeBooking();

        $this->expectException(BookingRuleException::class);
        $this->pay($booking, $booking->total + 1);
    }

    public function test_nominal_nol_ditolak(): void
    {
        $this->expectException(BookingRuleException::class);
        $this->pay($this->makeBooking(), 0);
    }

    public function test_dp_lalu_pelunasan(): void
    {
        $booking = $this->makeBooking();
        $this->pay($booking, 1000000);
        $this->assertSame('unpaid', $booking->fresh()->payment_status);

        $dp = $this->pay($booking, 4800000);
        $this->assertSame('dp', $booking->fresh()->payment_status);
        $this->assertMatchesRegularExpression('#^KWT/\d{4}/\d{2}/\d{3}$#', $dp->receipt_number);

        $this->pay($booking, 5800000, 'pelunasan');
        $this->assertSame('paid', $booking->fresh()->payment_status);
        $this->assertSame(0, $booking->fresh()->restAmount());
    }

    public function test_void_menghitung_ulang(): void
    {
        $booking = $this->makeBooking();
        $first = $this->pay($booking, 5800000);

        $this->service()->void($first, 'Salah input');
        $this->assertSame('unpaid', $booking->fresh()->payment_status);
        $this->assertSame(0, $booking->fresh()->paidAmount());
        $this->assertSame('Salah input', $first->fresh()->void_reason);

        $second = $this->pay($booking, 5800000);
        $this->assertNotSame($first->receipt_number, $second->receipt_number);
    }

    public function test_void_alasan_wajib(): void
    {
        $payment = $this->pay($this->makeBooking(), 1000);

        $this->expectException(BookingRuleException::class);
        $this->service()->void($payment, '   ');
    }

    public function test_void_dua_kali_ditolak(): void
    {
        $payment = $this->pay($this->makeBooking(), 1000);
        $this->service()->void($payment, 'Salah');

        $this->expectException(BookingRuleException::class);
        $this->service()->void($payment->fresh(), 'Salah lagi');
    }

    public function test_void_confirmed_di_bawah_dp_tetap_confirmed(): void
    {
        $booking = $this->confirmedBooking();
        $payment = $booking->payments()->first();

        $this->service()->void($payment, 'Transfer gagal');

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('unpaid', $booking->payment_status);
    }

    public function test_void_completed_ditolak(): void
    {
        $booking = $this->confirmedBooking();
        $this->pay($booking, $booking->restAmount(), 'pelunasan');
        app(BookingService::class)->complete($booking);

        $this->expectException(BookingRuleException::class);
        $this->service()->void($booking->payments()->first(), 'Salah');
    }

    #[DataProvider('statusNonaktif')]
    public function test_bayar_booking_nonaktif_ditolak(string $status): void
    {
        $booking = $this->makeBooking();
        Booking::whereKey($booking->id)->update(['status' => $status]);

        $this->expectException(BookingRuleException::class);
        $this->pay($booking->fresh(), 1000);
    }

    public static function statusNonaktif(): array
    {
        return [['rejected'], ['cancelled'], ['completed']];
    }

    public function test_void_booking_cancelled_boleh(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->pay($booking, 1000);
        app(BookingService::class)->cancel($booking, null);

        $this->service()->void($payment, 'Dana dikembalikan');

        $this->assertNotNull($payment->fresh()->voided_at);
    }

    public function test_pembayaran_booking_batal_tetap_pendapatan(): void
    {
        Carbon::setTestNow('2030-03-10 10:00:00');
        $booking = $this->makeBooking();
        $this->pay($booking, 5800000, 'dp', '2030-03-10');
        app(BookingService::class)->cancel($booking, null);

        $this->assertSame(5800000, app(RevenueService::class)->today());
        Carbon::setTestNow();
    }
}
