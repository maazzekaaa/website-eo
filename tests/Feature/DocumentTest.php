<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Setting;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
        $this->actingAs(Admin::first(), 'admin');
    }

    public function test_invoice_isi_lengkap(): void
    {
        Setting::current()->update(['bank_name' => 'Bank Uji', 'bank_account' => '123456']);
        $booking = $this->makeBooking('2030-12-14');
        $payment = $this->pay($booking, 5800000);

        $this->get(route('admin.bookings.invoice', $booking))->assertOk()
            ->assertSee($booking->invoice_number)
            ->assertSee($booking->booking_code)
            ->assertSee('14 Desember 2030')
            ->assertSee('Sound System – Paket 2')
            ->assertSee('Rp11.600.000')
            ->assertSee('Rp5.800.000')
            ->assertSee($payment->receipt_number)
            ->assertSee('Bank Uji')
            ->assertSee('123456')
            ->assertSee('DP DIBAYAR');
    }

    public function test_cap_lunas(): void
    {
        $booking = $this->makeBooking();
        $this->pay($booking, $booking->total, 'pelunasan');

        $this->get(route('admin.bookings.invoice', $booking))->assertSee('LUNAS')->assertSee('stamp--paid', false);
    }

    public function test_ppn_tampil_hanya_jika_ada(): void
    {
        $plain = $this->makeBooking('2030-06-01');
        $this->get(route('admin.bookings.invoice', $plain))->assertDontSee('PPN');

        Setting::current()->update(['tax_enabled' => true]);
        $taxed = $this->makeBooking('2030-06-02');
        $this->get(route('admin.bookings.invoice', $taxed))->assertSee('PPN 11%')->assertSee('Rp1.276.000');
    }

    public function test_invoice_mengabaikan_pembayaran_void(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->pay($booking, 5800000);
        app(PaymentService::class)->void($payment, 'Salah');

        $this->get(route('admin.bookings.invoice', $booking))->assertDontSee($payment->receipt_number)->assertDontSee('DP DIBAYAR');
    }

    public function test_kuitansi_terbilang(): void
    {
        $booking = $this->makeBooking();
        $first = $this->pay($booking, 5800000);
        $this->pay($booking, 1000000, 'pelunasan');

        $this->get(route('admin.payments.receipt', $first))->assertOk()
            ->assertSee($first->receipt_number)
            ->assertSee('Lima juta delapan ratus ribu rupiah')
            ->assertSee('CV Cahaya Abadi')
            ->assertSee($booking->invoice_number)
            ->assertSee('Sisa Rp5.800.000');
    }

    public function test_kuitansi_void_ditandai(): void
    {
        $payment = $this->pay($this->makeBooking(), 1000000);
        app(PaymentService::class)->void($payment, 'Salah input');

        $this->get(route('admin.payments.receipt', $payment))->assertSee('DIBATALKAN')->assertSee('Salah input');
    }

    public function test_pdf(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->pay($booking, 5800000);

        foreach ([route('admin.bookings.invoice', [$booking, 'pdf' => 1]), route('admin.payments.receipt', [$payment, 'pdf' => 1])] as $url) {
            $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF', $response->getContent());
            $this->assertStringNotContainsString('/', str_replace('attachment; filename=', '', (string) $response->headers->get('Content-Disposition')));
        }
    }

    public function test_dokumen_escape_nama(): void
    {
        $booking = $this->makeBooking('2030-06-01', ['customer_name' => '<b>Jahat</b>']);

        $this->assertStringNotContainsString('<b>Jahat</b>', $this->get(route('admin.bookings.invoice', $booking))->getContent());
    }

    public function test_dokumen_butuh_login(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->pay($booking, 1000);
        auth('admin')->logout();

        $this->get(route('admin.bookings.invoice', $booking))->assertNotFound();
        $this->get(route('admin.payments.receipt', $payment))->assertNotFound();
    }
}
