<?php

namespace App\Services;

use App\Exceptions\BookingRuleException;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private DocumentNumberService $numbers) {}

    /** Catat pembayaran, terbitkan kuitansi, hitung ulang status bayar. */
    public function record(Booking $booking, array $data, ?int $adminId): Payment
    {
        return DB::transaction(function () use ($booking, $data, $adminId) {
            $locked = Booking::lockForUpdate()->findOrFail($booking->id);

            if (! $locked->isOpen()) {
                throw new BookingRuleException('Pembayaran hanya bisa dicatat pada booking yang masih aktif.');
            }
            $amount = (int) $data['amount'];
            if ($amount <= 0 || $amount > $locked->restAmount()) {
                throw new BookingRuleException('Nominal harus lebih dari 0 dan tidak melebihi sisa tagihan ('.rupiah($locked->restAmount()).').');
            }

            $payment = $locked->payments()->create([
                'receipt_number' => $this->numbers->next('KWT'),
                'amount' => $amount,
                'type' => $data['type'],
                'method' => $data['method'],
                'paid_at' => $data['paid_at'],
                'proof_path' => $data['proof_path'] ?? null,
                'note' => $data['note'] ?? null,
                'admin_id' => $adminId,
            ]);

            $this->syncStatus($locked);

            return $payment;
        });
    }

    /** Batalkan pembayaran salah input. Nomor kuitansi tidak dipakai ulang. */
    public function void(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            $locked = Payment::lockForUpdate()->findOrFail($payment->id);
            $booking = Booking::lockForUpdate()->findOrFail($locked->booking_id);

            if (trim($reason) === '') {
                throw new BookingRuleException('Alasan pembatalan wajib diisi.');
            }
            if ($locked->voided_at) {
                throw new BookingRuleException('Pembayaran ini sudah dibatalkan.');
            }
            if ($booking->status === 'completed') {
                throw new BookingRuleException('Pembayaran pada booking yang sudah selesai tidak bisa dibatalkan.');
            }

            $locked->update(['voided_at' => now(), 'void_reason' => trim($reason)]);
            $this->syncStatus($booking);
        });
    }

    private function syncStatus(Booking $booking): void
    {
        $paid = $booking->paidAmount();

        $booking->update(['payment_status' => match (true) {
            $paid >= $booking->total => 'paid',
            $paid >= $booking->dp_amount => 'dp',
            default => 'unpaid',
        }]);
    }
}
