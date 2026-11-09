<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    private function payload(array $extra = []): array
    {
        return $extra + [
            'items' => ['sound' => $this->optionId('sound', 'Paket 2')],
            'qty' => ['rigging' => 12, 'videotron-indoor' => 0],
            'event_date' => '2030-06-01', 'customer_name' => 'Budi Santoso', 'customer_wa' => '081234567890',
            'event_time' => '18:00', 'event_location' => 'Gedung A', 'note' => 'Catatan', 'website' => '',
        ];
    }

    public function test_pesan_sukses_tersimpan_dan_ke_halaman_sukses(): void
    {
        $response = $this->post('/pesan', $this->payload());

        $booking = Booking::firstOrFail();
        $response->assertRedirect(route('pesan.success', $booking->booking_code));
        $this->assertSame('pending', $booking->status);
        $this->assertCount(2, $booking->items);
        $this->assertSame(6800000, $booking->total);
        $this->assertSame('6281234567890', $booking->customer_wa);

        $this->get(route('pesan.success', $booking->booking_code))->assertOk()
            ->assertSee($booking->booking_code)
            ->assertSee('https://wa.me/', false);
    }

    public function test_harga_dari_browser_diabaikan(): void
    {
        $this->post('/pesan', $this->payload(['total' => 1000, 'price' => 1, 'subtotal' => 1, 'dp_amount' => 1]));

        $this->assertSame(6800000, Booking::firstOrFail()->total);
        $this->assertSame(3400000, Booking::firstOrFail()->dp_amount);
    }

    public function test_tanggal_terkunci_ditolak(): void
    {
        $this->confirmedBooking('2030-06-01');

        $this->post('/pesan', $this->payload())->assertSessionHasErrors('event_date');
        $this->assertSame(1, Booking::count());
    }

    public function test_tanggal_lampau_ditolak(): void
    {
        $this->post('/pesan', $this->payload(['event_date' => '2020-01-01']))->assertSessionHasErrors('event_date');
        $this->assertSame(0, Booking::count());
    }

    public function test_honeypot_tidak_menyimpan(): void
    {
        $this->post('/pesan', $this->payload(['website' => 'http://spam.test']))->assertRedirect('/');
        $this->assertSame(0, Booking::count());
    }

    public function test_wa_tidak_valid_ditolak(): void
    {
        $this->post('/pesan', $this->payload(['customer_wa' => '12345']))->assertSessionHasErrors('customer_wa');
    }

    public function test_keranjang_kosong_ditolak(): void
    {
        $this->post('/pesan', $this->payload(['items' => [], 'qty' => []]))->assertSessionHasErrors('items');
    }

    public function test_throttle_pesan(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/pesan', ['website' => ''])->assertStatus(302);
        }

        $this->post('/pesan', ['website' => ''])->assertStatus(429);
    }

    public function test_availability(): void
    {
        $this->confirmedBooking('2030-06-01');
        $done = $this->confirmedBooking('2030-06-02');
        $this->pay($done, $done->restAmount(), 'pelunasan');
        app(\App\Services\BookingService::class)->complete($done);
        $this->makeBooking('2030-06-03');
        $this->confirmedBooking('2030-07-01');

        $response = $this->getJson('/api/availability?month=2030-06')->assertOk();

        $this->assertSame(['2030-06-01', '2030-06-02'], $response->json('locked'));
        $this->assertStringNotContainsString('Cahaya', $response->getContent());
    }

    public function test_availability_bulan_salah(): void
    {
        $this->getJson('/api/availability?month=abc')->assertStatus(422);
    }

    public function test_quote_menghitung_dari_database(): void
    {
        $this->postJson('/api/cart/quote', ['qty' => ['rigging' => 2], 'total' => 1])
            ->assertOk()->assertJson(['subtotal' => 300000, 'tax' => 0, 'total' => 300000, 'dp' => 150000]);
    }

    public function test_gagal_validasi_input_tetap_ada(): void
    {
        $option = $this->optionId('sound', 'Paket 2');

        $this->from('/pesan')->post('/pesan', $this->payload(['customer_wa' => '123', 'customer_name' => 'Nama Tetap']))
            ->assertRedirect('/pesan');

        $page = $this->get('/pesan')->assertOk();
        $page->assertSee('value="Nama Tetap"', false);
        $page->assertSee('"event_date":"2030-06-01"', false);
        $page->assertSee('name="items[sound]" value="'.$option.'" checked', false);
        $page->assertSee('name="qty[rigging]" min="0" max="500" value="12"', false);
    }

    public function test_sukses_butuh_sesi_pembuat(): void
    {
        $booking = $this->makeBooking();

        $this->get(route('pesan.success', $booking->booking_code))->assertNotFound();
    }

    public function test_halaman_pesan_menandai_tanggal_terkunci_bulan_ini(): void
    {
        $date = now()->addDays(1);
        $this->confirmedBooking($date->toDateString());

        $this->get('/pesan')->assertSee($date->isSameMonth(now()) ? $date->toDateString() : 'availabilityUrl', false);
    }
}
