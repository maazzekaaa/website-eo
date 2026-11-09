<?php

namespace App\Services;

/** Hasil perhitungan keranjang di server: sumber kebenaran harga. */
final readonly class PricedCart
{
    /** @param array<int, array{service_id:int, service_option_id:?int, description:string, qty:int, unit:string, unit_price:int, subtotal:int}> $lines */
    public function __construct(
        public array $lines,
        public int $subtotal,
        public int $taxRate,
        public int $tax,
        public int $total,
        public int $dp,
    ) {}
}
