<?php

namespace Tests\Feature;

use App\Services\DocumentNumberService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_nomor_berurutan_per_bulan(): void
    {
        $service = app(DocumentNumberService::class);
        $nov = Carbon::parse('2026-11-09');

        $this->assertSame('INV/2026/11/001', $service->next('INV', $nov));
        $this->assertSame('INV/2026/11/002', $service->next('INV', $nov));
        $this->assertSame('INV/2026/12/001', $service->next('INV', Carbon::parse('2026-12-01')));
    }

    public function test_tiap_prefix_punya_hitungan_sendiri(): void
    {
        $service = app(DocumentNumberService::class);
        $nov = Carbon::parse('2026-11-09');
        $service->next('INV', $nov);

        $this->assertSame('KWT/2026/11/001', $service->next('KWT', $nov));
    }
}
