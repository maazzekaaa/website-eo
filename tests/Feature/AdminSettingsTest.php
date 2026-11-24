<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
        Storage::fake('public');
        $this->actingAs(Admin::first(), 'admin');
    }

    private function payload(array $extra = []): array
    {
        return $extra + [
            'company_name' => 'FZM', 'address' => 'Jl. Uji 1', 'email' => 'halo@fzm.test', 'wa_number' => '0811-222-3333',
            'bank_name' => 'Bank Uji', 'bank_account' => '123', 'bank_holder' => 'FZM', 'tax_rate' => 11,
            'hero_title' => "Judul baru,\nbaris dua.", 'hero_subtitle' => 'Sub', 'about' => 'Tentang', 'opening_hours' => '09-17',
        ];
    }

    public function test_pesan_validasi_bahasa_indonesia(): void
    {
        $this->put('/admin/settings', $this->payload(['company_name' => '']))
            ->assertSessionHasErrors(['company_name' => 'Nama perusahaan wajib diisi.']);
    }

    public function test_form_terbuka(): void
    {
        $this->get('/admin/settings')->assertOk()->assertSee('FZM Production Mediatama');
    }

    public function test_pengaturan_tersimpan_dan_tampil_di_situs(): void
    {
        $this->get('/')->assertSee('Ada cerita di balik setiap perayaan.');

        $this->put('/admin/settings', $this->payload())->assertRedirect()->assertSessionHas('toast');

        $this->get('/')->assertSee('Judul baru,')->assertDontSee('Panggung yang hidup')->assertSee('628112223333', false);
        $this->get('/')->assertSee('Jl. Uji 1');
    }

    public function test_ppn_aktif_berlaku_untuk_booking_baru_saja(): void
    {
        $old = $this->makeBooking('2030-06-01');

        $this->put('/admin/settings', $this->payload(['tax_enabled' => '1']))->assertRedirect();

        $this->assertSame(11600000, $old->fresh()->total);
        $this->assertSame(1276000, $this->makeBooking('2030-06-02')->tax_amount);
    }

    public function test_ppn_nonaktif_jika_tidak_dicentang(): void
    {
        Setting::current()->update(['tax_enabled' => true]);

        $this->put('/admin/settings', $this->payload())->assertRedirect();

        $this->assertFalse(Setting::current()->tax_enabled);
    }

    public function test_map_embed_hanya_url_google_maps(): void
    {
        $this->put('/admin/settings', $this->payload(['map_embed' => 'https://www.google.com/maps/embed?pb=abc']))->assertSessionHasNoErrors();
        $this->assertSame('https://www.google.com/maps/embed?pb=abc', Setting::current()->map_embed);

        foreach (['javascript:alert(1)', 'https://evil.test/maps/embed', '<iframe src="x"></iframe>', 'http://www.google.com/maps/embed?pb=x'] as $bad) {
            $this->put('/admin/settings', $this->payload(['map_embed' => $bad]))->assertSessionHasErrors('map_embed');
        }
    }

    public function test_tautan_sosial_harus_http(): void
    {
        $this->put('/admin/settings', $this->payload(['instagram_url' => 'javascript:alert(1)']))->assertSessionHasErrors('instagram_url');
    }

    public function test_nomor_wa_dinormalisasi(): void
    {
        $this->put('/admin/settings', $this->payload())->assertRedirect();

        $this->assertSame('628112223333', Setting::current()->wa_number);
    }

    public function test_unggah_tanda_tangan_dan_hero(): void
    {
        $this->put('/admin/settings', $this->payload([
            'signature' => UploadedFile::fake()->image('ttd.png', 300, 100),
            'hero' => UploadedFile::fake()->image('hero.jpg', 1200, 800),
        ]))->assertSessionHasNoErrors();

        Storage::disk('public')->assertExists(Setting::current()->signature_path);
        Storage::disk('public')->assertExists(Setting::current()->hero_image);
    }

    public function test_ganti_sandi(): void
    {
        $this->put('/admin/password', ['current_password' => 'salah', 'password' => 'sandiBaru123', 'password_confirmation' => 'sandiBaru123'])
            ->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('admin123', Admin::first()->password));

        $this->put('/admin/password', ['current_password' => 'admin123', 'password' => 'sandiBaru123', 'password_confirmation' => 'sandiBaru123'])
            ->assertSessionHas('toast');
        $this->assertTrue(Hash::check('sandiBaru123', Admin::first()->password));
    }

    public function test_sandi_baru_minimal_8(): void
    {
        $this->put('/admin/password', ['current_password' => 'admin123', 'password' => 'pendek', 'password_confirmation' => 'pendek'])
            ->assertSessionHasErrors('password');
    }

    public function test_semua_butuh_login(): void
    {
        auth('admin')->logout();

        $this->put('/admin/settings', $this->payload())->assertNotFound();
        $this->put('/admin/password', [])->assertNotFound();
        $this->post('/admin/services', [])->assertNotFound();
        $this->delete('/admin/portfolios/x')->assertNotFound();
    }
}
