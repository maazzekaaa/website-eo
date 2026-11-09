<?php

namespace App\Services;

use App\Exceptions\BookingRuleException;
use App\Exceptions\DateUnavailable;
use App\Models\Booking;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /** Transaksi diulang bila MySQL membatalkannya karena deadlock antar-admin. */
    private const ATTEMPTS = 3;

    public function __construct(private DocumentNumberService $numbers) {}

    /** Simpan booking pending beserta salinan harga. Ditolak jika tanggal sudah terkunci. */
    public function create(array $data, PricedCart $cart): Booking
    {
        return DB::transaction(function () use ($data, $cart) {
            // Pending tidak mengunci tanggal, jadi cukup baca biasa tanpa kunci baris.
            if (Booking::whereDate('confirmed_date', $data['event_date'])->exists()) {
                throw new DateUnavailable;
            }

            $booking = Booking::create([
                'booking_code' => $this->newCode(),
                'invoice_number' => $this->numbers->next('INV'),
                'customer_name' => $data['customer_name'],
                'customer_wa' => app(WhatsAppService::class)->normalize($data['customer_wa']),
                'event_date' => $data['event_date'],
                'event_time' => $data['event_time'] ?? null,
                'event_location' => $data['event_location'],
                'note' => $data['note'] ?? null,
                'subtotal' => $cart->subtotal,
                'tax_rate' => $cart->taxRate,
                'tax_amount' => $cart->tax,
                'total' => $cart->total,
                'dp_amount' => $cart->dp,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);
            $booking->items()->createMany($cart->lines);

            return $booking;
        }, self::ATTEMPTS);
    }

    /** Kunci tanggal. Syarat: masih pending, DP tercatat, tanggal belum dikunci booking lain. */
    public function confirm(Booking $booking): void
    {
        try {
            DB::transaction(function () use ($booking) {
                $locked = Booking::lockForUpdate()->findOrFail($booking->id);
                $this->requireStatus($locked, ['pending'], 'Hanya booking Menunggu yang bisa dikonfirmasi.');

                if ($locked->paidAmount() < $locked->dp_amount) {
                    throw new BookingRuleException('DP 50% ('.rupiah($locked->dp_amount).') belum tercatat.');
                }
                if ($this->dateTaken($locked->event_date->toDateString(), $locked->id)) {
                    throw new DateUnavailable('Tanggal '.$locked->event_date->translatedFormat('j F Y').' sudah dikunci booking lain.');
                }

                $locked->update(['status' => 'confirmed']);
            }, self::ATTEMPTS);
        } catch (UniqueConstraintViolationException) {
            throw new DateUnavailable('Tanggal '.$booking->event_date->translatedFormat('j F Y').' sudah dikunci booking lain.');
        }
    }

    public function reject(Booking $booking, ?string $reason): void
    {
        $this->close($booking, ['pending'], 'rejected', 'Hanya booking Menunggu yang bisa ditolak.', 'Ditolak', $reason);
    }

    public function cancel(Booking $booking, ?string $reason): void
    {
        $this->close($booking, ['pending', 'confirmed'], 'cancelled', 'Booking ini sudah tidak aktif.', 'Dibatalkan', $reason);
    }

    public function complete(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $locked = Booking::lockForUpdate()->findOrFail($booking->id);
            $this->requireStatus($locked, ['confirmed'], 'Konfirmasi booking dulu sebelum menandai selesai.');

            if ($locked->restAmount() > 0) {
                throw new BookingRuleException('Masih ada sisa tagihan '.rupiah($locked->restAmount()).'.');
            }

            $locked->update(['status' => 'completed']);
        }, self::ATTEMPTS);
    }

    protected function dateTaken(string $date, ?int $exceptId = null): bool
    {
        return Booking::whereDate('event_date', $date)
            ->whereIn('status', Booking::LOCKING_STATUSES)
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->lockForUpdate()
            ->exists();
    }

    private function close(Booking $booking, array $allowed, string $status, string $error, string $label, ?string $reason): void
    {
        DB::transaction(function () use ($booking, $allowed, $status, $error, $label, $reason) {
            $locked = Booking::lockForUpdate()->findOrFail($booking->id);
            $this->requireStatus($locked, $allowed, $error);

            $note = $locked->admin_note;
            if (filled($reason)) {
                $note = trim($note."\n{$label}: ".trim($reason));
            }

            $locked->update(['status' => $status, 'admin_note' => $note]);
        }, self::ATTEMPTS);
    }

    private function requireStatus(Booking $booking, array $allowed, string $message): void
    {
        if (! in_array($booking->status, $allowed, true)) {
            throw new BookingRuleException($message);
        }
    }

    private function newCode(): string
    {
        do {
            $code = 'FZM-'.now()->format('Ymd').'-'.random_int(1000, 9999);
        } while (Booking::where('booking_code', $code)->exists());

        return $code;
    }
}
