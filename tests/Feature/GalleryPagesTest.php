<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Portfolio;
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
            'title' => 'Konser Kampus', 'slug' => 'konser-kampus', 'category' => 'Corporate', 'folder_color' => 'maroon',
            'is_featured' => true, 'event_date' => '2026-06-20', 'location' => 'Malang', 'services' => 'Sound, Lighting',
            'client_id' => Client::firstOrCreate(['name' => 'Mitra Satu'])->id,
        ]);
        for ($i = 1; $i <= $photos; $i++) {
            $portfolio->images()->create(['path' => "portfolios/{$portfolio->slug}-{$i}.webp", 'sort_order' => $i]);
        }

        return $portfolio;
    }

    public function test_navbar_berisi_semua_tautan_dan_menandai_halaman_aktif(): void
    {
        $page = $this->get('/layanan/sound')->assertOk();

        // Web satu halaman: menu menggulir ke bagian beranda
        foreach (['layanan', 'portofolio', 'tentang', 'kontak'] as $id) {
            $page->assertSee('href="'.route('home').'#'.$id.'" data-section="'.$id.'"', false);
        }
        $page->assertSee('href="'.route('pesan.create').'"', false);
        $page->assertSee('class="brand-pill"', false);
        $page->assertSee('data-section="layanan" aria-current="page"', false);
        $page->assertDontSee('data-section="beranda" aria-current="page"', false);
        $page->assertDontSee('>Klien</a>', false);
    }

    public function test_beranda_hero_tipografi_tanpa_gambar(): void
    {
        $this->portfolio();

        $page = $this->get('/')->assertOk();

        $page->assertSee('aria-label="Rangkai Cipta, Hidupkan Karya."', false);
        $page->assertSeeInOrder(['Rangkai', 'Cipta,', 'Hidupkan', 'Karya.'], false);
        $page->assertSee('class="hero-word serif"', false);
        // Hero tetap tipografi murni; foto proyek baru muncul di seksi setelahnya
        $html = $page->getContent();
        $hero = substr($html, strpos($html, '<header class="hero">'), strpos($html, '</header>') - strpos($html, '<header class="hero">'));
        $this->assertStringNotContainsString('<img', $hero);
        $page->assertDontSee('Masuk admin', false);
        $page->assertDontSee('meta-num', false);
        $page->assertSee('<span class="ch">R</span><span class="ch">a</span>', false);
        $page->assertSee('Ada cerita di balik setiap perayaan. Mari tulis dan wujudkan ceritamu bersama kami.');
        $page->assertDontSee('class="hero-brand"', false);
    }

    public function test_beranda_menampilkan_foto_proyek_klien(): void
    {
        $this->portfolio();
        $this->portfolio(['title' => 'Tanpa Foto', 'slug' => 'tanpa-foto'], 0);

        $page = $this->get('/')->assertOk();

        $page->assertSee('class="showcase"', false);
        $page->assertSee('portfolios/konser-kampus-1.webp', false);
        $page->assertSee('<span class="show-client">Mitra Satu</span>', false);
        $page->assertSee('href="'.route('portofolio.show', 'konser-kampus').'"', false);
        $page->assertDontSee('href="'.route('portofolio.show', 'tanpa-foto').'"', false);
        $page->assertSee('class="eyebrow"', false);
    }

    public function test_web_satu_halaman_semua_bagian_ada_di_beranda(): void
    {
        $this->portfolio();
        $page = $this->get('/')->assertOk();

        foreach (['layanan', 'portofolio', 'klien', 'tentang', 'kontak'] as $id) {
            $page->assertSee('id="'.$id.'"', false);
        }
        $page->assertSee('id="enquiry"', false);
        foreach (['/layanan' => '/#layanan', '/portofolio' => '/#portofolio', '/tentang' => '/#tentang', '/kontak' => '/#kontak'] as $from => $to) {
            $this->get($from)->assertStatus(301)->assertRedirect($to);
        }
    }

    public function test_nomor_whatsapp_bisa_diketuk(): void
    {
        $this->get('/')->assertOk()->assertSee('class="wa-link accent" href="https://wa.me/', false);
    }

    public function test_beranda_menampilkan_strip_logo_klien(): void
    {
        $this->portfolio();
        Client::create(['name' => 'Mitra Dua']);

        $page = $this->get('/')->assertOk();

        $page->assertSee('class="logo-strip"', false);
        $page->assertSee('Mitra Satu')->assertSee('Mitra Dua');
        $page->assertDontSee('/klien/', false);
    }

    public function test_footer_berisi_wordmark_fzm_production(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('class="footer-mark"', false)
            ->assertSee('<span>FZM</span> <span>PRODUCTION</span>', false);
    }

    public function test_halaman_klien_publik_dihapus_dan_dialihkan(): void
    {
        $portfolio = $this->portfolio();

        $this->get('/klien')->assertStatus(301)->assertRedirect('/#klien');
        $this->get('/klien/'.$portfolio->client_id)->assertStatus(301)->assertRedirect('/#klien');
    }

    public function test_portofolio_beranda_membuka_galeri_dan_studi_kasus(): void
    {
        $this->portfolio();

        $page = $this->get('/')->assertOk();

        $page->assertSee('aria-label="Lihat foto Konser Kampus"', false);
        $page->assertSee('href="'.route('portofolio.show', 'konser-kampus').'"', false);
        $page->assertSee('id="pfDetail"', false);
        $page->assertDontSee('cursor-pill', false);
        $page->assertDontSee('class="client-count"', false);
    }

    public function test_portofolio_beranda_dibatasi(): void
    {
        foreach (range(1, 7) as $n) {
            $this->portfolio(['title' => 'Acara '.$n, 'slug' => 'acara-'.$n, 'event_date' => '2026-0'.$n.'-10'], 1);
        }

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(\App\Http\Controllers\HomeController::PROJECTS, substr_count($html, 'class="show-item"'));
        $this->assertCount(\App\Http\Controllers\HomeController::PROJECTS, json_decode($this->extractJson($html, 'portfolioData'), true));
    }

    public function test_detail_portofolio_membawa_klien_tanggal_lokasi_dan_layanan(): void
    {
        $this->portfolio();

        $page = $this->get('/')->assertOk();

        $data = json_decode($this->extractJson($page->getContent(), 'portfolioData'), true);
        $this->assertSame('Mitra Satu', $data[0]['client']);
        $this->assertSame('20 Juni 2026', $data[0]['date']);
        $this->assertSame('Malang', $data[0]['location']);
        $this->assertSame('Sound, Lighting', $data[0]['services']);
        $this->assertSame(route('portofolio.show', 'konser-kampus'), $data[0]['link']);
        foreach (['pdClient', 'pdDate', 'pdLoc', 'pdSvc', 'pdImg', 'pdClose'] as $id) {
            $page->assertSee('id="'.$id.'"', false);
        }
    }

    public function test_portofolio_hanya_pernikahan_dan_korporat(): void
    {
        $this->portfolio(['title' => 'Konser Lama', 'slug' => 'konser-lama', 'category' => 'Concert']);
        $this->portfolio(['title' => 'Resepsi', 'slug' => 'resepsi', 'category' => 'Wedding']);

        $page = $this->get('/')->assertOk();
        $data = json_decode($this->extractJson($page->getContent(), 'portfolioData'), true);

        $this->assertSame(['Pernikahan'], array_values(array_unique(array_column($data, 'category'))));
        $page->assertDontSee('Konser Lama');
    }

    public function test_portofolio_unggulan_tampil_lebih_dulu(): void
    {
        $this->portfolio(['title' => 'Arsip Lama', 'slug' => 'arsip-lama', 'is_featured' => false, 'event_date' => '2026-09-01']);
        $this->portfolio();

        $data = json_decode($this->extractJson($this->get('/')->getContent(), 'portfolioData'), true);

        $this->assertSame('Konser Kampus', $data[0]['title']);
    }

    public function test_formulir_pesan_punya_jam_mulai_dan_selesai(): void
    {
        $this->get('/pesan')->assertOk()
            ->assertSee('name="event_time"', false)
            ->assertSee('name="event_end_time"', false)
            ->assertSee('Jam selesai acara');
    }

    public function test_font_dan_warna_desain_baru(): void
    {
        $base = file_get_contents(public_path('assets/css/base.css'));

        $this->assertStringContainsString('--red: #C2272D;', $base);
        $this->assertStringContainsString('"Open Sauce One"', $base);
        $this->assertFileExists(public_path('assets/fonts/open-sauce-one-latin-800-normal.woff2'));
        // Warna flat: tanpa gradasi, termasuk wordmark footer.
        $this->assertStringNotContainsString('linear-gradient', file_get_contents(public_path('assets/css/site.css')));
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

        foreach (['/', '/portofolio/jahat'] as $url) {
            $this->assertStringNotContainsString('<script>alert(1)', $this->get($url)->assertOk()->getContent(), $url);
        }
    }

    private function extractJson(string $html, string $id): string
    {
        preg_match('#<script id="'.$id.'" type="application/json">(.*?)</script>#s', $html, $m);

        return $m[1] ?? 'null';
    }
}
