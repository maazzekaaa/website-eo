<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    private function portfolio(array $attributes = []): Portfolio
    {
        return Portfolio::create($attributes + [
            'title' => 'Konser Kampus', 'slug' => 'konser-kampus', 'category' => 'Corporate',
            'folder_color' => 'maroon', 'is_featured' => true, 'caption' => 'Sound besar', 'services' => 'Sound, Lighting',
            'client_id' => Client::firstOrCreate(['name' => 'Mitra Satu'])->id,
        ]);
    }

    #[DataProvider('halamanUtama')]
    public function test_halaman_publik_200(string $url): void
    {
        $this->get($url)->assertOk();
    }

    public static function halamanUtama(): array
    {
        return [['/'], ['/pesan'], ['/layanan/sound'], ['/cari']];
    }

    public function test_beranda_dari_database(): void
    {
        $this->portfolio();
        Setting::current()->update(['hero_title' => 'Judul dari pengaturan']);

        $this->get('/')->assertOk()
            ->assertSee('Rp3 – 10 jt')
            ->assertSee('Rp400 rb')
            ->assertSee('Mitra Satu')
            ->assertSee('Judul dari pengaturan');
    }

    public function test_detail_portofolio_menampilkan_klien(): void
    {
        $portfolio = $this->portfolio();

        $this->get('/portofolio/'.$portfolio->slug)->assertOk()->assertSee('Konser Kampus')->assertSee('Mitra Satu');
    }

    public function test_layanan_nonaktif_404_dan_tidak_terdaftar(): void
    {
        Service::where('slug', 'rigging')->update(['is_active' => false]);

        $this->get('/layanan/rigging')->assertNotFound();
        $this->get('/')->assertDontSee('Rigging');
        $this->get('/')->assertDontSee('Struktur truss');
    }

    public function test_badge_promo_tampil(): void
    {
        Service::where('slug', 'rigging')->update(['badge' => 'promo']);

        $this->get('/')->assertSee('(Promo)');
    }

    public function test_detail_layanan_punya_tombol_wa_dan_pesan(): void
    {
        $this->get('/layanan/sound')->assertOk()
            ->assertSee('Tanya layanan ini')
            ->assertSee('/pesan?layanan=sound', false)
            ->assertSee('Rp5.000.000');
    }

    public function test_cari_menemukan_layanan_dan_portofolio(): void
    {
        $this->portfolio();

        $this->get('/cari?q=rigging')->assertOk()->assertSee('Rigging')->assertDontSee('Konser Kampus');
        $this->get('/cari?q=konser')->assertOk()->assertSee('Konser Kampus');
    }

    public function test_cari_tidak_menampilkan_booking(): void
    {
        $this->makeBooking('2030-06-01', ['customer_name' => 'Rahasia Pemesan']);

        $this->get('/cari?q=Rahasia')->assertOk()->assertDontSee('Rahasia Pemesan');
    }

    public function test_sitemap_xml(): void
    {
        $this->portfolio();

        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('/layanan/sound', false)
            ->assertSee('/portofolio/konser-kampus', false)
            ->assertDontSee('/admin', false);
    }

    public function test_meta_per_halaman(): void
    {
        $this->get('/layanan/sound')->assertSee('<title>Sound System — FZM Production Mediatama</title>', false)
            ->assertSee('property="og:title" content="Sound System', false);
    }

    public function test_peta_hanya_tampil_jika_ada(): void
    {
        $this->get('/')->assertDontSee('<iframe', false);

        Setting::current()->update(['map_embed' => 'https://www.google.com/maps/embed?pb=abc']);
        $this->get('/')->assertSee('<iframe', false)->assertSee('https://www.google.com/maps/embed?pb=abc', false);
    }
}
