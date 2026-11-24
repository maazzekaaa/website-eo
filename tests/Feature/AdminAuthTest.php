<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    #[DataProvider('ruteAdmin')]
    public function test_tamu_mendapat_404(string $url): void
    {
        $this->get($url)->assertNotFound();
    }

    public static function ruteAdmin(): array
    {
        return [['/admin'], ['/admin/bookings'], ['/admin/revenue'], ['/admin/bookings/1'], ['/admin/payments/1/proof'], ['/admin/services'], ['/admin/portfolios'], ['/admin/clients'], ['/admin/settings']];
    }

    public function test_tamu_tidak_bisa_post_aksi(): void
    {
        $booking = $this->makeBooking();

        $this->post("/admin/bookings/{$booking->id}/confirm")->assertNotFound();
        $this->post("/admin/bookings/{$booking->id}/payments", ['amount' => 1])->assertNotFound();
    }

    public function test_login_benar(): void
    {
        $this->get('/admin/login')->assertOk()->assertDontSee('admin123');

        $this->post('/admin/login', ['email' => 'admin@fzm.id', 'password' => 'admin123'])->assertRedirect('/admin');

        $this->assertAuthenticated('admin');
    }

    public function test_login_salah_pesan_umum(): void
    {
        $this->from('/admin/login')->post('/admin/login', ['email' => 'admin@fzm.id', 'password' => 'salah'])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        $this->assertGuest('admin');
    }

    public function test_login_dibatasi(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'admin@fzm.id', 'password' => 'salah']);
        }

        $this->post('/admin/login', ['email' => 'admin@fzm.id', 'password' => 'admin123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_logout_harus_post(): void
    {
        $admin = Admin::first();

        $this->actingAs($admin, 'admin')->get('/admin/logout')->assertStatus(405);
        $this->assertAuthenticated('admin');

        $this->actingAs($admin, 'admin')->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    public function test_sandi_tersimpan_bcrypt(): void
    {
        $admin = Admin::first();

        $this->assertNotSame('admin123', $admin->password);
        $this->assertTrue(Hash::check('admin123', $admin->password));
    }

    public function test_halaman_admin_tidak_diindeks_dan_tidak_dicache(): void
    {
        $this->get('/admin/login')->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_daftar_ip_admin_menolak_ip_lain(): void
    {
        config(['app.admin_allowed_ips' => '10.0.0.5, 10.0.0.6']);

        $this->get('/admin/login')->assertNotFound();
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.6'])->get('/admin/login')->assertOk();
        $this->get('/')->assertOk();                                   // situs publik tetap terbuka
    }

    public function test_login_tanpa_token_csrf_ditolak(): void
    {
        $this->app['env'] = 'local';                                    // aktifkan cek CSRF (dimatikan saat unit test)
        $this->post('/admin/login', ['email' => 'admin@fzm.id', 'password' => 'admin123'])->assertStatus(419);
        $this->assertGuest('admin');
    }

    public function test_login_tidak_menampilkan_email_admin(): void
    {
        $this->get('/admin/login')->assertDontSee('admin@fzm.id')->assertSee('placeholder="nama@email.com"', false);
    }
}
