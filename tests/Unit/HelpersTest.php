<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_rupiah(): void
    {
        $this->assertSame('Rp5.800.000', rupiah(5800000));
        $this->assertSame('Rp0', rupiah(0));
    }

    public function test_terbilang(): void
    {
        $this->assertSame('lima juta delapan ratus ribu', terbilang(5800000));
        $this->assertSame('seribu seratus', terbilang(1100));
        $this->assertSame('seratus sebelas', terbilang(111));
        $this->assertSame('dua belas', terbilang(12));
        $this->assertSame('nol', terbilang(0));
    }
}
