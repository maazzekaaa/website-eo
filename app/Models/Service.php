<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'unit_price' => 'integer'];
    }

    public function options(): HasMany
    {
        return $this->hasMany(ServiceOption::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function isTier(): bool
    {
        return $this->pricing_type === 'tier';
    }

    /** Teks harga ringkas untuk daftar layanan, contoh "Rp3 – 10 jt" atau "Rp400 rb". */
    public function priceLabel(): string
    {
        if (! $this->isTier()) {
            return 'Rp'.self::short((int) $this->unit_price);
        }
        $prices = $this->options->where('is_active', true)->pluck('price');
        if ($prices->isEmpty()) {
            return '-';
        }
        $min = (int) $prices->min();
        $max = (int) $prices->max();
        if ($min === $max) {
            return 'Rp'.self::short($min);
        }
        if ($min >= 1000000) {
            return 'Rp'.self::number($min / 1000000).' – '.self::number($max / 1000000).' jt';
        }

        return 'Rp'.self::short($min).' – '.self::short($max);
    }

    /** Keterangan harga kecil, contoh "4 paket" atau "per m²". */
    public function priceNote(): string
    {
        if (! $this->isTier()) {
            return $this->unit_label === 'm' ? 'per meter' : 'per '.$this->unit_label;
        }

        return $this->options->where('is_active', true)->count().' paket';
    }

    private static function short(int $n): string
    {
        return $n >= 1000000 ? self::number($n / 1000000).' jt' : self::number($n / 1000).' rb';
    }

    private static function number(float $n): string
    {
        return rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');
    }
}
