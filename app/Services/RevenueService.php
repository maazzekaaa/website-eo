<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\CarbonImmutable;

class RevenueService
{
    public function today(): int
    {
        return (int) Payment::valid()->whereDate('paid_at', now()->toDateString())->sum('amount');
    }

    public function month(): int
    {
        return (int) Payment::valid()
            ->whereBetween('paid_at', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('amount');
    }

    /** Data untuk `dashboard.js`: pendapatan per hari dan per bulan, serta daftar booking terbaru. */
    public function dashboardData(): array
    {
        $today = CarbonImmutable::parse(now()->toDateString());

        return [
            'today' => $today->toDateString(),
            ...$this->series($today),
            'bookings' => $this->bookings(),
        ];
    }

    private function series(CarbonImmutable $today): array
    {
        $byDay = Payment::valid()->where('paid_at', '<=', $today->toDateString())
            ->selectRaw('paid_at as day, SUM(amount) as total')->groupBy('paid_at')
            ->pluck('total', 'day')
            ->mapWithKeys(fn ($total, $day) => [substr((string) $day, 0, 10) => (int) $total]);

        $first = $byDay->isEmpty() ? $today : CarbonImmutable::parse($byDay->keys()->min());

        $daily = [];
        for ($m = $first->startOfMonth(); $m <= $today; $m = $m->addMonth()) {
            $days = $m->isSameMonth($today) ? $today->day : $m->daysInMonth;
            for ($d = 1; $d <= $days; $d++) {
                $daily[$m->format('Y-m')][] = $byDay[$m->day($d)->toDateString()] ?? 0;
            }
        }

        $monthly = [];
        for ($y = $first->year; $y <= $today->year; $y++) {
            $months = $y === $today->year ? $today->month : 12;
            for ($i = 1; $i <= $months; $i++) {
                $key = sprintf('%04d-%02d', $y, $i);
                $monthly[$y][] = array_sum($daily[$key] ?? []);
            }
        }

        return ['daily' => $daily, 'monthly' => $monthly];
    }

    private function bookings(): array
    {
        return Booking::with('items')
            ->withSum(['payments as paid' => fn ($q) => $q->whereNull('voided_at')], 'amount')
            ->latest('id')->limit(100)->get()
            ->map(fn (Booking $b) => [
                'id' => $b->id,
                'code' => $b->booking_code,
                'name' => $b->customer_name,
                'date' => $b->event_date->toDateString(),
                'total' => $b->total,
                'dp' => $b->dp_amount,
                'paid' => (int) $b->paid,
                'status' => $b->status,
                'items' => $b->items->map(fn ($i) => $i->unit === 'Paket'
                    ? $i->description
                    : "{$i->description} {$i->qty} {$i->unit}")->implode(' · '),
                'wa' => $b->customer_wa,
            ])->all();
    }

    /**
     * Data rekap pendapatan untuk PDF (Dashboard → Unduh rekap).
     * Pendapatan = pembayaran tercatat (tidak di-void) berdasarkan paid_at, sesuai aturan bisnis.
     *
     * @param  bool  $yearly  true = rekap setahun (grafik per bulan), false = sebulan (grafik per hari)
     */
    public function recap(CarbonImmutable $from, CarbonImmutable $to, bool $yearly): array
    {
        $payments = Payment::valid()->with('booking')
            ->whereDate('paid_at', '>=', $from->toDateString())
            ->whereDate('paid_at', '<=', $to->toDateString())
            ->orderBy('paid_at')->orderBy('id')
            ->get();

        // Grafik: satu batang per hari (bulanan) atau per bulan (tahunan)
        $buckets = [];
        if ($yearly) {
            for ($m = 1; $m <= 12; $m++) {
                $buckets[$m] = ['label' => $from->month($m)->translatedFormat('M'), 'total' => 0];
            }
            foreach ($payments as $p) {
                $buckets[$p->paid_at->month]['total'] += $p->amount;
            }
        } else {
            for ($d = 1; $d <= $from->daysInMonth; $d++) {
                $buckets[$d] = ['label' => (string) $d, 'total' => 0];
            }
            foreach ($payments as $p) {
                $buckets[$p->paid_at->day]['total'] += $p->amount;
            }
        }

        // Pembanding: periode sebelumnya dengan panjang yang sama
        [$prevFrom, $prevTo] = $yearly
            ? [$from->subYear(), $to->subYear()]
            : [$from->subMonthNoOverflow(), $from->subMonthNoOverflow()->day(min($to->day, $from->subMonthNoOverflow()->daysInMonth))];
        $previous = (int) Payment::valid()
            ->whereDate('paid_at', '>=', $prevFrom->toDateString())
            ->whereDate('paid_at', '<=', $prevTo->toDateString())
            ->sum('amount');

        // Acara yang berlangsung pada periode ini, beserta yang sudah dibayar
        $events = Booking::withSum(['payments as paid' => fn ($q) => $q->whereNull('voided_at')], 'amount')
            ->whereBetween('event_date', [$from->toDateString(), $to->toDateString()])
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->orderBy('event_date')
            ->get();

        $total = (int) $payments->sum('amount');

        return [
            'payments' => $payments,
            'buckets' => array_values($buckets),
            'peak' => max(array_column($buckets, 'total') ?: [0]),
            'total' => $total,
            'count' => $payments->count(),
            'byType' => [
                'dp' => (int) $payments->where('type', 'dp')->sum('amount'),
                'pelunasan' => (int) $payments->where('type', 'pelunasan')->sum('amount'),
                'lainnya' => (int) $payments->where('type', 'lainnya')->sum('amount'),
            ],
            'byMethod' => [
                'transfer' => (int) $payments->where('method', 'transfer')->sum('amount'),
                'tunai' => (int) $payments->where('method', 'tunai')->sum('amount'),
            ],
            'previous' => $previous,
            'change' => $previous > 0 ? round(($total - $previous) / $previous * 100) : null,
            'events' => $events,
            'eventsValue' => (int) $events->sum('total'),
            'outstanding' => (int) $events->sum(fn (Booking $b) => max(0, $b->total - (int) $b->paid)),
        ];
    }
}
