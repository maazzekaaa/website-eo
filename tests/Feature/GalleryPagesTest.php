<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Portfolio;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class GalleryPagesTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    private function portfolio(array $attributes = [], int $photos = 2): Portfolio
    {
        $portfolio = Portfolio::create($attributes + [
            'title' => 'Konser Kampus', 'slug' => 'konser-kampus', 'category' => 'Concert', 'folder_color' => 'maroon',
            'is_featured' => true, 'event_date' => '2026-06-20', 'location' => 'Malang',
            'client_id' => Client::firstOrCreate(['name' => 'Mitra Satu'])->id,
        ]);
        for ($i = 1; $i <= $photos; $i++) {
            $portfolio->images()->create(['path' => "portfolios/{$portfolio->slug}-{$i}.webp", 'sort_order' => $i]);
        }

        return $portfolio;
    }

    public function test_navbar_berisi_semua_tautan_dan_menandai_halaman_aktif(): void
    {
        $page = $this->get('/klien')->assertOk();

        foreach (['home', 'layanan.index', 'portofolio.index', 'klien.index', 'tentang', 'kontak', 'pesan.create'] as $route) {
            $page->assertSee('href="'.route($route).'"', false);
        }
        $page->assertSee('class="brand-pill"', false);
        $page->assertSee('href="'.route('klien.index').'" aria-current="page"', false);
        $page->assertDontSee('href="'.route('tentang').'" aria-current="page"', false);
    }

    public function test_beranda_menggantung_foto_portofolio_unggulan(): void
    {
        $this->portfolio();
        $this->portfolio(['title' => 'Arsip Lama', 'slug' => 'arsip-lama', 'is_featured' => false]);

        $page = $this->get('/')->assertOk();

        $page->assertSee('class="deck-card"', false);
        $page->assertSee('portfolios/konser-kampus-1.webp', false);
        $page->assertSee('href="'.route('portofolio.show', 'konser-kampus').'"', false);
        $page->assertDontSee('arsip-lama-1.webp', false);
    }

    public function test_beranda_tidak_lagi_memakai_tali_jemuran(): void
    {
        $this->portfolio();

        $this->get('/')->assertOk()
            ->assertDontSee('stage-wire', false)
            ->assertDontSee('line-clip', false)
            ->assertDontSee('line-print', false);
    }

    public function test_beranda_memakai_gambar_hero_pengaturan_sebagai_foto_pertama(): void
    {
        $this->portfolio();
        Setting::current()->update(['hero_image' => 'site/hero.webp']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'portfolios/konser-kampus-1.webp'), strpos($html, 'site/hero.webp'));
    }

    public function test_beranda_tanpa_foto_tetap_tampil_dengan_placeholder(): void
    {
        $this->get('/')->assertOk()->assertSee('deck-card is-placeholder', false)->assertSee('00 — 00');
    }

    public function test_klien_berupa_tabel_dengan_pratinjau(): void
    {
        $portfolio = $this->portfolio();
        Client::create(['name' => 'Mitra Kosong']);

        $page = $this->get('/klien')->assertOk();

        $page->assertSee('2 klien');
        $page->assertSee('Mitra Satu')->assertSee('Mitra Kosong');
        $page->assertSee('href="'.route('klien.show', $portfolio->client_id).'"', false);
        $page->assertSee('data-preview="', false);
        $page->assertSee('portfolios/konser-kampus-1.webp', false);
        $page->assertSee('id="ledgerPreview"', false);
        $page->assertSeeInOrder(['Mitra Satu', 'Concert', '2026'], false);
    }

    public function test_portofolio_satu_cetakan_per_foto_dan_tanpa_foto_tetap_muncul(): void
    {
        $this->portfolio();
        $this->portfolio(['title' => 'Tanpa Foto', 'slug' => 'tanpa-foto', 'category' => 'Wedding', 'folder_color' => 'red'], 0);

        $html = $this->get('/portofolio')->assertOk()->assertSee('3 foto')->getContent();

        $this->assertSame(3, substr_count($html, 'class="table-print folder--'));
        $this->assertStringContainsString('data-index="1"', $html);
        $this->assertStringContainsString('aria-label="Buka galeri Tanpa Foto"', $html);
        $this->assertStringContainsString('id="lightbox"', $html);
        $this->assertStringContainsString('data-filter="Concert"', $html);
    }

    public function test_jumlah_pada_filter_sesuai_cetakan(): void
    {
        $this->portfolio();

        $html = $this->get('/portofolio')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#data-filter="Semua"[^>]*>Semua <span>2</span>#', $html);
        $this->assertMatchesRegularExpression('#data-filter="Concert"[^>]*>Concert <span>2</span>#', $html);
        $this->assertMatchesRegularExpression('#data-filter="Wedding"[^>]*>Wedding <span>0</span>#', $html);
    }

    public function test_portofolio_paling_banyak_15_foto(): void
    {
        $this->portfolio([], 20);

        $html = $this->get('/portofolio')->assertOk()->assertSee('15 foto')->getContent();

        $this->assertSame(15, substr_count($html, 'class="table-print folder--'));
        $this->assertMatchesRegularExpression('#data-filter="Semua"[^>]*>Semua <span>15</span>#', $html);
    }

    public function test_foto_portofolio_membawa_klien_kategori_tempat_dan_tanggal(): void
    {
        $this->portfolio();

        $page = $this->get('/portofolio')->assertOk();

        $page->assertSee('data-client="Mitra Satu"', false);
        $page->assertSee('data-category="Concert"', false);
        $page->assertSee('data-location="Malang"', false);
        $page->assertSee('data-date="20 Juni 2026"', false);
        foreach (['lbImg', 'lbTitle', 'lbMeta', 'lbClose'] as $id) {
            $page->assertSee('id="'.$id.'"', false);
        }
    }

    public function test_font_kembali_seperti_proyek_lama(): void
    {
        $this->assertStringNotContainsString('Anton', $this->get('/')->assertOk()->getContent());
        $this->assertStringNotContainsString('Anton', file_get_contents(public_path('assets/css/site.css')));
    }

    public function test_foto_beranda_bisa_diklik_dan_membawa_data_galeri(): void
    {
        $this->portfolio();

        $page = $this->get('/')->assertOk();

        $page->assertSee('id="lightbox"', false);
        $page->assertSee('data-client="Mitra Satu"', false);
        $page->assertSee('data-category="Concert"', false);
        $page->assertSee('data-location="Malang"', false);
        $page->assertSee('data-date="20 Juni 2026"', false);
        $page->assertSee('data-photos="', false);
        $page->assertSee('data-no="01"', false);
    }

    public function test_judul_portofolio_tanpa_latar(): void
    {
        $css = file_get_contents(public_path('assets/css/site.css'));

        $this->assertStringNotContainsString('.table-copy::before', $css);
        $this->assertStringContainsString('.filters--pills .filter-btn { min-height: 42px; padding: 0 18px; border-radius: var(--pill); font-size: 14px; color: var(--ink); background: transparent;', $css);
    }

    public function test_navbar_tidak_lagi_bersembunyi_saat_digulir(): void
    {
        $this->assertStringNotContainsString('is-hidden', file_get_contents(public_path('assets/js/site.js')));
        $this->assertStringNotContainsString('.nav.is-hidden', file_get_contents(public_path('assets/css/site.css')));
    }

    public function test_judul_berbahaya_di_escape_di_semua_halaman_baru(): void
    {
        $evil = '"><script>alert(1)</script>';
        $client = Client::create(['name' => $evil]);
        $this->portfolio(['title' => $evil, 'slug' => 'jahat', 'location' => $evil, 'client_id' => $client->id]);

        foreach (['/', '/klien', '/portofolio'] as $url) {
            $this->assertStringNotContainsString('<script>alert(1)', $this->get($url)->assertOk()->getContent(), $url);
        }
    }
}
