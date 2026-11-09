<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['month' => ['required', 'date_format:Y-m']]);

        return response()->json(['locked' => self::lockedDates(Carbon::createFromFormat('!Y-m', $data['month']))]);
    }

    /** Tanggal terkunci dalam satu bulan, tanpa data pemesan. */
    public static function lockedDates(Carbon $month): array
    {
        return Booking::whereBetween('confirmed_date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->orderBy('confirmed_date')->pluck('confirmed_date')
            ->map(fn ($date) => substr((string) $date, 0, 10))->all();
    }
}
