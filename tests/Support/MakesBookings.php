<?php

namespace Tests\Support;

use App\Models\Booking;
use App\Models\Service;
use App\Services\BookingService;
use App\Services\CartPricingService;
use App\Services\PaymentService;
use App\Services\PricedCart;
use Database\Seeders\DatabaseSeeder;

trait MakesBookings
{
    protected function seedCatalog(): void
    {
        $this->seed(DatabaseSeeder::class);
    }

    protected function optionId(string $slug, string $name): int
    {
        return Service::where('slug', $slug)->firstOrFail()->options()->where('name', $name)->firstOrFail()->id;
    }

    /** Keranjang contoh prototipe: total Rp11.600.000, DP Rp5.800.000. */
    protected function sampleCart(): PricedCart
    {
        return app(CartPricingService::class)->price(
            ['sound' => $this->optionId('sound', 'Paket 2')],
            ['videotron-indoor' => 12, 'rigging' => 12],
        );
    }

    protected function customerData(string $date = '2030-06-01', array $extra = []): array
    {
        return $extra + [
            'customer_name' => 'CV Cahaya Abadi', 'customer_wa' => '0812-3456-7890', 'event_date' => $date,
            'event_time' => '18:00', 'event_location' => 'Gedung Serbaguna', 'note' => null,
        ];
    }

    protected function makeBooking(string $date = '2030-06-01', array $extra = []): Booking
    {
        return app(BookingService::class)->create($this->customerData($date, $extra), $this->sampleCart());
    }

    protected function pay(Booking $booking, int $amount, string $type = 'dp', string $date = '2030-01-15')
    {
        return app(PaymentService::class)->record($booking, ['amount' => $amount, 'type' => $type, 'method' => 'transfer', 'paid_at' => $date], null);
    }

    protected function confirmedBooking(string $date = '2030-06-01'): Booking
    {
        $booking = $this->makeBooking($date);
        $this->pay($booking, $booking->dp_amount);
        app(BookingService::class)->confirm($booking);

        return $booking->fresh();
    }
}
