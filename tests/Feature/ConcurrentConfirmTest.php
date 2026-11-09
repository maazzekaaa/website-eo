<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Tests\Support\MakesBookings;
use Tests\TestCase;

/** Dua proses sungguhan berebut tanggal yang sama. Tidak memakai transaksi pembungkus test. */
class ConcurrentConfirmTest extends TestCase
{
    use MakesBookings;

    protected function tearDown(): void
    {
        RefreshDatabaseState::$migrated = false;
        parent::tearDown();
    }

    public function test_dua_konfirmasi_bersamaan_satu_menang_satu_ditolak_rapi(): void
    {
        Artisan::call('migrate:fresh');
        $this->seedCatalog();

        foreach (range(0, 4) as $round) {
            $date = now()->addDays(100 + $round)->toDateString();
            $a = $this->makeBooking($date);
            $b = $this->makeBooking($date);
            $this->pay($a, $a->dp_amount, 'dp', now()->toDateString());
            $this->pay($b, $b->dp_amount, 'dp', now()->toDateString());

            $start = microtime(true) + 1.0;
            $processes = collect([$a, $b])->map(fn ($booking) => new Process(
                [PHP_BINARY, base_path('tests/Support/race-confirm.php'), (string) $booking->id, (string) $start],
                base_path(),
                ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_PORT' => '3310', 'DB_DATABASE' => 'fzm_production_test', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'],
            ));
            $processes->each->start();
            $processes->each->wait();

            $outputs = $processes->map(fn (Process $p) => trim($p->getOutput()).trim($p->getErrorOutput()))->sort()->values()->all();
            $this->assertSame(['BERHASIL', 'DITOLAK'], $outputs, "Putaran {$round}");
            $this->assertSame(1, \App\Models\Booking::whereDate('event_date', $date)->where('status', 'confirmed')->count());
        }
    }
}
