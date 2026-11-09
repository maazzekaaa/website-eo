<?php

namespace App\Http\Controllers;

use App\Exceptions\DateUnavailable;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Service;
use App\Services\BookingService;
use App\Services\CartPricingService;
use App\Services\WhatsAppService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function create(): View
    {
        return view('site.pesan', [
            'services' => Service::active()->with(['options' => fn ($q) => $q->where('is_active', true)])->orderBy('sort_order')->get(),
            'locked' => AvailabilityController::lockedDates(now()),
        ]);
    }

    public function store(StoreBookingRequest $request, CartPricingService $pricing, BookingService $bookings): RedirectResponse
    {
        if ($request->filled('website')) {
            return redirect()->route('home');          // honeypot: bot tidak perlu tahu ditolak
        }

        $cart = $pricing->price((array) $request->input('items', []), (array) $request->input('qty', []));

        try {
            $booking = $bookings->create($request->validated(), $cart);
        } catch (DateUnavailable $e) {
            return back()->withErrors(['event_date' => $e->getMessage()])->withInput();
        }

        $request->session()->push('booking_codes', $booking->booking_code);

        return redirect()->route('pesan.success', $booking->booking_code);
    }

    /** Hanya sesi pembuatnya yang bisa membuka: kode booking mudah ditebak. */
    public function success(Request $request, string $code, WhatsAppService $whatsApp): View
    {
        abort_unless(in_array($code, $request->session()->get('booking_codes', []), true), 404);

        $booking = Booking::with('items')->where('booking_code', $code)->firstOrFail();

        return view('site.pesan-sukses', [
            'booking' => $booking,
            'message' => $whatsApp->bookingMessage($booking),
            'waUrl' => $whatsApp->bookingUrl($booking),
        ]);
    }
}
