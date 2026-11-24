<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class AdminBookingTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
        $this->actingAs(Admin::first(), 'admin');
    }

    private function payment(array $extra = []): array
    {
        return $extra + ['amount' => '5.800.000', 'type' => 'dp', 'method' => 'transfer', 'paid_at' => now()->toDateString()];
    }

    public function test_dashboard_data_json_valid(): void
    {
        $booking = $this->makeBooking();

        $html = $this->get('/admin')->assertOk()->getContent();

        $this->assertSame(1, preg_match('#<script id="dashData" type="application/json">(.*?)</script>#s', $html, $m));
        $data = json_decode($m[1], true);
        $this->assertSame(now()->toDateString(), $data['today']);
        $this->assertSame($booking->booking_code, $data['bookings'][0]['code']);
        $this->assertSame(route('admin.bookings.show', $booking), $data['bookings'][0]['url']);
        $this->assertSame(route('admin.bookings.confirm', $booking), $data['bookings'][0]['confirmUrl']);
    }

    public function test_data_pendapatan_json(): void
    {
        $this->getJson('/admin/revenue')->assertOk()->assertJsonStructure(['today', 'daily', 'monthly']);
    }

    public function test_filter_daftar_booking(): void
    {
        $a = $this->makeBooking('2030-06-01', ['customer_name' => 'Andi Pratama']);
        $b = $this->confirmedBooking('2030-06-02');

        $codes = fn (string $url) => $this->get($url)->assertOk()->viewData('bookings')->pluck('booking_code')->all();

        $this->assertEqualsCanonicalizing([$a->booking_code, $b->booking_code], $codes('/admin/bookings'));
        $this->assertSame([$b->booking_code], $codes('/admin/bookings?status=confirmed'));
        $this->assertSame([$b->booking_code], $codes('/admin/bookings?payment_status=dp'));
        $this->assertSame([$a->booking_code], $codes('/admin/bookings?q=Andi'));
        $this->assertSame([$a->booking_code], $codes('/admin/bookings?q='.$a->booking_code));
    }

    public function test_aksi_status_lewat_http(): void
    {
        $booking = $this->makeBooking();

        $this->from(route('admin.bookings.show', $booking))->post(route('admin.bookings.confirm', $booking))
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHas('toast_error');
        $this->assertSame('pending', $booking->fresh()->status);

        $this->pay($booking, $booking->dp_amount);
        $this->post(route('admin.bookings.confirm', $booking))->assertSessionHas('toast');
        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_tolak_dengan_alasan_lewat_http(): void
    {
        $booking = $this->makeBooking();

        $this->post(route('admin.bookings.reject', $booking), ['reason' => 'Kru penuh'])->assertSessionHas('toast');

        $this->assertSame('rejected', $booking->fresh()->status);
        $this->assertStringContainsString('Kru penuh', $booking->fresh()->admin_note);
    }

    public function test_catat_pembayaran_lewat_http(): void
    {
        $booking = $this->makeBooking();

        $this->post(route('admin.payments.store', $booking), $this->payment())->assertSessionHas('toast');

        $this->assertSame(5800000, $booking->fresh()->paidAmount());
        $this->assertSame('dp', $booking->fresh()->payment_status);
        $this->assertMatchesRegularExpression('#^KWT/#', $booking->payments()->first()->receipt_number);
    }

    public function test_pembayaran_melebihi_sisa_pesan_error(): void
    {
        $booking = $this->makeBooking();

        $this->post(route('admin.payments.store', $booking), $this->payment(['amount' => '99.000.000']))->assertSessionHas('toast_error');

        $this->assertSame(0, $booking->fresh()->paidAmount());
    }

    public function test_tanggal_bayar_masa_depan_ditolak(): void
    {
        $booking = $this->makeBooking();

        $this->post(route('admin.payments.store', $booking), $this->payment(['paid_at' => now()->addDay()->toDateString()]))
            ->assertSessionHasErrors('paid_at');
    }

    public function test_void_lewat_http(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->pay($booking, 1000);

        $this->post(route('admin.payments.void', $payment), ['reason' => ''])->assertSessionHasErrors('reason');
        $this->assertNull($payment->fresh()->voided_at);

        $this->post(route('admin.payments.void', $payment), ['reason' => 'Salah input'])->assertSessionHas('toast');
        $this->assertNotNull($payment->fresh()->voided_at);
    }

    public function test_bukti_privat(): void
    {
        Storage::fake('local');
        $booking = $this->makeBooking();

        $this->post(route('admin.payments.store', $booking), $this->payment([
            'proof' => UploadedFile::fake()->image('bukti.jpg', 400, 300),
        ]))->assertSessionHas('toast');

        $payment = $booking->payments()->first();
        $this->assertNotNull($payment->proof_path);
        Storage::disk('local')->assertExists($payment->proof_path);

        $this->get(route('admin.payments.proof', $payment))->assertOk();

        auth('admin')->logout();
        $this->get(route('admin.payments.proof', $payment))->assertNotFound();
    }

    public function test_bukti_bukan_gambar_ditolak(): void
    {
        Storage::fake('local');
        $booking = $this->makeBooking();

        $this->post(route('admin.payments.store', $booking), $this->payment([
            'proof' => UploadedFile::fake()->create('virus.php', 10, 'text/x-php'),
        ]))->assertSessionHasErrors('proof');
        $this->assertSame(0, $booking->fresh()->paidAmount());
    }

    public function test_bukti_lebih_2mb_ditolak(): void
    {
        Storage::fake('local');
        $booking = $this->makeBooking();

        $this->post(route('admin.payments.store', $booking), $this->payment([
            'proof' => UploadedFile::fake()->image('besar.jpg')->size(2049),
        ]))->assertSessionHasErrors('proof');
    }

    public function test_detail_menampilkan_data_dan_peringatan_void(): void
    {
        $booking = $this->confirmedBooking();
        app(\App\Services\PaymentService::class)->void($booking->payments()->first(), 'Transfer gagal');

        $this->get(route('admin.bookings.show', $booking))->assertOk()
            ->assertSee($booking->booking_code)
            ->assertSee('Sound System – Paket 2')
            ->assertSee('di bawah DP');
    }

    public function test_nama_berbahaya_di_escape(): void
    {
        $evil = '</script><script>alert(1)</script>';
        $booking = $this->makeBooking('2030-06-01', ['customer_name' => $evil, 'note' => $evil, 'event_location' => $evil]);

        foreach (['/admin', '/admin/bookings', route('admin.bookings.show', $booking)] as $url) {
            $this->assertStringNotContainsString($evil, $this->get($url)->assertOk()->getContent(), $url);
        }
    }
}
