<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portfolio extends Model
{
    public const CATEGORIES = ['Wedding', 'Corporate', 'Concert', 'Photoshoot'];

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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
