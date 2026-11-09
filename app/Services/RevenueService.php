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
}
