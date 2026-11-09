<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    /** Status yang mengunci tanggal acara. */
    public const LOCKING_STATUSES = ['confirmed', 'completed'];

    public const STATUS_LABELS = ['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'completed' => 'Selesai', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'];

    public const PAYMENT_LABELS = ['unpaid' => 'Belum bayar', 'dp' => 'DP', 'paid' => 'Lunas'];

    protected $guarded = ['id', 'confirmed_date'];

    protected function casts(): array
    {
        return [
            'event_date' => 'date', 'contacted_at' => 'datetime',
            'subtotal' => 'integer', 'tax_rate' => 'integer', 'tax_amount' => 'integer',
            'total' => 'integer', 'dp_amount' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_at')->orderBy('id');
    }

    /** Jumlah pembayaran yang sah (tidak di-void). */
    public function paidAmount(): int
    {
        return (int) $this->hasMany(Payment::class)->whereNull('voided_at')->sum('amount');
    }

    public function restAmount(): int
    {
        return $this->total - $this->paidAmount();
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true);
    }
}
