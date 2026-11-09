<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const TYPE_LABELS = ['dp' => 'DP', 'pelunasan' => 'Pelunasan', 'lainnya' => 'Lainnya'];

    public const METHOD_LABELS = ['transfer' => 'Transfer', 'tunai' => 'Tunai'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'paid_at' => 'date', 'voided_at' => 'datetime'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopeValid(Builder $q): Builder
    {
        return $q->whereNull('voided_at');
    }
}
