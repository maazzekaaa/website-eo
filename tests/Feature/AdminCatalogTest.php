<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Portfolio;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MakesBookings;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
        Storage::fake('public');
        $this->actingAs(Admin::first(), 'admin');
    }

    private function tierPayload(array $extra = []): array
    {
        return $extra + [
            'name' => 'Genset', 'description' => 'Listrik cadangan', 'pricing_type' => 'tier', 'is_active' => '1', 'sort_order' => 9,
            'options' => [['name' => 'Kecil', 'price' => '1.000.000'], ['name' => 'Besar', 'price' => '2500000']],
        ];
    }

    public function test_daftar_dan_form_terbuka(): void
    {
        foreach (['/admin/services', '/admin/services/create', '/admin/portfolios', '/admin/portfolios/create', '/admin/clients', '/admin/clients/create'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get(route('admin.services.edit', Service::first()))->assertOk();
    }

    public function test_crud_layanan_tier(): void
    {
        $this->post('/admin/services', $this->tierPayload())->assertRedirect('/admin/services');

        $service = Service::where('slug', 'genset')->firstOrFail();
        $this->assertSame([1000000, 2500000], $service->options()->pluck('price')->all());

        $kept = $service->options()->first();
        $this->put(route('admin.services.update', $service), $this->tierPayload([
            'name' => 'Genset Baru', 'badge' => 'promo',
            'options' => [['id' => $kept->id, 'name' => 'Kecil', 'price' => '1500000']],
        ]))->assertRedirect('/admin/services');

        $service->refresh();
        $this->assertSame('Genset Baru', $service->name);
        $this->assertSame('promo', $service->badge);
        $this->assertSame('genset', $service->slug);
        $this->assertSame(1500000, $service->options()->sole()->price);

        $this->delete(route('admin.services.destroy', $service))->assertRedirect('/admin/services');
        $this->assertNull(Service::find($service->id));
    }

    public function test_tier_wajib_punya_opsi(): void
    {
        $this->post('/admin/services', $this->tierPayload(['options' => []]))->assertSessionHasErrors('options');
    }

    public function test_per_unit_wajib_satuan_dan_harga(): void
    {
        $base = ['name' => 'Panggung', 'pricing_type' => 'per_unit'];

        $this->post('/admin/services', $base + ['unit_label' => '', 'unit_price' => '0'])->assertSessionHasErrors(['unit_label', 'unit_price']);

        $this->post('/admin/services', $base + ['unit_label' => 'm²', 'unit_price' => '200.000', 'is_active' => '1'])->assertRedirect('/admin/services');
        $this->assertSame(200000, Service::where('slug', 'panggung')->first()->unit_price);
    }

    public function test_slug_layanan_kembar_dapat_akhiran(): void
    {
        $this->post('/admin/services', $this->tierPayload(['name' => 'Sound']))->assertRedirect();

        $this->assertSame(1, Service::where('slug', 'sound-2')->count());
    }

    public function test_ubah_harga_tidak_mengubah_booking_lama(): void
    {
        $booking = $this->makeBooking();
        $rigging = Service::where('slug', 'rigging')->first();

        $this->put(route('admin.services.update', $rigging), ['name' => 'Rigging', 'pricing_type' => 'per_unit', 'unit_label' => 'm', 'unit_price' => '999000', 'is_active' => '1'])->assertRedirect();

        $this->assertSame(999000, $rigging->fresh()->unit_price);
        $this->assertSame(150000, $booking->items()->where('description', 'Rigging')->first()->unit_price);
        $this->assertSame(11600000, $booking->fresh()->total);
    }

    public function test_nonaktif_hilang_dari_pesan(): void
    {
        $rigging = Service::where('slug', 'rigging')->first();

        $this->put(route('admin.services.update', $rigging), ['name' => 'Rigging', 'pricing_type' => 'per_unit', 'unit_label' => 'm', 'unit_price' => '150000'])->assertRedirect();

        $this->assertFalse($rigging->fresh()->is_active);
        $this->get('/pesan')->assertDontSee('name="qty[rigging]"', false);
    }

    public function test_hapus_layanan_dengan_booking(): void
    {
        $booking = $this->makeBooking();

        $this->delete(route('admin.services.destroy', Service::where('slug', 'rigging')->first()))->assertRedirect();

        $item = $booking->items()->where('description', 'Rigging')->first();
        $this->assertNull($item->service_id);
        $this->assertSame(1800000, $item->subtotal);
    }

    private function portfolioPayload(array $extra = []): array
    {
        return $extra + ['title' => 'Konser Kampus', 'category' => 'Concert', 'folder_color' => 'maroon', 'event_date' => '2026-06-10', 'is_featured' => '1'];
    }

    public function test_crud_portofolio_foto_jadi_webp(): void
    {
        $this->post('/admin/portfolios', $this->portfolioPayload([
            'photos' => [UploadedFile::fake()->image('a.jpg', 800, 600), UploadedFile::fake()->image('b.png', 800, 600)],
        ]))->assertRedirect('/admin/portfolios');

        $portfolio = Portfolio::where('slug', 'konser-kampus')->firstOrFail();
        $this->assertCount(2, $portfolio->images);
        $this->assertStringEndsWith('.webp', $portfolio->images[0]->path);
        Storage::disk('public')->assertExists($portfolio->images[0]->path);

        $this->put(route('admin.portfolios.update', $portfolio), $this->portfolioPayload(['title' => 'Konser Baru']))->assertRedirect();
        $this->assertSame('Konser Baru', $portfolio->fresh()->title);
        $this->assertCount(2, $portfolio->fresh()->images);

        $this->get(route('admin.portfolios.edit', $portfolio))->assertOk();

        $image = $portfolio->images[0];
        $this->delete(route('admin.portfolios.images.destroy', $image))->assertRedirect();
        Storage::disk('public')->assertMissing($image->path);
        $this->assertCount(1, $portfolio->fresh()->images);
    }

    public function test_warna_folder_hanya_palet(): void
    {
        $this->post('/admin/portfolios', $this->portfolioPayload(['folder_color' => 'hijau']))->assertSessionHasErrors('folder_color');
    }

    public function test_slug_unik(): void
    {
        $this->post('/admin/portfolios', $this->portfolioPayload());
        $this->post('/admin/portfolios', $this->portfolioPayload());

        $this->assertEqualsCanonicalizing(['konser-kampus', 'konser-kampus-2'], Portfolio::pluck('slug')->all());
    }

    public function test_hapus_portofolio_menghapus_file(): void
    {
        $this->post('/admin/portfolios', $this->portfolioPayload(['photos' => [UploadedFile::fake()->image('a.jpg')]]));
        $portfolio = Portfolio::first();

        $this->delete(route('admin.portfolios.destroy', $portfolio))->assertRedirect('/admin/portfolios');

        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertSame(0, Portfolio::count());
    }

    public function test_unggah_bukan_gambar_ditolak(): void
    {
        $this->post('/admin/portfolios', $this->portfolioPayload(['photos' => [UploadedFile::fake()->create('x.php', 10, 'text/x-php')]]))
            ->assertSessionHasErrors('photos.0');
    }

    public function test_unggah_svg_ditolak(): void
    {
        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        $this->post('/admin/portfolios', $this->portfolioPayload(['photos' => [$svg]]))->assertSessionHasErrors('photos.0');
    }

    public function test_crud_klien_logo(): void
    {
        $this->post('/admin/clients', ['name' => 'Mitra Baru', 'website' => 'https://mitra.test', 'logo' => UploadedFile::fake()->image('l.png', 200, 100)])
            ->assertRedirect('/admin/clients');
        $client = Client::where('name', 'Mitra Baru')->firstOrFail();
        Storage::disk('public')->assertExists($client->logo);

        $this->put(route('admin.clients.update', $client), ['name' => 'Mitra Ubah'])->assertRedirect();
        $this->assertSame('Mitra Ubah', $client->fresh()->name);
        $this->get(route('admin.clients.edit', $client))->assertOk();

        $this->post('/admin/clients', ['name' => 'X', 'website' => 'javascript:alert(1)'])->assertSessionHasErrors('website');
    }

    public function test_hapus_klien_portofolio_tetap_ada(): void
    {
        $client = Client::create(['name' => 'Mitra']);
        $portfolio = Portfolio::create(['title' => 'A', 'slug' => 'a', 'category' => 'Concert', 'folder_color' => 'red', 'client_id' => $client->id]);

        $this->delete(route('admin.clients.destroy', $client))->assertRedirect('/admin/clients');

        $this->assertNull($portfolio->fresh()->client_id);
    }
}
