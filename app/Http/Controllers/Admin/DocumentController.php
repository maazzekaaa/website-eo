<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    public function invoice(Request $request, Booking $booking): mixed
    {
        $booking->load('items');
        $payments = $booking->payments()->valid()->get();

        $data = [
            'booking' => $booking,
            'payments' => $payments,
            'paid' => (int) $payments->sum('amount'),
            'statusLabel' => ['unpaid' => 'Belum Dibayar', 'dp' => 'DP Dibayar', 'paid' => 'Lunas'][$booking->payment_status],
            'stamp' => ['unpaid' => null, 'dp' => 'DP DIBAYAR', 'paid' => 'LUNAS'][$booking->payment_status],
        ];

        return $request->boolean('pdf')
            ? $this->pdf('admin.documents.pdf.invoice', $data, 'a4', 'portrait', $booking->invoice_number)
            : view('admin.documents.invoice', $data);
    }

    public function receipt(Request $request, Payment $payment): mixed
    {
        $booking = $payment->booking;

        $data = [
            'payment' => $payment,
            'booking' => $booking,
            // Kondisi saat kuitansi ini terbit, tidak berubah oleh pembayaran sesudahnya.
            'paidUntil' => (int) $booking->payments()->valid()->where('id', '<=', $payment->id)->sum('amount'),
        ];

        return $request->boolean('pdf')
            ? $this->pdf('admin.documents.pdf.receipt', $data, 'a5', 'landscape', $payment->receipt_number)
            : view('admin.documents.receipt', $data);
    }

    private function pdf(string $view, array $data, string $paper, string $orientation, string $number): Response
    {
        return Pdf::loadView($view, $data + ['signature' => $this->signature()])
            ->setPaper($paper, $orientation)
            ->download(str_replace('/', '-', $number).'.pdf');
    }

    /** dompdf membaca gambar lebih andal sebagai data URI. */
    private function signature(): ?string
    {
        $path = setting('signature_path');
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return 'data:image/webp;base64,'.base64_encode(Storage::disk('public')->get($path));
    }
}
