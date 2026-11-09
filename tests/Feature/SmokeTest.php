<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_koneksi_memakai_mysql(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('fzm_production_test', DB::connection()->getDatabaseName());
    }

    public function test_aset_prototipe_tersedia(): void
    {
        $this->assertFileExists(public_path('assets/css/base.css'));
    }
}
