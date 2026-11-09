<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['tax_enabled' => 'boolean', 'tax_rate' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.current'));
    }

    /** Satu-satunya baris pengaturan, disimpan di cache. */
    public static function current(): self
    {
        // Simpan atribut mentah, bukan objek: Laravel 13 tidak meng-unserialize objek dari cache.
        $attributes = Cache::rememberForever('settings.current', fn () => static::query()->first()?->getAttributes() ?? []);

        return $attributes === []
            ? new static(['company_name' => 'FZM Production Mediatama'])
            : (new static)->newFromBuilder($attributes);
    }
}
