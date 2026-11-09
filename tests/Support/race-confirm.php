<?php

// Dijalankan sebagai proses terpisah oleh ConcurrentConfirmTest.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$booking = App\Models\Booking::findOrFail((int) $argv[1]);
time_sleep_until((float) $argv[2]);

try {
    app(App\Services\BookingService::class)->confirm($booking);
    echo 'BERHASIL';
} catch (App\Exceptions\DateUnavailable) {
    echo 'DITOLAK';
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage();
}
