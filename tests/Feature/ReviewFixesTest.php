<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class ReviewFixesTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
        Storage::fake('public');
        $this->actingAs(Admin::first(), 'admin');
    }

    /** PNG sah secara tipe, tetapi resolusinya ditolak ImageService setelah lolos validasi. */
    private function oversized(): UploadedFile
    {
        $header = "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR".pack('NN', 20000, 20000)."\x08\x06\x00\x00\x00\x00\x00\x00\x00";

        return UploadedFile::fake()->createWithContent('besar.png', $header);
    }

    private function settings(array $extra = []): array
    {
        return $extra + ['company_name' => 'FZM', 'wa_number' => '08112223333', 'tax_rate' => 11];
    }

    public function test_tahun_salah_ketik_pada_tanggal_bayar_ditolak(): void
    {
        $booking = $this->makeBooking();

        $this->post(route('admin.payments.store', $booking), ['amount' => '1000', 'type' => 'dp', 'method' => 'tunai', 'paid_at' => '0026-11-09'])
            ->assertSessionHasErrors('paid_at');

        $this->assertSame(0, $booking->fresh()->paidAmount());
    }

    public function test_tanggal_bayar_tahun_lalu_masih_boleh(): void
    {
        $booking = $this->makeBooking();

        $this->post(route('admin.payments.store', $booking), ['amount' => '1000', 'type' => 'dp', 'method' => 'tunai', 'paid_at' => now()->subMonths(6)->toDateString()])
            ->assertSessionHasNoErrors();

        $this->assertSame(1000, $booking->fresh()->paidAmount());
    }

    public function test_gagal_olah_hero_baru_tidak_menghapus_yang_lama(): void
    {
        $this->put('/admin/settings', $this->settings(['hero' => UploadedFile::fake()->image('a.jpg', 200, 100)]))->assertSessionHasNoErrors();
        $old = Setting::current()->hero_image;

        $this->put('/admin/settings', $this->settings(['hero' => $this->oversized()]))->assertSessionHasErrors();

        Storage::disk('public')->assertExists($old);
        $this->assertSame($old, Setting::current()->hero_image);
    }

    public function test_gagal_olah_logo_baru_tidak_menghapus_yang_lama(): void
    {
        $this->post('/admin/clients', ['name' => 'Mitra', 'logo' => UploadedFile::fake()->image('l.png', 200, 100)]);
        $client = Client::where('name', 'Mitra')->firstOrFail();

        $this->put(route('admin.clients.update', $client), ['name' => 'Mitra', 'logo' => $this->oversized()])->assertSessionHasErrors();

        Storage::disk('public')->assertExists($client->logo);
    }

    public function test_ganti_gambar_menghapus_yang_lama(): void
    {
        $this->put('/admin/settings', $this->settings(['hero' => UploadedFile::fake()->image('a.jpg', 200, 100)]));
        $old = Setting::current()->hero_image;

        $this->put('/admin/settings', $this->settings(['hero' => UploadedFile::fake()->image('b.jpg', 200, 100)]))->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($old);
    }

    public function test_halaman_admin_memberi_tahu_batas_unggahan_server(): void
    {
        $limit = UploadedFile::getMaxFilesize();

        $this->get('/admin/portfolios/create')->assertOk()
            ->assertSee('data-upload-limit="'.$limit.'"', false)
            ->assertSee('Batas server saat ini: '.round($limit / 1048576, 1).' MB');
    }

    public function test_unggahan_terlalu_besar_dapat_pesan_jelas(): void
    {
        // Halaman debug memuat cuplikan kode test ini, sehingga bisa meloloskan assertSee secara keliru.
        config(['app.debug' => false]);

        $this->withServerVariables(['CONTENT_LENGTH' => 999999999999])
            ->post('/admin/portfolios', [])
            ->assertStatus(413)
            ->assertSee('Berkas terlalu besar');
    }
}
