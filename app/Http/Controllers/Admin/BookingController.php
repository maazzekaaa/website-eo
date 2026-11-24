<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingRuleException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $list = Booking::query()
            ->when($request->query('status'), fn ($w, $v) => $w->where('status', $v))
            ->when($request->query('payment_status'), fn ($w, $v) => $w->where('payment_status', $v))
            ->when($q !== '', function ($w) use ($q) {
                $like = '%'.addcslashes($q, '%_\\').'%';
                $w->where(fn ($s) => $s->where('customer_name', 'like', $like)->orWhere('booking_code', 'like', $like));
            })
            ->latest('id')->paginate(20)->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $list,
            'q' => $q,
            'statusCounts' => Booking::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Booking $booking): View
    {
        $booking->load(['items', 'payments']);

        $data = [
            'code' => $booking->booking_code,
            'eventDate' => $booking->event_date->translatedFormat('j F Y'),
            'status' => $booking->status,
            'total' => $booking->total,
            'dp' => $booking->dp_amount,
            'urls' => [
                'confirm' => route('admin.bookings.confirm', $booking),
                'complete' => route('admin.bookings.complete', $booking),
                'reject' => route('admin.bookings.reject', $booking),
                'cancel' => route('admin.bookings.cancel', $booking),
            ],
            'payments' => $booking->payments->map(fn (Payment $p) => [
                'no' => $p->receipt_number,
                'date' => $p->paid_at->toDateString(),
                'method' => Payment::METHOD_LABELS[$p->method],
                'type' => Payment::TYPE_LABELS[$p->type],
                'amount' => $p->amount,
                'voided' => (bool) $p->voided_at,
                'voidReason' => $p->void_reason,
                'canVoid' => ! $p->voided_at && $booking->status !== 'completed',
                'receiptUrl' => route('admin.payments.receipt', $p),
                'voidUrl' => route('admin.payments.void', $p),
                'proofUrl' => $p->proof_path ? route('admin.payments.proof', $p) : null,
            ])->all(),
        ];

        return view('admin.bookings.show', ['booking' => $booking, 'bookingData' => $data]);
    }

    public function confirm(Booking $booking): RedirectResponse
    {
        return $this->run(fn () => $this->bookings->confirm($booking),
            'Dikonfirmasi. '.$booking->event_date->translatedFormat('j F Y').' kini terkunci di kalender publik.');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        return $this->run(fn () => $this->bookings->complete($booking), 'Booking ditandai selesai.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:500']])['reason'] ?? null;

        return $this->run(fn () => $this->bookings->reject($booking, $reason), 'Booking ditolak.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:500']])['reason'] ?? null;

        return $this->run(fn () => $this->bookings->cancel($booking, $reason), 'Booking dibatalkan. Tanggal kembali terbuka.');
    }

    private function run(callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (BookingRuleException $e) {
            return back()->with('toast_error', $e->getMessage());
        }

        return back()->with('toast', $message);
    }
}
