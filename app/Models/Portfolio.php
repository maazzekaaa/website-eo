<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portfolio extends Model
{
    /** Nilai tersimpan => label tampilan. Hanya dua kategori yang dipakai. */
    public const CATEGORY_LABELS = ['Wedding' => 'Pernikahan', 'Corporate' => 'Korporat'];

    public const CATEGORIES = ['Wedding', 'Corporate'];

    public const COLORS = ['red', 'black', 'maroon', 'silver'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'is_featured' => 'boolean'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('sort_order');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? (string) $this->category;
    }

    /** Data satu proyek untuk galeri foto di beranda (dibaca portfolio.js). Muat relasi client & images dulu. */
    public function galleryItem(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->categoryLabel(),
            'client' => $this->client?->name ?? 'Klien pribadi',
            'date' => $this->event_date?->translatedFormat('j F Y'),
            'location' => $this->location,
            'services' => $this->services,
            'caption' => $this->caption,
            'link' => route('portofolio.show', $this),
            'photos' => $this->images->map(fn (PortfolioImage $image) => $image->url())->values()->all(),
            'cover' => $this->images->first()?->url(),
            'index' => 0,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
