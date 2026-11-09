<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Setting;
use App\Services\CartPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\MakesBookings;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CartPricingTest extends TestCase
{
    use MakesBookings, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    private function price(array $items = [], array $qty = [])
    {
        return app(CartPricingService::class)->price($items, $qty);
    }

    public function test_keranjang_multi_item(): void
    {
        $cart = $this->sampleCart();

        $this->assertSame(11600000, $cart->subtotal);
        $this->assertSame(11600000, $cart->total);
        $this->assertSame(0, $cart->tax);
        $this->assertSame(5800000, $cart->dp);
        $this->assertSame('Sound System – Paket 2', $cart->lines[0]['description']);
        $this->assertSame(['Paket', 'm²', 'm'], array_column($cart->lines, 'unit'));
        $this->assertSame([5000000, 4800000, 1800000], array_column($cart->lines, 'subtotal'));
    }

    public function test_dp_dibulatkan_ke_atas(): void
    {
        Service::where('slug', 'rigging')->update(['unit_price' => 1500500]);

        $this->assertSame(751000, $this->price([], ['rigging' => 1])->dp);
    }

    public function test_ppn_aktif(): void
    {
        Setting::current()->update(['tax_enabled' => true, 'tax_rate' => 11]);

        $cart = $this->price(['sound' => $this->optionId('sound', 'Paket 1')]);

        $this->assertSame(3000000, $cart->subtotal);
        $this->assertSame(11, $cart->taxRate);
        $this->assertSame(330000, $cart->tax);
        $this->assertSame(3330000, $cart->total);
        $this->assertSame(1665000, $cart->dp);
    }

    public function test_qty_nol_diabaikan(): void
    {
        $cart = $this->price([], ['rigging' => 2, 'videotron-indoor' => 0]);

        $this->assertCount(1, $cart->lines);
        $this->assertSame(300000, $cart->total);
    }

    #[DataProvider('kasusDitolak')]
    public function test_ditolak(callable $input): void
    {
        [$items, $qty] = $input($this);

        $this->expectException(ValidationException::class);
        $this->price($items, $qty);
    }

    public static function kasusDitolak(): array
    {
        return [
            'keranjang kosong' => [fn ($t) => [[], []]],
            'semua qty nol' => [fn ($t) => [[], ['rigging' => 0]]],
            'opsi milik layanan lain' => [fn ($t) => [['sound' => $t->optionId('lighting', 'Paket 1')], []]],
            'qty 501' => [fn ($t) => [[], ['rigging' => 501]]],
            'qty negatif' => [fn ($t) => [[], ['rigging' => -1]]],
            'qty bukan angka' => [fn ($t) => [[], ['rigging' => 'abc']]],
            'slug tak dikenal' => [fn ($t) => [['tidak-ada' => 1], []]],
            'qty untuk layanan tier' => [fn ($t) => [[], ['sound' => 2]]],
            'opsi untuk layanan per unit' => [fn ($t) => [['rigging' => $t->optionId('sound', 'Paket 1')], []]],
            'layanan nonaktif' => [function ($t) {
                Service::where('slug', 'rigging')->update(['is_active' => false]);

                return [[], ['rigging' => 1]];
            }],
            'opsi nonaktif' => [function ($t) {
                $id = $t->optionId('sound', 'Paket 1');
                Service::where('slug', 'sound')->first()->options()->whereKey($id)->update(['is_active' => false]);

                return [['sound' => $id], []];
            }],
        ];
    }

    public function optionId(string $slug, string $name): int
    {
        return Service::where('slug', $slug)->firstOrFail()->options()->where('name', $name)->firstOrFail()->id;
    }
}
