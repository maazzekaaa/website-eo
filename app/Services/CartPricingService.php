<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Validation\ValidationException;

class CartPricingService
{
    public const MAX_QTY = 500;

    /**
     * Hitung keranjang dari harga di database.
     *
     * @param  array<string, mixed>  $items  slug layanan tier => id opsi
     * @param  array<string, mixed>  $qty  slug layanan per unit => jumlah
     */
    public function price(array $items, array $qty): PricedCart
    {
        $slugs = array_unique([...array_keys($items), ...array_keys($qty)]);
        $services = Service::active()->with(['options' => fn ($q) => $q->where('is_active', true)])
            ->whereIn('slug', $slugs)->get()->keyBy('slug');

        $lines = [];

        foreach ($items as $slug => $optionId) {
            if ($optionId === null || $optionId === '') {
                continue;
            }
            $service = $services->get($slug);
            $option = $service?->isTier() ? $service->options->firstWhere('id', (int) $optionId) : null;
            if (! $option) {
                $this->fail('Layanan atau paket yang dipilih tidak tersedia.');
            }
            $lines[] = [
                'service_id' => $service->id, 'service_option_id' => $option->id,
                'description' => "{$service->name} – {$option->name}",
                'qty' => 1, 'unit' => 'Paket', 'unit_price' => $option->price, 'subtotal' => $option->price,
            ];
        }

        foreach ($qty as $slug => $value) {
            if ($value === null || $value === '' || (string) $value === '0') {
                continue;
            }
            $service = $services->get($slug);
            if (! $service || $service->isTier()) {
                $this->fail('Layanan yang dipilih tidak tersedia.');
            }
            if (filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => self::MAX_QTY]]) === false) {
                $this->fail("Jumlah {$service->name} harus bilangan bulat 1 sampai ".self::MAX_QTY.'.');
            }
            $count = (int) $value;
            $lines[] = [
                'service_id' => $service->id, 'service_option_id' => null, 'description' => $service->name,
                'qty' => $count, 'unit' => $service->unit_label, 'unit_price' => $service->unit_price,
                'subtotal' => $service->unit_price * $count,
            ];
        }

        if ($lines === []) {
            $this->fail('Pilih minimal satu layanan.');
        }

        $subtotal = array_sum(array_column($lines, 'subtotal'));
        $taxRate = setting('tax_enabled') ? (int) setting('tax_rate', 0) : 0;
        $tax = intdiv($subtotal * $taxRate + 50, 100);
        $total = $subtotal + $tax;
        $dp = min($total, intdiv($total + 1999, 2000) * 1000);

        return new PricedCart($lines, $subtotal, $taxRate, $tax, $total, $dp);
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['items' => $message]);
    }
}
