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
    public function test_tamu_dialihkan_ke_login(string $url): void
    {
        $this->get($url)->assertRedirect('/admin/login');
    }

    public static function ruteAdmin(): array
    {
        return [['/admin'], ['/admin/bookings'], ['/admin/revenue'], ['/admin/bookings/1'], ['/admin/payments/1/proof'], ['/admin/services'], ['/admin/portfolios'], ['/admin/clients'], ['/admin/settings']];
    }

    public function test_tamu_tidak_bisa_post_aksi(): void
    {
        $booking = $this->makeBooking();

        $this->post("/admin/bookings/{$booking->id}/confirm")->assertRedirect('/admin/login');
        $this->post("/admin/bookings/{$booking->id}/payments", ['amount' => 1])->assertRedirect('/admin/login');
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
}
