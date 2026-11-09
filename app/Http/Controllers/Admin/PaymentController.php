<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingRuleException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\ImageService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments, private ImageService $images) {}

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $request->merge(['amount' => preg_replace('/\D/', '', (string) $request->input('amount'))]);

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'type' => ['required', Rule::in(array_keys(Payment::TYPE_LABELS))],
            'method' => ['required', Rule::in(array_keys(Payment::METHOD_LABELS))],
            'paid_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.*' => 'Nominal harus lebih dari 0.',
            'paid_at.*' => 'Tanggal bayar tidak boleh di masa depan.',
            'proof.*' => 'Bukti harus gambar JPG/PNG/WebP maks. 2 MB.',
        ]);

        $proof = $request->file('proof') ? $this->images->store($request->file('proof'), 'proofs', 'local') : null;

        try {
            $payment = $this->payments->record($booking, ['proof_path' => $proof] + $data, auth('admin')->id());
        } catch (BookingRuleException $e) {
            $this->images->delete($proof, 'local');

            return back()->with('toast_error', $e->getMessage());
        }

        return back()->with('toast', 'Pembayaran '.rupiah($payment->amount)." tercatat · kuitansi {$payment->receipt_number} terbit.");
    }

    public function void(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']], ['reason.*' => 'Alasan pembatalan wajib diisi.']);

        try {
            $this->payments->void($payment, $data['reason']);
        } catch (BookingRuleException $e) {
            return back()->with('toast_error', $e->getMessage());
        }

        return back()->with('toast', "Kuitansi {$payment->receipt_number} dibatalkan.");
    }

    public function proof(Payment $payment): StreamedResponse
    {
        abort_unless($payment->proof_path && Storage::disk('local')->exists($payment->proof_path), 404);

        return Storage::disk('local')->response($payment->proof_path);
    }
}
